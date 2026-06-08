<div class="modal fade push-outbound-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="">
                    <?php echo t("Push Outbound"); ?>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frmPushOutbound" class="frm" method="POST" action="<?= Yii::app()->createUrl('/otr/push-outbound') ?>">
                    <?php echo CHtml::hiddenField('action', 'addPushOutbound') ?>
                    <?php echo CHtml::hiddenField('pusho_id_inbound', '') ?>
                    <div class="inner">

                        <div id="main-push-outbound" style="display: block;">

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("HAWB"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pusho_hawb', '', array(
                                        'placeholder' => t("HAWB"),
                                        'required' => true,
                                        'readOnly' => '',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("Destination"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pusho_destination', '', array(
                                        'placeholder' => t("Destination"),
                                        'required' => true,
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("Transporter"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pusho_transporter', '', array(
                                        'placeholder' => t("Transporter"),
                                        'required' => true,
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("Ship Date"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pusho_ship_date', '', array(
                                        'class' => "form-control datetimepicker",
                                        'required' => true,
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("GON Number"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pusho_gon_number', '', array(
                                        'placeholder' => t("GON Number"),
                                        'required' => true,
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>

                            <div class="row top10">
                                <div class="col-md-4 top10" id="hawb_title">
                                    <?php echo t("PSO Delivery ID"); ?>
                                </div>
                                <div class="col-md-8 ">
                                    <?php echo CHtml::textField('pusho_pso_delivery_id', '', array(
                                        'placeholder' => t("PSO Delivery ID"),
                                        'required' => true,
                                        'autocomplete' => 'off',
                                    )) ?>
                                </div>
                            </div>


                        </div>

                        <div class="row top20">
                            <div class="col-md-6" style="text-align: left;">
                                <!-- <a id="upload-doc_sh" class="btn btn-warning"><?php //echo t("Upload Document") 
                                                                                    ?></a>
                                <div id="progressBar_sh"></div>
                                <div id="progressOuter_sh"></div>
                                <div id="msgBox_sh"></div> -->
                            </div>
                            <div class="col-md-6" style="text-align: right;">
                                <button class="btn btn-primary" type="submit" id="submitPushOutbound"><?php echo t("Submit") ?></button>
                            </div>
                        </div>


                    </div>
                </form>

            </div>

        </div>
    </div>
</div>