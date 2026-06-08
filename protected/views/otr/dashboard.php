
<?php
$display = '';
?>

<?php if(!empty($search_result)): ?>
    
    <?php $display = 'style="display:block"'; ?>

    <?php $this->renderPartial('/otr/search_result',array('shipment_search' => $shipment_search)); ?>

<?php endif; ?>

<div class="PanelOverlay" <?= $display ?>></div>

<div id="wrapper dashboard">
    <div id="map-wrapper" class="gray-bg">
        
        <div class="row border-bottom white-bg">
            <?php $this->renderPartial('/tpl/top_otr',array()); ?>
        </div>

        <div class="wrapper wrapper-content dashboard-work-area" id="ical">
            <div id="primary_map" class="primary_map"></div>
        </div>

        <div class="footer fixed" id="footer">
            <div>
                <strong>Copyright</strong> <a href="http://elog.id/" target="_blank">eLogistik System Indonesia &copy; <?php echo date("Y")?></a>
            </div>
        </div>

    </div>
</div>

<?php
$this->renderPartial('/otr/panel',array());
$this->renderPartial('/otr/about',array());

$this->renderPartial('/otr/shipment-list',array());
$this->renderPartial('/otr/shipment-details',array());
$this->renderPartial('/otr/new-shipment',array());
$this->renderPartial('/otr/next-shipment',array());
$this->renderPartial('/otr/details_sh',array());

$this->renderPartial('/otr/push-outbound',array());

$this->renderPartial('/otr/inbound-list',array());
$this->renderPartial('/otr/inbound-details',array());
$this->renderPartial('/otr/new-inbound',array());
$this->renderPartial('/otr/next-inbound',array());
$this->renderPartial('/otr/qrcode',array());
$this->renderPartial('/otr/putaway',array());
$this->renderPartial('/otr/details',array());

$this->renderPartial('/otr/outbound-list',array());
$this->renderPartial('/otr/outbound-details',array());
$this->renderPartial('/otr/new-outbound',array());

$this->renderPartial('/otr/outbound-list-schenker',array());
$this->renderPartial('/otr/outbound-details-schenker',array());
$this->renderPartial('/otr/new-outbound-schenker',array());

$this->renderPartial('/otr/moving-list',array());
$this->renderPartial('/otr/new-moving',array());

$this->renderPartial('/otr/loc-list',array());
$this->renderPartial('/otr/new-loc',array());

$this->renderPartial('/otr/user-list',array());
$this->renderPartial('/otr/new-user',array());

$this->renderPartial('/otr/apk-list',array());
$this->renderPartial('/otr/new-apk',array());

$this->renderPartial('/otr/reports',array());
?>