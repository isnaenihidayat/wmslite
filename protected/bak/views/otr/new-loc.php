
<div class="modal fade new-loc-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Location")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmLoc" class="frmLoc frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','addLoc')?>
                    <?php echo CHtml::hiddenField('idLoc','')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Location");?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('loc_name','',array(
                                    'placeholder'=>t("Location"),
                                    'required'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Description");?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('loc_descr','',array(
                                    'placeholder'=>t("Description")
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