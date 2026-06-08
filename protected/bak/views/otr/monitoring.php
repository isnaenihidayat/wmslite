<style>
    body {
        background-color: #fff;
    }

    #table-monitoring {
        font-family: Arial, Helvetica, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    #table-monitoring td,
    #table-monitoring th {
        border: 1px solid #ddd;
        padding: 8px;
    }

    #table-monitoring tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    #table-monitoring tr:hover {
        background-color: #ddd;
    }

    #table-monitoring th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: #293a4a;
        color: white;
    }
</style>

<div id="wrapperx">
    <div id="map-wrappexr" class="white-bg">

        <div class="row border-bottom white-bg">
            <?php //$this->renderPartial('/tpl/top_otr', array()); 
            ?>
        </div>

        <div class="wrapper wrapper-content dashboard-work-area" id="ical">

            <div style="text-align: right;">
                <a href="<?php echo Yii::app()->createUrl('/otr') ?>">Exit</a>
            </div>

            <h1 style="font-weight: 600; text-align: center; margin-bottom: 30px;">Queue List of Warehouse</h1>

            <div class="row">
                <div class="col-md-6">
                    <?php
                    $sql = "SELECT h.*, (SELECT SUM(d.weight) FROM el_inbound_details d WHERE d.hawb=h.hawb) AS gw FROM el_inbound_header h WHERE h.status='created' OR h.status='Warehouse in Transit' ORDER BY h.date_updated DESC";
                    $query = Yii::app()->db->createCommand($sql)->queryAll();
                    //var_dump($query);
                    ?>

                    <table class="table table-bordered" id="table-monitoring">
                        <tr>
                            <th colspan="5" style="text-align: center;">Inbound</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;">#</th>
                            <th style="text-align: center;">HAWB</th>
                            <th style="text-align: center;">QTY</th>
                            <th style="text-align: center;">PO Number</th>
                            <th style="text-align: center;">GW</th>
                        </tr>
                        <?php $no = 1 ?>
                        <?php if (empty($query)) : ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No data available</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($query as $r) : ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $no ?></td>
                                    <td style="text-align: center;"><?php echo $r['hawb'] ?></td>
                                    <td style="text-align: center;"><?php echo $r['qty'] ?></td>
                                    <td style="text-align: center;"><?php echo $r['po'] ?></td>
                                    <td style="text-align: center;"><?php echo $r['gw'] ?></td>
                                </tr>
                                <?php $no++ ?>
                            <?php endforeach ?>
                        <?php endif ?>
                    </table>
                </div>
                <div class="col-md-6">
                    <?php
                    $sql = "SELECT h.*, d.descr as hawb FROM el_outbound_header h 
                    JOIN el_outbound_details d ON d.id=h.id
                    WHERE h.status='created' OR h.status='inprogress' ORDER BY h.date_created ASC";
                    $query = Yii::app()->db->createCommand($sql)->queryAll();
                    //var_dump($query);
                    ?>

                    <table class="table table-bordered" id="table-monitoring">
                        <tr>
                            <th colspan="5" style="text-align: center;">Outbound</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;">#</th>
                            <th style="text-align: center;">GON</th>
                            <th style="text-align: center;">Transporter</th>
                            <th style="text-align: center;">Destination</th>
                            <th style="text-align: center;">SKU/SUB-HAWB</th>
                        </tr>
                        <?php $no = 1 ?>
                        <?php if (empty($query)) : ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No data available</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($query as $r) : ?>
                                <tr>
                                    <td style="text-align: center;"><?php echo $no ?></td>
                                    <td style="text-align: center;"><?php echo $r['po'] ?></td>
                                    <td style="text-align: center;"><?php echo $r['transporter'] ?></td>
                                    <td style="text-align: center;"><?php echo $r['destination'] ?></td>
                                    <td style="text-align: center;"><?php echo $r['hawb'] ?></td>
                                </tr>
                                <?php $no++ ?>
                            <?php endforeach ?>
                        <?php endif ?>
                    </table>
                </div>
            </div>

        </div>

        <div class="footer fixed" id="footer">
            <div>
                <strong>Copyright</strong> <a href="http://elog.id/" target="_blank">eLogistik System Indonesia &copy; <?php echo date("Y") ?></a>
            </div>
        </div>

    </div>
</div>