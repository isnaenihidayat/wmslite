<div class='container popup searchPopup' style="display: block;">
    <div class="panel panel-success">
        <div class="panel-heading">
            <h3 class="panel-title"><?php echo t("Search Result for: " . $_GET['search']) ?></h3><a href="<?= $this->createUrl('otr/dashboard') ?>" class="glyphicon glyphicon-remove" style="color: #fff;"></a>
        </div>
        <div class="panel-body search-list" id="search-list">

            <?php if ($_SESSION['wmslite']['type'] == 2): ?>
            <?php endif ?>

            <table id="search_list" class="table table-striped table-bordered table-hover dataTableSearchResult">
                <thead>
                    <tr>
                        <th><?php echo t("ID") ?></th>
                        <th><?php echo t("HAWB") ?></th>
                        <th><?php echo t("SKU/SUB-HAWB") ?></th>
                        <th><?php echo t("Description") ?></th>
                        <th><?php echo t("PO Number") ?></th>
                        <th><?php echo t("Ship Method") ?></th>
                        <th><?php echo t("ATA") ?></th>
                        <th><?php echo t("SPPB Date") ?></th>
                        <th><?php echo t("Oracle Locator") ?></th>
                        <!-- <th><?php //echo t("Current Location") ?></th> -->
                        <th><?php echo t("Destination (Out)") ?></th>
                        <th><?php echo t("GON Number (Out)") ?></th>
                        <th><?php echo t("Delivery ID (Out)") ?></th>
                        <th><?php echo t("Transporter (Out)") ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shipment_search as $r): ?>
                        <tr>
                            <td><?= $r['id']; ?></td>
                            <td><?= $r['hawb']; ?></td>
                            <td><?= $r['sku']; ?></td>
                            <td><?= $r['descr']; ?></td>
                            <td><?= $r['po']; ?></td>
                            <td><?= $r['ship_method']; ?></td>
                            <td><?= $r['ata']; ?></td>
                            <td><?= $r['sppb_date']; ?></td>
                            <td><?= $r['locator']; ?></td>

                            <td><?= $r['destination']; ?></td>
                            <td><?= $r['externOrderKey_ob']; ?></td>
                            <td><?= $r['pso_delivery_id']; ?></td>
                            <td><?= $r['transporter']; ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>


        </div>
    </div>
</div>