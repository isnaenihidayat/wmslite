<style>
    body{
        background-color: white;
    }

    #wrapper{
        background-color: #2f4050;
    }
</style>
<div id="wrapper" style="min-height: 700px;">

    <div class="row border-bottom white-bg">
        <?php $this->renderPartial('/tpl/top_otr', array()); ?>
    </div>

    <div class="wrapper wrapper-content" style="margin-top: 65px;">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo t("Outbound") ?></h3>
                <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
            </div>
            <div class="panel-body">
                <div>
                    <a class="btn btn-sm btn-primary recipient_new" href="<?php echo Yii::app()->createUrl("otr-outbound/create", array()) ?>">
                        <?php echo t("Add Outbound") ?>
                    </a>
                    <a class="btn btn-sm btn-warning" href="<?php echo Yii::app()->createUrl("otr-outbound/index", array()) ?>">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
                <br>

                <?php $this->renderPartial('/layouts/alert', array()); ?>

                <table id="datatable" class="table table-striped table-bordered table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><?php echo t("ID") ?></th>
                            <th><?php echo t("Qty") ?></th>
                            <th><?php echo t("GON Number") ?></th>
                            <th><?php echo t("Destination") ?></th>
                            <th><?php echo t("PSO Delivery ID") ?></th>
                            <th><?php echo t("Transporter") ?></th>
                            <th><?php echo t("Created") ?></th>
                            <th><?php echo t("Scan Time") ?></th>
                            <th><?php echo t("Last Modified") ?></th>
                            <th><?php echo Driver::t("Status") ?></th>
                            <th style="width: 210px; text-align:center;"><?php echo Driver::t("Action") ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php $this->renderPartial('/layouts/copyright', array()); ?>

<?php

Yii::app()->clientScript->registerScript('datatable-custom', '
    $(document).ready(function() {
        var dataTable = $(\'#datatable\').DataTable({
            responsive: true,
            order: [[ 0, "desc" ]],
            processing: true,
            serverSide: true,
            ajax: {
                url: "' . $this->createUrl('otr-outbound/get-outbound') . '",
                type: "POST"
            },
        });
    });
', CClientScript::POS_END);

?>