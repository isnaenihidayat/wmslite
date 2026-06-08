<div class='container popup searchPopup' style="display: block;">
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Search Result for: " . $_GET['search']) ?></h3><a href="<?= $this->createUrl('otr/dashboard') ?>" class="glyphicon glyphicon-remove" style="color: #fff;"></a>
        </div>
        <div class="panel-body search-list" id="search-list">

            <?php if($_SESSION['wmslite']['type'] == 2): ?>
            <?php endif ?>

                <table id="search_list" class="table table-striped table-bordered table-hover dataTableSearchResult">
                    <thead>
                    <tr>
                        <th><?php echo t("ID")?></th>
                        <th><?php echo t("HAWB")?></th>
                        <th><?php echo t("SKU/SUB-HAWB")?></th>
                        <th><?php echo t("Description")?></th>
                        <th><?php echo t("PO Number")?></th>
                        <th><?php echo t("Ship Method")?></th>
                        <th><?php echo t("ATA")?></th>
                        <th><?php echo t("SPPB Date")?></th>
                        <th><?php echo t("Oracle Locator")?></th>
                        <th><?php echo t("Current Location")?></th>
                        <th><?php echo t("Destination (Out)")?></th>
                        <th><?php echo t("GON Number (Out)")?></th>
                        <th><?php echo t("Delivery ID (Out)")?></th>
                        <th><?php echo t("Transporter (Out)")?></th>
                        <th><?php echo t("Last Modified (Out)")?></th>
                        <th><?php echo Driver::t("Status")?></th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach($shipment_search as $r): ?>

                            <?php
                            // $created_by = '';
                            // if(!empty($r['created_by'])){
                            //     $created_by = ' by ' . $r['created_by_nama'];
                            // }

                            $updated_by = '';
                            if(!empty($r['date_updated'])){
                                $updated_by = ' by ' . $r['updated_by_nama_out'];
                            }

                            // $date_created=Yii::app()->functions->prettyDate($r['date_created'],true);
                            // $date_created=Yii::app()->functions->translateDate($date_created) . $created_by;

                            $date_updated=Yii::app()->functions->prettyDate($r['date_updated'],true);
                            $date_updated=Yii::app()->functions->translateDate($date_updated) . $updated_by;

                            $valstat = '';
                            switch ($r['status_in'])
                            {
                                case "acknowledged":
                                case "successful":
                                case "Warehouse in Transit":
                                    $valstat='primary';
                                    break;
                                case "started":
                                    $valstat='info';
                                    break;
                                case "assigned":
                                    $valstat='warning';
                                    break;
                                case "inprogress":
                                    $valstat='success';
                                    break;
                                case "failed":
                                case "canceled":
                                case "cancelled":
                                case "declined":
                                case "suspended":
                                case "blocked":
                                    $valstat='danger';
                                    break;
                            }
                            $status="<span class=\"label label-".$valstat." \">".Driver::t($r['status_in'])."</span>";
                            ?>

                            <tr>
                            <td><?= $r['id_ih']; ?></td>
                            <td><?= $r['hawb']; ?></td>
                            <td><?= $r['descr']; ?></td>
                            <td><?= $r['descr_detail']; ?></td>
                            <td><?= $r['po_in']; ?></td>
                            <td><?= $r['ship_method']; ?></td>
                            <td><?= $r['ata']; ?></td>
                            <td><?= $r['sppb_date']; ?></td>
                            <td><?= $r['locator']; ?></td>
                            
                            <td><?= $r['loc_inb_dm']; ?></td>

                            <td><?= $r['destination']; ?></td>
                            <td><?= $r['po_out']; ?></td>
                            <td><?= $r['delivery_id']; ?></td>
                            <td><?= $r['transporter']; ?></td>
                            <td><?= $date_updated ?> </td>
                            <td><?= $status ?> </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>


        </div>
    </div>
</div>
