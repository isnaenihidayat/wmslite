<div class='container popup outPopupSchenker'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Outbound Schenker") ?></h3>
            <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
        </div>
        <div class="panel-body outbound-list-schenker" id="outbound-list-schenker">
            <div class="row">
                <div class="col-md-12">
                    <?php if ($_SESSION['wmslite']['admin'] == 1 || $_SESSION['wmslite']['type'] == 1) : ?>
                        <a class="btn btn-primary new-outbound-schenker" href="javascript:;">
                            <?php echo t("Add Outbound Schenker") ?>
                        </a>
                    <?php endif ?>
                    <a class="btn btn-warning refresh-table" href="javascript:;">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <div style="height: 70vh; overflow: scroll;">
                <form id="frm_table" class="frm_table">
                    <?php echo CHtml::hiddenField('action', 'outListSchenker') ?>
                    <table id="outbound_list_schenker" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th><?php echo t("ID") ?></th>
                                <th><?php echo t("Qty") ?></th>
                                <th><?php echo t("GON Number") ?></th>
                                <th><?php echo t("Destination") ?></th>
                                <th><?php echo t("PSO Delivery ID") ?></th>
                                <th><?php echo t("Transporter") ?></th>
                                <th><?php echo t("Created") ?></th>
                                <th><?php echo t("Last Modified") ?></th>
                                <th><?php echo Driver::t("Status") ?></th>
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