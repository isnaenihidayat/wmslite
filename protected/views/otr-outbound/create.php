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
                <h3 class="panel-title"><?php echo t("Create Outbound") ?></h3>
                <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
            </div>
            <div class="panel-body">

                <form method="POST" class="frm" enctype="multipart/form-data" action="<?php echo Yii::app()->createUrl("otr-outbound/store", array()) ?>">
                    <div class="inner">

                        <div class="row">
                            <div class="col-md-4 ">
                                <?php echo t("Destination"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('destination', '', array(
                                    'placeholder' => t("Destination"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("GON Number"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::textField('po', '', array(
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
                                <?php echo CHtml::textField('delivery_id', '', array(
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
                                <?php echo CHtml::textField('transporter', '', array(
                                    'placeholder' => t("Transporter"),
                                    'required' => true
                                )) ?>
                            </div>
                        </div>

                        <div class="row top10">
                            <div class="col-md-4 ">
                                <?php echo t("Document"); ?><span style="color:red;">*</span>
                            </div>
                            <div class="col-md-8 ">
                                <?php echo CHtml::fileField('file', '', array(
                                    'required' => true
                                )) ?>
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