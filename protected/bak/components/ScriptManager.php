<?php

/**
 * Created by IntelliJ IDEA.
 * User: isnaeni.hidayat
 * Date: 4/1/2017
 * Time: 11:53 AM
 */
class ScriptManager
{
    public static function scriptsLogin()
    {

        $ajaxurl=Yii::app()->baseUrl.'/ajax';
        $site_url=Yii::app()->baseUrl.'/';
        $home_url=Yii::app()->baseUrl.'/';
        $uploadurl=Yii::app()->baseUrl.'/upload';

        Yii::app()->clientScript->scriptMap=array(
            'jquery.js'=>false,
            'jquery.min.js'=>false
        );

        $cs = Yii::app()->getClientScript();
        $cs->registerScript(
            'ajaxurl',
            "var ajax_url='$ajaxurl';",
            CClientScript::POS_HEAD
        );
        $cs->registerScript(
            'site_url',
            "var site_url='$site_url';",
            CClientScript::POS_HEAD
        );
        $cs->registerScript(
            'home_url',
            "var home_url='$home_url';",
            CClientScript::POS_HEAD
        );
        
        $cs->registerScript(
            'uploadurl',
            "var uploadurl='$uploadurl';",
            CClientScript::POS_HEAD
        );

        $appname = Functions::getCompanyName();
        $cs->registerScript(
            'appname',
            "var appname='$appname';",
            CClientScript::POS_HEAD
        );
        /** END Set general settings */

        /*Js File*/
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/jquery-3.1.1.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/bootstrap.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/backstretch/backstretch.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/form-validator/jquery.form-validator.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/toastr/toastr.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/metisMenu/jquery.metisMenu.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/slimscroll/jquery.slimscroll.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/pace/pace.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/datapicker/bootstrap-datepicker.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/dataTables/datatables.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/select2/select2.full.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/multiselect/jquery.multiselect.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/DataTables/fnReloadAjax.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/jplayer/jquery.jplayer.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/datetimepicker/jquery.datetimepicker.full.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js.kookie.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/jquery.raty/jquery.raty.js',
            CClientScript::POS_END
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/viewer.js?ver=1.0',
            CClientScript::POS_END
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/driver-js.js?ver=1.0',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/moment.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/SimpleAjaxUploader.min.js',
            CClientScript::POS_END
        );

