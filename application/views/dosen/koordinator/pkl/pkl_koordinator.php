

<div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-note icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <div>Persetujuan KP/PKL
                                        <div class="page-title-subheading">
                                        </div>
                                    </div>
                                </div>
                                <div class="page-title-actions">
                                    <!--<a href="<?php echo site_url("mahasiswa/tugas-akhir/tema/form") ?>" class="btn-shadow btn btn-success">-->
                                    <!--        <span class="btn-icon-wrapper pr-2 opacity-7">-->
                                    <!--            <i class="fas fa-file fa-w-20"></i>-->
                                    <!--        </span>-->
                                    <!--        Form Pengajuan Tema-->
                                    <!--</a>-->
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
                        <?php 
                        // if ($akun->ttd == NULL){
                        //     echo "<script>
                        //     alert('Silahkan Lengkapi Informasi Akun & Biodata Anda Terlebih Dahulu');
                        //     window.location.href='biodata';
                        //     </script>";
                        // } 
                        
                        ?>
                        
                         <div class="main-card mb-3 card">
                                <div class="card-body">
                                <div class="table-responsive">
                                    <table class="mb-0 table table-striped" id="example">
                                        <thead>
                                        <tr>
                                            <th stlye="width:10%">Tahun</th>
                                            <th stlye="width:5%">Periode</th>
                                            <th stlye="width:40%">Lokasi</th>
                                            <th stlye="width:20%">Nomor Surat</th>
                                            <th stlye="width:5%">Jumlah<br>Mahasiswa</th>
                                            <th stlye="width:10%">Aksi</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                       
                                        <?php
                                        if(empty($pkl))
                                        {
                                            echo "<tr><td colspan='6'>Data tidak tersedia</td></tr>";
                                        }
                                        else
                                        {
                                            foreach($pkl as $row) {
                                               
                                        ?>
                                            <tr>
                                                <td class="align-top">
                                                    <?php 
                                                       echo "$row->tahun";
                                                    ?>
                                                </td>
                                                <td class="align-top">
                                                    <?php 
                                                       echo "$row->periode";
                                                    ?> 
                                                </td>

                                                <td class="align-top">
                                                    <?php 
                                                        echo "<b>$row->lokasi</b>";
                                                        echo "<br>";
                                                        echo "$row->alamat";
                                                    
                                                    ?>
                                                </td>

                                                <td class="align-top">
                                                    <?php echo "$row->no_penetapan" ?>
                                                </td>

                                                <td class="align-top">
                                                    <?php 
                                                       $jml_mahasiswa = $this->pkl_model->get_jml_mahasiswa_lokasi_daftar_koor($row->id,$this->session->userdata('userId'),$row->no_penetapan)->jml ;
                                                       echo "<b>$jml_mahasiswa</b>";
                                                    ?>
                                                </td>
                                                <td class="align-top">
                                                <?php 
                                                    if($jml_mahasiswa == "0"){$disabled="disabled";}else{$disabled="";}
                                                    if($row->status == 1 ){
                                                        $class = "success";
                                                        $ket = "Menunggu";
                                                    }    
                                                    elseif($row->status == 2){
                                                        $class = "danger";
                                                        $ket = "Setujui";
                                                    }
                                                    elseif($row->status >= 3){
                                                        $class = "primary";
                                                        $ket = "Selesai";
                                                    }
                                                    elseif($row->status==0){
                                                        $class = "danger";
                                                        $ket = "Setujui";
                                                    }
                                                ?>
                                                <a href="<?php echo site_url("dosen/pkl/pengajuan/koordinator/approve?periode=$row->id_pkl&id=".$this->encrypt->encode($row->approval_id)) ?>" class="btn-wide mb-1 btn btn-<?php echo $class ?> btn-sm btn-block <?php echo $disabled ?> " ><?php echo $ket ?></a>
                                              
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
$(document).ready(function() {
    $('#example').DataTable();
} );
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

    $(".passingID4").click(function () {
                var id = $(this).attr('data-id');
                var data = id.split("#$#$");
                $("#ID").val( data[0] );
                $("#status").val( data[1] );

    });      
       
</script>
                        