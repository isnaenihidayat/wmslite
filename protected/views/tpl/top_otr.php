<nav class="navbar navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
            <i class="fa fa-reorder"></i>
        </button>
        <a href="<?php echo Yii::app()->createUrl('/otr/index') ?>" class="navbar-brand logo">
            <img src="<?php echo Functions::getLogoURL() ?>">
        </a>
    </div>

    <div class="navbar-collapse collapse" id="navbar">
        <ul class="nav navbar-nav navbar-top-links">
            <li class="active">
                <a aria-expanded="false" role="button" href="javascript:;" class="grey-button logo-client">
                    <img src="<?php echo Functions::getLogoClient() ?>">
                </a>
            </li>
            <?php if ($_SESSION['wmslite']['type'] == '2') : ?>
                <li>
                    <a aria-expanded="false" role="button" href="javascript:;" class="top_menu shipments" title="Shipment List">
                        <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/shipment.png" ?>">
                    </a>
                </li>
                <li>
                    <a aria-expanded="false" role="button" href="javascript:;" class="top_menu reports" title="Reports">
                        <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/report.svg" ?>">
                    </a>
                </li>
                <li>
                    <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/otr/monitoring') ?>" class="top_menu monitoring" title="Monitoring">
                        <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/monitoring.png" ?>">
                    </a>
                </li>
            <?php endif ?>
            <?php if ($_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3' || $_SESSION['wmslite']['type'] == '0') : ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/cube.png" ?>"> </a>
                    <ul class="dropdown-menu" style="min-width: 60px;">
                        <li>
                            <a aria-expanded="false" role="button" href="javascript:;" class="top_menu shipments" title="Shipment List">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/shipment.png" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="javascript:;" class="top_menu inbounds" data-warehouse="marunda" title="Inbound List">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/inbound.svg" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="<?= $this->createUrl('otrschenkeroutbound/index') ?>" class="top_menu" title="Outbound List">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/outbound.svg" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/otr/demo_movement') ?>" class="top_menu movement" title="Demo Movement">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/movement.png" ?>">
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a aria-expanded="false" role="button" href="javascript:;" class="top_menu reports" title="Reports">
                        <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/report.svg" ?>">
                    </a>
                </li>
                <li>
                    <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/otr/monitoring') ?>" class="top_menu monitoring" title="Monitoring">
                        <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/monitoring.png" ?>">
                    </a>
                </li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/gears.png" ?>"> </a>
                    <ul class="dropdown-menu" style="min-width: 60px;">
                        <li>
                            <a aria-expanded="false" role="button" href="javascript:;" class="top_menu locs" title="Locations List">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/cmd.svg" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="javascript:;" class="top_menu users" title="User List">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/user.svg" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="javascript:;" class="top_menu apk" title="User Checker">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/apk.svg" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/otr/product_category') ?>" class="top_menu product_category" title="Product Category">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/category.png" ?>">
                            </a>
                        </li>
                        <li>
                            <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/otr/recipient') ?>" class="top_menu recipient" title="Recipient">
                                <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/email.png" ?>">
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif ?>
        </ul>
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/service') ?>"> Switch to Service</a>
            </li>
            <!-- <li><?php //= $_SESSION['wmslite']['email_address'] 
                        ?></li> -->
            <form role="search" class="navbar-form-custom" action="<?= $this->createUrl('otr/dashboard') ?>" method="GET">
                <div class="form-group">
                    <input type="text" placeholder="Search for something..." class="form-control" name="search" id="top-searchx" style="height: 50px;">
                </div>
            </form>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="about" title="About">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/info.svg" ?>" style="display:inline;">
                </a>
            </li>
            <li>
                <a href="<?php echo Yii::app()->createUrl('logout') ?>" title="Logout">
                    <i class="fa fa-sign-out"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>