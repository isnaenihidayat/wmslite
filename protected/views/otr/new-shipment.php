<div class="modal fade new-sh-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Add Shipment"); ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmSh" class="frm frmSh" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action', 'addSh') ?>
                    <?php echo CHtml::hiddenField('idInb_sh', '') ?>
                    <?php echo CHtml::hiddenField('filename_sh', '') ?>
                    <?php echo CHtml::hiddenField('method_sh', '') ?>
                    <?php echo CHtml::hiddenField('hawb_old_sh', '') ?>
                    <div class="inner">

                        <div id="main-shipment" style="display: block;">

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("Warehouse"); ?><span style="color:red;">*</span>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::dropDownList('warehouse_sh', '', [
                                        'marunda' => 'Marunda',
                                        // 'arcadia' => 'Arcadia',
                                    ], array(
                                        //'required' => true
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("HAWB"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('hawb_sh', '', array(
                                        'placeholder' => t("HAWB"),
                                        // 'required' => true,
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="desc_title">
                                    <?php echo t("Description"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('hawb_descr_sh', '', array(
                                        'placeholder' => t("Description"),
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="product_cat_title"><?php echo t("Product Category"); ?></div>
                                <div class="col-md-8 ">
                                    <?php
                                    $product = Yii::app()->db->createCommand()
                                        ->select('id, name')
                                        ->from('el_product_category')
                                        ->queryAll();

                                    $product_array = ['' => '- Choose Product Category -'];
                                    foreach ($product as $r) {
                                        $product_array[$r['id']] = $r['name'];
                                    }

                                    ?>
                                    <?php echo CHtml::dropDownList('product_category_sh', '', $product_array, array(
                                        //'required' => true
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("Modality"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('modality_sh', '', array(
                                        'placeholder' => t("Modality"),
                                        'autocomplete' => 'off',
                                        //'required' => true,
                                        'maxlength' => 3,
                                        'onkeypress' => 'validateNumericOnly(event)'
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10"  id="delivery_id_title">
                                    <?php echo t("SSO Delivery ID"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('delivery_id_sh', '', array(
                                        'placeholder' => t("SSO Delivery ID"),
                                        'autocomplete' => 'off',
                                        //'required' => true,
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10"><?php echo t("Ship Method"); ?></div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::dropDownList('ship_method_sh', '', array('' => '- Choose Ship Method -', 'Air' => 'Air', 'Ocean' => 'Ocean', 'Land' => 'Land', 'Express' => 'Express'), array(
                                        //'required' => true
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10"><?php echo t("ETD"); ?></div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField(
                                        'etd_sh',
                                        '',
                                        array('class' => "form-control datetimepicker", 'autocomplete' => 'off')
                                    )
                                    ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10"><?php echo t("ETA"); ?></div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField(
                                        'eta_sh',
                                        '',
                                        array('class' => "form-control datetimepicker", 'autocomplete' => 'off')
                                    )
                                    ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("Qty"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('qty_sh', '', array(
                                        'placeholder' => t("Qty Koli"),
                                        //'required' => true,
                                        'autocomplete' => 'off',
                                        'onkeypress' => 'validateNumericOnly(event)'
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("PO Number"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('po_number_sh', '', array(
                                        'placeholder' => t("PO Number"),
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>
                        </div>

                        <?php
                        //update #1 munculin form dgn data2 awal + kolom tambahan untuk ATA & PIB Number --> save --> status Custom Process
                        //update #2 SPPB date, locator -> save -> warehouse in transit
                        ?>


                        <div id="update1-shipment" style="display: none;">
                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("ATA"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField(
                                        'ata_sh',
                                        '',
                                        array('class' => "form-control datetimepicker", 'autocomplete' => 'off')
                                    )
                                    ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("PIB Number"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pib_number_sh', '', array(
                                        'placeholder' => t("PIB Number"),
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>
                        </div>

                        <div id="update2-shipment" style="display: none;">

                            <div class="row top10">
                                <div class="col-md-4 top10"><?php echo t("SPPB Date"); ?></div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField(
                                        'sppb_date_sh',
                                        '',
                                        array('class' => "form-control datetimepicker", 'autocomplete' => 'off')
                                    )
                                    ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("Oracle Locator"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('locator_number_sh', '', array(
                                        'placeholder' => t("Oracle Locator"),
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>
                        </div>



                        <div class="row top20">
                            <div class="col-md-6" style="text-align: left;">
                                <a id="upload-doc_sh" class="btn btn-warning"><?php echo t("Upload Document") ?></a>
                                <div id="progressBar_sh"></div>
                                <div id="progressOuter_sh"></div>
                                <div id="msgBox_sh"></div>
                            </div>
                            <div class="col-md-6" style="text-align: right;">
                                <button class="btn btn-primary" type="submit" id="submitSh" disabled><?php echo t("Submit") ?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>

<script>
    function validateNumericOnly(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }
    }
</script>

<?php

// Yii::app()->clientScript->registerScript('myjquery', '
// 	$(\'#frmSh\').on(\'submit\', function(){
// 		if ($("input[name=\'delivery_id_sh\']").val() == \'\') {
//             alert(\'SSO delivery ID harus diisi\')
//             e.preventDefault();
//             return false;
//         }
// 	})
// ');

?>