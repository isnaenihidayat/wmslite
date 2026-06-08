<div class="modal fade recipient_new_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Recipient") ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="" class="frm" method="POST" action="<?php echo Yii::app()->createUrl("otr/recipient_insert", array()) ?>">
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 top10"><?php echo t("Name"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('name', '', array(
                                    'placeholder' => t("Name"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 top10"><?php echo t("Email"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('email', '', array(
                                    'placeholder' => t("Email"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top20">
                            <div class="col-md-5">
                                <button class="btn btn-primary" type="submit"><?php echo t("Submit") ?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>