<?php
class Parameter_model extends CI_Model
{
	function select_agama()
	{
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get('agama');
		return $query->result();
	}

	function select_asal_sekolah()
	{
		$this->db->order_by('id_asal_sekolah', 'ASC');
		$query = $this->db->get('asal_sekolah');
		return $query->result();
	}

	function select_jalur_masuk()
	{
		$this->db->order_by('id_jalur_masuk', 'ASC');
		$query = $this->db->get('jalur_masuk');
		return $query->result();
	}

	function select_jenis_berkas()
	{
		$this->db->order_by('nama', 'ASC');
		$query = $this->db->get('jenis_berkas_lampiran');
		return $query->result();
	}

	//edit raihan
	function select_bidang_ilmu($jur,$pro)
	{
		$this->db->where(array("jurusan" => $jur,"prodi" => $pro));
		$this->db->order_by('nama', 'ASC');
		$query = $this->db->get('bidang_ilmu');
		return $query->result();
	}

	// D3 Ilmu Komputer 
	function get_bidang_ilmu_ta(){
		$this->db->select('*'); 
		$this->db->from('bidang_ilmu');
		$this->db->where('jurusan', '5');
		$this->db->where('prodi','11');

		$query = $this->db->get();
		return $query->result();
	}

	function check_bidang_ilmu_ta($id)
	{
		$this->db->select('*'); 
		$this->db->from('verifikasi_ta_komponen');
		$this->db->where('bidang', $id);

		$query = $this->db->get();
		return $query->result();
	}
	
	//berkas lampiran instansi
	function select_jenis_berkas_instansi()
	{
		$this->db->select('*'); 
		$this->db->from('jenis_berkas_lampiran');
		$this->db->where('id_jenis', '16');

		$query = $this->db->get();
		return $query->result();
	}

	//berkas lampiran lab
	function select_jenis_berkas_lab()
	{
		$this->db->select('*'); 
		$this->db->from('jenis_berkas_lampiran');
		$this->db->where('id_jenis', '17');

		$query = $this->db->get();
		return $query->result();
	}

	function get_lab_by_nama($nama)
	{
		$result = $this->db->query("SELECT * FROM `laboratorium` WHERE nama_lab LIKE '%$nama%'");
		return $result->row();
	}

	function get_month($month)
    {
        $bulan = "";
        
        if($month == '01' || $month == 'January'){
            $bulan = "Januari";
        }
        
        elseif($month == '02' || $month == 'February'){
            $bulan = "Februari";
        }
        
         elseif($month == '03' || $month == 'March'){
            $bulan = "Maret";
        }
        
         elseif($month == '04'  || $month == 'April'){
            $bulan = "April";
        }
        
         elseif($month == '05'  || $month == 'May'){
            $bulan = "Mei";
        }
        
         elseif($month == '06'  || $month == 'June'){
            $bulan = "Juni";
        }
        
         elseif($month == '07'  || $month == 'July'){
            $bulan = "Juli";
        }
        
         elseif($month == '08'  || $month == 'August'){
            $bulan = "Agustus";
        }
        
         elseif($month == '09'  || $month == 'September'){
            $bulan = "September";
        }
        
         elseif($month == '10'  || $month == 'October'){
            $bulan = "Oktober";
        }
        
         elseif($month == '11'  || $month == 'November'){
            $bulan = "November";
        }
        
        else{
            $bulan = "Desember";
        }
        
        return $bulan;
    }
	
}