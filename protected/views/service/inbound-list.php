<div class='container popup inPopup'>
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Inbound")?></h3><span class="glyphicon glyphicon-remove"></span>
        </div>
        <div class="panel-body inbound-list" id="inbound-list">
            <a class="btn btn-primary new-inbound" href="javascript:;">
                <?php echo t("Add Inbound")?>
            </a>
            <a class="btn btn-warning refresh-table" href="javascript:;">
                <?php echo t("Refresh")?>
            </a>
            <form id="frm_table" class="frm_table">
                <?php echo CHtml::hiddenField('action','inLists')?>
                <table id="inbound_list" class="table table-striped table-bordered table-hover dataTables-example">
                    <thead>
                    <tr>
                        <th ><?php echo t("ID")?></th>
                        <th ><?php echo t("HAWB")?></th>
                        <th ><?php echo t("PO Number")?></th>
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
