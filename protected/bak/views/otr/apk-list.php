<div class='container popup apkPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Checker") ?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body apk-list" id="apk-list">
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-primary new-apk" href="javascript:;">
                        <?php echo t("Add Checker") ?>
                    </a>
                    <a class="btn btn-warning refresh-table" href="javascript:;">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <br>
            <div style="height: 70vh; overflow: scroll;">
                <form id="frm_table" class="frm_table">
                    <?php echo CHtml::hiddenField('action', 'apkList') ?>
                    <table id="apk_list" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th><?php echo t("Name") ?></th>
                                <th><?php echo t("username") ?></th>
                                <th><?php echo t("last login") ?></th>
                                <th><?php echo Driver::t("Action") ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>