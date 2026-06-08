<?php echo CHtml::hiddenField('currentForm', 'pickingListSchenker')?>

<div class="wrapper wrapper-content">
    <div class="ibox-content">
        <div class="text-center">
            <h2><?php echo Driver::getCompanyName();?></h2>
            <h4>Picking List (Schenker)</h4>
            <br><br>
        </div>

        <div class="table-responsive m-t">
            <table class="table invoice-table">
                <thead>
                    <tr>
                        <th>HAWB#</th>
                        <th>LPN</th>
                        <th>Qty</th>
                        <th>Location</th>
                        <th>Destination</th>
                    </tr>
                </thead>
                <tbody id="tbPicklist"></tbody>
            </table>
        </div><!-- /table-responsive -->

        <div class="well m-t text-center">WMSLite powered by <strong>eLog.ID</strong></div>
    </div>
</div>