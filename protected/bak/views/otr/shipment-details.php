<div class="modal fade list-sh-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("HAWB#")?> - <span class="hawb-id"></span>
                </h4>
            </div>

            <div class="modal-body" style="padding: 0px;">

                <form id="frm" class="frm" method="POST" onsubmit="return false;">
                    <?php echo CHtml::hiddenField('idHawb_sh','')?>
                </form>
                    <div class="inner">
                        
                        <div class="table-responsive">
                            <table id="sh_List" class="table table-hover issue-tracker" style="margin-bottom: 0px;">
                            <thead>
                            <tr>
                                <th class="text-center"><?php echo t("No")?></th>
                                <th class="text-center"><?php echo t("HAWB#")?></th>
                                <th class="text-center"><?php echo t("Weight")?></th>
                                <th class="text-center"><?php echo t("Length")?></th>
                                <th class="text-center"><?php echo t("Width")?></th>
                                <th class="text-center"><?php echo t("Height")?></th>
                                <th class="text-center"><?php echo t("Action")?></th>
                            </tr>
                            </thead>
                                <tbody id="tbllistsh"></tbody>
                            </table>
                        </div>

                    </div>

            </div>

            <div class="modal-footer">
                <span class="docfile"></span>
            </div>

        </div>
    </div>
</div>