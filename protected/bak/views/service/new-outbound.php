<div class="modal fade new-out-modal" style="overflow:hidden;" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Outbound") ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmOut" class="frm frmOut" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action', 'addOuts') ?>
                    <?php echo CHtml::hiddenField('idOut', '') ?>
                    <?php echo CHtml::hiddenField('loc', '') ?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Destination"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('destination', '', array(
                                    'placeholder' => t("Destination")
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Delivery Address"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('delivery', '', array(
                                    'placeholder' => t("Delivery Address")
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Contact Person"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('cp', '', array(
                                    'placeholder' => t("Contact Person")
                                )) ?>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="row"> <!-- hawb -->
                            <div class="col-md-4 ">
                                <?php echo t("HAWB#"); ?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList(
                                    'hawb_select',
                                    '',
                                    array("" => t("Choose"),),
                                    array(
                                        'class' => "select2_class form-control",
                                        'style' => "width: 85%;"
                                    )
                                ) ?>
                            </div>
                        </div>

                        <div class="row top10"> <!-- part -->
                            <div class="col-md-4 ">
                                <?php echo t("Part Number#"); ?>
                            </div>
                            <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::dropDownList(
                                    'part_select',
                                    '',
                                    array("" => t("Choose"),),
                                    array(
                                        'class' => "select2_class form-control",
                                        'style' => "width: 85%;"
                                    )
                                ) ?>
                            </div>
                        </div>

                        <div class="row top10"> <!-- lot -->
                            <div class="col-md-4 ">
                                <?php echo t("Qty Lot"); ?>
                            </div>
                            <div class="col-md-7 " style="padding-bottom: 0px; padding-top: 0px;">
                                <?php echo CHtml::textField('lot_select', '', array()) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("PO#"); ?>
                            </div>
                            <div class="col-md-7 ">
                                <?php echo CHtml::textField('po', '', array(
                                    'placeholder' => t("PO Number")
                                )) ?>
                            </div>
                            <div class="col-md-1 " style="padding-bottom: 0px; padding-top: 0px;">
                                <button class="btn btn-primary addDown" type="button"><?php echo t("+") ?></button>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="table-responsive">
                                <table class="table table-hover issue-tracker" id="tblDetails">
                                    <thead>
                                        <tr>
                                            <th><?php echo t("HAWB#") ?></th>
                                            <th><?php echo t("Part#") ?></th>
                                            <th><?php echo t("Lot#") ?></th>
                                            <th><?php echo t("Loc#") ?></th>
                                            <th><?php echo t("PO#") ?></th>
                                            <th><?php echo t("Action") ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblout"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row top20">
                            <button class="btn btn-primary" type="submit"><?php echo t("Submit") ?></button>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>