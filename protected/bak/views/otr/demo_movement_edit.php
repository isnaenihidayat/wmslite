<div class="modal fade demo_movement_edit_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Edit Demo Movement") ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="" class="frm" method="POST" action="<?php echo Yii::app()->createUrl("otr/demo_movement_update", array()) ?>">
                    <?php echo CHtml::hiddenField('id_edit', '') ?>
                    <?php echo CHtml::hiddenField('from_loc_edit', '') ?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 top10"><?php echo t("Demo Request Number"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('ref_edit', '', array(
                                    'placeholder' => t("Demo Request Number"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 top10"><?php echo t("Requested By"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('requested_by_edit', '', array(
                                    'placeholder' => t("Requested By"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10 loc_block_edit" style="">
                            <div class="col-md-4 top10"><?php echo t("To Location"); ?></div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('to_loc_edit', '', array(
                                    'placeholder' => t("To Location"),
                                    //'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 top10">
                                <?php echo t("HAWB"); ?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList(
                                    'hawb_demo_movement_edit',
                                    '',
                                    array("" => t("- Choose HAWB -"),),
                                    array(
                                        'class' => "select2_class form-control",
                                        'style' => "width: 100%;"
                                    )
                                ) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4"></div>
                            <div class="col-md-8">
                                <button class="btn btn-primary addDownDemoEdit" type="button"><?php echo t("Add Item +") ?></button>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="table-responsive" style="height:200px !important;">
                                <table class="table table-hover" id="tblDetailDemoEdit">
                                    <thead>
                                        <tr>
                                            <th><?php echo t("HAWB") ?></th>
                                            <th><?php echo t("Current Location / Warehouse") ?></th>
                                            <th><?php echo t("Action") ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbloutdemoedit"></tbody>
                                </table>
                            </div>
                        </div>


                        <div class="row top20">
                            <div class="col-md-5">
                                <button class="btn btn-primary" type="submit"><?php echo t("Update") ?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>