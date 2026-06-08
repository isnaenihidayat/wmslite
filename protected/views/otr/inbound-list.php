<div class='container popup inPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title" id="inbound-title"><?php echo t("Inbound") ?></h3>
            <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
        </div>
        <div class="panel-body inbound-list" id="inbound-list">
            <div class="row">
                <div class="col-md-12">
                    <?php if ($_SESSION['wmslite']['admin'] == 1 || $_SESSION['wmslite']['type'] == 1) : ?>
                        <a class="btn btn-primary new-inbound" href="javascript:;" data-warehouse="">
                            <?php echo t("Add Inbound") ?>
                        </a>
                    <?php endif ?>
                    <a class="btn btn-warning refresh-table" href="javascript:;">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <br>
            <div style="height: 70vh; overflow: scroll;">
                <form id="frm_table" class="frm_table">
                    <?php echo CHtml::hiddenField('action', 'inList') ?>
                    <table id="inbound_list" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th><?php echo t("ID") ?></th>
                                <th><?php echo t("HAWB") ?></th>
                                <th style="width: 120px;"><?php echo t("Description") ?></th>
                                <th><?php echo t("Product Category") ?></th>
                                <th><?php echo t("Modality") ?></th>
                                <th><?php echo t("SSO Delivery ID") ?></th>
                                <th><?php echo t("PO Number") ?></th>
                                <th><?php echo t("Oracle Locator") ?></th>
                                <th><?php echo t("ETD") ?></th>
                                <th><?php echo t("ETA") ?></th>
                                <th><?php echo t("ATA") ?></th>
                                <th><?php echo t("SPPB Date") ?></th>
                                <th><?php echo t("Date Created") ?></th>
                                <th><?php echo t("Last Modified") ?></th>
                                <th><?php echo Driver::t("Status") ?></th>
                                <th>totalQtyReceived</th>
                                <th>itemInInbound</th>
                                <th>totalPick</th>
                                <th><?php echo Driver::t("Action") ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>