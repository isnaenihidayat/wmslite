<div class="modal fade list-in-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("HAWB#")?> - <span class="hawb-id"></span> / SSO Delivery ID: <span class="delivery-id"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('idHawb','')?>
                    <?php echo CHtml::hiddenField('deliveryId','')?>
                </form>
                    <div class="inner">
                        
                        <div class="table-responsive">
                            <table id="in_List" class="table table-hover issue-tracker">
                            <thead>
                            <tr>
                                <th class="text-center"><?php echo t("No")?></th>
                                <th class="text-center"><?php echo t("SKU")?></th>
                                <th class="text-center"><?php echo t("Descr")?></th>
                                <th class="text-center"><?php echo t("Location")?></th>
                                <th class="text-center"><?php echo t("Scan Time")?></th>
                                <th class="text-center"><?php echo t("Qty")?></th>
                            </tr>
                            </thead>
                                <tbody id="tbllist"></tbody>
                            </table>
                        </div>

                    </div>

            </div>

            <div class="modal-footer">
                <span class="docfile"></span>
                <button id="QRPrintAll" type="button" class="btn btn-success QRPrintAll">Print All</button>
            </div>

        </div>
    </div>
</div>