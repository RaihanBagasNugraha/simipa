
<div class="app-page-title">
                        <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-file icon-gradient bg-mean-fruit">
                                        </i>
                                    </div>
                                    <div>Approval Seminar
                                        <div class="page-title-subheading">Setujui Atau Tolak Pengajuan Seminar
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                        <?php
                            // debug
                            //echo "<pre>";
                            //print_r($data_ta);
                            //echo "</pre>";
                            if(!empty($_GET['status']) && $_GET['status'] == 'sukses') {

                                echo '<div class="alert alert-success fade show" role="alert">Biodata Anda sudah diperbarui, jangan lupa untuk memperbarui <a href="javascript:void(0);" class="alert-link">Akun</a> sebelum menggunakan layanan.</div>';
                            }
                        ?>

                        <div class="row">
                        <div class="col-md-12">
                         <div class="main-card mb-3 card">
                                <div class="card-header">Approval Seminar</div>
                                <div class="card-body">
                                <form method="post" action="<?php echo site_url('dosen/tugas-akhir/seminar/koordinator/approve') ?>" >
                                    <input value="<?php echo $seminar->id ?>" type = "hidden" required name="id" id="id">
                                    <input value="<?php echo $seminar->id_tugas_akhir ?>" type = "hidden" required name="id_ta" id="id">
                                    <input value="<?php echo $status ?>" type = "hidden" required name="status" id="status">


                                    <!-- NPM -->
                                    <div class="position-relative row form-group">
                                            <label class="col-sm-3 col-form-label"><b>Npm</b></label>
                                            <div class="col-sm-3">
                                                <input value="<?php echo $seminar->npm ?>" required name="npm" class="form-control input-mask-trigger" readonly >
                                            </div>
                                    </div>

                                    <!-- NAMA -->
                                    <div class="position-relative row form-group">
                                            <label class="col-sm-3 col-form-label"><b>Nama</b></label>
                                            <div class="col-sm-9">
                                                <input value="<?php echo $this->user_model->get_mahasiswa_name($seminar->npm) ?>" required name="nama" class="form-control input-mask-trigger" readonly >
                                            </div>
                                    </div>

                                    <!-- Judul -->
                                    <div class="position-relative row form-group">
                                            <label for="prodi" class="col-sm-3 col-form-label" ><b>Judul Utama</b></label>
                                            <div class="col-sm-9">
                                                <?php if($seminar->judul_approve == 1){?>
                                                    <textarea required name="judul" class="form-control" readonly placeholder="Judul Utama" id="inputother"><?php echo $seminar->judul1 ?></textarea>
                                                <?php } elseif($seminar->judul_approve == 2){?>
                                                    <textarea required name="judul" class="form-control" readonly placeholder="Judul Utama" id="inputother"><?php echo $seminar->judul2 ?></textarea>
                                                <?php } ?>
                                            </div>
                                    </div>

                                    <!-- IPK -->
                                    <div class="position-relative row form-group">
                                            <label class="col-sm-3 col-form-label"><b>IPK</b></label>
                                            <div class="col-sm-3">
                                                <input value="<?php echo $seminar->ipk ?>" required name="ipk" class="form-control input-mask-trigger" readonly data-inputmask="'mask': '9.99'" im-insert="true">
                                            </div>
                                    </div>

                                    <!-- SKS -->
                                    <div class="position-relative row form-group">
                                            <label class="col-sm-3 col-form-label"><b>Jumlah SKS</b></label>
                                            <div class="col-sm-3">
                                                <input value="<?php echo $seminar->ipk ?>" required name="sks" class="form-control input-mask-trigger" readonly data-inputmask="'mask': '999'" im-insert="true">
                                            </div>
                                    </div>

                                     <!-- TOEFL -->
                                    <div class="position-relative row form-group">
                                            <label class="col-sm-3 col-form-label"><b>Nilai TOEFL</b></label>
                                            <div class="col-sm-3">
                                                <input value="<?php echo $seminar->toefl ?>" name="toefl" class="form-control input-mask-trigger" readonly data-inputmask="'mask': '999'" im-insert="true">
                                            </div>
                                    </div>
                                    
                                    <!-- Status -->
                                    <div class="position-relative row form-group">
                                            <label class="col-sm-3 col-form-label"><b>Status</b></label>
                                            <div class="col-sm-9">
                                                <?php if($status == "kaprodi"){ ?>
                                                    <input value="Kaprodi" name="status" class="form-control" readonly >
                                                <?php } else {?>
                                                    <input value="Koordinator" name="status" class="form-control" readonly >
                                                <?php } ?>
                                            </div>
                                    </div>

                                    <!-- Komisi Pebimbing -->
                                    <br>
                                    <h5><b>Komisi Pembimbing</b></h5>
                                        <input value="" readonly required name="Pembimbing_1" type="hidden" class="form-control input-mask-trigger" >
                                        <input value="" readonly required name="Pembimbing_2" type="hidden" class="form-control input-mask-trigger" >
                                        <input value="" readonly required name="Pembimbing_3" type="hidden" class="form-control input-mask-trigger" >
                                        <input value="" readonly required name="Penguji_1" type="hidden" class="form-control input-mask-trigger" >
                                        <input value="" readonly required name="Penguji_2" type="hidden" class="form-control input-mask-trigger" >
                                        <input value="" readonly required name="Penguji_3" type="hidden" class="form-control input-mask-trigger" >

                                     <?php 
                                        //komisi
                                        $komisi_pembimbing = $this->ta_model->get_pembimbing_ta($seminar->id_tugas_akhir); 
                                            
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
                                        ?>

                                        <div class="position-relative row form-group">
                                            <label for="nama" class="col-sm-3 col-form-label"><b><?php echo $kom->status; ?></b></label>
                                            <div class="col-sm-9">
                                                <input value="<?php echo $g_depan.$kom->nama.$g_belakang; ?>" readonly required name="<?php echo "nama ".$kom->status; ?>" class="form-control input-mask-trigger" >
                                                <input value="<?php echo $kom->id_user; ?>" readonly required name="<?php echo $kom->status; ?>" type="hidden" class="form-control input-mask-trigger" >
                                            </div>
                                        </div>

                                    <?php } ?>

                                    <br>
                                        <h5><b>Komisi Penguji</b></h5>

                                        <?php $komisi_penguji = $this->ta_model->get_penguji_ta($seminar->id_tugas_akhir); 
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
                                        ?>

                                        <div class="position-relative row form-group">
                                            <label for="nama" class="col-sm-3 col-form-label"><b><?php echo $kom->status; ?></b></label>
                                            <div class="col-sm-9">
                                                <input value="<?php echo $g_depan.$kom->nama.$g_belakang; ?>" readonly required name="<?php echo "nama ".$kom->status; ?>" class="form-control input-mask-trigger" >
                                                <input value="<?php echo $kom->id_user; ?>" readonly required name="<?php echo $kom->status; ?>" type="hidden" class="form-control input-mask-trigger" >
                                            </div>
                                        </div>

                                    <?php } ?>


                                    <!-- TTD -->
                                    <div class="position-relative row form-group"><label for="ttd" class="col-sm-3 col-form-label"><b>Tanda Tangan Digital</b></label>
                                            <div class="col-sm-4">
                                            <canvas style="border: 1px solid #aaa; height: 200px; background-color: #efefef;" id="signature-pad" class="signature-pad col-sm-12" height="200px"></canvas>
                                            <small class="form-text text-muted"> </small>
                                            </div>
                                            <div class="col-sm-5">
                                            <div role="group" class="btn-group btn-group btn-group-toggle"  style="margin-bottom: 10px;">
                                                    <label class="btn btn-dark">
                                                        <input type="radio" name="pen_color" class="pen_color" value="0" checked>
                                                        Hitam
                                                    </label>
                                                    <label class="btn btn-primary">
                                                        <input type="radio" name="pen_color" class="pen_color" value="1">
                                                        Biru
                                                    </label>
                                                    
                                                </div>
                                                
                                            </a>
                                            <a id="clear" class="mb-2 btn btn-light" onclick="document.getElementById('output').value = ''">Hapus
                                            </a>
                                            <!--<a id="preview" class="mb-2 btn btn-light">Oke-->
                                            <!--</a>-->
                                            <input type="hidden" style="background-color: #efefef;" type="text" class="form-control readonly" required placeholder=" " name="ttd" id="output" value="">
                                            <input type="hidden" name="aksi" value="<?php if(!empty($this->input->get("aksi"))) echo $this->input->get("aksi") ?>">
                                            </div>
                                    
                                        </div>

                                    <div class="position-relative row form-group">
                                            <div class="col-sm-9 offset-sm-3">
                                            <button id="preview" value="<?php if($this->input->get('aksi') == "ubah") echo "ubah"; ?>" type="submit" class="btn-shadow btn btn-info">
                                            <span class="btn-icon-wrapper pr-2 opacity-7">
                                                <i class="fa fa-save fa-w-20"></i>
                                            </span>
                                            <?php if($this->input->get('aksi') == "ubah") echo "Ubah"; else echo "Setujui Pengajuan" ?>
                                        </button>
                                            </div>
                                    </div>
                                
                                </form>



                    

