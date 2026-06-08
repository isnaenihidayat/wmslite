
<div class="modal fade edit-detail-modal" style="overflow:hidden;" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-detail">
                    <?php echo t("Edit Koli")?> - <span class="idPut"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmKoli" class="frm frmKoli" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','updateKoli')?>
                    <?php echo CHtml::hiddenField('Hawbdetail','')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Weight#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::textField('weight','',array(
                                    'placeholder'=>t("Weight")
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Length#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::textField('long','',array(
                                    'placeholder'=>t("Length")
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Width#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::textField('wide','',array(
                                    'placeholder'=>t("Width")
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Height#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::textField('high','',array(
                                    'placeholder'=>t("Height")
                                ))?>
                            </div>
                        </div>

                        <div class="row top20">
                            <button class="btn btn-primary" type="submit"><?php echo t("Submit")?></button>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>