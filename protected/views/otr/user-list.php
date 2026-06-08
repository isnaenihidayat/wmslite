<div class='container popup userPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("User") ?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body user-list" id="user-list">
            <div class="row">
                <div class="col-md-12">
                    <?php if ($_SESSION['wmslite']['admin'] == 1) : ?>
                        <a class="btn btn-primary new-user" href="javascript:;">
                            <?php echo t("Add User") ?>
                        </a>
                    <?php endif ?>
                    <a class="btn btn-warning refresh-table" href="javascript:;">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <div style="height: 70vh; overflow: scroll;">
                <form id="frm_table" class="frm_table">
                    <?php echo CHtml::hiddenField('action', 'userList') ?>
                    <?php echo CHtml::hiddenField('module', '1') ?>
                    <table id="user_list" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th><?php echo t("Name") ?></th>
                                <th><?php echo t("Phone") ?></th>
                                <th><?php echo t("Email") ?></th>
                                <th><?php echo t("Type") ?></th>
                                <th><?php echo t("is Admin?") ?></th>
                                <th><?php echo t("Status") ?></th>
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