</div>
<script src="<?php echo site_url("assets/scripts/jquery_3.4.1_jquery.min.js") ?>"></script>
<script src="<?php echo site_url("assets/scripts/select2.full.js") ?>"></script>
<script type="text/javascript">

</script>
<script src="<?php echo site_url("assets/scripts/signature_pad.js") ?>"></script>
<script>
var canvas = document.getElementById('signature-pad');

var signaturePad = new SignaturePad(canvas);

<?php if($this->input->get('aksi') == 'ubah' && !empty($this->input->get('id'))) { 
    
    $ttd_img = json_encode($data_ta['ttd']);
    
    ?>


<?php } ?>

$('#preview').click(function(){
  var data = signaturePad.toDataURL('image/png');
  $('#output').val(data);

 });

 $(".pen_color").change(function(){
    var radioValue = $("input[name='pen_color']:checked").val();
    if(radioValue == 1){
        signaturePad.penColor = 'rgb(0, 0, 255)'
    } else {
        signaturePad.penColor = 'rgb(0, 0, 0)'
    }
});

 

document.getElementById('clear').addEventListener('click', function () {
  signaturePad.clear();
});

document.getElementById('undo').addEventListener('click', function () {
	var data = signaturePad.toData();
  if (data) {
    data.pop(); // remove the last dot or line
    signaturePad.fromData(data);
  }
});

</script>