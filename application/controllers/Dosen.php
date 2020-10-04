<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dosen extends CI_Controller {
    
    public function __construct()
	{
		parent::__construct();
		$this->load->model('wilayah_model');
		$this->load->model('parameter_model');
		$this->load->model('jurusan_model');
		$this->load->model('user_model');
		$this->load->model('ta_model');
		$this->load->model('pkl_model');
		$this->load->library('pdf');
		$this->load->library('encrypt');
		 // Load PHPMailer library
        // $this->load->library('phpmailer_lib');

		
		if($this->session->has_userdata('username')) {
		    if($this->session->userdata('state') <> 2) {
		        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
		    }
		} else {
		    redirect(site_url('?access=ditolak'));
		}
	}

	public function index()
	{
		redirect(site_url("dosen/kelola-akun"));
	}

	public function akun()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('dosen/header');
		
		$this->load->view('dosen/akun', $data);

        $this->load->view('footer_global');
	}
	
	public function ubah_akun()
	{
// 		echo "<pre>";
		//print_r($_POST);
		//print_r($_FILES);

		$data = array(
			'name' => $this->input->post('nama'),
			'mobile' => $this->input->post('hp'),
			'email' => $this->input->post('email')
		);

		$this->session->set_userdata(array('name' => $this->input->post('nama')));

		if($this->input->post('output_ttd') != "")
			$data['ttd'] = $this->input->post('output_ttd');
		if(!empty($this->input->post('password')))
			$data['password'] = $this->input->post('password');

		if(!empty($_FILES)) {
			$file = $_FILES['file']['tmp_name']; 
			$sourceProperties = getimagesize($file);
			$fileNewName = $this->input->post('username');
			$folderPath = "assets/uploads/pas-foto/";
			$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

			$this->resize_crop_image(200, 300, $file, $folderPath. $fileNewName. ".". $ext);

			$data['foto'] = $folderPath. $fileNewName. ".". $ext;
		}
		//print_r($data);
		//echo $this->session->userdata('userId');
		$this->user_model->update($data, $this->session->userdata('userId'));
		redirect(site_url("dosen/kelola-akun?status=sukses"));
	}

	public function biodata()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['biodata'] = $this->user_model->select_biodata_by_ID($this->session->userdata('userId'), 2)->row();

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');
		
		$this->load->view('dosen/biodata_dosen', $data);

        $this->load->view('footer_global');
	}
	//edit raihan
	public function ubah_biodata()
	{
		//echo "<pre>";
		//print_r($_POST);
		//echo $this->session->userdata('userId');
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$data_dosen = array(
			'nidn' => $this->input->post('nidn'),
			'gelar_depan' => $this->input->post('gelar_depan'),
			'gelar_belakang' => $this->input->post('gelar_belakang'),
			'id_sinta' => $this->input->post('id_sinta'),
			'jurusan' => $this->input->post('jurusan'),
			'pangkat_gol' => $this->input->post('pangkat'),
			'fungsional' => $this->input->post('jabfung')
		);

		$this->user_model->update_dosen($data_dosen, $this->session->userdata('userId'));

		$tgl_lahir = new DateTime($this->input->post('tanggal_lahir'));

		$data_akun = array(
			'jenis_kelamin' => $this->input->post('jenkel'),
			'agama' => $this->input->post('agama'),
			'tempat_lahir' => $this->input->post('tempat_lahir'),
			'tanggal_lahir' => $tgl_lahir->format('Y-m-d'),
			'jalan' => $this->input->post('jalan'),
			'provinsi' => $this->input->post('provinsi'),
			'kota_kabupaten' => $this->input->post('kota_kabupaten'),
			'kecamatan' => $this->input->post('kecamatan'),
			'kelurahan_desa' => $this->input->post('kelurahan_desa'),
			'kode_pos' => $this->input->post('kode_pos')
		);
		$this->user_model->update($data_akun, $this->session->userdata('userId'));
		redirect(site_url("dosen/kelola-biodata?status=sukses"));
	}

	function tugas_tambahan()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$iduser = $data['iduser'];
		$tugas = $data['tugas_tambahan'];
		$prodi = $data['prodi'];
		$jurusan = $data['jurusan'];
		$periode = $data['periode'];
		$status = $data['status_tgs'];

		if($jurusan == ""){
			$jurusan = 0;
		}
		if($prodi == ""){
			$prodi = 0;
		}

		$check = $this->user_model->check_tugas_tambahan($iduser,$tugas,$jurusan,$prodi,$status);
		
		if(!empty($check)){
			redirect(site_url("dosen/kelola-biodata?status=duplikat"));
		}
		else{
			if($tugas != 16 || $tugas != 18){
				$check_double =  $this->user_model->check_tugas_tambahan_duplikat($tugas,$jurusan,$prodi,$status,$periode);
				if(!empty($check_double)){
					$id_user_double = $check_double->id_user;
					redirect(site_url("dosen/kelola-biodata?status=duplikat_user&id=".$this->encrypt->encode($id_user_double)));
				}
				else{
					$data_tugas = array(
						'id_user' => $iduser,
						'tugas' => $tugas,
						'jurusan_unit' => $jurusan,
						'prodi' => $prodi,
						'periode' => $periode,
						'aktif' => $status,
					);
			
					$this->user_model->insert_tugas_tambah($data_tugas);
				}
			}
			else{
			$data_tugas = array(
				'id_user' => $iduser,
				'tugas' => $tugas,
				'jurusan_unit' => $jurusan,
				'prodi' => $prodi,
				'periode' => $periode,
				'aktif' => $status,
			);
	
			$this->user_model->insert_tugas_tambah($data_tugas);		
			}
			redirect(site_url("dosen/kelola-biodata?status=sukses"));
		}
	}

	function tugas_tambahan_nonaktif()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id_tugas'];
		$ket = $data['ket'];

		$this->user_model->update_tugas_tambahan($id,$ket);	
		redirect(site_url("dosen/kelola-biodata?status=sukses"));

	}

	function ambil_data(){

		$modul=$this->input->post('modul');
		$id=$this->input->post('id');
		
		if($modul=="kabupaten"){
			echo $this->wilayah_model->kabupaten($id);
		}
		else if($modul=="kecamatan"){
			echo $this->wilayah_model->kecamatan($id);
		}
		else if($modul=="kelurahan"){	
			echo $this->wilayah_model->desa($id);
		}
	}

	function resize_crop_image($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
		$imgsize = getimagesize($source_file);
		$width = $imgsize[0];
		$height = $imgsize[1];
		$mime = $imgsize['mime'];
	 
		switch($mime){
			case 'image/gif':
				$image_create = "imagecreatefromgif";
				$image = "imagegif";
				break;
	 
			case 'image/png':
				$image_create = "imagecreatefrompng";
				$image = "imagepng";
				$quality = 7;
				break;
	 
			case 'image/jpeg':
				$image_create = "imagecreatefromjpeg";
				$image = "imagejpeg";
				$quality = 80;
				break;
	 
			default:
				return false;
				break;
		}
		 
		$dst_img = imagecreatetruecolor($max_width, $max_height);
		$src_img = $image_create($source_file);
		 
		$width_new = $height * $max_width / $max_height;
		$height_new = $width * $max_height / $max_width;
		//if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
		if($width_new > $width){
			//cut point by height
			$h_point = (($height - $height_new) / 2);
			//copy image
			imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
		}else{
			//cut point by width
			$w_point = (($width - $width_new) / 2);
			imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
		}
		 
		$image($dst_img, $dst_dir, $quality);
	 
		if($dst_img)imagedestroy($dst_img);
		if($src_img)imagedestroy($src_img);
	}


	// Manajemen Tugas Akhir
	//raihan
	function tugas_akhir()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('dosen/header');

		$data['ta'] = $this->ta_model->get_approval_ta($this->session->userdata('userId'));
		$data['pa'] = $this->ta_model->get_approval_ta_by_pa($this->session->userdata('userId'));
		$data['approve'] = $this->ta_model->get_approval_ta_list($this->session->userdata('userId'));
		
		$this->load->view('dosen/tema_ta', $data);
		
		//$this->load->view('dosen/tugas_akhir', $data);

        $this->load->view('footer_global');
	}

	function tugas_akhir_struktural()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('dosen/header');

		$data['ta'] = $this->ta_model->get_approval_ta_kajur($this->session->userdata('userId'));
		
		$this->load->view('dosen/kajur/tema_ta', $data);
		
		//$this->load->view('dosen/tugas_akhir', $data);

        $this->load->view('footer_global');
	}

	function seminar_struktural()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('dosen/header');

		$data['seminar'] = $this->ta_model->get_approval_seminar_kajur($this->session->userdata('userId'));
		
		$this->load->view('dosen/kajur/seminar_ta',$data);
		
		//$this->load->view('dosen/tugas_akhir', $data);

        $this->load->view('footer_global');
	}

	function form_tugas_akhir()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['biodata'] = $this->user_model->select_biodata_by_ID($this->session->userdata('userId'), 3)->row();
		
		
		if($this->input->get('aksi') == "edit")
		{
			
			
		}
		else
		{
			$data_ta = array(
				'judul1' => null,
				'judul2' => null,
				'ipk' => null,
				'sks' => null,
				'toefl' => null,
				'pembimbing1' => null,
				'bidang_ilmu' => null
			);
		}

		$data['data_ta'] = $data_ta;

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/form_tema_ta', $data);
		
		//$this->load->view('dosen/tugas_akhir', $data);

        $this->load->view('footer_global');
	}
	

	
	public function print() {
	    //echo "<pre>";
	    //print_r($_POST);
	    //echo sizeof($_POST['cekNrp']);
	    
	    
	    
	    $pdf = new PDF('L', 'mm', array(210, 317));
	    $pdf->AddPage();
	    $pdf->SetFillColor(255,255,255);
	    $pdf->SetLeftMargin(15);
	    $pdf->SetFont('Arial', '', 11);

        $wt = array(12, 50, 30, 60, 40, 30, 35, 30);
        
        $pdf->SetWidths($wt);
        $pdf->SetSpacing(5);
        $pdf->SetAligns(array('C'));
        //$pdf->SetY(46);
        $no = 0;
        foreach($_POST['cekNrp'] as $row) {
            $no++;
	        $personil = $this->personil_model->select_by_nrp($row)->row();
	        $pelanggaran = $this->nilai_model->select_by_nrp($row)->result();
	        
	        //print_r($personil);
	        //print_r($pelanggaran);
	        //echo "<br>";
	        if(!empty($pelanggaran)) {
	            $idx = 1;
	            foreach($pelanggaran as $res) {
	                if($idx == 1) {
	                    $number = $no.".";
	                    $bio = $personil->nama."\n".$personil->pangkat." / ".$personil->nrp."\n\n".$personil->jabatan;
	                } else {
	                    $number = "";
	                    $bio = "";
	                }
	                
	                $pdf->Row(array(
    	                $number, 
    	                $bio,
    	                $res->tempat.", tanggal ".$res->waktu,
                        $res->jenis_pelanggaran,
                        $res->jenis_hukuman,
                        "Putusan sidang KKEP nomor:\n(".$res->no_putusan.")",
                        $res->batas_waktu,
                        $res->keterangan
    	           ));
    	           $idx++;
	            }
	            
	        } else {
	            $pdf->Row(array(
	                $no.".", 
	                $personil->nama."\n".$personil->pangkat." / ".$personil->nrp."\n\n".$personil->jabatan,
	                'NIHIL',
                    'NIHIL',
                    'NIHIL',
                    'NIHIL',
                    'NIHIL',
                    'NIHIL'
	           ));
	        }
	    }
        
        
	    $pdf->Output();
	    
	}
	//raihan
	function tugas_akhir_approve()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id_pengajuan'];
		// $id = $this->encrypt->decode($id);
		$ttd = $data['ttd'];
		$aksi = $data['aksi'];
		$status = $data['jenis'];
		$dosenid = $this->session->userdata('userId');

		$this->ta_model->approve_ta($id,$ttd,$status,$dosenid);

		redirect(site_url("dosen/tugas-akhir/tema"));
		
	}

	function tugas_akhir_approve_struktural()
	{
		$data = $this->input->post();
		
		// echo "<pre>";
		// print_r($data);

		$id = $data['id_pengajuan'];
		$ttd = $data['ttd'];
		$alter = $this->ta_model->get_komisi_alter_id($id);		

		$pb1 = $data['Pembimbing_1'];
		$pb2 = $data['Pembimbing_2'];
		$pb3 = $data['Pembimbing_3'];
		$ps1 = $data['Penguji_1'];
		$ps2 = $data['Penguji_2'];
		$ps3 = $data['Penguji_3'];
	

		$dosenid = $this->session->userdata('userId');

		$status = "kajur";
		
		//send email
		if(!empty($alter)){
			$config = Array(  
				'protocol' => 'smtp',  
				'smtp_host' => 'ssl://smtp.googlemail.com',  
				'smtp_port' => 465,  
				'smtp_user' => 'apps.fmipa.unila@gmail.com',   
				'smtp_pass' => 'apps_fmipa 2020',   
				'mailtype' => 'html',   
				'charset' => 'iso-8859-1'  
			);  
			$jml_email = count($alter);
			$n = 0;
			foreach($alter as $row){
				$this->load->library('email', $config);
				$this->email->set_newline("\r\n");  
				$this->email->from('apps.fmipa.unila@gmail.com', 'SIMIPA');   
				$this->email->to($row->email);//$row->email   
				$this->email->subject('Approve Tema Penelitian Fakultas Matematika dan Ilmu Pengetahuan Alam');   
				$this->email->message("
				Kepada Yth. $row->nama
				<br>
				Untuk Melakukan Approval Tema Penelitian Mahasiswa Fakultas Matematika Dan Ilmu Pengetahuan Alam Sebagai $row->status Silahkan Klik Link Berikut :<br>
				http://apps.fmipa.unila.ac.id/simipa/approval/ta?token=$row->token
				<br><br>
				Terimakasih.
				
				");
				if (!$this->email->send()) {  
					    $n = 0;
				   }else{  
					  $n++;
				}   
			}
            
		}
		else{
		    $jml_email = 0;
			$n = 0;
		}
		// check apakah email terkirim semua
		if($jml_email == $n)
		{
		    $this->ta_model->approve_ta($id,$ttd,$status,$dosenid);
		    redirect(site_url("dosen/struktural/tema"));
		}
		else{
		    redirect(site_url("dosen/struktural/tema?status=error"));
		}
		
	}

	function tugas_akhir_decline()
	{
		$id = $this->input->post('id_ta');
		$status = $this->input->post('status');
		$keterangan = $this->input->post('keterangan');
		$dosenid = $this->session->userdata('userId');
		$ket = $status."###".$keterangan;
		// echo "<pre>";
		// print_r($id);
		
		$data = array("id_pengajuan" => $id);
		$where = $data['id_pengajuan'];

		$this->ta_model->decline_ta($id,$dosenid,$status,$ket);
		redirect(site_url("dosen/tugas-akhir/tema"));
	}

	function tugas_akhir_koordinator_decline()
	{
		$id = $this->input->post('id_ta');
		$keterangan = $this->input->post('keterangan');
		$dosenid = $this->session->userdata('userId');
		$status = "koor";

        $jenis = $this->ta_model->get_ta_by_id($id)->jenis;
		// echo "<pre>";
		// print_r($id);
		
		// $data = array("id_pengajuan" => $id);
		// $where = $data['id_pengajuan'];

		$this->ta_model->decline_ta($id,$dosenid,$status,$keterangan);
		if($jenis != 'Skripsi'){
		    redirect(site_url("dosen/struktural/kaprodi/tugas-akhir"));
		}
		else{
		    redirect(site_url("dosen/tugas-akhir/tema/koordinator"));    
		}
		
	}

	function tugas_akhir_aksi()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$aksi = $this->input->get('aksi');
		$jenis = $this->input->get('jenis');
		// echo "<pre>";
		// print_r($id);
		$data['ta'] = $this->ta_model->get_ta_by_id($id);
		$data['aksi'] = $aksi;
		$data['jenis'] = $jenis;

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/approve_tema_ta',$data);
		
		$this->load->view('footer_global');
		
	}

	function tugas_akhir_koordinator()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_approval_ta_koordinator($this->session->userdata('userId'));

		// print_r($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/tema_ta_koordinator',$data);
		
		$this->load->view('footer_global');
	}

	function form_tugas_akhir_koordinator()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_approval_ta_koordinator($this->session->userdata('userId'));

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$aksi = $this->input->get('aksi');

		$data['ta'] = $this->ta_model->get_ta_by_id($id);
		$data['aksi'] = $aksi;

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/approve_tema_ta',$data);
		
		$this->load->view('footer_global');
	}

	function form_tugas_akhir_struktural()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$aksi = $this->input->get('aksi');

		$data['ta'] = $this->ta_model->get_ta_by_id($id);

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/approve_tema_ta',$data);
		
		$this->load->view('footer_global');
	}

	function form_seminar_struktural()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$seminar = $this->ta_model->get_seminar_by_id($id);
		$data['status'] = "kajur";
		$data['seminar'] = $seminar[0];

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/approve_seminar',$data);
		
		$this->load->view('footer_global');
	}

	function add_tugas_akhir()
	{
		//echo "<pre>";
		//print_r($this->input->post());
		//echo $this->session->userdata('username');

		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$no = $data['no_penetapan'];
		$nomor = $data['nomor'];
		$jenis = $data['jenis'];

		$dosenid = $this->session->userdata('userId');
		$no_penetapan = $no.$nomor;

		$id = $data['id_pengajuan'];
		$ttd = $data['ttd'];
		$judul_approve = $data['judul'];
		$judul1 = $data['judul1'];
		$judul2 = $data['judul2'];

		$pb1 = $data['pembimbing1'];
		$pb2 = $data['pembimbing2'];
		$pb3 = $data['pembimbing3'];
		$ps1 = $data['pembahas1'];
		$ps2 = $data['pembahas2'];
		$ps3 = $data['pembahas3'];

		// pb ps alt
		$pb2_nip = $data['pb2_alter_nip'];
		$pb2_nama = $data['pb2_alter_nama'];
		$pb2_email = $data['pb2_alter_email'];
		$pb3_nip = $data['pb3_alter_nip'];
		$pb3_nama = $data['pb3_alter_nama'];
		$pb3_email = $data['pb3_alter_email'];
		$ps1_nip = $data['ps1_alter_nip'];
		$ps1_nama = $data['ps1_alter_nama'];
		$ps1_email = $data['ps1_alter_email'];
		$ps2_nip = $data['ps2_alter_nip'];
		$ps2_nama = $data['ps2_alter_nama'];
		$ps2_email = $data['ps2_alter_email'];
		$ps3_nip = $data['ps3_alter_nip'];
		$ps3_nama = $data['ps3_alter_nama'];
		$ps3_email = $data['ps3_alter_email'];
		

		if($pb2 == NULL && ($pb2_nip != NULL && $pb2_nama != NULL)){
			$status = "Pembimbing 2";
			$this->ta_model->set_komisi_alter($id,$pb2_nip,$pb2_nama,$status);
			$this->ta_model->set_komisi_alter_access($id,$pb2_nip,$pb2_nama,$status,$pb2_email);

			$data_approval = array(
				'id_pengajuan' => $id,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}
		if($pb3 == NULL && ($pb3_nip != NULL && $pb3_nama != NULL)){
			$status = "Pembimbing 3";
			$this->ta_model->set_komisi_alter($id,$pb3_nip,$pb3_nama,$status);
			$this->ta_model->set_komisi_alter_access($id,$pb3_nip,$pb3_nama,$status,$pb3_email);

			$data_approval = array(
				'id_pengajuan' => $id,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}
		if($ps1 == NULL && ($ps1_nip != NULL && $ps1_nama != NULL)){
			$status = "Penguji 1";
			$this->ta_model->set_komisi_alter($id,$ps1_nip,$ps1_nama,$status);
			$this->ta_model->set_komisi_alter_access($id,$ps1_nip,$ps1_nama,$status,$ps1_email);

			$data_approval = array(
				'id_pengajuan' => $id,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);

		}
		if($ps2 == NULL && ($ps2_nip != NULL && $ps2_nama != NULL)){
			$status = "Penguji 2";
			$this->ta_model->set_komisi_alter($id,$ps2_nip,$ps2_nama,$status);
			$this->ta_model->set_komisi_alter_access($id,$ps2_nip,$ps2_nama,$status,$ps2_email);

			$data_approval = array(
				'id_pengajuan' => $id,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}
		if($ps3 == NULL && ($ps3_nip != NULL && $ps3_nama != NULL)){
			$status = "Penguji 3";
			$this->ta_model->set_komisi_alter($id,$ps3_nip,$ps3_nama,$status);
			$this->ta_model->set_komisi_alter_access($id,$ps3_nip,$ps3_nama,$status,$ps3_email);

			$data_approval = array(
				'id_pengajuan' => $id,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}

		if($jenis == "Tugas Akhir"){
			$this->ta_model->approval_koordinator_ta($id,$ttd,$dosenid,$no_penetapan,$judul_approve,$judul1,$judul2);
			$this->ta_model->set_komisi_ta($id,$pb1,$ps1,$ps2);

			if($ps1 != NULL && $ps1 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => 'Penguji 1',
					'id_user' => $ps1,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}

			// $this->ta_model->approve_ta_kaprodi($id);
			$data_approval = array(
				'id_pengajuan' => $id,
				'status_slug' => "Ketua Program Studi",
				'id_user' => $dosenid,
				'ttd' => $ttd,
			);
			$this->ta_model->insert_approve_ta_kaprodi($data_approval);
		}

		else{
			$this->ta_model->approval_koordinator($id,$ttd,$dosenid,$no_penetapan,$judul_approve,$judul1,$judul2);
			$this->ta_model->set_komisi($id,$pb1,$pb2,$pb3,$ps1,$ps2,$ps3);

			if($jenis != 'Skripsi'){
				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => "Ketua Program Studi",
					'id_user' => $dosenid,
					'ttd' => $ttd,
				);
				$this->ta_model->insert_approve_ta_kaprodi($data_approval);
			}

			if($pb2 != NULL && $pb2 != '0'){

				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => 'Pembimbing 2',
					'id_user' => $pb2,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($pb3 != NULL && $pb3 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => 'Pembimbing 3',
					'id_user' => $pb3,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($ps1 != NULL && $ps1 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => 'Penguji 1',
					'id_user' => $ps1,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($ps2 != NULL && $ps2 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => 'Penguji 2',
					'id_user' => $ps2,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($ps3 != NULL && $ps3 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id,
					'status_slug' => 'Penguji 3',
					'id_user' => $ps3,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
		}
		if($jenis != 'Skripsi'){
		     redirect(site_url("dosen/struktural/kaprodi/tugas-akhir"));
		}
		else{
		    redirect(site_url("dosen/tugas-akhir/tema/koordinator"));
		}
		
		
	}

	//Manajemen Seminar
	function seminar()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('dosen/header');

		// $data['ta'] = $this->ta_model->get_approval_ta($this->session->userdata('userId'));
		$data['pa'] = $this->ta_model->get_approval_seminar_by_pa($this->session->userdata('userId'));
		$data['approve'] = $this->ta_model->get_approval_seminar_list($this->session->userdata('userId'));
		
		$this->load->view('dosen/seminar/seminar_ta',$data);
		
		//$this->load->view('dosen/tugas_akhir', $data);

        $this->load->view('footer_global');
	}

	function seminar_approve()
	{
		$data = $this->input->post();

		$id = $data['id'];
		$ttd = $data['ttd'];
		$status = $data['status'];
		$dosenid = $this->session->userdata('userId');
		// echo "<pre>";
		// print_r($data);

		$this->ta_model->approve_seminar($id,$ttd,$status,$dosenid);
		if($status == 'Koordinator'){
			$id_ta = $data['id_ta'];

			$komisi = $this->ta_model->get_komisi_alter_seminar_id($id_ta);
			if(!empty($komisi)){
				foreach($komisi as $kom)
				{
					$keys = "raihanbagasnugraha";
					$date = date("Y-m-d H:i:s");
					$token = md5($keys.$kom->nip_nik.$kom->status.$kom->nama.$date);

					$data_approval = array(
						'id_seminar' => $id,
						'status' => $kom->status,
						'nip_nik' => $kom->nip_nik,
						'nama' => $kom->nama,
						'email' => $kom->email,	
						'token' => $token	
					);
					$this->ta_model->insert_seminar_approval_alter($data_approval);
				}
			}

			redirect(site_url("dosen/tugas-akhir/seminar/koordinator"));
		}

		elseif($status == 'Kaprodi'){
			$id_ta = $data['id_ta'];

			$komisi = $this->ta_model->get_komisi_alter_seminar_id($id_ta);

			if(!empty($komisi)){
				foreach($komisi as $kom)
				{
					$keys = "raihanbagasnugraha";
					$date = date("Y-m-d H:i:s");
					$token = md5($keys.$kom->nip_nik.$kom->status.$kom->nama.$date);

					$data_approval = array(
						'id_seminar' => $id,
						'status' => $kom->status,
						'nip_nik' => $kom->nip_nik,
						'nama' => $kom->nama,
						'email' => $kom->email,	
						'token' => $token	
					);
					$this->ta_model->insert_seminar_approval_alter($data_approval);
				}
			}

			redirect(site_url("dosen/struktural/kaprodi/seminar-sidang"));
		}

		elseif($status == 'kajur'){

		$alter = $this->ta_model->get_komisi_seminar_alter_id($id);

		//send email
		if(!empty($alter)){
			$config = Array(  
				'protocol' => 'smtp',  
				'smtp_host' => 'ssl://smtp.googlemail.com',  
				'smtp_port' => 465,  
				'smtp_user' => 'apps.fmipa.unila@gmail.com',   
				'smtp_pass' => 'apps_fmipa 2020',   
				'mailtype' => 'html',   
				'charset' => 'iso-8859-1'  
			);  
			$jml_email = count($alter);
			$n = 0;
			foreach($alter as $row){
					$this->load->library('email', $config);
					$this->email->set_newline("\r\n");  
					$this->email->from('apps.fmipa.unila@gmail.com', 'SIMIPA');   
					$this->email->to($row->email);   
					$this->email->subject('Penilaian Seminar/Sidang Fakultas Matematika dan Ilmu Pengetahuan Alam');   
					$this->email->message("
					Kepada Yth. $row->nama
					<br>
					Untuk Melakukan Penilaian Seminar/Sidang Mahasiswa Fakultas Matematika Dan Ilmu Pengetahuan Alam Sebagai $row->status Silahkan Klik Link Berikut :<br>
					http://apps.fmipa.unila.ac.id/simipa/approval/seminar?token=$row->token
					<br><br>
					Terimakasih.
					
					");
					if (!$this->email->send()) {  
						$n = 0;   
					}else{  
						$n++;
					}   
				}
			}
			else{
    		    $jml_email = 0;
    			$n = 0;
		    }
		    // check apakah email terkirim semua
    		if($jml_email == $n)
    		{
    		    $data = $this->ta_model->get_komisi_seminar_id($id);

    			foreach($data as $row){
    				$data_cek = array(
    					'status' => $row->status,
    					'saran' => '',
    					'ket' => '0',
    					'id_seminar' => $id,	
    				);
    				$this->ta_model->insert_seminar_nilai_check($data_cek);
    			}
    		    redirect(site_url("dosen/struktural/seminar"));
    		}
    		else{
    		    redirect(site_url("dosen/struktural/seminar?status=gagal"));
    		}
		

			
		}
	
	    else{
	        redirect(site_url("dosen/tugas-akhir/seminar"));
	    }
	}

	function seminar_decline()
	{
		$id = $this->input->post('id_seminar');
		$status = $this->input->post('status');
		$keterangan = $this->input->post('keterangan');
		$dosenid = $this->session->userdata('userId');
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		
		// $data = array("id" => $id);
		// $where = $data['id'];

		$this->ta_model->decline_seminar($id,$dosenid,$status,$keterangan);
		$id_ta = $this->ta_model->get_seminar_id($id)->id_tugas_akhir; 
        $jenis = $this->ta_model->get_ta_by_id($id_ta)->jenis; 
		if($status == 'koor'){
		    if($jenis != 'Skripsi'){
		        redirect(site_url("dosen/struktural/kaprodi/seminar-sidang"));
		    }
		    else{
		        redirect(site_url("dosen/tugas-akhir/seminar/koordinator"));
		    }
		    
		
		}
		elseif($status == 'admin'){
			redirect(site_url("tendik/verifikasi-berkas/seminar"));
		}
		else{
			redirect(site_url("dosen/tugas-akhir/seminar"));
		}

	}

	function seminar_aksi()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$status = $this->input->get('status');
		// echo "<pre>";
		// print_r($id);
		$seminar = $this->ta_model->get_seminar_by_id($id);
		
		$data['status'] = $status;
		$data['seminar'] = $seminar[0];

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/seminar/approve_seminar',$data);
		
		$this->load->view('footer_global');
		
	}

	function seminar_aksi_koor()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$status = $this->input->get('status');
		// echo "<pre>";
		// print_r($id);
		$seminar = $this->ta_model->get_seminar_by_id($id);
		
		$data['status'] = $status;
		$data['seminar'] = $seminar[0];

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/approve_seminar',$data);
		
		$this->load->view('footer_global');
		
	}

	function seminar_koordinator()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_approval_seminar_koordinator($this->session->userdata('userId'));

		// print_r($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/seminar_koordinator',$data);
		
		$this->load->view('footer_global');
	}

	//nilai seminar dosen
	function nilai_seminar()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_nilai_seminar($this->session->userdata('userId'));
		// print_r($data);
		// $jml = count($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/seminar/nilai/nilai_seminar',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_seminar_add()
	{
		$id = $this->input->get('id');
		$status = $this->input->get('status');

		switch($status){
			case "pb1":
			$status = "Pembimbing Utama";
			break;
			case "pb2":
			$status = "Pembimbing 2";
			break;
			case "pb3":
			$status = "Pembimbing 3";
			break;
			case "ps1":
			$status = "Penguji 1";
			break;
			case "ps2":
			$status = "Penguji 2";
			break;
			case "ps3":
			$status = "Penguji 3";
			break;
		}

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_seminar_id($id);
		$data['ta'] = $this->ta_model->get_tugas_akhir_seminar_id($id);
		$data['status'] = $status;

		$meta = $this->ta_model->get_komponen_nilai_meta($data['ta']->npm,$data['ta']->jenis,$data['seminar']->jenis);

		if(!empty($meta)){
			$this->load->view('header_global', $header);
			$this->load->view('dosen/header');

			$this->load->view('dosen/seminar/nilai/add_nilai_seminar',$data);
			
			$this->load->view('footer_global');
		}
		else{
			redirect(site_url("dosen/tugas-akhir/nilai-seminar?status=null"));	
		}

		// print_r($data);
		// $jml = count($data);

	}


	// rekap koordinator
	function rekap_ta()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		// $data['ta'] = $this->ta_model->get_ta_rekap($this->session->userdata('userId'));
		// print_r($data);
		// $jml = count($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/rekap/rekap_ta_koor');
		
		$this->load->view('footer_global');
	}

	function rekap_ta_detail()
	{
		$detail = $this->input->get('detail');
		$jenis = $this->input->get('jenis');
		$angkatan = $this->input->get('angkatan');

		switch($jenis)
		{
			case "ta":
			$jenis = 'Tugas Akhir';
			$npm1 = $npm2 = "0";
			break;
			case "skripsi":
			$jenis = 'Skripsi';
			$npm1 = "1";
			$npm2 = "5";
			break;
			case "tesis":
			$jenis = 'Tesis';
			$npm1 = $npm2 = "2";
			break;
			case "disertasi":
			$jenis = 'Disertasi';
			$npm1 = $npm2 = "3";
			break;
		}

		if($detail == "diterima"){
			$data['ta'] = $this->ta_model->get_ta_rekap_detail_terima($this->session->userdata('userId'),$angkatan,$jenis,$npm1,$npm2);
		}
		else{
			$data['ta'] = $this->ta_model->get_ta_rekap_detail_tolak($this->session->userdata('userId'),$angkatan,$jenis,$npm1,$npm2);
		}

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/rekap/rekap_ta_koor_detail',$data);
		
		$this->load->view('footer_global');
	}

	function rekap_seminar_koor()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		// $data['seminar'] = $this->ta_model->get_seminar_rekap_koor($this->session->userdata('userId'));
		// print_r($data);
		// $jml = count($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/rekap/rekap_seminar_koor');
		
		$this->load->view('footer_global');
	}

	function rekap_seminar_koor_detail()
	{
		$seminar = $this->input->get('seminar');
		$jenis = $this->input->get('jenis');
		$angkatan = $this->input->get('angkatan');

		switch($jenis)
		{
			case "ta":
			$jenis = 'Tugas Akhir';
			$npm1 = $npm2 = "0";
			break;
			case "skripsi":
			$jenis = 'Skripsi';
			$npm1 = "1";
			$npm2 = "5";
			break;
			case "tesis":
			$jenis = 'Tesis';
			$npm1 = $npm2 = "2";
			break;
			case "disertasi":
			$jenis = 'Disertasi';
			$npm1 = $npm2 = "3";
			break;
		}

		switch($seminar)
		{
			case "ta":
			$seminar = 'Seminar Tugas Akhir';
			break;
			case "usul":
			$seminar = 'Seminar Usul';
			break;
			case "hasil":
			$seminar = 'Seminar Hasil';
			break;
			case "kompre":
			$seminar = 'Sidang Komprehensif';
			break;
		}

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$data['seminar'] = $this->ta_model->get_seminar_rekap_koor_detail($this->session->userdata('userId'),$angkatan,$npm1,$npm2,$seminar,$jenis);

		$this->load->view('dosen/koordinator/rekap/rekap_seminar_koor_detail',$data);
		
		$this->load->view('footer_global');
	}

	function rekap_mahasiswa_ta()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		// $data['seminar'] = $this->ta_model->get_seminar_rekap_koor($this->session->userdata('userId'));
		// print_r($data);
		// $jml = count($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/rekap/rekap_mahasiswa_ta');
		
		$this->load->view('footer_global');
	}

	function rekap_mahasiswa_ta_detail()
	{
		$detail = $this->input->get('detail');
		$strata = $this->input->get('strata');
		$angkatan = $this->input->get('angkatan');

		switch($strata)
		{
			case "d3":
			$npm1 = $npm2 = "0";
			break;
			case "s1":
			$npm1 = "1";
			$npm2 = "5";
			break;
			case "s2":
			$npm1 = $npm2 = "2";
			break;
			case "s3":
			$npm1 = $npm2 = "3";
			break;
		}
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');
		if($detail == 'mahasiswa'){
			$data['mhs'] = $this->ta_model->get_mahasiswa_ta_rekap_mahasiswa_detail($this->session->userdata('userId'),$angkatan,$npm1,$npm2);
			$this->load->view('dosen/koordinator/rekap/rekap_mahasiswa_ta_mahasiswa',$data);
		}
		elseif($detail == 'ta'){
		    if($strata == 'd3'){
		        $data['ta'] = $this->ta_model->get_mahasiswa_ta_rekap_ta_detail_d3($this->session->userdata('userId'),$angkatan,$npm1,$npm2);
		    }
		    else{
		        $data['ta'] = $this->ta_model->get_mahasiswa_ta_rekap_ta_detail($this->session->userdata('userId'),$angkatan,$npm1,$npm2);
		    }
		    $this->load->view('dosen/koordinator/rekap/rekap_mahasiswa_ta_ta',$data);
		
		}
		elseif($detail == 'lulus'){
		    if($strata == 'd3'){
		        $data['lulus'] = $this->ta_model->get_mahasiswa_ta_rekap_lulus_detail_d3($this->session->userdata('userId'),$angkatan,$npm1,$npm2);
		    }
		    else{
		        $data['lulus'] = $this->ta_model->get_mahasiswa_ta_rekap_lulus_detail($this->session->userdata('userId'),$angkatan,$npm1,$npm2);
		    }
			
			$this->load->view('dosen/koordinator/rekap/rekap_mahasiswa_ta_lulus',$data);
		}
		$this->load->view('footer_global');
	}

	function rekap_bimbingan_dosen()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['dosen'] = $this->ta_model->get_bimbingan_dosen($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/rekap/rekap_bimbingan_dosen',$data);
		
		$this->load->view('footer_global');
	}

	function rekap_bimbingan_dosen_detail()
	{
		$id_user = $this->input->get('dosen');
		$id_user = $this->encrypt->decode($id_user);
		$jenis = $this->input->get('jenis');
		$strata = $this->input->get('strata');

		switch($strata)
		{
			case "d3":
			$npm1 = $npm2 = "0";
			break;
			case "s1":
			$npm1 = "1";
			$npm2 = "5";
			break;
			case "s2":
			$npm1 = $npm2 = "2";
			break;
			case "s3":
			$npm1 = $npm2 = "3";
			break;
		}
		switch($jenis)
		{
			case "pb1":
			$status = 'Pembimbing Utama';
			break;
			case "pb2":
			$status = 'Pembimbing 2';
			break;
			case "pb3":
			$status = 'Pembimbing 3';
			break;
			case "ps1":
			$status = 'Penguji 1';
			break;
			case "ps2":
			$status = 'Penguji 2';
			break;
			case "ps3":
			$status = 'Penguji 3';
			break;
		}

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['bimbingan'] = $this->ta_model->get_bimbingan_dosen_detail($id_user,$status,$npm1,$npm2);
		$data['id_dosen'] = $id_user;

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/rekap/rekap_bimbingan_dosen_detail',$data);
		
		$this->load->view('footer_global');
	}

	function rekap_ganti_pbb()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id_ta = $data['id_pengajuan'];

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$data['ta'] = $this->ta_model->get_ta_id_ganti_pbb($id_ta);

		$this->load->view('dosen/koordinator/rekap/rekap_ta_koor_detail_ganti_pbb',$data);

		$this->load->view('footer_global');
	}

	function rekap_ganti_ta()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id = $data['id_ta'];
		$ket = $data['keterangan'];

		$this->ta_model->rekap_ganti_ta($id,$ket);
		redirect(site_url("/dosen/koordinator/rekap/tugas-akhir"));
	}

	function rekap_ganti_pbb_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$no = $data['no_penetapan'];
		$nomor = $data['nomor'];
		$jenis = $data['jenis'];

		$dosenid = $this->session->userdata('userId');
		$no_penetapan = $no.$nomor;

		$npm = $data['npm'];

		$id_old = $data['id_pengajuan'];
		$ttd = $data['ttd'];
		$judul_approve = $data['judul'];
		$judul1 = $data['judul1'];
		$judul2 = $data['judul2'];

		$pb1_old = $data['pb1_old'];

		$pb1 = $data['pembimbing1'];
		$pb2 = $data['pembimbing2'];
		$pb3 = $data['pembimbing3'];
		$ps1 = $data['pembahas1'];
		$ps2 = $data['pembahas2'];
		$ps3 = $data['pembahas3'];

		// pb ps alt
		$pb2_nip = $data['pb2_alter_nip'];
		$pb2_nama = $data['pb2_alter_nama'];
		$pb2_email = $data['pb2_alter_email'];
		$pb3_nip = $data['pb3_alter_nip'];
		$pb3_nama = $data['pb3_alter_nama'];
		$pb3_email = $data['pb3_alter_email'];
		$ps1_nip = $data['ps1_alter_nip'];
		$ps1_nama = $data['ps1_alter_nama'];
		$ps1_email = $data['ps1_alter_email'];
		$ps2_nip = $data['ps2_alter_nip'];
		$ps2_nama = $data['ps2_alter_nama'];
		$ps2_email = $data['ps2_alter_email'];
		$ps3_nip = $data['ps3_alter_nip'];
		$ps3_nama = $data['ps3_alter_nama'];
		$ps3_email = $data['ps3_alter_email'];

		//copy ta
		$id_new = $this->ta_model->copy_row_ta($id_old,$pb1);
		//insert to tugas_akhir_ganti_pbb
		$insert_data = array(
			'npm' => $npm,
			'id_ta_old' => $id_old,
			'id_ta_new' => $id_new,		
		);
		$this->ta_model->tugas_akhir_ganti_pbb($insert_data);

		//pb 1 sama copy semua ttd
		if($pb1 == $pb1_old){
			//copy approval non pbb
			$this->ta_model->copy_row_ta_approval($id_old,$id_new);
		}
		//pb 1 berbeda ttd pb 1 null
		else{
			$this->ta_model->copy_row_ta_approval_non_pb1($id_old,$id_new);
			$this->ta_model->copy_row_ta_approval_pb1($id_old,$id_new,$pb1);
		}

		//copy surat
		$this->ta_model->copy_staff_surat_ta($id_old,$id_new);
		
		//update status ta old > -2
		$this->ta_model->update_ganti_pbb_ta_old($id_old);
		//insert tugas_akhir_komisi_pb1
		$nip_pb1 = $this->db->query('SELECT nip_nik FROM tbl_users_dosen WHERE id_user ='.$pb1)->row()->nip_nik;
		$nama_pb1 = $this->db->query('SELECT name FROM tbl_users WHERE userId ='.$pb1)->row()->name;
		$data_pbb1=array(
			'id_tugas_akhir' => $id_new,
			'status' => 'Pembimbing Utama',
			'nip_nik' => $nip_pb1,
			'id_user' => $pb1,
			'nama' => $nama_pb1,
		);
		$this->ta_model->insert_ta_komisi_pb1($data_pbb1);
		//copy berkas
		$this->ta_model->copy_berkas_ganti_pbb($id_old,$id_new);

		
		//insert tugas_akhir_komisi
		if($pb2 == NULL && ($pb2_nip != NULL && $pb2_nama != NULL)){
			$status = "Pembimbing 2";
			$this->ta_model->set_komisi_alter($id_new,$pb2_nip,$pb2_nama,$status);
			$this->ta_model->set_komisi_alter_access($id_new,$pb2_nip,$pb2_nama,$status,$pb2_email);

			$data_approval = array(
				'id_pengajuan' => $id_new,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}

		if($pb3 == NULL && ($pb3_nip != NULL && $pb3_nama != NULL)){
			$status = "Pembimbing 3";
			$this->ta_model->set_komisi_alter($id_new,$pb3_nip,$pb3_nama,$status);
			$this->ta_model->set_komisi_alter_access($id_new,$pb3_nip,$pb3_nama,$status,$pb3_email);

			$data_approval = array(
				'id_pengajuan' => $id_new,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}

		if($ps1 == NULL && ($ps1_nip != NULL && $ps1_nama != NULL)){
			$status = "Penguji 1";
			$this->ta_model->set_komisi_alter($id_new,$ps1_nip,$ps1_nama,$status);
			$this->ta_model->set_komisi_alter_access($id_new,$ps1_nip,$ps1_nama,$status,$ps1_email);

			$data_approval = array(
				'id_pengajuan' => $id_new,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);

		}

		if($ps2 == NULL && ($ps2_nip != NULL && $ps2_nama != NULL)){
			$status = "Penguji 2";
			$this->ta_model->set_komisi_alter($id_new,$ps2_nip,$ps2_nama,$status);
			$this->ta_model->set_komisi_alter_access($id_new,$ps2_nip,$ps2_nama,$status,$ps2_email);

			$data_approval = array(
				'id_pengajuan' => $id_new,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}

		if($ps3 == NULL && ($ps3_nip != NULL && $ps3_nama != NULL)){
			$status = "Penguji 3";
			$this->ta_model->set_komisi_alter($id_new,$ps3_nip,$ps3_nama,$status);
			$this->ta_model->set_komisi_alter_access($id_new,$ps3_nip,$ps3_nama,$status,$ps3_email);

			$data_approval = array(
				'id_pengajuan' => $id_new,
				'status_slug' => $status,
				'id_user' => '0',
				'ttd' => '',	
			);
			$this->ta_model->insert_approval_ta($data_approval);
		}

		if($jenis == "Tugas Akhir"){
			$this->ta_model->approval_koordinator_ta($id_new,$ttd,$dosenid,$no_penetapan,$judul_approve,$judul1,$judul2);
			$this->ta_model->set_komisi_ta($id_new,$pb1,$ps1,$ps2);

			if($ps1 != NULL && $ps1 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => 'Penguji 1',
					'id_user' => $ps1,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}

			// $this->ta_model->approve_ta_kaprodi($id);
			$data_approval = array(
				'id_pengajuan' => $id_new,
				'status_slug' => "Ketua Program Studi",
				'id_user' => $dosenid,
				'ttd' => $ttd,
			);
			$this->ta_model->insert_approve_ta_kaprodi($data_approval);
		}
		else{
			$this->ta_model->approval_koordinator($id_new,$ttd,$dosenid,$no_penetapan,$judul_approve,$judul1,$judul2);
			$this->ta_model->set_komisi($id_new,$pb1,$pb2,$pb3,$ps1,$ps2,$ps3);

			if($jenis != 'Skripsi'){
				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => "Ketua Program Studi",
					'id_user' => $dosenid,
					'ttd' => $ttd,
				);
				$this->ta_model->insert_approve_ta_kaprodi($data_approval);
			}

			if($pb2 != NULL && $pb2 != '0'){

				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => 'Pembimbing 2',
					'id_user' => $pb2,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($pb3 != NULL && $pb3 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => 'Pembimbing 3',
					'id_user' => $pb3,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($ps1 != NULL && $ps1 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => 'Penguji 1',
					'id_user' => $ps1,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($ps2 != NULL && $ps2 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => 'Penguji 2',
					'id_user' => $ps2,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
	
			if($ps3 != NULL && $ps3 != '0'){
	
				$data_approval = array(
					'id_pengajuan' => $id_new,
					'status_slug' => 'Penguji 3',
					'id_user' => $ps3,
					'ttd' => '',	
				);
	
				$this->ta_model->insert_approval_ta($data_approval);
			}
		}
		redirect(site_url("dosen/koordinator/rekap/tugas-akhir"));
	}
	
	function komposisi_nilai()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$data['nilai'] = $this->ta_model->get_komposisi_nilai($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/komposisi_nilai/komposisi_nilai',$data);
		
		$this->load->view('footer_global');
	}

	function komposisi_nilai_tambah()
	{
		
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$data['nilai'] = $this->ta_model->get_komposisi_nilai($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/komposisi_nilai/komposisi_nilai_add',$data);
		
		$this->load->view('footer_global');
	}

	function komposisi_nilai_simpan()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$tipe = $data['tipe'];

		// if($tipe != "Sidang Komprehensif"){
			$jml = count($data['ujian_komponen']);
			$jml2 = count($data['skripsi_komponen']);
		// }
		// else{
		// 	$jml = count($data['ujian_komponen_kompre']);
		// 	$jml2 = count($data['skripsi_komponen_kompre']);
		// }
		
		
		$ujian = 0;
		$skripsi = 0;

		//check
		// if($tipe != "Sidang Komprehensif"){
			for($i=0; $i<$jml; $i++){
				$ujian += $data['ujian_nilai'][$i];
			}
			for($i=0; $i<$jml2; $i++){
				$skripsi += $data['skripsi_nilai'][$i];
			}
		// }
		// else{
		// 	for($i=0; $i<$jml; $i++){
		// 		$ujian += $data['ujian_nilai_kompre'][$i];
		// 	}i
		// 	for($i=0; $i<$jml2; $i++){
		// 		$skripsi += $data['skripsi_nilai_kompre'][$i];
		// 	}
		// }

		if($tipe != "Sidang Komprehensif"){
			$persentase =  $ujian + $skripsi;
		}
		else{
			$persentase = 100;
		}
		
		$cek = $this->ta_model->cek_komposisi_nilai($data['jurusan'],$data['jenis'],$tipe);

		if(!empty($cek)){
			redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=duplikat"));
		}
		else{

		if($data['jenis'] == "Skripsi"){
			$total1 = $data['skripsi_pb1_1'] + $data['skripsi_pb2_1'] + $data['skripsi_ps1_1'];
			$total2 = $data['skripsi_pb1_2'] + $data['skripsi_ps1_2'] + $data['skripsi_ps2_2']; 
			// echo $total1;
			// echo $total2;
			if($total1 < 100 || $total2 < 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=kurang"));
			}
			elseif($total1 > 100 || $total2 > 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=lebih"));
			}
			else{
				$bobot = $data['skripsi_pb1_1'].'-'.$data['skripsi_pb2_1'].'-'.$data['skripsi_ps1_1'].'#'.$data['skripsi_pb1_2'].'-'.$data['skripsi_ps1_2'].'-'.$data['skripsi_ps2_2'];
			}

		}

		elseif($data['jenis'] == "Tugas Akhir"){
			$total = $data['ta_pb1'] + $data['ta_ps1'];

			if($total < 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=kurang"));
			}
			elseif($total > 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=lebih"));
			}
			else{
				$bobot = $data['ta_pb1'].'-'.$data['ta_ps1'];
			}
		}

		elseif($data['jenis'] == "Tesis"){
			if($data['tesis_pb1'] == NULL){
				$data['tesis_pb1'] = 0;
			}
			if($data['tesis_pb2'] == NULL){
				$data['tesis_pb2'] = 0;
			}
			if($data['tesis_pb3'] == NULL){
				$data['tesis_pb3'] = 0;
			}
			if($data['tesis_ps1'] == NULL){
				$data['tesis_ps1'] = 0;
			}
			if($data['tesis_ps2'] == NULL){
				$data['tesis_ps2'] = 0;
			}
			if($data['tesis_ps3'] == NULL){
				$data['tesis_ps3'] = 0;
			}
			$total = $data['tesis_pb1'] + $data['tesis_pb2']+ $data['tesis_pb3'] + $data['tesis_ps1'] + $data['tesis_ps2'] + $data['tesis_ps3'];
			
			$bobot = $data['tesis_pb1'].'-'.$data['tesis_pb2'].'-'.$data['tesis_pb3'].'-'.$data['tesis_ps1'].'-'.$data['tesis_ps2'].'-'.$data['tesis_ps3'];


		}

		elseif($data['jenis'] == "Disertasi"){
			if($data['disertasi_pb1'] == NULL){
				$data['disertasi_pb1'] = 0;
			}
			if($data['disertasi_pb2'] == NULL){
				$data['disertasi_pb2'] = 0;
			}
			if($data['disertasi_pb3'] == NULL){
				$data['disertasi_pb3'] = 0;
			}
			if($data['disertasi_ps1'] == NULL){
				$data['disertasi_ps1'] = 0;
			}
			if($data['disertasi_ps2'] == NULL){
				$data['disertasi_ps2'] = 0;
			}
			if($data['disertasi_ps3'] == NULL){
				$data['disertasi_ps3'] = 0;
			}
			$total = $data['disertasi_pb1'] + $data['disertasi_pb2']+ $data['disertasi_pb3'] + $data['disertasi_ps1'] + $data['disertasi_ps2'] + $data['disertasi_ps3'];
			
			$bobot = $data['disertasi_pb1'].'-'.$data['disertasi_pb2'].'-'.$data['disertasi_pb3'].'-'.$data['disertasi_ps1'].'-'.$data['disertasi_ps2'].'-'.$data['disertasi_ps3'];


		}

		if($persentase < 100){
			redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=kurang"));
		}
		elseif($persentase > 100){
			redirect(site_url("/dosen/struktural/komposisi-nilai/add?status=lebih"));
		}
		elseif($persentase = 100){
			$komponen_nilai = array(
				'id_prodi' => $data['jurusan'],
				'semester' => $data['semester'],
				'tahun_akademik' => $data['tahun_akademik'],
				'jenis' => $data['jenis'],
				'tipe' => $data['tipe'],
				'bobot' => $bobot,
				'status' => '0',	
			);
	
		$lastid = $this->ta_model->komposisi_nilai_save($komponen_nilai);
	
		// if($data['tipe'] != "Sidang Komprehensif"){
			for($i=0; $i<$jml; $i++){
	
				$data_ujian = array(
					'id_komponen' => $lastid,
					'unsur' => 'Ujian',
					'attribut' => $data['ujian_komponen'][$i],
					'persentase' => $data['ujian_nilai'][$i],
				);
				$this->ta_model->komposisi_nilai_meta_save($data_ujian);
	
			}
	
			for($i=0; $i<$jml2; $i++){
				$data_ujian = array(
					'id_komponen' => $lastid,
					'unsur' => $data['jenis'],
					'attribut' => $data['skripsi_komponen'][$i],
					'persentase' => $data['skripsi_nilai'][$i],
				);
				$this->ta_model->komposisi_nilai_meta_save($data_ujian);
			}
		// }
		// else{
		// 	for($i=0; $i<$jml; $i++){
		// 		$data_ujian = array(
		// 			'id_komponen' => $lastid,
		// 			'unsur' => 'Ujian',
		// 			'attribut' => $data['ujian_komponen_kompre'][$i],
		// 			'persentase' => "100",
		// 		);
		// 		$this->ta_model->komposisi_nilai_meta_save($data_ujian);
	
		// 	}
	
		// 	for($i=0; $i<$jml2; $i++){
		// 		$data_ujian = array(
		// 			'id_komponen' => $lastid,
		// 			'unsur' => $data['jenis'],
		// 			'attribut' => $data['skripsi_komponen_kompre'][$i],
		// 			'persentase' => "100",
		// 		);
		// 		$this->ta_model->komposisi_nilai_meta_save($data_ujian);
		// 	}
		// }

			redirect(site_url("dosen/struktural/bidang-nilai/komposisi-nilai"));
		}
		}
	}

	function komponen_nilai()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['komponen'] = $this->ta_model->get_komposisi_nilai_id($id);
		$data['meta'] = $this->ta_model->get_komposisi_nilai_meta_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/komposisi_nilai/show_komponen_nilai',$data);
		
		$this->load->view('footer_global');
	}

	function komposisi_nilai_nonaktif()
	{
		$data = $this->input->post();

		$id = $data['id'];
		$this->ta_model->nonaktifkan_komposisi($id);

		redirect(site_url("dosen/struktural/bidang-nilai/komposisi-nilai"));
	}

	function komposisi_nilai_ubah()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['komponen'] = $this->ta_model->get_komposisi_nilai_id($id);
		$data['meta'] = $this->ta_model->get_komposisi_nilai_meta_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/komposisi_nilai/komposisi_nilai_ubah',$data);
		
		$this->load->view('footer_global');
	}

	function komposisi_nilai_edit()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id'];
		$tipe = $data['tipe'];

		// if($tipe != "Sidang Komprehensif"){
			$jml = count($data['ujian_komponen']);
			$jml2 = count($data['skripsi_komponen']);
		// }
		// else{
		// 	$jml = count($data['ujian_komponen_kompre']);
		// 	$jml2 = count($data['skripsi_komponen_kompre']);
		// }

		$ujian = 0;
		$skripsi = 0;
		//check
		// if($tipe != "Sidang Komprehensif"){
			for($i=0; $i<$jml; $i++){
				$ujian += $data['ujian_nilai'][$i];
			}
			for($i=0; $i<$jml2; $i++){
				$skripsi += $data['skripsi_nilai'][$i];
			}
		// }
		// else{
		// 	for($i=0; $i<$jml; $i++){
		// 		$ujian += $data['ujian_nilai_kompre'][$i];
		// 	}
		// 	for($i=0; $i<$jml2; $i++){
		// 		$skripsi += $data['skripsi_nilai_kompre'][$i];
		// 	}
		// }


		if($tipe != "Sidang Komprehensif"){
			$persentase =  $ujian + $skripsi;
		}
		else{
			$persentase = 100;
		}


		if($data['jenis'] == "Skripsi"){
			$total1 = $data['skripsi_pb1_1'] + $data['skripsi_pb2_1'] + $data['skripsi_ps1_1'];
			$total2 = $data['skripsi_pb1_2'] + $data['skripsi_ps1_2'] + $data['skripsi_ps2_2']; 
			
			if($total1 < 100 || $total2 < 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/ubah?id=$id&status=kurang"));
			}
			elseif($total1 > 100 || $total2 > 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/ubah?id=$id&status=lebih"));
			}
			else{
				$bobot = $data['skripsi_pb1_1'].'-'.$data['skripsi_pb2_1'].'-'.$data['skripsi_ps1_1'].'#'.$data['skripsi_pb1_2'].'-'.$data['skripsi_ps1_2'].'-'.$data['skripsi_ps2_2'];
			}
		}

		elseif($data['jenis'] == "Tugas Akhir"){
			$total = $data['ta_pb1'] + $data['ta_ps1'];

			if($total < 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/ubah?status=kurang"));
			}
			elseif($total > 100){
				redirect(site_url("/dosen/struktural/komposisi-nilai/ubah?status=lebih"));
			}
			else{
				$bobot = $data['ta_pb1'].'-'.$data['ta_ps1'];
			}
		}

		elseif($data['jenis'] == "Tesis"){
			if($data['tesis_pb1'] == NULL){
				$data['tesis_pb1'] = 0;
			}
			if($data['tesis_pb2'] == NULL){
				$data['tesis_pb2'] = 0;
			}
			if($data['tesis_pb3'] == NULL){
				$data['tesis_pb3'] = 0;
			}
			if($data['tesis_ps1'] == NULL){
				$data['tesis_ps1'] = 0;
			}
			if($data['tesis_ps2'] == NULL){
				$data['tesis_ps2'] = 0;
			}
			if($data['tesis_ps3'] == NULL){
				$data['tesis_ps3'] = 0;
			}
			$total = $data['tesis_pb1'] + $data['tesis_pb2']+ $data['tesis_pb3'] + $data['tesis_ps1'] + $data['tesis_ps2'] + $data['tesis_ps3'];
			$bobot = $data['tesis_pb1'].'-'.$data['tesis_pb2'].'-'.$data['tesis_pb3'].'-'.$data['tesis_ps1'].'-'.$data['tesis_ps2'].'-'.$data['tesis_ps3'];
		}

		elseif($data['jenis'] == "Disertasi"){
			if($data['disertasi_pb1'] == NULL){
				$data['disertasi_pb1'] = 0;
			}
			if($data['disertasi_pb2'] == NULL){
				$data['disertasi_pb2'] = 0;
			}
			if($data['disertasi_pb3'] == NULL){
				$data['disertasi_pb3'] = 0;
			}
			if($data['disertasi_ps1'] == NULL){
				$data['disertasi_ps1'] = 0;
			}
			if($data['disertasi_ps2'] == NULL){
				$data['disertasi_ps2'] = 0;
			}
			if($data['disertasi_ps3'] == NULL){
				$data['disertasi_ps3'] = 0;
			}
			$total = $data['disertasi_pb1'] + $data['disertasi_pb2']+ $data['disertasi_pb3'] + $data['disertasi_ps1'] + $data['disertasi_ps2'] + $data['disertasi_ps3'];
			$bobot = $data['disertasi_pb1'].'-'.$data['disertasi_pb2'].'-'.$data['disertasi_pb3'].'-'.$data['disertasi_ps1'].'-'.$data['disertasi_ps2'].'-'.$data['disertasi_ps3'];
			

		}

		if($persentase < 100){
			redirect(site_url("/dosen/struktural/komposisi-nilai/ubah?id=$id&status=kurang"));
		}
		elseif($persentase > 100){
			redirect(site_url("/dosen/struktural/komposisi-nilai/ubah?id=$id&status=lebih"));
		}
		elseif($persentase = 100){
			
			$komponen_nilai = array(
				'id_prodi' => $data['jurusan'],
				'semester' => $data['semester'],
				'tahun_akademik' => $data['tahun_akademik'],
				'jenis' => $data['jenis'],
				'tipe' => $tipe,
				'bobot' => $bobot,
				'status' => '0',	
			);
	
			$this->ta_model->update_komposisi($id,$komponen_nilai);
			$this->ta_model->delete_komposisi_meta($id);
			
			// if($data['tipe'] != "Sidang Komprehensif"){
			for($i=0; $i<$jml; $i++){
	
				$data_ujian = array(
					'id_komponen' => $id,
					'unsur' => 'Ujian',
					'attribut' => $data['ujian_komponen'][$i],
					'persentase' => $data['ujian_nilai'][$i],
				);
				$this->ta_model->komposisi_nilai_meta_save($data_ujian);
	
			}
	
			for($i=0; $i<$jml2; $i++){
				$data_ujian = array(
					'id_komponen' => $id,
					'unsur' => $data['jenis'],
					'attribut' => $data['skripsi_komponen'][$i],
					'persentase' => $data['skripsi_nilai'][$i],
				);
				$this->ta_model->komposisi_nilai_meta_save($data_ujian);
			}
		// }
		// else{
		// 	for($i=0; $i<$jml; $i++){
		// 		$data_ujian = array(
		// 			'id_komponen' => $id,
		// 			'unsur' => 'Ujian',
		// 			'attribut' => $data['ujian_komponen_kompre'][$i],
		// 			'persentase' => "100",
		// 		);
		// 		$this->ta_model->komposisi_nilai_meta_save($data_ujian);
	
		// 	}
	
		// 	for($i=0; $i<$jml2; $i++){
		// 		$data_ujian = array(
		// 			'id_komponen' => $id,
		// 			'unsur' => $data['jenis'],
		// 			'attribut' => $data['skripsi_komponen_kompre'][$i],
		// 			'persentase' => "100",
		// 		);
		// 		$this->ta_model->komposisi_nilai_meta_save($data_ujian);
		// 	}
		// }


			redirect(site_url("dosen/struktural/bidang-nilai/komposisi-nilai"));
		}

	}

	function nilai_seminar_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		
		$id = $data['id'];
		$status = $data['status'];
		$saran = $data['saran'];
		$id_komponen = $data['id_komponen'];
		$ttd = $data['ttd'];

		$jml = $data['jml'];

		$nilai = $data['nilai'];
		$attribut = $data['attribut'];

		$counts = $this->ta_model->cek_seminar_nilai_fill($id);
		$count = count($counts);

		// echo $count;
		for($i=1;$i<$jml;$i++)
		{
			$data = array(
				'id_seminar_sidang' => $id,
				'id_komponen' => $id_komponen,
				'komponen' => $attribut[$i],
				'nilai' => $nilai[$i],
				'status' => $status,
			);
			$this->ta_model->insert_seminar_nilai($data);
		}
		if($count == 1){
			$this->ta_model->seminar_sidang_nilai_dosen_update($id);
		}

		$this->ta_model->update_nilai_seminar_check($id,$status,$saran,$ttd);
		redirect(site_url("dosen/tugas-akhir/nilai-seminar"));
	}

	function nilai_seminar_koor()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_approval_nilai_seminar_koordinator($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/nilai_seminar/nilai_seminar',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_seminar_sidang_kaprodi()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_approval_nilai_seminar_kaprodi($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/nilai_seminar/nilai_seminar',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_seminar_koor_approve()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_seminar_id($id);
		$data['ta'] = $this->ta_model->get_tugas_akhir_seminar_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/nilai_seminar/nilai_seminar_approve',$data);
		
		$this->load->view('footer_global');

	}

	function nilai_seminar_sidang_kaprodi_approve()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_seminar_id($id);
		$data['ta'] = $this->ta_model->get_tugas_akhir_seminar_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/nilai_seminar/nilai_seminar_approve',$data);
		
		$this->load->view('footer_global');

	}

	function nilai_seminar_koor_approve_add()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id'];
		$id_ta = $data['id_ta'];
		$ttd = $data['ttd'];
		$jenis = $data['jenis'];
		$jenis_ta = $data['jenis_ta'];
		$status = "Koordinator";
		$user_id = $this->session->userdata('userId');

		if($jenis_ta != "Skripsi"){
			$data_ta = array(
				'status' => "Ketua Program Studi",
				'saran' => '',
				'ket' => $user_id,
				'id_seminar' => $id,
				'ttd' => $ttd,
			);
			$this->ta_model->insert_nilai_seminar_koor($data_ta);
		}

		if($jenis == "Sidang Komprehensif"){
			$data_kompre = array(
				'npm' => $data['npm'],
				'id_ta' => $id_ta,
				'id_seminar' => $id,
				'ket' => $data['keterangan'],
			);
			$this->ta_model->insert_nilai_seminar_koor_kompre($data_kompre);
		}

		$data_koor = array(
			'status' => $status,
			'saran' => '',
			'ket' => $user_id,
			'id_seminar' => $id,
			'ttd' => $ttd,
		);
		$this->ta_model->insert_nilai_seminar_koor($data_koor);

		//update seminar
		$this->ta_model->update_nilai_seminar_koor($id);
		if($jenis_ta != "Skripsi"){
			redirect(site_url("dosen/struktural/kaprodi/nilai-seminar-sidang"));
		}
		else{
			redirect(site_url("dosen/tugas-akhir/nilai-seminar/koordinator"));
		}
		

	}

	function nilai_seminar_kajur()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_approval_nilai_seminar_kajur($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/nilai_seminar/nilai_seminar',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_seminar_kajur_approve()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_seminar_id($id);
		$data['ta'] = $this->ta_model->get_tugas_akhir_seminar_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/nilai_seminar/nilai_seminar_approve',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_seminar_kajur_approve_add()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id'];
		$ttd = $data['ttd'];
		$jenis = $data['jenis'];
		$status = "Ketua Jurusan";
		$user_id = $this->session->userdata('userId');

		$data = array(
			'status' => $status,
			'saran' => '',
			'ket' => $user_id,
			'id_seminar' => $id,
			'ttd' => $ttd,
		);
		$this->ta_model->insert_nilai_seminar_kajur($data);

		if($jenis == "Sidang Komprehensif"){
			$this->ta_model->update_seminar_sidang_kompre_id_seminar($id);
		}

		//update seminar
		$this->ta_model->update_nilai_seminar_kajur($id);
		redirect(site_url("dosen/struktural/nilai-seminar"));
	}

	function bidang_jurusan()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['bidang'] = $this->user_model->get_dosen_prodi($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/bidang/bidang_jurusan',$data);
		
		$this->load->view('footer_global');
	}

	function bidang_jurusan_show()
	{
		$jurusan = $this->input->get('jurusan');
		// $jurusan = $this->encrypt->decode($jurusan);

		$prodi = $this->input->get('prodi');
		// $prodi = $this->encrypt->decode($prodi);


		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['bidang'] = $this->parameter_model->select_bidang_ilmu($jurusan,$prodi);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/bidang/bidang_jurusan_show',$data);
		
		$this->load->view('footer_global');
	}

	function bidang_jurusan_add()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$prodi = $data['prodi'];
		$jurusan = $data['jurusan'];
		$nama = $data['nama'];

		$this->ta_model->insert_bidang_jurusan($data);
		redirect(site_url("dosen/struktural/bidang-nilai/bidang-jurusan/show?jurusan=$jurusan&prodi=$prodi"));
	}

	function bidang_jurusan_delete()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id = $data['id'];
		$prodi = $data['prodi'];
		$jurusan = $data['jurusan'];

		$this->ta_model->delete_bidang_jurusan($id);
		redirect(site_url("dosen/struktural/bidang-nilai/bidang-jurusan/show?jurusan=$jurusan&prodi=$prodi"));
	}

	function komposisi_ta()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['bidang'] = $this->parameter_model->get_bidang_ilmu_ta();

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/komposisi_ta/komposisi_ta',$data);
		
		$this->load->view('footer_global');
	}

	function komposisi_ta_add()
	{
		$id = $this->input->get('id');

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['wajib'] = $this->ta_model->get_verifikasi_ta_komponen_wajib($id);
		$data['konten'] = $this->ta_model->get_verifikasi_ta_komponen_konten($id);
		$data['bidang'] = $this->ta_model->get_bidang_ilmu_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/komposisi_ta/komposisi_ta_add',$data);
		
		$this->load->view('footer_global');

	}

	function komposisi_ta_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['bidang'];
		// $ket = $data['ket'];
		// $komponen = $data['nama'];

		$this->ta_model->insert_komponen_ta($data);
		redirect(site_url("dosen/struktural/bidang-nilai/komposisi-ta/add?id=$id"));

	}

	function komposisi_ta_delete()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id'];
		$bidang = $data['bidang'];

		$this->ta_model->delete_komponen_ta($id,$bidang);
		redirect(site_url("dosen/struktural/bidang-nilai/komposisi-ta/add?id=$bidang"));
	}

	function tugas_akhir_kaprodi(){
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_approval_ta_kaprodi($this->session->userdata('userId'));
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kaprodi/tema_ta/tema_ta',$data);
		
		$this->load->view('footer_global');
	}

	function tugas_akhir_kaprodi_form()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_approval_ta_koordinator($this->session->userdata('userId'));

		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$aksi = $this->input->get('aksi');

		$data['ta'] = $this->ta_model->get_ta_by_id($id);
		$data['aksi'] = $aksi;

		// print_r($data);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/approve_tema_ta',$data);
		
		$this->load->view('footer_global');
	}

	function tugas_akhir_kaprodi_approve()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id_pengajuan'];
		$id_user = $data['id_user'];
		$ttd = $data['ttd'];

		$this->ta_model->approve_ta_kaprodi($id);

		$data_approval = array(
			'id_pengajuan' => $id,
			'status_slug' => "Ketua Program Studi",
			'id_user' => $id_user,
			'ttd' => $ttd,
		);
		$this->ta_model->insert_approve_ta_kaprodi($data_approval);
		redirect(site_url("dosen/struktural/kaprodi/tugas-akhir"));
	}

	function nilai_verifikasi()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_verifikasi_program_ta_dosen($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/verifikasi_ta/verifikasi_ta',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_verifikasi_form()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$data['ta'] = $this->ta_model->get_verifikasi_program_ta_nilai($id);
		$data['komponen'] = $this->ta_model->get_verifikasi_program_ta_komponen($data['ta']->bidang_ilmu);
		$data['wajib'] = $this->ta_model->get_verifikasi_program_ta_pertemuan_wajib($data['ta']->id_pengajuan);
		$data['konten'] = $this->ta_model->get_verifikasi_program_ta_pertemuan_konten($data['ta']->id_pengajuan);

		if(!empty($data['komponen'])){
			$this->load->view('header_global', $header);
			$this->load->view('dosen/header');

			$this->load->view('dosen/verifikasi_ta/verifikasi_ta_nilai',$data);
			
			$this->load->view('footer_global');
		}
		else{
			redirect(site_url("dosen/tugas-akhir/nilai-verifikasi-ta?status=error"));
		}
	}

	function nilai_verifikasi_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id = $data['id_pengajuan'];
		$pertemuan = $this->ta_model->get_verifikasi_program_ta_pertemuan($id);
		
		foreach ($pertemuan as $prt){
			 $day = $data[$prt->id_verif];
			 $this->ta_model->update_verifikasi_ta_pertemuan($day, $prt->id_verif);
		}
		redirect(site_url("dosen/tugas-akhir/nilai-verifikasi-ta"));
	}

	function nilai_verifikasi_nilai()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_ta_by_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/verifikasi_ta/verifikasi_ta_ttd',$data);
		
		$this->load->view('footer_global');
	}

	function nilai_verifikasi_verifikasi()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id = $data['id_pengajuan'];
		$id_encode = $data['id_encode'];
		$nilai = $data['nilai'];
		$ttd = $data['ttd'];
		$nilai_date = date("Y-m-d H:i:s",strtotime('+7 hours'));

		$cek = is_numeric($nilai);
		if($cek == 1){
			if($nilai <= 0 || $nilai >= 100){
				redirect(site_url("dosen/tugas-akhir/nilai-verifikasi-ta/nilai?id=$id_encode&status=error"));
			}
			else{
				$this->ta_model->update_verifikasi_ta_nilai($id, $nilai, $ttd, $nilai_date);
				redirect(site_url("dosen/tugas-akhir/nilai-verifikasi-ta"));
			}
		}
		else{
			redirect(site_url("dosen/tugas-akhir/nilai-verifikasi-ta/nilai?id=$id_encode&status=error"));
		}

	}

	function tugas_akhir_kaprodi_verifikasi()
	{

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_verifikasi_ta_list_kaprodi($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kaprodi/verifikasi_ta/verifikasi_ta',$data);
		
		$this->load->view('footer_global');
	}

	function verifikasi_ta_dosen()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_verifikasi_ta_list($this->session->userdata('userId'));
		$data['pa'] = $this->ta_model->get_verifikasi_ta_list_pa($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/verifikasi_ta/verifikasi_ta_list',$data);
		
		$this->load->view('footer_global');
	}

	function verifikasi_ta_dosen_form()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$status = $this->input->get('status');

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_ta_by_id($id);
		$data['status'] = $status;

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/verifikasi_ta/verifikasi_ta_approve',$data);
		
		$this->load->view('footer_global');
	}

	function verifikasi_ta_dosen_form_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);	

		$id = $data['id_pengajuan'];
		$id_user = $data['id_user'];
		$status = $data['status'];
		$ttd = $data['ttd'];

		$data_approval = array(
			'id_ta' => $id,
			'status' => $status,
			'id_user' => $id_user,
			'ttd' => $ttd,
		);

		$this->ta_model->insert_approve_ta_verifikasi($data_approval);
		$this->ta_model->update_nilai_ta_verifikasi($status,$id);
		
		if($status == 'Ketua Program Studi'){
		    redirect(site_url("dosen/struktural/kaprodi/verifikasi-tugas-akhir"));
		}
		else
		{
		    redirect(site_url("dosen/tugas-akhir/verifikasi-ta"));    
		}
		

	}

	function tugas_akhir_kaprodi_verifikasi_form()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);
		$status = $this->input->get('status');

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['ta'] = $this->ta_model->get_ta_by_id($id);
		$data['status'] = $status;

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/verifikasi_ta/verifikasi_ta_approve',$data);
		
		$this->load->view('footer_global');
	}

	function seminar_sidang_kaprodi()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['seminar'] = $this->ta_model->get_approval_seminar_kaprodi($this->session->userdata('userId'));
		// print_r($data);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kaprodi/seminar/seminar_ta_kaprodi',$data);
		
		$this->load->view('footer_global');
	}
	
	//PKL
	function kajur_add_pkl()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->get_pkl_kajur($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/pkl/pkl_add',$data);
		
		$this->load->view('footer_global');
	}

	function kajur_add_pkl_form()
	{
		$id = $this->input->get('id');
		$aksi = $this->input->get('aksi');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');
		if($aksi == "ubah"){
			$data['pkl'] = $this->pkl_model->get_pkl_kajur_by_id($id);
			$this->load->view('dosen/kajur/pkl/pkl_add_form_edit',$data);
		}
		else{
			$this->load->view('dosen/kajur/pkl/pkl_add_form');
		}
		$this->load->view('footer_global');
	}

	function kajur_add_pkl_form_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$periode = $data['periode'];
		$tahun = $data['tahun'];
		$jurusan = $data['jurusan'];
		$aksi = $data['aksi'];

		if($aksi == "ubah"){
			$id = $data['ID'];

			//update table pkl_periode;
			$id_periode = $this->pkl_model->update_pkl_periode($id,$periode,$tahun);

			$n = 12;
			for($i=1;$i<=$n;$i++)
			{
				$start = $data[$i.'_start'];
				$end = $data[$i.'_end'];
				$id_meta = $data[$i.'_id'];
				$this->pkl_model->pkl_periode_meta_update($id_meta,$start,$end);
			}
			redirect(site_url("dosen/struktural/pkl/add-pkl")); 
		}
		else{
			//cek duplikat
			$cek = $this->pkl_model->cek_pkl_periode($periode,$tahun,$jurusan);

			if(empty($cek)){
				//insert table pkl_periode;
				$id_periode = $this->pkl_model->insert_pkl_periode($jurusan,$periode,$tahun);

				$n = 12;
				for($i=1;$i<=$n;$i++)
				{
					$start = $data[$i.'_start'];
					$end = $data[$i.'_end'];
					$this->pkl_model->insert_pkl_periode_meta($id_periode,$i,$start,$end);
				}
				redirect(site_url("dosen/struktural/pkl/add-pkl")); 
			}
			else{
				redirect(site_url("dosen/struktural/pkl/add-pkl?status=duplikat")); 
			}
			
		}
		
	}

	function kajur_add_pkl_show()
	{
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->get_pkl_kajur_by_id($id);

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/pkl/pkl_add_show',$data);
		
		$this->load->view('footer_global');

	}
	
	function kajur_add_pkl_delete()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id = $data['id_pkl'];

		//delete pkl_periode
		$this->pkl_model->delete_pkl_periode($id);
		//delete pkl_periode_meta
		$this->pkl_model->delete_pkl_periode_meta($id);
		redirect(site_url("dosen/struktural/pkl/add-pkl")); 

	}

	function kajur_add_lokasi_pkl()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->get_pkl_kajur($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/pkl/pkl_add_lokasi',$data);
		
		$this->load->view('footer_global');
	}

	function kajur_add_lokasi_pkl_aksi()
	{
		$aksi = $this->input->get('aksi');
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['id'] = $id;
		$data['pkl'] = $this->pkl_model->get_pkl_kajur_by_id($id);
		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/kajur/pkl/pkl_add_lokasi_tambah',$data);
		
		$this->load->view('footer_global');
	}

	function kajur_add_lokasi_pkl_aksi_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$lokasi = $data['lokasi'];
		$id_pkl = $data['id_pkl'];
		$alamat = $data['alamat'];

		$id_aksi = $data['id_aksi'];
		$aksi = $data['aksi'];

		$data_lokasi=array(
			"id_pkl" => $id_pkl,
			"lokasi" => $lokasi,
			"alamat" => $alamat,
		);
		$this->pkl_model->insert_pkl_lokasi($data_lokasi);
		redirect(site_url("/dosen/struktural/pkl/add-lokasi-pkl/aksi?aksi=$aksi&id=$id_aksi")); 

	}

	function kajur_add_lokasi_pkl_aksi_delete()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id_lokasi = $data['id_lokasi'];
		$aksi = $data['id_aksi'];

		$this->pkl_model->delete_pkl_lokasi($id_lokasi);
		redirect(site_url("/dosen/struktural/pkl/add-lokasi-pkl/aksi?aksi=tambah&id=$aksi"));
	}

	function kajur_add_lokasi_pkl_aksi_edit()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id_lokasi = $data['id_lokasi'];
		$id_aksi = $data['id_aksi'];
		$lokasi = $data['lokasi'];
		$alamat = $data['alamat'];

		$this->pkl_model->update_pkl_lokasi($id_lokasi,$lokasi,$alamat);
		redirect(site_url("/dosen/struktural/pkl/add-lokasi-pkl/aksi?aksi=tambah&id=$id_aksi"));
	}
	
	function pkl_approve()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->get_approve_pa_pkl($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/pkl/pkl_approve',$data);
		
		$this->load->view('footer_global');
	}

	function pkl_approve_perbaiki()
	{
		$id = $this->input->post('pkl_id');
		$status = $this->input->post('status');
		$keterangan = $this->input->post('keterangan');
		$ket = $keterangan."$#$".$status;

		$this->pkl_model->perbaikan_pkl($id,$ket);
		redirect(site_url("/dosen/pkl/approve"));
	}

	function pkl_approve_setujui()
	{
		$status = $this->input->get('status');
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->select_pkl_by_id_pkl($id);
		$data['status'] = $status;

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/pkl/pkl_approve_ttd',$data);
		
		$this->load->view('footer_global');
	}

	function pkl_approve_setujui_save()
	{
		$data = $this->input->post();
		// print_r($data);
		$pkl_id = $data['id_pengajuan'];
		$status = $data['status'];
		$user_id = $this->session->userdata('userId');
		$ttd = $data['ttd'];

		if($status == "pa"){
			//save surat
			$data_surat_pa = array(
				"jenis" => 3,
				"id_jenis" => $pkl_id
			);
			$this->pkl_model->save_surat_pa($data_surat_pa);	
		}
		$this->pkl_model->pkl_approve_setujui($status,$pkl_id,$user_id,$ttd);
		redirect(site_url("/dosen/pkl/approve"));
	}

	//koor pkl
	function pkl_approve_koor()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->get_approve_koor_lokasi($this->session->userdata('userId'));

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/pkl/pkl_koordinator',$data);
		
		$this->load->view('footer_global');
	}

	function pkl_approve_koor_tolak()
	{
		$id = $this->input->post('pkl_id');
		$status = $this->input->post('status');
		$keterangan = $this->input->post('keterangan');
		$ket = $keterangan."$#$".$status;

		$periode = $this->input->post('periode');
		$id_al = $this->input->post('id_al');

		$this->pkl_model->tolak_pkl($id,$ket);
		redirect(site_url("/dosen/pkl/pengajuan/koordinator/approve?periode=$periode&id=$id_al"));
	}

	function pkl_approve_koor_approve()
	{
		$periode = $this->input->get('periode');
		$id = $this->input->get('id');
		$id = $this->encrypt->decode($id);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['pkl'] = $this->pkl_model->get_lokasi_pkl_by_id($id);
		$data['periode'] = $periode;

		$this->load->view('header_global', $header);
		$this->load->view('dosen/header');

		$this->load->view('dosen/koordinator/pkl/pkl_koordinator_ttd',$data);
		
		$this->load->view('footer_global');
	}

	function pkl_approve_koor_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$pkl_id = $data['pkl_id'];
		$pembimbing = $data['pembimbing'];
		$lokasi = $data['lokasi'];

		$id_alm = $data['id_alamat'];
		$periode_alm = $data['periode_alamat'];

		//input approval
		$data_approval = array(
			"lokasi_id"=>$lokasi,
		);
		$approval_id = $this->pkl_model->add_approval_pkl($data_approval);

		//input approval_meta
		$data_app_meta = array(
			"approval_id" => $approval_id,
			"pkl_id" => $pkl_id
		);
		$this->pkl_model->add_approval_pkl_meta($data_app_meta);

		//update pembimbing & status
		$data_koor = array(
			"pembimbing" => $pembimbing,
			"status" => "3"
		);
		$this->pkl_model->approval_koor_pkl($pkl_id,$data_koor);

		redirect(site_url("/dosen/pkl/pengajuan/koordinator/approve?periode=$periode_alm&id=$id_alm"));
	}
}
