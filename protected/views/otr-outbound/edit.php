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
                <h3 class="panel-title"><?php echo t("Edit Outbound") ?></h3>
                <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
            </div>
            <div class="panel-body">

                <?php $this->renderPartial('/layouts/alert', array()); ?>

                <form method="POST" class="frm" enctype="multipart/form-data" action="<?php echo Yii::app()->createUrl("otr-outbound/update", array('id' => $header['id'])) ?>">
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Destination"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('destination', $header['destination'], array(
                                    'placeholder' => t("Destination"),
                                    'required' => true,
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("GON Number"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('po', $header['po'], array(
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
                                <?php echo CHtml::textField('delivery_id', $header['delivery_id'], array(
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
                                <?php echo CHtml::textField('transporter', $header['transporter'], array(
                                    'placeholder' => t("Transporter"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Status"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::dropDownList(
                                    'status',
                                    $header['status'],
                                    [
                                        'draft' => 'draft',
                                        'inprogress' => 'inprogress',
                                        // 'successful' => 'successful',
                                    ],
                                    array(
                                        'class' => "",
                                        'style' => "width: 25%;"
                                    )
                                ) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Document"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::fileField('file', '', array(
                                    'required' => false
                                )) ?>
                                <br>
                                <a href="/upload/<?= $header['docfile'] ?>" target="_blank">Download Document</a>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                            </div>
                            <div class="col-md-8">
                                <button class="btn btn-sm btn-primary" type="submit"><?php echo t("Submit") ?></button>
                                <a class="btn btn-sm btn-warning" href="<?php echo Yii::app()->createUrl("otr-outbound/index", array()) ?>">
                                    <?php echo t("Cancel") ?>
                                </a>
                            </div>
                        </div>


                    </div>
                </form>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h3 style="margin-left: 25px;">Add Detail Outbound</h3>

                        <?php if ($header['status'] != 'successful') : ?>

                        <div class="row">
                            <div class="col-md-6">
                                <form method="POST" class="frm" action="<?php echo Yii::app()->createUrl("otr-outbound/store-item", array('id' => $header['id'])) ?>">
                                    <div class="inner">

                                        <div class="row">
                                            <div class="col-md-4 ">
                                                <?php echo t("HAWB"); ?><span style="color:red;">*</span>
                                            </div>
                                            <div class="col-md-8 ">
                                                <?php echo CHtml::dropDownList(
                                                    'hawb',
                                                    '',
                                                    array("" => t("Choose"),),
                                                    array(
                                                        'class' => "select2_class form-control",
                                                        // 'style' => "width: 70%;"
                                                    )
                                                ) ?>
                                            </div>
                                        </div>

                                        <div class="row top10">
                                            <div class="col-md-4 ">
                                            </div>
                                            <div class="col-md-8">
                                                <button class="btn btn-sm btn-primary" type="submit"><?php echo t("Add Item") ?></button>
                                            </div>
                                        </div>


                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6" style="text-align: right;">
                                <form action="<?php echo Yii::app()->createUrl("otr-outbound/delete-all-item", array('id' => $header['id'])) ?>" method="POST">
                                    <button class="btn btn-sm btn-danger" type="submit" onclick="return confirm('Are you sure to delete all item?')" style="margin-right: 25px;">Delete All Item</button>
                                </form>
                            </div>
                        </div>

                        <br>

                        <?php endif ?>

                        <div style="padding: 0 25px;">

                            <table class="table table-striped table-borderd table-hovered">
                                <tr>
                                    <th>#</th>
                                    <th>ID Inb Details</th>
                                    <th>HAWB</th>
                                    <th>SKU</th>
                                    <th>Qty</th>
                                    <th>Notes</th>
                                    <th style="width: 150px; text-align:center;">Action</th>
                                </tr>
                                <?php $no = 1; ?>
                                <?php foreach ($details as $r) : ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= $r['id_inbound_details'] ?></td>
                                        <td><?= $r['hawb'] ?></td>
                                        <td><?= $r['descr'] ?></td>
                                        <td>
                                            <?php if ($header['status'] != 'successful') : ?>
                                                <form action="<?php echo Yii::app()->createUrl("otr-outbound/update-item", array('idk' => $r['idk'])) ?>" class="form-inline" method="POST">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="id_inbound_details" value="<?= $r['id_inbound_details'] ?>">
                                                    <input type="text" name="qty_edit" value="<?= $r['qty'] ?>" class="form-control" style="width:50px;">
                                                    <input type="submit" class="btn btn-sm btn-primary" value="Update">
                                                </form>
                                            <?php else : ?>
                                                <?= $r['qty'] ?>
                                            <?php endif ?>
                                        </td>
                                        <td><?= $r['notes'] ?></td>
                                        <td style="text-align: center;">
                                            <?php if ($header['status'] != 'successful') : ?>
                                                <form action="<?php echo Yii::app()->createUrl("otr-outbound/delete-item", array('idk' => $r['idk'])) ?>" method="POST">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="id_inbound_details" value="<?= $r['id_inbound_details'] ?>">
                                                    <input type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure to delete this item?')" value="Delete">
                                                </form>
                                            <?php endif ?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </table>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php //$this->renderPartial('/layouts/copyright', array()); ?>

<?php

Yii::app()->clientScript->registerScript('select2-custom', '
$("#hawb").select2({
    ajax: {
        url: "' . $this->createUrl('otr-outbound/get-hawb') . '",
        dataType: \'json\',
        delay: 250,
        data: function (params) {
            return {
                idHawb: params.term
            };
        },
        processResults: function (data) {
            return {
                results: data
            };
        },
        cache: true
    },
    minimumInputLength: 1
});

function empty(data) {
    if (typeof data === "undefined" || data == null || data == "") {
        return true;
    }
    return false;
}

// $(\'#hawb\').on(\'change\', function () {
//     idHawb = $(this).val();
//     console.log(idHawb);
//     if (!empty(idHawb)) {
//          $.ajax({
//             type: "GET",
//             url: "' . $this->createUrl('otr-outbound/get-hawb-detail') . '",
//             data: "idHawb=" + idHawb,
//             cache: false,
//             success: function(data){
//                 $("#qty").val(data);
//             }
//         });
//     }
// });
   
', CClientScript::POS_END);

?>