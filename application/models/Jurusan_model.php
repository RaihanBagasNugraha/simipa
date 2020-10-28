<?php
class Jurusan_model extends CI_Model
{
	function select_all()
	{
	    $this->db->where('fakultas', '1');
		$this->db->order_by('id_jurusan', 'ASC');
		$query = $this->db->get('jurusan');
		return $query;
	}

	function select_prodi_by_jur($jur)
	{
		$this->db->where('jurusan', $jur);
		$this->db->order_by('id_prodi', 'ASC');
		$query = $this->db->get('prodi')->result();
		return $query;
	}

	//edit raihan
	function get_jurusan_all()
	{
		$this->db->where('fakultas', '1');
		$this->db->order_by('id_jurusan', 'ASC');
		$query = $this->db->get('jurusan')->result();
		return $query;
	}

	function get_prodi_id($id)
	{
		$result = $this->db->query("SELECT * FROM prodi WHERE id_prodi = $id");
		return $result->row();
	}
	
	
}