<h1 style="margin-bottom: 0px; font-size: 20px;">WMSLite Inbound Details</h1>
<p style="font-style: italic;">Generated at: <?= date('Y-m-d H:i') ?></p>

<table border="1" style="border-collapse:collapse; font-size: 12px; color: #4d4d4d; width: 100%; table-layout: fixed;">
    <thead>
        <tr>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">HAWB</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">Description</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">SSO Delivery ID</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">PO Number</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">ATA</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">SPPB Date</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">Oracle Locator</th>
            <th style="background-color: #1ab394; color: white; padding: 10px; vertical-align: middle;">Location</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($detail as $r) : ?>
            <tr>
                <td style="padding: 5px; word-wrap: break-word; width: 10%;"><?= $r['hawb'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 12%;"><?= $r['descr'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 13%;"><?= $r['delivery_id'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 15%;"><?= $r['po'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 10%;"><?= $r['ata'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 10%;"><?= $r['sppb_date'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 15%;"><?= $r['locator'] ?></td>
                <td style="padding: 5px; word-wrap: break-word; width: 15%;"><?= $r['loc'] ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>