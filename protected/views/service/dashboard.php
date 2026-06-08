
<div class="PanelOverlay"></div>

<div id="wrapper dashboard">
    <div id="map-wrapper" class="gray-bg">
        
        <div class="row border-bottom white-bg">
            <?php $this->renderPartial('/tpl/top_service',array()); ?>
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
$this->renderPartial('/service/panel',array());
$this->renderPartial('/service/about',array());

$this->renderPartial('/service/user-list',array());
$this->renderPartial('/service/new-user',array());

$this->renderPartial('/service/apk-list',array());
$this->renderPartial('/service/new-apk',array());

$this->renderPartial('/service/loc-list',array());
$this->renderPartial('/service/new-loc',array());

$this->renderPartial('/service/moving-list',array());
$this->renderPartial('/service/new-moving',array());

$this->renderPartial('/service/inbound-list',array());
$this->renderPartial('/service/inbound-details',array());
$this->renderPartial('/service/details',array());
$this->renderPartial('/service/new-inbound',array());
$this->renderPartial('/service/qrcode',array());
$this->renderPartial('/service/putaway',array());

$this->renderPartial('/service/outbound-list',array());
$this->renderPartial('/service/outbound-details',array());
$this->renderPartial('/service/new-outbound',array());

$this->renderPartial('/service/reports',array());

?>