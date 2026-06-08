<?php
    $start_date = date('Y-m-d', strtotime("-1 day"));
    $end_date = date("Y-m-d", strtotime("0 day"));
?>

<div class='container popup repPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Reports")?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body reports-lists" id="reports-lists">
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
                    <?php echo CHtml::dropDownList(
                            'type_report', '',
                            array(''=>'Select Report', 'inbound'=>'INBOUND', 'outbound'=>'OUTBOUND', 'inventory'=>'INVENTORY'),
                            array('class'=>'form-control'))
                    ?>

                    <p class="top20">
                        <a href="javascript:;" class="btn btn-primary getReports"><?php echo Driver::t("Load")?></a>
                    </p>

                </div>

                <div class="col-md-9">
                    <div class="table-ota-wrap">
                        <form id="frm_table" class="frm_table">
                            <div id="table-ota" class="table-ota"></div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>