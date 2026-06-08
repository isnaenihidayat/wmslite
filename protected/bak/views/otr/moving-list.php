<div class='container popup movPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Moving") ?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body moving-list" id="moving-list">
            <div class="row">
                <div class="col-md-12">
                    <?php if ($_SESSION['wmslite']['admin'] == 1 || $_SESSION['wmslite']['type'] == 1) : ?>
                        <a class="btn btn-primary new-moving" href="javascript:;">
                            <?php echo t("New Moving") ?>
                        </a>
                    <?php endif ?>
                    <a class="btn btn-warning refresh-table" href="javascript:;">
                        <?php echo t("Refresh") ?>
                    </a>
                </div>
            </div>
            <br>
            <div style="height: 70vh; overflow: scroll;">
                <form id="frm_table" class="frm_table">
                    <?php echo CHtml::hiddenField('action', 'movList') ?>
                    <table id="moving_list" class="table table-striped table-bordered table-hover dataTables-example">
                        <thead>
                            <tr>
                                <th><?php echo t("ID") ?></th>
                                <th><?php echo t("Hawb") ?></th>
                                <th><?php echo t("Hawb-Sub") ?></th>
                                <th><?php echo t("Loc Before") ?></th>
                                <th><?php echo t("Loc After") ?></th>
                                <th><?php echo t("Date") ?></th>
                                <th><?php echo t("Users") ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>