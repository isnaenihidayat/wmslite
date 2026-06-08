<div class="modal fade demo_movement_detail_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button aria-label="Close" data-dismiss="modal" class="close" type="button">
                    <span aria-hidden="true"><i class="ion-android-close"></i></span>
                </button>
                <h4 id="mySmallModalLabel" class="modal-title">
                    <?php echo t("Detail Demo Movement") ?>
                </h4>
            </div>

            <div class="modal-body">

                <table id="demo_movement_list_detail" class="table table-striped table-bordered table-hover" style="width: 100% !important;">
                    <thead>
                        <tr>
                            <th><?php echo t("HAWB") ?></th>
                            <th><?php echo t("Current Location / Warehouse") ?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>

        </div>
    </div>
</div>