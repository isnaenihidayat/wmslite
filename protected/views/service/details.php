
<div class="modal fade detail-in-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-titles">
                    <?php echo t("Part Number#")?> - <span class="partnumber"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('idPart','')?>
                </form>
                    <div class="inner">
                        
                        <div class="table-responsive">
                            <table id="in_Lists" class="table table-hover issue-tracker">
                            <thead>
                            <tr>
                                <th ><?php echo t("No")?></th>
                                <th ><?php echo t("Lot Number")?></th>
                                <th ><?php echo t("Location")?></th>
                                <th ><?php echo t("Action")?></th>
                            </tr>
                            </thead>
                                <tbody id="tbllists"></tbody>
                            </table>
                        </div>

                    </div>

            </div>

            <div class="modal-footer">
                <!--<span class="docfile"></span>-->
                <button id="QRPrintAll" type="button" class="btn btn-success QRPrintAll">Print All</button>
            </div>

        </div>
    </div>
</div>
