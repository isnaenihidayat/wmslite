
<div class="modal fade qrcode-in-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog" style="width: 70mm;">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("QRCode Lot#")?> - <span class="lot_id"></span>
                </h4>
            </div>

            <div class="modal-body">
                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('id_lot','')?>
                </form>

                <table border="0" id="FmyQRCode">
                    <tbody id="qrinpallet"></tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button id="QRPrint" type="button" class="btn btn-default QRPrint">Print</button>
            </div>

        </div>
    </div>
</div>