<div class='container popup outPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Outbound")?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body outbound-list" id="outbound-list">
            <a class="btn btn-primary new-outbound" href="javascript:;">
                <?php echo t("Add Outbound")?>
            </a>
            <a class="btn btn-warning refresh-table" href="javascript:;">
                <?php echo t("Refresh")?>
            </a>
            <form id="frm_table" class="frm_table">
                <?php echo CHtml::hiddenField('action','outLists')?>
                <table id="outbound_list" class="table table-striped table-bordered table-hover dataTables-example">
                    <thead>
                    <tr>
                        <th ><?php echo t("ID")?></th>
                        <th ><?php echo t("Qty")?></th>
                        <th ><?php echo t("Destination")?></th>
                        <th ><?php echo t("Checker")?></th>
                        <th ><?php echo t("Create Date")?></th>
                        <th ><?php echo Driver::t("Status")?></th>
                        <th ><?php echo Driver::t("Action")?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </form>
        </div>
    </div>
</div>
