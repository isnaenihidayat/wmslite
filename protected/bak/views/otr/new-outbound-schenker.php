<style>
    .MultiCheckBox {
        border: 1px solid #e2e2e2;
        padding: 5px;
        border-radius: 4px;
        cursor: pointer;
        z-index: 99999999;
    }

    .MultiCheckBox .k-icon {
        font-size: 15px;
        float: right;
        font-weight: bolder;
        margin-top: -7px;
        height: 10px;
        width: 14px;
        color: #787878;
    }

    .MultiCheckBoxDetail {
        display: none;
        position: absolute;
        border: 1px solid #e2e2e2;
        overflow-y: hidden;
        z-index: 99999999;
    }

    .MultiCheckBoxDetailBody {
        background-color: white;
        height: 200px;
        overflow: scroll;
        z-index: 99999999;
    }

    .MultiCheckBoxDetail .cont {
        clear: both;
        overflow: hidden;
        padding: 2px;
    }

    .MultiCheckBoxDetail .cont:hover {
        background-color: #cfcfcf;
    }

    .MultiCheckBoxDetailBody>div>div {
        float: left;
    }

    .MultiCheckBoxDetail>div>div:nth-child(1) {}

    .MultiCheckBoxDetailHeader {
        overflow: hidden;
        position: relative;
        height: 28px;
        background-color: #3d3d3d;
    }

    .MultiCheckBoxDetailHeader>input {
        position: absolute;
        top: 4px;
        left: 3px;
    }

    .MultiCheckBoxDetailHeader>div {
        position: absolute;
        top: 5px;
        left: 24px;
        color: #fff;
    }
</style>
<div class="modal fade new-out-modal-schenker" style="overflow:hidden;" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Outbound Schenker") ?>
                </h4>
            </div>

            <div class="modal-body" style="height:550px; overflow-y: scroll;">

                <form id="frmOutSchenker" class="frm frmOutSchenker" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action', 'addOutSchenker') ?>
                    <?php echo CHtml::hiddenField('idOutSchenker', '') ?>
                    <?php echo CHtml::hiddenField('filename_ob', '') ?>
                    <?php echo CHtml::hiddenField('loc', '') ?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Destination"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('destination_schenker', '', array(
                                    'placeholder' => t("Destination"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("GON Number"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('po_number_o_schenker', '', array(
                                    'placeholder' => t("PO Number"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("PSO Delivery ID"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('delivery_id_schenker', '', array(
                                    'placeholder' => t("PSO Delivery ID"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Transporter"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('transporter_schenker', '', array(
                                    'placeholder' => t("Transporter"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>


                        <div class="frmDetail">
                            <div class="hr-line-dashed"></div>

                            <div class="row"> <!-- hawb -->
                                <div class="col-md-4 ">
                                    <?php echo t("HAWB#"); ?>
                                </div>
                                <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                    <?php echo CHtml::dropDownList(
                                        'hawb_select_schenker',
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
                                    <?php echo t("SKU#"); ?>
                                </div>
                                <div class="col-md-8 " style="padding-bottom: 0px; padding-top: 0px;">
                                    <select id="sku_select" name="sku_select[]">

                                    </select>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 ">

                                </div>
                                <div class="col-md-7 " style="padding-bottom: 0px; padding-top: 0px;">
                                    <input type="checkbox" value="1" name="all_qty"> All Qty
                                </div>
                            </div>
                            <div class="row top10 qtylotdiv"> <!-- lot -->
                                <div class="col-md-4 ">
                                    <?php echo t("Qty"); ?>
                                </div>
                                <div class="col-md-7 " style="padding-bottom: 0px; padding-top: 0px;">
                                    <?php echo CHtml::textField('qty_select', '', array()) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 ">

                                </div>
                                <div class="col-md-7 ">
                                </div>
                                <div class="col-md-1 " style="padding-bottom: 0px; padding-top: 0px;">
                                    <button class="btn btn-primary addDownSchenker" type="button"><?php echo t("+") ?></button>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="table-responsive">
                                    <table class="table table-hover issue-tracker" id="tblDetails">
                                        <thead>
                                            <tr>
                                                <th><?php echo t("HAWB#") ?></th>
                                                <th><?php echo t("SKU#") ?></th>
                                                <th><?php echo t("QTY#") ?></th>
                                                <th><?php echo t("LPN#") ?></th>
                                                <th><?php echo t("Description#") ?></th>
                                                <th><?php echo t("Action") ?></th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbloutschenker"></tbody>
                                    </table>
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