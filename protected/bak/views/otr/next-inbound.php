<div class="modal fade listin-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <!--<button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>-->
                <h4 id="mySmallModalLabel" class="listin-title">
                    <?php echo t("HAWB#")?> - <span class="hawb-id"></span>
                </h4>
            </div>

            <div class="modal-body">

                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('idHawbs','')?>
                </form>
                    <div class="inner">
                        
                        <div class="table-responsive">
                            <table id="inList" class="table table-hover issue-tracker">
                            <thead>
                            <tr>
                                <th ><?php echo t("No")?></th>
                                <th ><?php echo t("HAWB#")?></th>
                                <th ><?php echo t("Gross Weight")?></th>
                                <th ><?php echo t("Length")?></th>
                                <th ><?php echo t("Width")?></th>
                                <th ><?php echo t("Height")?></th>
                                <th ><?php echo t("Action")?></th>
                            </tr>
                            </thead>
                                <tbody id="listin"></tbody>
                            </table>
                        </div>

                    </div>

            </div>

            <div class="modal-footer">
                <!--<span class="docsfile"></span>
                <button id="QRPrintAll" type="button" class="btn btn-success QRPrintAll">Print All</button>-->
                <button id="submitNext" type="button" class="btn btn-primary submitNext"><?php echo t("Submit")?></button>
            </div>

        </div>
    </div>
</div>