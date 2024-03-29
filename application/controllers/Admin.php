<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {
    
    public function __construct()
	{
		parent::__construct();
		$this->load->model('wilayah_model');
		$this->load->model('parameter_model');
		$this->load->model('jurusan_model');
		$this->load->model('user_model');
		$this->load->model('ta_model');
		$this->load->model('pkl_model');
		$this->load->model('layanan_model');
		$this->load->library('pdf');
		$this->load->library('encrypt');
		
		if($this->session->has_userdata('username')) {
			if($this->session->userdata('state') <> 1) {
		        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
		    }
		} else {
		    redirect(site_url('?access=ditolak'));
		}
	}

	public function index()
	{
		redirect(site_url("admin/kelola-akun"));
	}

	// public function index()
	// {
	// 	$this->load->view('admin/header');
	// 	$this->load->view('admin/dashboard');
	// 	//echo base_url()."<br>".site_url();
	// 	$this->load->view('admin/footer');
	// }

	public function kelola_akun()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('admin/header');
		
		$this->load->view('admin/akun',$data);

        $this->load->view('footer_global');
	}

	function mahasiswa_registrasi()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$this->load->view('admin/header');
		$data['mahasiswa'] = $this->user_model->get_mhs_regis();

		$this->load->view('admin/user/mhs_registrasi',$data);

        $this->load->view('footer_global');
	}

	function simpan_registrasi_mhs()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		if($data['aksi'] == 'hapus'){
			$this->user_model->delete_user($data['id']);
		}else{
			//get user data
			$mhs = $this->user_model->get_mahasiswa_data($data['id']);

			//insert token
			$token = md5($data['id'].'apps_fmipa_2020'.date('Y-m-d H:i:s'));
			$data_regis = array(
				"userId" => $data['id'],
				"token" => $token,
			);
			

			$config = Array(  
				'protocol' => 'smtp',  
				'smtp_host' => 'ssl://smtp.googlemail.com',  
				'smtp_port' => 465,  
				'smtp_user' => 'apps.fmipa.unila@gmail.com',   
				'smtp_pass' => 'apps_fmipa 2020',   
				'mailtype' => 'html',   
				'charset' => 'iso-8859-1'  
			);  

			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");  
			$this->email->from('apps.fmipa.unila@gmail.com', 'SIMIPA');   
			$this->email->to($mhs->email);//$row->email   
			$this->email->subject('Verifikasi Email Registrasi SIMIPA');   
			$this->email->message("
			Silahkan Klik Link Berikut Untuk Menyelesaikan Verifikasi Registrasi <br>
			https://apps.fmipa.unila.ac.id/simipa/verifikasi-registrasi/$token
			<br><br>
			Terimakasih.
			
			");
			if (!$this->email->send()) {  
					
			   }else{  
				$this->user_model->insert_mhs_registrasi($data_regis);
			}   

		}

		redirect(site_url("admin/mahasiswa/registrasi?status=sukses"));
	}

	function struktural_tugas()
	{
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$data['tugas'] = $this->user_model->get_tugas_tambahan_all();

		$this->load->view('admin/header');
		$this->load->view('admin/struktural/tugas_tambahan',$data);

        $this->load->view('footer_global');
	}

	function tambah_tugas()
	{
		$tgs = $this->input->post('tugas');
		$data = array(
			"nama" => $tgs
		);
		$this->user_model->tambah_tugas($data);
		redirect(site_url("admin/struktural/tugas"));
	}

	function struktural_tugas_form()
	{
		$tgs = $this->input->get('tugas');
		// echo $tgs;
		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$this->load->view('header_global', $data);
		$data['tugas'] = $this->user_model->get_tugas_periode($tgs);

		$this->load->view('admin/header');
		$this->load->view('admin/struktural/tugas_tambahan_form',$data);

        $this->load->view('footer_global');
	}

	function simpan_tugas()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$tugas = $data['tugas'];
		$status = 1;
		$jurusan = $data['jurusan'];
		//set
		if($jurusan == ""){
			$jurusan = 0;
		}
		if($data['prodi'] == ""){
			$prodi = 0;
		}else{
			$prodi = $data['prodi'];
		}
		if($data['lab'] == ""){
			$lab = 0;
		}else{
			$lab = $data['lab'];
		}

		//get jurusan, unit, lab
		if($tugas == 16 || $tugas == 15)
		{
			$jur_unit = $lab;
		}
		elseif($tugas == 14)
		{
			$jur_prodi = $this->jurusan_model->get_prodi_id($prodi);
			$jur_unit = $jur_prodi->jurusan;
		}
		else{
			$jur_unit = $jurusan;
		}
		$array = array(16,17,18,19,11);
		$check = $this->user_model->check_tugas_tambahan($data['user'],$tugas,$jur_unit,$prodi,$status);

		if(!empty($check)){
			redirect(site_url("admin/struktural/tugas/form?tugas=$tugas&status=duplikat"));
		}else{
			if(!in_array($tugas, $array)){
				$check_double =  $this->user_model->check_tugas_tambahan_duplikat($tugas,$jur_unit,$prodi,$status,$data['periode']);
				if(!empty($check_double)){
					$id_user_double = $check_double->id_user;
					redirect(site_url("admin/struktural/tugas/form?tugas=$tugas&status=duplikat_user&id=".$this->encrypt->encode($id_user_double)));
				}else{
					$data_tugas = array(
						'id_user' => $data['user'],
						'tugas' => $tugas,
						'jurusan_unit' => $jur_unit,
						'prodi' => $prodi,
						'periode' => $data['periode'],
						'aktif' => $status,
					);			
					$this->user_model->insert_tugas_tambah($data_tugas);
					
				}

			}else{
				$data_tugas = array(
					'id_user' => $data['user'],
					'tugas' => $tugas,
					'jurusan_unit' => $jur_unit,
					'prodi' => $prodi,
					'periode' => $data['periode'],
					'aktif' => $status,
				);			
				$this->user_model->insert_tugas_tambah($data_tugas);
			}
			redirect(site_url("admin/struktural/tugas/form?tugas=$tugas&status=sukses"));
		}
	}

	function aksi_tugas()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$tgs = $data['tugas'];
		if($data['aksi'] == 'nonaktif'){
			$this->user_model->nonaktifkan_tugas($data['id']);
		}else{
			$this->user_model->hapus_user_tugas($data['id']);
		}
		
		redirect(site_url("admin/struktural/tugas/form?tugas=".$tgs));
	}

	function user_registrasi()
	{
		$seg = $this->uri->segment(3);

		if($seg == 'dosen'){
			$role = 2;
			// $tbl = 'tbl_users_dosen';
		}elseif($seg == 'tendik'){
			$role = 4;
			// $tbl = 'tbl_users_tendik';
		}elseif($seg == 'mahasiswa'){
			$role = 3;
		}

		$data['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['user'] = $this->user_model->get_user_by_role($role);

		$this->load->view('header_global', $data);
		$this->load->view('admin/header');
		if($role == 3){
			$this->load->view('admin/user/tambah_user_mahasiswa');
		}else{
			$this->load->view('admin/user/tambah_user');
		}
		

        $this->load->view('footer_global');
	}

	function add_user_admin()
	{
		if($this->session->userdata('state') == 1) {

			$jns = $this->input->post('jns');

			$nama = ucwords(strtolower($this->security->xss_clean($this->input->post('nama'))));
			$email = strtolower($this->security->xss_clean($this->input->post('email')));
			$password = $this->input->post('password');
			$nip = $this->input->post('nip');
			$hp = $this->security->xss_clean($this->input->post('hp'));
			$status = $this->security->xss_clean($this->input->post('sts'));

			//tbl users
			$userInfo = array(
				'email' => $email,
				'password' => getHashedPassword($password),
				'roleId' => $status, // Default Mahasiswa
				'name' => $nama,
				'mobile' => $hp,
				'createdBy' => $this->session->userdata('userId'), // user
				'createdDtm' => date('Y-m-d H:i:s'),
				'is_active' => 1
			);

			$cek_email = $this->user_model->cek_email($email);
			if($cek_email)
			{
				$this->session->set_flashdata('error', 'Email Anda telah terdaftar!');
			}
			else
			{
				if($status == 2){
					//dosen
					$cek = $this->user_model->cek_nip_dosen($nip);

					$data_user = array(
						"nip_nik" => $nip,
						"nidn" => $this->input->post('nidn'),
						"gelar_depan" => $this->input->post('gelar_depan'),
						"gelar_belakang" => $this->input->post('gelar_belakang'),
						"id_sinta" => $this->input->post('sinta'),
						"jurusan" => $this->input->post('jurusan'),
						"pangkat_gol" => $this->input->post('pangkat'),
						"fungsional" => $this->input->post('jabfung')
					);
				}else{
					//tendik
					$cek = $this->user_model->cek_nip_tendik($nip);
					$data_user = array(
						"nip_nik" => $nip,
						"gelar_depan" => $this->input->post('gelar_depan'),
						"gelar_belakang" => $this->input->post('gelar_belakang'),
						"unit_kerja" => $this->input->post('jurusan'),
						"pangkat_gol" => $this->input->post('pangkat'),
					);
				}

				if($cek)
				{
					$this->session->set_flashdata('error', 'NIP / NIK Telah Terdaftar');
				}
				else{
					$result = $this->user_model->addNewUser2($userInfo, $data_user);

					if ($result > 0) {
						$this->session->set_flashdata('success', 'Berhasil Menambah User');
					} else {
						$this->session->set_flashdata('error', 'Gagal Mendaftar!');
					}

				}
			}
			redirect(site_url('admin/user/'.$jns));
		} else {
			echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
		}

		
	}

	function hapus_user()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id = $data['id'];
		$seg = $data['seg'];
		$aksi = $data['aksi'];

		if($this->session->userdata('state') == 1) {
			$this->user_model->delete_user_admin($id,$seg);
		}else{
			echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
		}

		redirect(site_url("admin/user/$seg?status=sukses"));
	}

	function edit_user_admin()
	{

		if($this->session->userdata('state') == 1) {
			$jns = $this->input->post('jns');

			$nama = ucwords(strtolower($this->security->xss_clean($this->input->post('nama'))));
			$email = strtolower($this->security->xss_clean($this->input->post('email')));
			$password = $this->input->post('password');
			$nip = $this->input->post('nip');
			$hp = $this->security->xss_clean($this->input->post('hp'));
			$status = $this->security->xss_clean($this->input->post('sts'));
			$id_user = $this->input->post('id');
			//tbl users

			if($password == ''){
				$userInfo = array(
					'email' => $email,
					'roleId' => $status, // Default Mahasiswa
					'name' => $nama,
					'mobile' => $hp,
					'updatedBy' => $this->session->userdata('userId'), // user
				);
			}else{
				$userInfo = array(
					'email' => $email,
					'password' => getHashedPassword($password),
					'roleId' => $status, // Default Mahasiswa
					'name' => $nama,
					'mobile' => $hp,
					'updatedBy' => $this->session->userdata('userId'), // user
				);
			}

			$cek_email = $this->user_model->cek_email_edit($email,$id_user);
			if($cek_email)
			{
				$this->session->set_flashdata('error', 'Email Anda telah terdaftar!');
			}
			else
			{
				if($status == 2){
					//dosen
					$cek = $this->user_model->cek_nip_dosen_edit($nip,$id_user);

					$data_user = array(
						"nip_nik" => $nip,
						"nidn" => $this->input->post('nidn'),
						"gelar_depan" => $this->input->post('gelar_depan'),
						"gelar_belakang" => $this->input->post('gelar_belakang'),
						"id_sinta" => $this->input->post('sinta'),
						"jurusan" => $this->input->post('jurusan'),
						"pangkat_gol" => $this->input->post('pangkat'),
						"fungsional" => $this->input->post('jabfung')
					);
				}else{
					//tendik
					$cek = $this->user_model->cek_nip_tendik_edit($nip,$id_user);

					$data_user = array(
						"nip_nik" => $nip,
						"gelar_depan" => $this->input->post('gelar_depan'),
						"gelar_belakang" => $this->input->post('gelar_belakang'),
						"unit_kerja" => $this->input->post('jurusan'),
						"pangkat_gol" => $this->input->post('pangkat'),
					);
				}

				if($cek)
				{
					$this->session->set_flashdata('error', 'NIP / NIK Telah Terdaftar');
				}
				else{
					// print_r($data_user);
					//edit
					$result = $this->user_model->updateNewUser2($userInfo, $data_user,$id_user);

					// if ($result > 0) {
						$this->session->set_flashdata('success', 'Berhasil Ubah Data User');
					// } else {
					// 	$this->session->set_flashdata('error', 'Gagal Edit!');
					// }

				}
			}
			redirect(site_url('admin/user/'.$jns));
		}else{
			echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
		}
		
	}

	function tambah_atribut()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$this->load->view('header_global', $header);
		$this->load->view('admin/header');

		$this->load->view('admin/fakultas/tambah_atribut');
		
		$this->load->view('footer_global');
	}

	function tambah_atribut_add()
	{
		$jns = $this->input->post('jns_layanan');

		if($jns == 'Akademik'){
			$form = $this->input->post('form');
		}
		elseif($jns == 'Umum dan Keuangan'){
			$form = $this->input->post('form2');
		}
		elseif($jns == 'Kemahasiswaan'){
			$form = $this->input->post('form3');
		}

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		$data['jenis'] = $jns;
		$data['form'] = $form;

		$this->load->view('header_global', $header);
		$this->load->view('admin/header');

		$this->load->view('admin/fakultas/tambah_atribut_tambah',$data);
		
		$this->load->view('footer_global');

	}

	function tambah_atribut_save()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		$id_form = $data['id_form'];
		$nama = $data['nama'];
		$ttd = $data['ttd'];

		$i = 0;
		foreach($nama as $row){
			$slug = str_replace(" ","-",strtolower($data['nama'][$i]));

			$input = array(
				"id_layanan" => $id_form,
				"slug" => $slug,
				"nama" => $data['nama'][$i],
				"placeholder" => $data['placeholder'][$i],
				"tipe" => $data['tipe'][$i],
				"pilihan" => $data['pilihan'][$i]
			);
			//input atribut layanan
			$this->layanan_model->insert_atribut_layanan($input);
			$i++;
		}

		//input approver
		if($ttd == ""){
			$ttd = null;
		}
		$this->layanan_model->insert_approver($id_form,$ttd);
		redirect(site_url("/admin/layanan-fakultas/form-atribut"));
	}

	function unit()
	{
		$seg = $this->uri->segment(3);

		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();
		
		$this->load->view('header_global', $header);
		$this->load->view('admin/header');

		if($seg == 'prodi'){
			$data['list'] = $this->jurusan_model->get_prodi_all(); 
			$this->load->view('admin/unit/unit_prodi',$data);	
		}elseif($seg == 'jurusan'){
			$data['list'] = $this->jurusan_model->get_jurusan_all(); 
			$this->load->view('admin/unit/unit_jurusan',$data);
		}elseif($seg == 'lab'){
			$data['list'] = $this->jurusan_model->get_lab_all();
			$this->load->view('admin/unit/unit_lab',$data);
		}elseif($seg == 'unit-tendik'){
			$data['list'] = $this->jurusan_model->get_unit_kerja_all();
			$this->load->view('admin/unit/unit_tendik',$data);
		}
		$this->load->view('footer_global');

	}

	function tambah_unit()
	{
		$data = $this->input->post();
	
		if($data['aksi'] == 'prodi'){
			$insert = array(
				"nama" => $data['prodi'],
				"jurusan" => $data['jurusan']
			);
			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			$this->jurusan_model->insert_prodi($insert);
			redirect(site_url("/admin/unit/prodi"));
		}
		elseif($data['aksi'] == 'jurusan')
		{
			$insert = array(
				"nama" => $data['jurusan'],
				"fakultas" => 1
			);
			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			//tbl jurusan
			$this->jurusan_model->insert_jurusan($insert);
			redirect(site_url("/admin/unit/jurusan"));
		}
		elseif($data['aksi'] == 'lab')
		{
			$insert = array(
				"nama_lab" => $data['lab'],
			);

			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			$this->jurusan_model->insert_lab($insert);
			redirect(site_url("/admin/unit/lab"));
		}
		elseif($data['aksi'] == 'unit')
		{
			$id = $this->jurusan_model->get_las_id_unit_kerja();
			$id = $id+1;
			$insert = array(
				"id_unit_kerja" => $id,
				"parent" => $data['jurusan'],
				"nama" => $data['unit']
			);

			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			$this->jurusan_model->insert_unit($insert);
			redirect(site_url("/admin/unit/unit-tendik"));
		}
	}

	function edit_unit()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);

		if($data['aksi'] == 'prodi'){
			$insert = array(
				"nama" => $data['prodi'],
				"jurusan" => $data['jurusan']
			);
			
			$this->jurusan_model->edit_prodi($data['id'],$insert);
			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			redirect(site_url("/admin/unit/prodi"));
		}elseif($data['aksi'] == 'jurusan'){
			$insert = array(
				"nama" => $data['jurusan'],
			);
			
			$this->jurusan_model->edit_jurusan($data['id'],$insert);
			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			redirect(site_url("/admin/unit/jurusan"));
		}elseif($data['aksi'] == 'lab'){
			$insert = array(
				"nama_lab" => $data['lab'],
			);
			
			$this->jurusan_model->edit_lab($data['id'],$insert);
			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			redirect(site_url("/admin/unit/lab"));
		}elseif($data['aksi'] == 'unit'){
			$insert = array(
				"parent" => $data['jurusan'],
				"nama" => $data['unit']
			);
			$this->jurusan_model->edit_unit($data['id'],$insert);
			$this->session->set_flashdata('success', 'Berhasil Ubah Data');
			redirect(site_url("/admin/unit/unit-tendik"));
		}
	}

	function tambah_form()
	{
		$header['akun'] = $this->user_model->select_by_ID($this->session->userdata('userId'))->row();

		$this->load->view('header_global', $header);
		$this->load->view('admin/header');
		$data['form'] = $this->layanan_model->get_layanan_order_bagian();
		$this->load->view('admin/fakultas/tambah_form',$data);
		
		$this->load->view('footer_global');
	}

	function save_form()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$insert = array(
			"bagian" => $data['bagian'],
			"nama" => $data['nama']
		);

		$this->layanan_model->insert_form_layanan_fakultas($insert);
		$this->session->set_flashdata('success', 'Berhasil Tambah Data');
		redirect(site_url("/admin/layanan-fakultas/tambah-form"));
	}
	
	function edit_form()
	{
		$data = $this->input->post();
		// echo "<pre>";
		// print_r($data);
		$id = $data['id'];
		$insert = array(
			"bagian" => $data['bagian'],
			"nama" => $data['nama']
		);
		$this->layanan_model->edit_form_layanan_fakultas($id,$insert);
		$this->session->set_flashdata('success', 'Berhasil Ubah Data');
		redirect(site_url("/admin/layanan-fakultas/tambah-form"));
	}
	
	
	public function list()
	{
		$data['list_peserta'] = $this->peserta_model->select_all()->result();
		$data['count_feb'] = $this->peserta_model->count_by_faculty('EB');
		$data['count_fh'] = $this->peserta_model->count_by_faculty('HUKUM');
		$data['count_fp'] = $this->peserta_model->count_by_faculty('Pertanian');
		$data['count_fkip'] = $this->peserta_model->count_by_faculty('KIP');
		$data['count_ft'] = $this->peserta_model->count_by_faculty('Teknik');
		$data['count_fisip'] = $this->peserta_model->count_by_faculty('ISIP');
		$data['count_fmipa'] = $this->peserta_model->count_by_faculty('MIPA');
		$data['count_fk'] = $this->peserta_model->count_by_faculty('Kedokteran');
		$data['count_pasca'] = $this->peserta_model->count_by_pasca();
	    $this->load->view('admin/header');
		$this->load->view('admin/report_fakultas', $data);
		$this->load->view('admin/footer');
	}

	public function data_unit($unit)
	{
	    if($unit == 'pasca') 
	    {
	        $data['list'] = $this->nilai_model->get_by_pasca();
	    }
	    else 
	    {
	        $data['list'] = $this->nilai_model->get_by_faculty($unit);
	    }
		
		//echo "<pre>";
		//print_r($data);
		$this->load->view('admin/header');
		$this->load->view('admin/list_unit', $data);
		$this->load->view('admin/footer');
	}

	public function data_unit_detail()
	{
	    if($this->uri->segment(2) == 'pasca')
	    {
	        $data['list_peserta'] = $this->nilai_model->get_by_pasca_year($this->uri->segment(3));
	    }
	    else 
	    {
	        $data['list_peserta'] = $this->nilai_model->get_by_faculty_year($this->uri->segment(2), $this->uri->segment(3));
	    }
		
		//echo "<pre>";
		//print_r($data);
		$this->load->view('admin/header');
		$this->load->view('admin/list', $data);
		$this->load->view('admin/footer');
	}
	
	public function upload()
	{
	    $this->load->view('admin/header');
		$this->load->view('admin/upload');
		$this->load->view('admin/footer');
	}
	
	public function user()
	{
	    if($this->session->userdata('state') == 1) {
    	    $data['list_user'] = $this->user_model->select_all()->result();
    	    $this->load->view('admin/header');
    		$this->load->view('admin/user', $data);
    		$this->load->view('admin/footer');
	    } else {
	        echo "<script>alert('Aksesaaa ditolak!!');javascript:history.back();</script>";
	    }
	}
	
	public function add_user()
	{
	    if($this->session->userdata('state') == 'Administrator') {
    	    $data['user'] = array();
    	    $this->load->view('admin/header');
    		$this->load->view('admin/add_user', $data);
    		$this->load->view('admin/footer');
	    } else {
	        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
	    }
	}
	
	public function edit_user()
	{
	    if($this->session->userdata('state') == 'Administrator') {
    	    $data['user'] = $this->user_model->select_by_ID($this->uri->segment(2))->row();
    	    $this->load->view('admin/header');
    		$this->load->view('admin/add_user', $data);
    		$this->load->view('admin/footer');
	    } else {
	        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
	    }
	}
	
	public function detail_peserta()
	{
	    
	    $data['peserta'] = $this->peserta_model->select_by_ID($this->uri->segment(2))->row();
	    $data['nilai'] = $this->nilai_model->select_by_user_ID($this->uri->segment(2))->result();
	    
	    $this->load->view('admin/header');
		$this->load->view('admin/detail_peserta', $data);
		$this->load->view('admin/footer');
	}
	
	public function add_item_pelanggaran()
	{
	    $data['personil'] = $this->personil_model->select_by_nrp($this->uri->segment(2))->row();
	    $this->load->view('admin/header');
		$this->load->view('admin/item_pelanggaran', $data);
		$this->load->view('admin/footer');
	}
	
	public function edit_item_pelanggaran()
	{
	    $data['personil'] = $this->personil_model->select_by_nrp($this->uri->segment(2))->row();
	    $data['pelanggaran'] = $this->nilai_model->select_by_ID($this->uri->segment(3))->row();
	    $this->load->view('admin/header');
		$this->load->view('admin/item_pelanggaran', $data);
		$this->load->view('admin/footer');
	}
	
	public function do_upload(){
	    echo "<pre>";
	    print_r($_POST);
	    print_r($_FILES);
	}
	
	public function edit_personil() {
	    //echo "<pre>";
	    
	    
	    $data = array(
			'nama'		=>	$this->input->post('nama'),
			'pangkat'	=>	$this->input->post('pangkat'),
			'jabatan'	=>	$this->input->post('jabatan')
		);
		
		//print_r($_POST);
		$this->personil_model->update($data, $this->input->post('nrp'));
		
		redirect(site_url('ubah-pelanggaran/'.$this->input->post('nrp')));
	}
	
	public function aksi_tambah_pelanggaran() {
	    //echo "<pre>";
	    //print_r($_POST);
	    $data = array(
	        'nrp' => $this->input->post('nrp'),
	        'no_putusan' => $this->input->post('no_putusan'),
	        'tgl_no_putusan' => $this->input->post('tgl_no_putusan'),
	        'waktu' => $this->input->post('waktu'),
	        'tempat' => $this->input->post('tempat'),
	        'jenis_pelanggaran' => str_replace("\r\n","<br />",$this->input->post('jenis')),
	        'jenis_hukuman' => str_replace("\r\n","<br />",$this->input->post('jenis_hukuman')),
	        'batas_waktu' => str_replace("\r\n","<br />",$this->input->post('batas')),
	        'keterangan' => str_replace("\r\n","<br />",$this->input->post('ket')),
	        'user' => '1'
	        );
	        
	   $this->nilai_model->replace($data);
	   
	   redirect(site_url('ubah-pelanggaran/'.$this->input->post('nrp')));
	}
	
	public function aksi_ubah_pelanggaran() {
	    //echo "<pre>";
	    //print_r($_POST);
	    $data = array(
	        'no_putusan' => $this->input->post('no_putusan'),
	        'tgl_no_putusan' => $this->input->post('tgl_no_putusan'),
	        'waktu' => $this->input->post('waktu'),
	        'tempat' => $this->input->post('tempat'),
	        'jenis_pelanggaran' => str_replace("\r\n","<br />",$this->input->post('jenis')),
	        'jenis_hukuman' => str_replace("\r\n","<br />",$this->input->post('jenis_hukuman')),
	        'batas_waktu' => str_replace("\r\n","<br />",$this->input->post('batas')),
	        'keterangan' => str_replace("\r\n","<br />",$this->input->post('ket')),
	        'user' => '1'
	        );
	        
	   $this->nilai_model->update($data, $this->input->post('ID'));
	   
	   redirect(site_url('ubah-pelanggaran/'.$this->input->post('nrp')));
	}
	
	public function hapus_pelanggaran() {

	    $ID = $this->input->post('pelanggaranID');
	    $data = array("ID" => $ID);
	    $this->nilai_model->delete($data);
	    
	    redirect(site_url('ubah-pelanggaran/'.$this->input->post('nrpPersonil')));
	    
	}
	
	public function aksi_tambah_pengguna() {
	    if($this->session->userdata('state') == 'Administrator') {
    	    //echo "<pre>";
    	    //print_r($_POST);
    	    $data = array(
    	        'username' => $this->input->post('username'),
    	        'password' => md5($this->input->post('password')),
    	        'nama_lengkap' => $this->input->post('name'),
    	        'role' => $this->input->post('state')
    	        );
    	        
    	   $this->user_model->insert($data);
    	   
    	   redirect(site_url('kelola-pengguna'));
	    } else {
	        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
	    }
    	  
	}
	
	public function aksi_ubah_pengguna() {
	    if($this->session->userdata('state') == 'Administrator') {
    	    //echo "<pre>";
    	    //print_r($_POST);
    	    $data = array(
    	        'username' => $this->input->post('username'),
    	        'password' => md5($this->input->post('password')),
    	        'nama_lengkap' => $this->input->post('name'),
    	        'role' => $this->input->post('state')
    	        );
    	        
    	   $this->user_model->update($data, $this->input->post('username_lama'));
    	   
    	   redirect(site_url('kelola-pengguna'));
	    } else {
	        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
	    }
	}
	
	public function hapus_pengguna() {
	    if($this->session->userdata('state') == 'Administrator') {
    	    $ID = $this->input->post('userID');
    	    $data = array("ID" => $ID);
    	    $this->user_model->delete($data);
    	    redirect(site_url('kelola-pengguna'));
	    } else {
	        echo "<script>alert('Akses ditolak!!');javascript:history.back();</script>";
	    }
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
	
	public function print2() {
	    $today = date("Y-m-d-His");
        header("Content-type: application/vnd.ms-word");
        header("Content-Disposition: attachment;Filename=Data-pelanggaran-".$today.".doc");
	    ?>
	    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<meta name=ProgId content=Word.Document>
<meta name=Generator content="Microsoft Word 9">
<meta name=Originator content="Microsoft Word 9">
<style>
@page Section1 {size:595.45pt 841.7pt; margin:1.0in 1.25in 1.0in 1.25in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
div.Section1 {page:Section1;}
@page Section2 {size:841.7pt 595.45pt;mso-page-orientation:landscape;margin:1.25in 1.0in 1.25in 1.0in;mso-header-margin:.5in;mso-footer-margin:.5in;mso-paper-source:0;}
div.Section2 {page:Section2;}

table {
  border-collapse: collapse;
  font-family: 'Arial';
    font-size: 11pt;
}

table, th, td {
  border: 1px solid black;
}

thead td {
    text-align: center;
}
</style>
</head>
<body>
    <div class=Section2>
	    <?php
	    echo "<table border='0' cellspacing='0' cellpadding='4'>";
	    echo "<thead>";
	    echo "<tr>";
	    echo "<td>NO</td>";
	    echo "<td>IDENTITAS PELANGGAR</td>";
	    echo "<td>WAKTU DAN TEMPAT GAR</td>";
	    echo "<td>JENIS PELANGGARAN</td>";
	    echo "<td>JENIS HUKUMAN</td>";
	    echo "<td>NO. PUTUSAN HUKUMAN</td>";
	    echo "<td>BATAS WAKTU PELAKSANAAN HUKUMAN</td>";
	    echo "<td>KET</td>";
	    echo "</tr>";
	    echo "<tr>";
	    echo "<td>1</td>";
	    echo "<td>2</td>";
	    echo "<td>3</td>";
	    echo "<td>4</td>";
	    echo "<td>5</td>";
	    echo "<td>6</td>";
	    echo "<td>7</td>";
	    echo "<td>8</td>";
	    echo "</tr>";
	    echo "</thead>";
	    
	    
	    
	    echo "<tbody>";
        $no = 0;
        foreach($_POST['cekNrp'] as $row) {
            $no++;
	        $personil = $this->personil_model->select_by_nrp($row)->row();
	        $pelanggaran = $this->nilai_model->select_by_nrp($row)->result();
	        
	        //print_r($personil);
	        //print_r($pelanggaran);
	        //echo "<br>";
	        if(!empty($pelanggaran)) {
	            $bio = $personil->nama."<br><br>".$personil->pangkat." / ".$personil->nrp."<br><br>".$personil->jabatan;
	            if(sizeof($pelanggaran) > 0) {
	                $idx = 1;
	                foreach($pelanggaran as $res) {
	                    echo "<tr>";
	                    if($idx == 1) {
	                        echo "<td align='center' valign='top' rowspan='".sizeof($pelanggaran)."'>".$no.".</td>";
	                        echo "<td valign='top' rowspan='".sizeof($pelanggaran)."'>".$bio."</td>";
	                        //$table->addCell(680.31, array('borderBottomSize' => 1))->addText($no, [], array('align' => 'center'));
	                        //$table->addCell(2834.65, array('borderBottomSize' => 1))->addText($bio);
	                    } else {
	                        //$table->addCell(680.31, array('borderBottomSize' => 1, 'borderTopSize' => 1));
	                        //$table->addCell(2834.65, array('borderBottomSize' => 1, 'borderTopSize' => 1));
	                    }
	                    echo "<td valign='top'>".$res->tempat.", tanggal ".$res->waktu."</td>";
	                    echo "<td valign='top'>".$res->jenis_pelanggaran."</td>";
	                    echo "<td valign='top'>".$res->jenis_hukuman."</td>";
	                    echo "<td valign='top'>Putusan sidang KKEP nomor:<br>(".$res->no_putusan.")</td>";
	                    echo "<td valign='top'>".$res->batas_waktu."</td>";
	                    echo "<td valign='top'>".$res->keterangan."</td>";
	                    
	                    $idx++;
	                    echo "</tr>";
	                }
	            } else {
	                echo "<tr>";
	                echo "<td align='center' valign='top'>".$no.".</td>";
	                echo "<td valign='top'>".$bio."</td>";
	                echo "<td valign='top'>".$res->tempat.", tanggal ".$res->waktu."</td>";
	                echo "<td valign='top'>".$res->jenis_pelanggaran."</td>";
	                echo "<td valign='top'>".$res->jenis_hukuman."</td>";
	                echo "<td valign='top'>Putusan sidang KKEP nomor:\n(".$res->no_putusan.")</td>";
	                echo "<td valign='top'>".$res->batas_waktu."</td>";
	                echo "<td valign='top'>".$res->keterangan."</td>";
	                echo "</tr>";
	            }
	            
	            /*
	            $idx = 1;
	            foreach($pelanggaran as $res) {
	                if($idx == 1) {
	                    $number = $no.".";
	                    $bio = $personil->nama."\n\n".$personil->pangkat." / ".$personil->nrp."\n\n".$personil->jabatan;
	                } else {
	                    $number = "";
	                    $bio = "";
	                }
	                
	                $table->addRow();
                    $table->addCell(680.31)->addText($number, [], array('align' => 'center'));
                    $table->addCell(2834.65)->addText($bio);
                    $table->addCell(1530.71)->addText($res->tempat.", tanggal ".$res->waktu);
                    $table->addCell(2834.65)->addText($res->jenis_pelanggaran);
                    $table->addCell(1984.25)->addText($res->jenis_hukuman);
                    $table->addCell(1417.32)->addText("Putusan sidang KKEP nomor:\n(".$res->no_putusan.")");
                    $table->addCell(1700.79)->addText($res->batas_waktu);
                    $table->addCell(1587.4)->addText($res->keterangan);
	                
	                
    	           $idx++;
	            }
	            */
	            
	        } else {
	            echo "<tr>";
	                echo "<td align='center' valign='top'>".$no.".</td>";
	                echo "<td>".$personil->nama."<br><br>".$personil->pangkat." / ".$personil->nrp."<br><br>".$personil->jabatan."</td>";
	                echo "<td align='center'>NIHIL</td>";
	                echo "<td align='center'>NIHIL</td>";
	                echo "<td align='center'>NIHIL</td>";
	                echo "<td align='center'>NIHIL</td>";
	                echo "<td align='center'>NIHIL</td>";
	                echo "<td align='center'>NIHIL</td>";
	                echo "</tr>";
	            
	            
	            
	        }
	    }
        
        echo "</tbody>";
	    echo "</table>";
	    echo "</table>";
	    echo "<br>";
	    echo "<table width='100%' border='0'>";
	    echo "<tr>";
	    echo "<td style=' border: none;' width='70%'>&nbsp;</td>";
	    echo "<td style=' border: none;' width='30%'>Bandar Lampung, .............................. ".date('Y')."<br>KABAG<br><br><br><br>AKBP<br>NRP. </td>";
	    echo "</tr>";
	    echo "</table>";
	    echo "</div>";
	    echo "</body>";
	    
	    
	}
}
