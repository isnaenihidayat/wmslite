<div class="wrapper wrapper-content">
    <div class="ibox-content">
        <div class="text-center">
            <h2><?php echo Driver::getCompanyName(); ?></h2>
            <h4>Picking List</h4>
            <br><br>
        </div>

        <div class="table-responsive m-t">
            <table class="table invoice-table">
                <thead>
                    <tr>
                        <th>No</th>;
                        <th>SKU</th>;
                        <th>Lottable07</th>;
                        <th>Lottable03</th>;
                        <th>shippedQty</th>;
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($details as $r) : ?>
                        <tr>
                            <td><?= $r['hawb'] ?></td>
                            <td><?= $r['descr'] ?></td>
                            <td><?= $r['qty'] ?></td>
                            <td><?= $r['po'] ?></td>
                            <td><?= $r['loc'] ?></td>
                            <td><?= $r['destination'] ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div><!-- /table-responsive -->

        <div class="well m-t text-center">WMSLite powered by <strong>eLog.ID</strong></div>
    </div>
</div>

<?php

Yii::app()->clientScript->registerScript('print-custom', '
window.print();   
', CClientScript::POS_END);

?>