<style>
    body {
        background-color: white;
    }

    @media print {
        body * {
            visibility: visible;
        }

        h1 {
            margin-bottom: 20px;
            padding-bottom: 20px;
        }
    }

    #wrapper {
        background-color: #2f4050;
    }
</style>
<!-- <div class="PanelOverlay" style="display: block;"></div> -->
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
                    <a class="btn btn-sm btn-warning" href="<?php echo Yii::app()->createUrl("otr-schenker-outbound/index", array()) ?>">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
                <br>

                <?php $this->renderPartial('/layouts/alert', array()); ?>

                <table id="datatable" class="table table-striped table-bordered table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>GON Number</th>
                            <th>Destination</th>
                            <th>PSO Delivery ID</th>
                            <th>Transporter</th>
                            <th>Ship Date</th>
                            <th>Last Update</th>
                            <th>Status</th>
                            <th style="width: 100px; text-align:center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="detail-schenker">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Detail</h4>
            </div>
            <div class="modal-body">
                <div id="detail-schenkerx" style="max-height: 400px; overflow-y: scroll;"></div>


                <h3>Lot</h3>
                <div id="detail-schenker-lot" style="max-height: 400px; overflow-y: scroll;"></div>

                <h3>Document</h3>
                <form action="<?= $this->createUrl('otrschenkeroutbound/upload') ?>" id="form-upload" method="POST" enctype="multipart/form-data">
                    <input type="hidden" value="" id="orderKey" name="orderKey">
                    <div class="form-group">
                        <label for="uploadfile">Upload Doc</label>
                        <input type="file" name="uploadfile" id="uploadfile">
                        <br>
                        <input type="submit" value="Upload" class="btn btn-primary">
                    </div>
                </form>

                <div id="docfile-schenkerx"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
            </div>
        </div>
    </div>
</div>

<?php $this->renderPartial('/layouts/copyright', array()); ?>

<?php

Yii::app()->clientScript->registerScript('datatable-custom', '
    $(document).ready(function() {
        var dataTable = $(\'#datatable\').DataTable({
            layout: {
                topStart: {
                    buttons: ["pageLength", "copy", "csv", "excel", "pdf", "print"]
                }
            },
            pageLength: 10,
            lengthMenu: [
                [10, 50, 100, -1],
                [10, 50, 100, "All"]
            ],
            responsive: true,
            order: [[ 0, "desc" ]],
            processing: true,
            serverSide: true,
            ajax: {
                url: "' . $this->createUrl('otrschenkeroutbound/getData') . '",
                type: "POST"
            },
            columnDefs: [{
                width: "100px",
                targets: -1,
                className: "dt-center"
            }],
        });
    });


    $("#detailModal").on("show.bs.modal", function (e) {
        var button = e.relatedTarget
        var id = button.getAttribute("data-id")
        var orderKey = button.getAttribute("data-orderkey")

        $.ajax({
            url: "' . $this->createUrl('otrschenkeroutbound/getDetail') . '",
            type: "POST",
            data: {id : id},
            dataType: "html",
            success: function(data){
                $("#detail-schenkerx").html(data);
            }
        });

        $.ajax({
            url: "' . $this->createUrl('otrschenkeroutbound/getDocfile') . '",
            type: "POST",
            data: {orderKey : orderKey},
            dataType: "html",
            success: function(data){
                $("#docfile-schenkerx").html(data);
            }
        });

        $.ajax({
            url: "' . $this->createUrl('otrschenkeroutbound/getLot') . '",
            type: "POST",
            data: {orderKey : orderKey},
            dataType: "html",
            success: function(data){
                $("#detail-schenker-lot").html(data);
            }
        });

        document.getElementById("orderKey").value = orderKey;
    })

    $("#detailModal").on("hide.bs.modal", function (e) {
        $("#detail-schenkerx").html("");
    })
', CClientScript::POS_END);




?>