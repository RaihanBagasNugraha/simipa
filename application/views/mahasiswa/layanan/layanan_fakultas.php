

<div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-note icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <?php 
                                        $jns = $this->uri->segment(3);
                                        switch($jns){
                                            case "akademik":
                                            $layanan = "Akademik";
                                            break;
                                            case "kemahasiswaan":
                                            $layanan = "Kemahasiswaan";
                                            break;
                                            case "umum-keuangan":
                                            $layanan = "Umum dan Keuangan";
                                            break;
                                        }
                                    ?>
                                    <div>Form Layanan <?php echo $layanan; ?>
                                        <div class="page-title-subheading">
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="page-title-actions">
                                    <a href="<?php echo site_url("mahasiswa/layanan-fakultas/$jns/form") ?>" class="btn-shadow btn btn-success">
                                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                                <i class="fas fa-file fa-w-20"></i>
                                            </span>
                                            Tambah Form
                                    </a>
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
                                            <th style="width: 25%;">Nama Form</th>
                                            <th style="width: 10%;">Waktu</th>
                                            <th style="width: 25%;">Keterangan</th>
                                            <th style="width: 20%;">Status</th>
                                            <th style="width: 15%;">Aksi</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if(empty($form))
                                        {
                                            echo "<tr><td colspan='6'>Data tidak tersedia</td></tr>";
                                        }
                                        else
                                        {
                                            $n = 0;
                                            foreach($form as $row) {
                                              
                                        ?>
                                        <tr>
                                            <td class="align-top">
                                                <?php echo ++$n; ?>
                                            </td>

                                            <td class="align-top">
                                                <?php echo $this->layanan_model->select_layanan_by_id($row->id_layanan_fakultas)->nama; ?>
                                            </td>

                                            <td class="align-top">
                                                <?php 
                                                    $wkt = explode("-",substr($row->created_at,0,10));
                                                    $waktu = $wkt[2]."-".$wkt[1]."-".$wkt[0];
                                                    echo $waktu; 
                                                ?>
                                            </td>

                                            <td class="align-top">
                                                <?php 
                                                $keterangan = $this->layanan_model->get_keterangan_form($row->id);
                                                $m = 1;
                                                if(!empty($keterangan)){
                                                    foreach($keterangan as $ket){
                                                        echo "<b>$m. $ket->nama : </b>$ket->meta_value<br>";
                                                        $m++;
                                                    }
                                                }else{ echo "-"; }
                                                ?>
                                            </td>

                                            <td class="align-top">
                                               <?php 
                                                $approval = $this->layanan_model->get_approval_layanan($row->id);
                                                $form_selesai = array(3,5,6,7,8,10,11,12,13,18,19,20,21,22,23,25,26,32,33,35,37,38,44,45);
                                                if(in_array($row->id_layanan_fakultas,$form_selesai)){
                                                    echo "<span style='color:white;background-color:#5cb85c' class='btn-sm'>Selesai</span>";
                                                }else{
                                                    if($row->status == 0 && ($row->tingkat == null || $row->tingkat == "")){
                                                        echo "<span style='color:white;background-color:#d9534f' class='btn-sm'>Belum Diajukan</span>";
                                                    }elseif($row->status == 0 && ($row->tingkat != null || $row->tingkat != "") ){
                                                        echo "<span style='color:white;background-color:#f0ad4e' class='btn-sm'>Menunggu</span>";
                                                    }elseif($row->status == 1){
                                                        echo "<span style='color:white;background-color:#0275d8' class='btn-sm'>Verifikasi</span>";
                                                    }elseif($row->status == 2){
                                                        echo "<span style='color:white;background-color:#5cb85c' class='btn-sm'>Selesai</span>";
                                                    }elseif($row->status == 3){
                                                        echo "<span style='color:white;background-color:#d9534f' class='btn-sm'>Ditolak</span>";
                                                    }
                                                    echo "<br>";

                                                    if($row->status != 3){
                                                        // if(!empty($approval)){
                                                        //     foreach($approval as $app){
                                                        //         // echo "<ul>";
                                                        //         echo "<li><b>Approval ".$this->layanan_model->get_approver_by_id($app->approver_id)->nama." : </b></li>";
                                                        //         // echo "<br>";
                                                        //         if($app->approver_id > 9){
                                                        //             echo substr($app->created_at,0,10);
                                                        //         }else{
                                                        //             echo substr($app->updated_at,0,10);
                                                        //         }
                                                        //         echo "<br>";
                                                        //     }
                                                        // }
                                                    }elseif($row->status == 3){
                                                        echo "<br>";
                                                        echo "<b><i><span style='color:red'>$row->keterangan<span></i></b>";
                                                    }
                                                }
                                               
                                               ?>
                                            </td>

                                            <td class="align-top">
                                            <?php 
                                            if(($row->tingkat == null || $row->tingkat == "") && $row->status < 1){
                                                //bebas lab
                                                if($row->id_layanan_fakultas == 2 ){

                                            ?>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/akademik/bebas-lab")  ?> " class="btn-wide mb-2 btn btn-primary btn-sm">Ajukan</a>
                                                <!-- <a data-toggle = "modal" data-id="<?php echo $row->id ?>" data-jns="<?php echo $jns ?>" class="passingID" >
                                                    <span type="button" class="btn mb-2 btn-danger btn-sm "  data-toggle="modal" data-target="#delFormMhs">
                                                        <i class="fa fa-trash" aria-hidden="true"></i> 
                                                    </span>
                                                </a> -->
                                            <?php
                                                }
                                                //form hardcopy
                                                elseif(in_array($row->id_layanan_fakultas,$form_selesai)){
                                            ?>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/$jns/unduh?id=".$row->id."&layanan=".$row->id_layanan_fakultas) ?>" class="btn-wide mb-2 btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i></a>
                                            <?php
                                                }
                                                else{
                                            ?>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/$jns/ajukan?id=".$this->encrypt->encode($row->id))  ?> " class="btn-wide mb-2 btn btn-primary btn-sm"><i class="fa fa-upload" aria-hidden="true"></i></a>
                                                <!-- &emsp; -->
                                                <a data-toggle = "modal" data-id="<?php echo $row->id ?>" data-jns="<?php echo $jns ?>" class="passingID" >
                                                    <span type="button" class="btn-wide btn mb-2 btn-danger btn-sm "  data-toggle="modal" data-target="#delFormMhs">
                                                        <i class="fa fa-trash" aria-hidden="true"></i> 
                                                    </span>
                                                </a>
                                            <?php }
                                            }
                                            //ditolak
                                            elseif($row->status == 3){

                                            ?>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/$jns/unduh?id=".$row->id."&layanan=".$row->id_layanan_fakultas) ?>" class="btn-wide mb-2 btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i></a>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/$jns/ajukan?id=".$this->encrypt->encode($row->id)."&aksi=perbaiki")  ?> " class="btn-wide mb-2 btn btn-primary btn-sm"><i class="fa fa-wrench" aria-hidden="true"></i></a>
                                                <!-- &emsp; -->
                                                <a data-toggle = "modal" data-id="<?php echo $row->id ?>" data-jns="<?php echo $jns ?>" class="passingID" >
                                                    <span type="button" class="btn-wide btn mb-2 btn-danger btn-sm "  data-toggle="modal" data-target="#delFormMhs">
                                                        <i class="fa fa-trash" aria-hidden="true"></i> 
                                                    </span>
                                                </a>
                                            <?php
                                            }
                                            else{ 
                                                if($row->id_layanan_fakultas == 2 ){
                                            ?>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/akademik/bebas-lab")  ?> " class="btn-wide mb-2 btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i></a>
                                            <?php }else{ ?>
                                                <a href="<?php echo site_url("/mahasiswa/layanan-fakultas/$jns/unduh?id=".$row->id."&layanan=".$row->id_layanan_fakultas) ?>" class="btn-wide mb-2 btn btn-success btn-sm"><i class="fa fa-download" aria-hidden="true"></i></a>
                                            <?php
                                                }
                                            }?>
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
                var jns = $(this).attr('data-jns');
                $("#ID").val( id );
                $("#Jns").val( jns );
            });              
     
</script>