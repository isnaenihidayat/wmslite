<div class="modal fade new-in-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php
                    if ($_SESSION['wmslite']['type'] == '1') {
                        $titleox = t("Add Inbound");
                    } else {
                        $titleox = t("Add Shipment");
                    }
                    ?>
                    xxx<?php echo $titleox . 'xx' . $_SESSION['wmslite']['type'] ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmIn" class="frm frmIn" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('action', 'addIn') ?>
                    <?php echo CHtml::hiddenField('idInb', '') ?>
                    <?php echo CHtml::hiddenField('filename', '') ?>
                    <?php echo CHtml::hiddenField('warehouse_in', '') ?>
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("HAWB"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('hawb', '', array(
                                    'placeholder' => t("HAWB"),
                                    // 'required' => true,
                                    'autocomplete' => 'off',
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Description"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('hawb_descr', '', array(
                                    'placeholder' => t("Description"),
                                    'autocomplete' => 'off',
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                                <div class="col-md-4 top10"><?php echo t("Product Category"); ?><span style="color:red;">*</span></div>
                                <div class="col-md-8 ">
                                    <?php
                                    $product = Yii::app()->db->createCommand()
                                    ->select('id, name')
                                    ->from('el_product_category')
                                    ->queryAll();
                                    
                                    $product_array = ['' => '- Choose Product Category -'];
                                    foreach($product as $r){
                                        $product_array[$r['id']] = $r['name'];
                                    }

                                    ?>
                                    <?php echo CHtml::dropDownList('product_category_in', '', $product_array, array(
                                        'required' => true
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("Modality"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('modality_in', '', array(
                                        'placeholder' => t("Modality"),
                                        'autocomplete' => 'off',
                                        // 'required' => true,
                                        'maxlength' => 3,
                                        'onkeypress' => 'validateNumericOnly(event)'
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10">
                                    <?php echo t("SSO Delivery ID"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('delivery_id_in', '', array(
                                        'placeholder' => t("SSO Delivery ID"),
                                        'autocomplete' => 'off',
                                        'required' => false,
                                        'data-validation-length' => 'min4',
                                    )) ?>
                                </div>
                            </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Qty"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('qty', '', array(
                                    'placeholder' => t("Qty Koli"),
                                    // 'required' => true,
                                    'autocomplete' => 'off',
                                    'onkeypress' => 'validateNumericOnly(event)',
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("PO Number"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('po_number', '', array(
                                    'placeholder' => t("PO Number"),
                                    'autocomplete' => 'off',
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Oracle Locator"); ?>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('locator_number', '', array(
                                    'placeholder' => t("Oracle Locator"),
                                    'autocomplete' => 'off',
                                )) ?>
                            </div>
                        </div>


                        <div class="row top20">
                            <div class="col-md-8">
                                <a id="upload-doc" class="btn btn-warning"><?php echo t("Upload Document") ?></a>
                                <button class="btn btn-primary" type="submit" id="submitIn" disabled><?php echo t("Next") ?></button>
                                <div id="progressBar"></div>
                                <div id="progressOuter"></div>
                                <div id="msgBox"></div>
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
  if( !regex.test(key) ) {
    theEvent.returnValue = false;
    if(theEvent.preventDefault) theEvent.preventDefault();
  }
}

</script>