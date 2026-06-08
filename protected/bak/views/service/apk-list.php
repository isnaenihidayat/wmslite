<div class='container popup apkPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Checker")?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body apk-lists" id="apk-lists">
            <a class="btn btn-primary new-apk" href="javascript:;">
                <?php echo t("Add Checker")?>
            </a>
            <a class="btn btn-warning refresh-table" href="javascript:;">
                <?php echo t("Refresh")?>
            </a>
            <form id="frm_table" class="frm_table">
                <?php echo CHtml::hiddenField('action','apkLists')?>
                <table id="apk_lists" class="table table-striped table-bordered table-hover dataTables-example">
                    <thead>
                    <tr>
                        <th ><?php echo t("Name")?></th>
                        <th ><?php echo t("username")?></th>
                        <th ><?php echo t("last login")?></th>
                        <th ><?php echo Driver::t("Action")?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </form>
        </div>
    </div>
</div>
