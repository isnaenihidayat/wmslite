
<div class="modal fade list-in-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("HAWB#")?> - <span class="hawb-id"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('idHawb','')?>
                </form>
                    <div class="inner">
                        
                        <div class="table-responsive">
                            <table id="in_List" class="table table-hover issue-tracker">
                            <thead>
                            <tr>
                                <th ><?php echo t("No")?></th>
                                <th ><?php echo t("Part Number")?></th>
                                <th ><?php echo t("Description")?></th>
                                <th ><?php echo t("Qty")?></th>
                            </tr>
                            </thead>
                                <tbody id="tbllist"></tbody>
                            </table>
                        </div>

                    </div>

            </div>

            <div class="modal-footer">
                <span class="docfile"></span>
                <button id="QRPrintAlls" type="button" class="btn btn-success QRPrintAlls">Print All</button>
            </div>

        </div>
    </div>
</div>
