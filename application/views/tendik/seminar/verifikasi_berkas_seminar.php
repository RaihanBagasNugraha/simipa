

<div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-note icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <div>Verifikasi Berkas
                                        <div class="page-title-subheading">Verifikasi Pengajuan Berkas Administrasi Jurusan
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="page-title-actions">
                                    <a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/form") ?>" class="btn-shadow btn btn-success">
                                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                                <i class="fas fa-file fa-w-20"></i>
                                            </span>
                                            Form Pengajuan Tema
                                    </a>
                                </div> -->
                                
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
                        <?php 
                        if ($akun->ttd == NULL){
                            echo "<script>
                            alert('Silahkan Lengkapi Informasi Akun & Biodata Anda Terlebih Dahulu');
                            window.location.href='akun';
                            </script>";
                        } 
                        
                        ?>
                        
                         <div class="main-card mb-3 card">
                                <div class="card-body">
                                <div class="table-responsive">
                                    <table class="mb-0 table table-striped">
                                        <thead>
                                        <tr>
                                            <th>Jenis</th>
                                            <th>NPM</th>
                                            <th>Pelaksanaan</th>
                                            
                                            <th>Berkas Lampiran</th>
                                           
                                            <th>Aksi</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if(empty($seminar))
                                        {
                                            echo "<tr><td colspan='6'>Data tidak tersedia</td></tr>";
                                        }
                                        else
                                        {
                                            foreach($seminar as $row) {
                                        ?>
                                            <tr>
                                              <td class="align-top"><b><?php echo $row->jenis;?></b></td>
                                              <td class="align-top"><?php echo $row->npm;?></td>
                                              <td class="align-top"><?php echo "$row->tempat<br>$row->tgl_pelaksanaan<br>$row->waktu_pelaksanaan<br>";  ?></td>
                                              <td class="align-top">
                                              <?php 
                                                $lampiran = $this->ta_model->select_lampiran_by_seminar($row->id);
                                                    if(empty($lampiran)) {
                                                        echo "<i>(Belum ada, silakan lengkapi berkas lampiran)</i>";
                                                    } else {
                                                        echo "<ul style='margin-left: -20px;'>";
                                                        foreach($lampiran as $rw) {
                                                        $nama_berkas = $this->ta_model->get_berkas_name($rw->jenis_berkas);
                                                        echo "<li><a href='".base_url($rw->file)."' download>".$nama_berkas."</a></li>";
                                                        }
                    
                                                        echo "</ul>";
                                                    }
                                                ?>
                                              </td>
                                              <td class="align-top">
                                              <a href="<?php echo site_url("tendik/verifikasi-berkas/seminar/form?&id=".$row->id) ?>" class="btn-wide mb-1 btn btn-primary btn-sm btn-block">Setujui
                                                </a>
                                                
                                                <a data-toggle = "modal" data-id="<?php echo $row->id ?>" class="passingID6" >
                                                            <button type="button" class="btn mb-2 btn-wide btn-danger btn-sm btn-block"  data-toggle="modal" data-target="#seminar-tolak">
                                                                Perbaiki <?php  ?>
                                                            </button>
                                                </a>
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

</script>

<script>

    $(".passingID6").click(function () {
                var id = $(this).attr('data-id');
                $("#ID").val( id );

    });        
</script>
                        