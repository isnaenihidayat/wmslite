
<div class="modal fade list-out-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Outbound#")?> - <span class="id_"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('idHawb_','')?>
                </form>
                    <div class="inner">

                        <div class="table-responsive">
                            <table id="out_List" class="table table-hover issue-tracker">
                            <thead>
                            <tr>
                                <th><?php echo t("No")?></th>
                                <th><?php echo t("HAWB#")?></th>
                                <th><?php echo t("SKU")?></th>
                                <th><?php echo t("Qty")?></th>
                                <th><?php echo t("Notes")?></th>
                            </tr>
                            </thead>
                                <tbody id="tbllist_"></tbody>
                            </table>
                        </div>

                    </div>

                    <p>Jumlah outbound di Schenker: <span id="total-schenker"></span></p>
                    <p><a href="#" id="link-outbound-schenker" target="_blank">Detail Outbound Schenker vs Wmslite</a></p>
                    <div id="display-details-schenker">
                        <p><a href="#" id="link-outbound-schenker-sync" target="_blank" onclick="return confirm('Are you sure to re-sync?')">Re-Sync Outbound with Schenker</a></p>
                    </div>

            </div>
            <div class="modal-footer">
                <span class="docfile"></span>
            </div>

        </div>
    </div>
</div>