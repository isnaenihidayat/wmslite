
<div class="modal fade new-apk-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Checker")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmApk" class="frmApk frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','addApks')?>
                    <?php echo CHtml::hiddenField('idApk','')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 "><?php echo t("Name");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('name','',array(
                                    'placeholder'=>t("Name"),
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Username");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('username','',array(
                                    'placeholder'=>t("username"),
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 "><?php echo t("Password");?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::passwordField('password','',array(
                                    'class'=>"validate",
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top20">
                            <div class="col-md-5">
                                <button class="btn btn-primary" type="submit"><?php echo t("Submit")?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>