        /*CSS FILE*/
        $baseUrl = Yii::app()->baseUrl."";
        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile($baseUrl."/assets/css/bootstrap.min.css");
        $cs->registerCssFile($baseUrl."/assets/font-awesome/css/font-awesome.css");
        $cs->registerCssFile("//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/animate.css");
        $cs->registerCssFile($baseUrl."/assets/css/style.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/ladda/ladda.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/toastr/toastr.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/dataTables/datatables.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/datapicker/datepicker3.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/select2/select2.min.css");
        $cs->registerCssFile($baseUrl."/assets/multiselect/jquery.multiselect.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/datetimepicker/jquery.datetimepicker.css");
    }

    public static function scripts()
    {

        $ajaxurl=Yii::app()->baseUrl.'/ajax';
        $site_url=Yii::app()->baseUrl.'/';
        $home_url=Yii::app()->baseUrl.'/';
        $uploadurl=Yii::app()->baseUrl.'/upload';

        Yii::app()->clientScript->scriptMap=array(
            'jquery.js'=>false,
            'jquery.min.js'=>false
        );

        $cs = Yii::app()->getClientScript();
        $cs->registerScript(
            'ajaxurl',
            "var ajax_url='$ajaxurl';",
            CClientScript::POS_HEAD
        );
        $cs->registerScript(
            'site_url',
            "var site_url='$site_url';",
            CClientScript::POS_HEAD
        );
        $cs->registerScript(
            'home_url',
            "var home_url='$home_url';",
            CClientScript::POS_HEAD
        );
        
        $cs->registerScript(
            'uploadurl',
            "var uploadurl='$uploadurl';",
            CClientScript::POS_HEAD
        );

        $appname = Functions::getCompanyName();
        $cs->registerScript(
            'appname',
            "var appname='$appname';",
            CClientScript::POS_HEAD
        );
        /** END Set general settings */

        /*Js File*/
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/jquery-3.1.1.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/bootstrap.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/backstretch/backstretch.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/form-validator/jquery.form-validator.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/toastr/toastr.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/metisMenu/jquery.metisMenu.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/slimscroll/jquery.slimscroll.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/pace/pace.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/datapicker/bootstrap-datepicker.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/dataTables/datatables.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/select2/select2.full.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/multiselect/jquery.multiselect.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/DataTables/fnReloadAjax.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/jplayer/jquery.jplayer.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/datetimepicker/jquery.datetimepicker.full.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js.kookie.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/jquery.raty/jquery.raty.js',
            CClientScript::POS_END
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/viewer.js?ver=1.0',
            CClientScript::POS_END
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/driver-js.js?ver=1.0',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/moment.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/SimpleAjaxUploader.min.js',
            CClientScript::POS_END
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js',
            CClientScript::POS_END
        );

        /*CSS FILE*/
        $baseUrl = Yii::app()->baseUrl."";
        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile($baseUrl."/assets/css/bootstrap.min.css");
        $cs->registerCssFile($baseUrl."/assets/font-awesome/css/font-awesome.css");
        $cs->registerCssFile("//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/animate.css");
        $cs->registerCssFile($baseUrl."/assets/css/style.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/ladda/ladda.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/toastr/toastr.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/dataTables/datatables.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/datapicker/datepicker3.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/select2/select2.min.css");
        $cs->registerCssFile($baseUrl."/assets/multiselect/jquery.multiselect.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/datetimepicker/jquery.datetimepicker.css");
    }

    public static function servicescript()
    {
        $ajaxurl=Yii::app()->baseUrl.'/ajax';
        $site_url=Yii::app()->baseUrl.'/';
        $home_url=Yii::app()->baseUrl.'/';
        $uploadurl=Yii::app()->baseUrl.'/upload';

        Yii::app()->clientScript->scriptMap=array(
            'jquery.js'=>false,
            'jquery.min.js'=>false
        );

        $cs = Yii::app()->getClientScript();
        $cs->registerScript(
            'ajaxurl',
            "var ajax_url='$ajaxurl';",
            CClientScript::POS_HEAD
        );
        $cs->registerScript(
            'site_url',
            "var site_url='$site_url';",
            CClientScript::POS_HEAD
        );
        $cs->registerScript(
            'home_url',
            "var home_url='$home_url';",
            CClientScript::POS_HEAD
        );
        
        $cs->registerScript(
            'uploadurl',
            "var uploadurl='$uploadurl';",
            CClientScript::POS_HEAD
        );

        $appname = Functions::getCompanyName();
        $cs->registerScript(
            'appname',
            "var appname='$appname';",
            CClientScript::POS_HEAD
        );
        /** END Set general settings */

        /*Js File*/
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/jquery-3.1.1.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/bootstrap.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/backstretch/backstretch.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/form-validator/jquery.form-validator.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/toastr/toastr.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/metisMenu/jquery.metisMenu.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/slimscroll/jquery.slimscroll.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/pace/pace.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/datapicker/bootstrap-datepicker.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/dataTables/datatables.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/select2/select2.full.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/multiselect/jquery.multiselect.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/DataTables/fnReloadAjax.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/jplayer/jquery.jplayer.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/datetimepicker/jquery.datetimepicker.full.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js.kookie.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/jquery.raty/jquery.raty.js',
            CClientScript::POS_END
        );

        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/service.js?ver=1.0',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/driver-js.js?ver=1.0',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/moment.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/SimpleAjaxUploader.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/typehead/bootstrap3-typeahead.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/touchspin/jquery.bootstrap-touchspin.min.js',
            CClientScript::POS_END
        );

        /*CSS FILE*/
        $baseUrl = Yii::app()->baseUrl."";
        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile($baseUrl."/assets/css/bootstrap.min.css");
        $cs->registerCssFile($baseUrl."/assets/font-awesome/css/font-awesome.css");
        $cs->registerCssFile("//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/animate.css");
        $cs->registerCssFile($baseUrl."/assets/css/service.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/ladda/ladda.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/toastr/toastr.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/dataTables/datatables.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/datapicker/datepicker3.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/select2/select2.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/touchspin/jquery.bootstrap-touchspin.min.css");
        $cs->registerCssFile($baseUrl."/assets/multiselect/jquery.multiselect.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/datetimepicker/jquery.datetimepicker.css");
    }

    public static function scriptsOption()
    {
        /*Js File*/
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/jquery-3.1.1.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/bootstrap.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/metisMenu/jquery.metisMenu.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/js/plugins/toastr/toastr.min.js',
            CClientScript::POS_END
        );
        Yii::app()->clientScript->registerScriptFile(
            Yii::app()->baseUrl . '/assets/tools.js?ver=1.0',
            CClientScript::POS_END
        );
        
        $baseUrl = Yii::app()->baseUrl."";
        $ajaxurl=Yii::app()->baseUrl.'/ajax';

        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile($baseUrl."/assets/css/bootstrap.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/animate.css");
        $cs->registerCssFile($baseUrl."/assets/style.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/toastr/toastr.min.css");
        $cs->registerCssFile($baseUrl."/assets/font-awesome/css/font-awesome.css");

        $cs->registerScript(
            'ajaxurl',
            "var ajax_url='$ajaxurl';",
            CClientScript::POS_HEAD
        );
        $appname = Functions::getCompanyName();
        $cs->registerScript(
            'appname',
            "var appname='$appname';",
            CClientScript::POS_HEAD
        );
    }
}