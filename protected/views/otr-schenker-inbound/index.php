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
                <h3 class="panel-title"><?php echo t("Schenker Inbound") ?></h3>
                <a href="<?php echo Yii::app()->createUrl("otr", array()) ?>" class="glyphicon glyphicon-remove" style="color: white;"></a>
            </div>
            <div class="panel-body">
                <div>
                    <a class="btn btn-sm btn-warning" href="<?php echo Yii::app()->createUrl("otr-schenker-inbound/index", array()) ?>">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
                <br>

                <?php $this->renderPartial('/layouts/alert', array()); ?>

                <table id="datatable" class="table table-striped table-bordered table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>receiptKey</th>
                            <th>externReceiptKey</th>
                            <th>lottable07</th>
                            <th>receiptDate</th>
                            <th>actualShipDate</th>
                            <th>status</th>
                            <th>totalQtyReceived</th>
                            <th>itemInInbound</th>
                            <th>totalPick</th>
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

                <h3>Inbound Detail</h3>
                <div id="detail-schenkerx" style="max-height: 400px; overflow-y: scroll;"></div>

                <h3>Pick List</h3>
                <div id="detail-schenkerx-pick" style="max-height: 400px; overflow-y: scroll;"></div>

                <h3>Lot Not Picked Yet</h3>
                <div id="detail-schenkerx-available" style="max-height: 400px; overflow-y: scroll;"></div>

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
            responsive: true,
            order: [[ 0, "desc" ]],
            processing: true,
            serverSide: true,
            ajax: {
                url: "' . $this->createUrl('otrschenkerinbound/getData') . '",
                type: "POST"
            },
            columnDefs: [{
                width: "100px",
                targets: -1,
                className: "dt-center"
            }]
        });
    });


    $("#detailModal").on("show.bs.modal", function (e) {
        var button = e.relatedTarget
        var id = button.getAttribute("data-id")
        var receiptKey = button.getAttribute("data-receiptkey")

        $.ajax({
            url: "' . $this->createUrl('otrschenkerinbound/getDetail') . '",
            type: "POST",
            data: {id : id},
            dataType: "html",
            success: function(data){
                $("#detail-schenkerx").html(data);
            }
        });

        $.ajax({
            url: "' . $this->createUrl('otrschenkerinbound/getPick') . '",
            type: "POST",
            data: {id : id, receiptKey : receiptKey},
            dataType: "html",
            success: function(data){
                $("#detail-schenkerx-pick").html(data);
            }
        });

        $.ajax({
            url: "' . $this->createUrl('otrschenkerinbound/getAvailableLot') . '",
            type: "POST",
            data: {id : id, receiptKey : receiptKey},
            dataType: "html",
            success: function(data){
                $("#detail-schenkerx-available").html(data);
            }
        });

    })

    $("#detailModal").on("hide.bs.modal", function (e) {
        $("#detail-schenkerx").html("");
    })
', CClientScript::POS_END);




?>