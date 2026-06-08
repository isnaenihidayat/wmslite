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
                    <?php echo CHtml::hiddenField('action', 'addOut') ?>
                    <?php echo CHtml::hiddenField('idOut', '') ?>
                    <?php echo CHtml::hiddenField('filename_ob', '') ?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Destination"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('destination', '', array(
                                    'placeholder' => t("Destination"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("GON Number"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('po_number_o', '', array(
                                    'placeholder' => t("PO Number"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("PSO Delivery ID"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('delivery_id', '', array(
                                    'placeholder' => t("PSO Delivery ID"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Transporter"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('transporter', '', array(
                                    'placeholder' => t("Transporter"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="table-responsive" style="height:200px !important;">
                                <table class="table table-hover issue-tracker" id="tblDetailxxx" style="width: 600px;">
                                    <thead>
                                        <tr>
                                            <th><?php echo t("HAWB") ?></th>
                                            <th><?php echo t("SKU / SUB HAWB") ?></th>
                                            <th><?php echo t("Description") ?></th>
                                            <th><?php echo t("Location") ?></th>
                                            <th><?php echo t("Qty") ?></th>
                                            <th><?php echo t("Action") ?></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tblout"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="frmDetail">
                            <div class="row top10">
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
                                            'style' => "width: 70%;"
                                        )
                                    ) ?>
                                    <button class="btn btn-primary addDown" type="button"><?php echo t("Next") ?></button>
                                </div>
                            </div>


                        </div>

                        <div class="row top20">
                            <div class="col-md-6" style="text-align: left;">
                                <a id="upload-doc_ob" class="btn btn-warning"><?php echo t("Upload Document") ?></a>
                                <div id="progressBar_ob"></div>
                                <div id="progressOuter_ob"></div>
                                <div id="msgBox_ob"></div>
                            </div>
                            <div class="col-md-6" style="text-align: right;">
                                <button id="submitOb" class="btn btn-primary" type="submit"><?php echo t("Submit") ?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>