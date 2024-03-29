

<div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-note icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <div>Kelola Tema Penelitian
                                        <div class="page-title-subheading">
                                        </div>
                                    </div>
                                </div>
                                <?php if(empty($status_ta)) { ?>
                                <div class="page-title-actions">
                                    <a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/form") ?>" class="btn-shadow btn btn-success">
                                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                                <i class="fas fa-file fa-w-20"></i>
                                            </span>
                                            Form Pengajuan Tema
                                    </a>
                                </div>
                                <?php } ?>
                                
                            </div>
                        </div> <!-- app-page-title -->
                        <?php
                        // debug
                        //echo "<pre>";
                        //print_r($biodata);
                        //echo "</pre>";
                        if(!empty($_GET['status']) && $_GET['status'] == 'sukses') {

                            echo '<div class="alert alert-success fade show" role="alert">Biodata Anda sudah diperbarui, jangan lupa untuk memperbarui <a href="javascript:void(0);" class="alert-link">Akun</a> sebelum menggunakan layanan.</div>';
                        }
                        ?>
                        
                         <div class="main-card mb-3 card">
                                <div class="card-body">
                                <div class="table-responsive">
                                    <table class="mb-0 table table-striped" id="example">
                                        <thead>
                                        <tr>
                                            <th style="width: 28%;">JUDUL</th>
                                            <th style="width: 19%;">PEMBIMBING</th>
                                            <th style="width: 19%;">PEMBAHAS</th>
                                            <th style="width: 14%;">LAMPIRAN</th>
                                            <th style="width: 13%;">STATUS</th>
                                            <th style="width: 7%;">AKSI</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if(empty($ta))
                                        {
                                            echo "<tr><td colspan='6'>Data tidak tersedia</td></tr>";
                                        }
                                        else
                                        {
                                            foreach($ta as $row) {
                                        ?>
                                        <!-- <?php 
                                        echo '<img src="'.$row->ttd.'" />';
                                        // echo $row->ttd;
                                        ?> -->
                                        <tr>
                                            <?php if($row->status == 4){ ?>
                                                <?php if($row->judul_approve == 1) {?>
                                                        <td class="align-top"><p><b>Judul Utama:</b><br><span class="text-primary"><?php echo $row->judul1 ?></span></p>
                                                        <p><b>Judul Alternatif:</b><br><del><span class="text-danger"><i><?php echo $row->judul2 ?></i></span></del></p>
                                                        </td>
                                                <?php } elseif($row->judul_approve == 2){  ?>            
                                                    <td class="align-top"><p><b>Judul Utama:</b><br><del><span class="text-primary"><?php echo $row->judul1 ?></span></del></p>
                                                        <p><b>Judul Alternatif:</b><br><span class="text-danger"><i><?php echo $row->judul2 ?></i></span></p>
                                                    </td>
                                            <?php
                                            } }
                                            else{ ?>
                                                <td class="align-top"><p><b>Judul Utama:</b><br><span class="text-primary"><?php echo $row->judul1 ?></span></p>
                                                <p><b>Judul Alternatif:</b><br><span class="text-danger"><i><?php echo $row->judul2 ?></i></span></p>
                                                </td>
                                            <?php }?>

                                            <td class="align-top">
                                            <?php
                                            if($row->status == -1 || $row->status == 0 || $row->status == 1 || $row->status == 2 || $row->status == 3 || $row->status == 5 || $row->status == 6)
                                            {
                                                $dosen_pmb = $this->user_model->get_dosen_name($row->pembimbing1);
                                                echo $dosen_pmb->gelar_depan." ".$dosen_pmb->name.", ".$dosen_pmb->gelar_belakang;
                                                echo "<br><i>(Calon pembimbing utama)</i>";
                                            }
                                            elseif($row->status == 4 || $row->status == 8 || $row->status == 7 || $row->status == 9 || $row->status == -2)
                                            {
                                                $komisi_pembimbing = $this->ta_model->get_pembimbing_ta($row->id_pengajuan);
                                                
                                                // echo "<b>Pembimbing Utama :</b> <br>";
                                                // echo $dosen_pmb->gelar_depan." ".$dosen_pmb->name.", ".$dosen_pmb->gelar_belakang;
                                                
                                                foreach($komisi_pembimbing as $kom) {
                                                    $gelar = $this->user_model->get_gelar_dosen_nip($kom->nip_nik);
                                                    if(empty($gelar)){
                                                        $g_depan = "";
                                                        $g_belakang = "";
                                                    }
                                                    else{
                                                        $g_depan = $gelar->gelar_depan;
                                                        $g_belakang = $gelar->gelar_belakang;
                                                    }

                                                    echo "<b>$kom->status</b><br>";
                                                    echo $g_depan.$kom->nama.$g_belakang."<br>";
                                                    echo "$kom->nip_nik<br>";
                                                }

                                                // echo "<br><i>(Calon pembimbing utama)</i>";
                                            }
                                            ?>
                                            </td>
                                            <td class="align-top">
                                            <?php
                                            if($row->status == -1 || $row->status == 0 || $row->status == 1 || $row->status == 2 || $row->status == 3 || $row->status == 5) {
                                                echo "<i>(Belum disetujui)</i>";
                                            }
                                            elseif($row->status == 4 || $row->status == 8 || $row->status == 7 || $row->status == 9 || $row->status == -2)
                                            {
                                                $komisi_penguji = $this->ta_model->get_penguji_ta($row->id_pengajuan);
                                                
                                                // echo "<b>Pembimbing Utama :</b> <br>";
                                                // echo $dosen_pmb->gelar_depan." ".$dosen_pmb->name.", ".$dosen_pmb->gelar_belakang;
                                                
                                                foreach($komisi_penguji as $kom) {
                                                    $gelar = $this->user_model->get_gelar_dosen_nip($kom->nip_nik);
                                                    if(empty($gelar)){
                                                        $g_depan = "";
                                                        $g_belakang = "";
                                                    }
                                                    else{
                                                        $g_depan = $gelar->gelar_depan;
                                                        $g_belakang = $gelar->gelar_belakang;
                                                    }

                                                    echo "<b>$kom->status</b><br>";
                                                    echo $g_depan.$kom->nama.$g_belakang."<br>";
                                                    echo "$kom->nip_nik<br>";
                                                }

                                                if($row->jenis == "Tugas Akhir"){
                                                    echo "<br>";
                                                    $verifikator = $this->ta_model->get_dosen_verifikator($row->id_pengajuan);

                                                    $gelarv = $this->user_model->get_gelar_dosen_nip($verifikator->nip_nik);
                                                    if(empty($gelarv)){
                                                        $g_depanv = "";
                                                        $g_belakangv = "";
                                                    }
                                                    else{
                                                        $g_depanv = $gelarv->gelar_depan;
                                                        $g_belakangv = $gelarv->gelar_belakang;
                                                    }

                                                    echo "<b>Dosen Verifikasi TA</b><br>";
                                                    echo "$g_depanv.$verifikator->nama.$g_belakangv<br>";
                                                    echo "$verifikator->nip_nik<br>";
                                                }
                                            }
                                            ?>
                                            </td>
                                            <td class="align-top">
                                            <?php
                                            $lampiran = $this->ta_model->select_lampiran_by_ta($row->id_pengajuan, $this->session->userdata('username'));
                                            if(empty($lampiran)) {
                                                echo "<i>(Belum ada, silakan lengkapi berkas lampiran)</i>";
                                            } else {
                                                echo "<ul style='margin-left: -20px;'>";
                                                if($row->status >= 0 && $row->status != 6 && $row->status != 5 || $row->status == -2){
                                                    echo "<li><a href=".site_url("mahasiswa/tugas-akhir/tema/form_pdf?jenis=pengajuan_bimbingan&id=$row->id_pengajuan").">Form Pengajuan</a></li>";   
                                                }
                                                if($row->status >= 3 && $row->status != 6 && $row->status != 5 || $row->status == -2){
                                                    echo "<li><a href=".site_url("mahasiswa/tugas-akhir/tema/form_pdf?jenis=form_verifikasi&id=$row->id_pengajuan").">Form Verifikasi</a></li>";   
                                                }
                                                if($row->status == 4 || $row->status == -2 ){
                                                    echo "<li><a href=".site_url("mahasiswa/tugas-akhir/tema/form_pdf?jenis=form_penetapan&id=$row->id_pengajuan").">Form Penetapan</a></li>";   
                                                }
                                                foreach($lampiran as $rw) {
                                                    echo "<li><a href='".base_url($rw->file)."' download>".$rw->nama_berkas."</a></li>";
                                                }

                                                echo "</ul>";
                                            }
                                            ?>
                                            </td>
                                            <td class="align-top">
                                            <?php
                                            if($row->status == 0) {
                                                echo '<i>Menunggu Approval</i>';
                                            }

                                            if($row->status == '-2') {
                                                echo '<i>Ganti Komisi Pembimbing/Penguji</i>';
                                            }

                                            if($row->status == '-1') {
                                                echo '<i>Belum diajukan</i>';
                                            }

                                            if($row->status == '1') {
                                                echo '<i>ACC Pembimbing Akademik</i>';
                                            }

                                            if($row->status == '2') {
                                                echo '<i>ACC Pembimbing Utama & Pembimbing Akademik</i>';
                                            }

                                            if($row->status == '3') {
                                                echo '<i>Berkas Diverifikasi</i>';
                                            }

                                            if($row->status == '4') {
                                                echo '<i>Disetujui</i>';
                                            }

                                            if($row->status == '7') {
                                                echo '<i>Acc Koordinator</i>';
                                            }

                                            if($row->status == '8') {
                                                echo '<i>Acc Pembimbing & Pembahas<br><br></i>';
                                                echo '<i>Menunggu Acc Ketua Jurusan</i>';
                                            }

                                            if($row->status == '5') {
                                                echo '<i>Perbaikan</i>';
                                                $ket = explode("###",$row->keterangan_tolak);
                                                echo "<br><br><span  style='color:red'>".$ket[1]."</span>";
                                                $ket_status = $ket[0];
                                            }

                                            if($row->status == '6') {
                                                echo '<i>Pengajuan Ditolak, Silahkan lakukan pengajuan ulang</i>';
                                                $ket = explode("###",$row->keterangan_tolak);
                                                echo "<br><br><span  style='color:red'>".$row->keterangan_tolak."</span>";
                                            }

                                            if($row->status == '9') {
                                                echo '<i>Menunggu Acc Kaprodi</i>';
                                            }
                                            ?>
                                            </td>
                                            <td class="align-top">
                                            <?php

                                            if(!empty($lampiran) && $row->status == '-1') { ?>
                                                <a data-toggle = "modal" data-id="<?php echo $row->id_pengajuan ?>" class="passingID2" >
                                                            <button type="button" class="btn-wide mb-1 btn btn-primary btn-sm btn-block"  data-toggle="modal" data-target="#Ajukan">
                                                                Ajukan <?php  ?>
                                                            </button>
                                                </a>
                                            <?php } ?>

                                            <?php if($row->status == -1) { ?>
                                                <a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/form?aksi=ubah&id=".$this->encrypt->encode($row->id_pengajuan)) ?>" class="btn-wide mb-2 btn btn-warning btn-sm btn-block">Ubah
                                                </a>
                                                <a data-toggle = "modal" data-id="<?php echo $row->id_pengajuan ?>" class="passingID" >
                                                            <button type="button" class="btn mb-2 btn-wide btn-danger btn-sm btn-block"  data-toggle="modal" data-target="#delPengajuan">
                                                                Hapus 
                                                            </button>
                                                </a>
                                                <a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/lampiran?id=".$this->encrypt->encode($row->id_pengajuan)) ?>" class="btn-wide mb-2 btn btn-focus btn-sm btn-block">Unggah Lampiran
                                                </a>
                                        <?php } 
                                        elseif($row->status == 5){ ?>
                                                <a data-toggle = "modal" data-id="<?php echo $row->id_pengajuan ?>" ket_status = "<?php echo $ket_status ?>" class="passingIDPerbaikan" >
                                                            <button type="button" class="btn-wide mb-1 btn btn-primary btn-sm btn-block"  data-toggle="modal" data-target="#AjukanPerbaikan">
                                                                Ajukan Perbaikan<?php  ?>
                                                            </button>
                                                </a>

                                                <a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/form?aksi=ubah&id=".$this->encrypt->encode($row->id_pengajuan)) ?>" class="btn-wide mb-2 btn btn-warning btn-sm btn-block">Ubah
                                                </a>

                                                <a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/lampiran?id=".$this->encrypt->encode($row->id_pengajuan)) ?>" class="btn-wide mb-2 btn btn-focus btn-sm btn-block">Unggah Lampiran
                                                </a>

                                        <?php    
                                        }
                                        elseif($row->status == 4 || $row->status == 6){echo"Selesai";}
                                        elseif($row->status == 0){echo "Menunggu";}

                                        else{echo "Menunggu";} ?>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        }
                                        ?>
                                        
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                            </div>
<script src="<?php echo site_url("assets/scripts/jquery_3.4.1_jquery.min.js") ?>"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.js"></script>
<script src="<?php echo site_url("assets/scripts/dataTables.bootstrap4.min.js") ?>"></script>
<script src="<?php echo site_url("assets/scripts/DataTables-1.10.21/jquery.dataTables.min.js") ?>"></script>
<script type="text/javascript">
$(document).ready(function(){
    $("select").select2({
        theme: "bootstrap"
    });
    $.ajaxSetup({
        type:"POST",
        url: "<?php echo site_url('mahasiswa/ambil_data') ?>",
        cache: false,
    });

    $("#provinsi").change(function(){
        var value=$(this).val();
        
        if(value>0){
            $.ajax({
                data:{modul:'kabupaten',id:value},
                success: function(respond){
 
                    $("#kota-kabupaten").html(respond);
                }
            })
        }

    });

    $("#kota-kabupaten").change(function(){
        var value=$(this).val();
        
        if(value>0){
            $.ajax({
                data:{modul:'kecamatan',id:value},
                success: function(respond){
                    
                    $("#kecamatan").html(respond);
                }
            })
        }

    });

    $("#kecamatan").change(function(){
        var value=$(this).val();
        
        if(value>0){
            $.ajax({
                data:{modul:'kelurahan',id:value},
                success: function(respond){
                    
                    $("#kelurahan-desa").html(respond);
                }
            })
        }

    });
});

$(document).ready(function() {
    $('#example').DataTable();
} );

</script>

<script>
    $(".passingID").click(function () {
                var id = $(this).attr('data-id');
                $("#ID").val( id );

            });

    $(".passingID2").click(function () {
                var id = $(this).attr('data-id');
                $("#ID2").val( id );

    });  

    $(".passingIDPerbaikan").click(function () {
                var id = $(this).attr('data-id');
                var status = $(this).attr('ket_status');
                $("#IDPerbaikan").val( id );
                $("#Status").val( status );

    });  
              
     
</script>