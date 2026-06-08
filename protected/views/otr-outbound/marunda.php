<style>
    body {
        background-color: white;
    }

    #wrapper {
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
                <h3 class="panel-title"><?php echo t("Outbound Marunda") ?></h3>
                <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
            </div>
            <div class="panel-body">
                <?php $this->renderPartial('/layouts/alert', array()); ?>

                <?php

                if (empty($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey)) {
                    echo ('tidak ditemukan');
                }

                $externOrderKey = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey;
                $actualShipDate = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->actualShipDate;
                if (!empty($actualShipDate)) {
                    $date = DateTime::createFromFormat('d/m/Y H:i:s', $actualShipDate);
                    $actualShipDate = $date->format('Y-m-d H:i:s');
                }


                $status = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->status;

                //if (($status == 4 || $status == 9) && !empty($actualShipDate)) {
                //if (!empty($actualShipDate)) {

                echo 'GON/PO: ' . $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey;
                echo '<br>actualShipDate: ' . $actualShipDate;
                ?>

                <table class="table table-striped table-bordered table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th><?php echo t("ID") ?></th>
                            <th><?php echo t("Qty") ?></th>
                            <th><?php echo t("GON Number") ?></th>
                            <th><?php echo t("Destination") ?></th>
                            <th><?php echo t("PSO Delivery ID") ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td> <?= $no ?> </td>
                            <td> <?= $r->sku ?></td>
                            <td> <?= $r->lottable07 ?></td>
                            <td> <?= $r->lottable03 ?></td>
                            <td> <?= $r->shippedQty ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php $this->renderPartial('/layouts/copyright', array()); ?>