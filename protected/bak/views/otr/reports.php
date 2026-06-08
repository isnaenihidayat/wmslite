<?php
    $start_date = date('Y-m-d', strtotime("-1 day"));
    $end_date = date("Y-m-d", strtotime("0 day"));
?>

<div class='container popup repPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Reports")?></h3>
            <a href="<?php echo Yii::app()->createUrl("otr",array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
        </div>
        <div class="panel-body reports-list" id="reports-list">
            <div class="row">
                <div class="col-md-3">

                    <p><?php echo Driver::t("Start Date")?></p>
                    <?php echo CHtml::textField(
                        'start_date',
                        $start_date,
                        array('class'=>"form-control datetimepicker"))
                    ?>
                    <p class="top10"><?php echo Driver::t("End Date")?></p>
                    <?php echo CHtml::textField(
                        'end_date',
                        $end_date,
                        array('class'=>"form-control datetimepicker"))
                    ?>

                    <p class="top20"><?php echo t("Type Report") ?></p>
                    <?php if($_SESSION['wmslite']['type'] == '1'): ?>
                    <?php echo CHtml::dropDownList(
                            'type_report', '',
                            array(''=>'Select Report', 'shipment'=>'SHIPMENT', 'inbound'=>'INBOUND', 'outbound'=>'OUTBOUND', 'inventory'=>'INVENTORY', 'inbound_detail'=>'INBOUND DETAIL', 'outbound_detail'=>'OUTBOUND DETAIL'),
                            array('class'=>'form-control'))
                    ?>
                    <?php else: ?>
                    <?php echo CHtml::dropDownList(
                        'type_report', '',
                        array(''=>'Select Report', 'shipment'=>'SHIPMENT'),
                        array('class'=>'form-control'))
                    ?>
                    <?php endif ?>

                    <p class="top20">
                        <a href="javascript:;" class="btn btn-primary getReports"><?php echo Driver::t("Load")?></a>
                    </p>

                </div>

                <div class="col-md-9">
                    <div class="table-ota-wrap">
                        <form id="frm_table" class="frm_table">
                            <div id="table-ota" class="table-ota" style="height: 500px; overflow: scroll;"></div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>