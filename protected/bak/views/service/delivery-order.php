<?php echo CHtml::hiddenField('currentForm', 'deliveryOrder')?>

<div class="wrapper wrapper-content">
    <div class="ibox-content">
        <div class="row">
            <div class="col-xs-4 ">
                <img style="max-height:70px; margin-bottom:10px;" src="<?php echo Yii::app()->getBaseUrl(true)."/upload/logoGE.png"; ?>">
            </div>
            <div class="pull-right">
                <img style="max-height:70px; margin-bottom:10px;" src="<?php echo Yii::app()->getBaseUrl(true)."/upload/lpi.png"; ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4 ">
                <?php echo t("PT GE OPERATIONS INDONESIA");?>
            </div>
            <div class="pull-right">
                <?php echo t("PT LOGISTICSPLUS INTERNATIONAL");?>
            </div>
        </div>
        <hr>

        <div class="text-center">
            <h3>DELIVERY ORDER</h3>
            <?php echo t("No.: ");?> <span class="noship"></span>
            <br><br>
        </div>

        <div class="row">
            <div class="col-xs-3 ">
                <?php echo t("Date");?>
            </div>
            <div class="col-xs-6 ">
                <?php echo t(": ");?> <span class="date_created"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-3 ">
                <?php echo t("Delivery Address");?>
            </div>
            <div class="col-xs-6 ">
                <?php echo t(": ");?> <span class="address"></span>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-3 ">
                <?php echo t("Contact Person");?>
            </div>
            <div class="col-xs-6 ">
                <?php echo t(": ");?> <span class="cp"></span>
            </div>
        </div>

        <br>

        <div class="table-responsive m-t">
            <table class="table invoice-table">
                <thead>
                    <tr>
                        <th>No#</th>
                        <th>Part Number</th>
                        <th>Qty</th>
                        <th>Description</th>
                        <th>SO#</th>
                        <th>PO#</th>
                    </tr>
                </thead>
                <tbody id="tbDOlist"></tbody>
            </table>
        </div><!-- /table-responsive -->

        <br>
        <div class="row">
            <div class="col-xs-6 text-center"><?php echo t("for & on behalf of#");?></div>
            <div class="col-xs-3"><?php echo t("Driver");?></div>
            <div class="col-xs-3"><?php echo t("Received by");?></div>
        </div>

        <div class="row">
            <div class="col-xs-3">
                <img style="max-height:70px; " src="<?php echo Yii::app()->getBaseUrl(true)."/upload/ttdGE.png"; ?>">
            </div>
            <div class="col-xs-3">
                <img style="max-height:70px; " src="<?php echo Yii::app()->getBaseUrl(true)."/upload/ttdLPI.png"; ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-xs-3">
                <?php echo t("Aqiela Raissa Syahriar");?>
            </div>
            <div class="col-xs-3">
                <?php echo t("Ricki Dwi Agusti");?>
            </div>
            <div class="col-xs-3">
                <?php echo t("Name : _____________");?>
            </div>
            <div class="col-xs-3">
                <?php echo t("Name : _____________");?>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="col-xs-3">
                <?php echo t("");?>
            </div>
            <div class="col-xs-3">
                <?php echo t("");?>
            </div>
            <div class="col-xs-3">
                <?php echo t("No. Pol : ____________");?>
            </div>
            <div class="col-xs-3">
                <?php echo t("Date : _______________");?>
            </div>
        </div>

        <div class="well m-t text-center">WMSLite powered by <strong>eLog.ID</strong></div>
    </div>
</div>