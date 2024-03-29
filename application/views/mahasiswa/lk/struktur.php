

<div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-note icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <div>Struktur Organisasi
                                        <div class="page-title-subheading">
                                          
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="page-title-actions">
                                </div>
                               
                                
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
                                            <th style="width: 5%;">#</th>
                                            <th style="width: 20%;">Nama LK</th>
                                            <th style="width: 10%;">Periode</th>
                                            <th style="width: 35%;">Struktur Organisasi</th>
                                            <th style="width: 15%;">Status</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if(empty($lk))
                                        {
                                            echo "<tr><td colspan='6'>Data tidak tersedia</td></tr>";
                                        }
                                        else
                                        {
                                            $no = 0;
                                            foreach($lk as $row) {
                                                $data = $this->layanan_model->get_lk_by_id($row->id_lk);
                                        ?>
                                            <tr>
                                                <td class="align-top"><?php echo ++$no; ?></td>
                                                <td class="align-top"><b><?php echo $data->nama_lk; ?></b></td>
                                                <td class="align-top"><?php echo $row->periode; ?></td>
                                                <td class="align-top">
                                                <?php 
                                                    $ketua = $this->layanan_model->get_jabatan_lk($row->id_lk,$row->periode,1);
                                                    $wakil = $this->layanan_model->get_jabatan_lk($row->id_lk,$row->periode,2);
                                                    $sekretaris = $this->layanan_model->get_jabatan_lk($row->id_lk,$row->periode,3);
                                                    $bendahara = $this->layanan_model->get_jabatan_lk($row->id_lk,$row->periode,4);
                                                ?>
                                                    <b>Ketua : </b><?php echo empty($ketua) ? "-" : $this->user_model->get_mahasiswa_data($ketua->id_user)->name." (".$this->user_model->get_mahasiswa_data($ketua->id_user)->npm.")" ?><br>
                                                    <b>Wakil : </b><?php echo empty($wakil) ? "-" : $this->user_model->get_mahasiswa_data($wakil->id_user)->name." (".$this->user_model->get_mahasiswa_data($wakil->id_user)->npm.")" ?><br>
                                                    <b>Sekretaris : </b><?php echo empty($sekretaris) ? "-" : $this->user_model->get_mahasiswa_data($sekretaris->id_user)->name." (".$this->user_model->get_mahasiswa_data($sekretaris->id_user)->npm.")" ?><br>
                                                    <b>Bendahara : </b><?php echo empty($bendahara) ? "-" : $this->user_model->get_mahasiswa_data($bendahara->id_user)->name." (".$this->user_model->get_mahasiswa_data($bendahara->id_user)->npm.")" ?><br>
                                                </td>
                                                <td class="align-top">
                                                    <?php 
                                                        $status = $this->layanan_model->get_verif_struktur($row->id_lk,$row->periode);
                                                        if($status->verifikasi == 0){
                                                            echo "Menunggu Verifikasi";
                                                        }else{
                                                            echo "Diverifikasi";
                                                        }
                                                    ?>
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
</script>