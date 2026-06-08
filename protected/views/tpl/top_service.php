<nav class="navbar navbar-fixed-top" role="navigation">
    <div class="navbar-header">
        <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
            <i class="fa fa-reorder"></i>
        </button>
        <a href="<?php echo Yii::app()->createUrl('/service/index')?>" class="navbar-brand logo">
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
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu inbounds" title="Inbound List">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/inbound.svg" ?>">
                </a>
            </li>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu outbounds" title="Outbound List">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/outbound.svg" ?>">
                </a>
            </li>
            
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu reports" title="Reports">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/report.svg" ?>">
                </a>
            </li>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu moving" title="Moving Stock">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/move.svg" ?>">
                </a>
            </li>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu locs" title="Locations List">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/cmd.svg" ?>">
                </a>
            </li>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu users" title="User List">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/user.svg" ?>">
                </a>
            </li>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu apk" title="User Checker">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/apk.svg" ?>">
                </a>
            </li>
            <li>
                <a aria-expanded="false" role="button" href="javascript:;" class="top_menu about" title="About">
                    <img class="img-menu" src="<?php echo Yii::app()->getBaseUrl(true)."/assets/images/info.svg" ?>">
                </a>
            </li>
        </ul>

        <ul class="nav navbar-top-links navbar-right">
            <li>
                <a aria-expanded="false" role="button" href="<?php echo Yii::app()->createUrl('/otr')?>"> Switch to OTR</a>
            </li>
            <form role="search" class="navbar-form-custom" action="#">
                <div class="form-group">
                    <input type="text" placeholder="Search for something..." class="form-control" name="top-search" id="top-search" style="height: 50px;">
                </div>
            </form>
            <li>
                <a href="<?php echo Yii::app()->createUrl('logout')?>" title="Logout">
                    <i class="fa fa-sign-out"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>