
<div class="modal fade send-loc-modal" style="overflow:hidden;" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Putaway")?> - <span class="idPut"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmSend" class="frm frmSend" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','sendLoc')?>
                    <?php echo CHtml::hiddenField('HawbLoc','')?>
                    <?php echo CHtml::hiddenField('Hawb','')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Loc#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList('Loc_select','',
                                            array(""=>t("Choose"),),
                                            array(
                                                'class'=>"select2_class form-control",
                                                'style'=>"width: 85%;"
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