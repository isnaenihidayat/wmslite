
<div class="modal fade mov-loc-modal" style="overflow:hidden;" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Moving Hawb")?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmMov" class="frm frmMov" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action','movLocs')?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("HAWB#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList('hawbselect','',
                                            array(""=>t("Choose"),),
                                            array(
                                                'class'=>"select2_class form-control",
                                                'style'=>"width: 85%;"
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Part Number#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList('partselect','',
                                            array(""=>t("Choose"),),
                                            array(
                                                'class'=>"select2_class form-control",
                                                'style'=>"width: 85%;"
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Lot Number#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList('lotselect','',
                                            array(""=>t("Choose"),),
                                            array(
                                                'class'=>"select2_class form-control",
                                                'style'=>"width: 85%;"
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("From Loc#");?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('locname','',array(
                                    'placeholder'=>t("Location"),
                                    'readonly'=>true
                                ))?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("To Loc#");?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList('Locselect','',
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