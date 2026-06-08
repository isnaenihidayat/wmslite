<div class="modal fade demo_movement_return_modal" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Return Demo Movement") ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="" class="frm" method="POST" action="<?php echo Yii::app()->createUrl("otr/demo_movement_return", array()) ?>">
                    <?php echo CHtml::hiddenField('id_return', '') ?>
                    <?php //echo CHtml::hiddenField('to_loc_return', '') ?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 top10"><?php echo t("Demo Request Number"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('ref_return', '', array(
                                    'placeholder' => t("Demo Request Number"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 top10"><?php echo t("Requested By"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('requested_by_return', '', array(
                                    'placeholder' => t("Requested By"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10 loc_block_return" style="">
                            <div class="col-md-4 top10"><?php echo t("From Location"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('from_loc_return', '', array(
                                    'placeholder' => t("From Location"),
                                    'readOnly' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="table-responsive" style="height:200px !important;">
                                <table class="table table-hover" id="tblDetailDemoReturn">
                                    <thead>
                                        <tr>
                                            <th><?php echo t("HAWB") ?></th>
                                            <th><?php echo t("To Loc") ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbloutdemoreturn"></tbody>
                                </table>
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