

<div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-note icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <div>Verifikasi Program Tugas Akhir
                                        <div class="page-title-subheading">
                                        </div>
                                    </div>
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
                                            <th style="width: 28%;">JUDUL</th>
                                            <th style="width: 19%;">VERIFIKATOR</th>
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
                                        else {
                                        ?>
                                        <tr>
                                            <td class="align-top">
                                                <?php echo $ta->judul_approve == 1 ? $ta->judul1 : $ta->judul2 ?>
                                            </td>
                                            <td class="align-top">
                                                <?php 
                                                echo "<b>$ta->nama</b>";
                                                echo "<br>";
                                                echo "$ta->nip_nik"; 
                                                
                                                ?>
                                            </td>
                                            <td class="align-top">
                                                <?php
                                                    if($ta->ket == 0){
                                                        echo "Menunggu";
                                                    }
                                                    elseif($ta->ket == 1){
                                                        echo "Penilaian";
                                                    }
                                                    else{
                                                        echo "Selesai";
                                                    }
                                                ?>
                                            </td>
                                            <td class="align-top">
                                            <?php if($ta->ket == 0 ){?>
                                                <a data-toggle = "modal" data-id="<?php echo $ta->id_pengajuan ?>" class="passingID" >
                                                        <button type="button" class="btn-wide mb-1 btn btn-danger btn-sm btn-block"  data-toggle="modal" data-target="#Ajukan">
                                                            Ajukan
                                                        </button>
                                                </a>
                                            <?php } elseif($ta->ket == 1){ echo "Menunggu"; }?>    
                                            </td>
                                            
                                        </tr>
                                            <?php } ?>
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
              
     
</script>