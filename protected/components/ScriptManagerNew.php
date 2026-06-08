<?php

class ScriptManagerNew
{
    public static function scripts()
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
            Yii::app()->baseUrl . '/assets/datatables_new/datatables.min.js',
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
        $cs->registerCssFile($baseUrl."/assets/datatables_new/datatables.min.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/datapicker/datepicker3.css");
        $cs->registerCssFile($baseUrl."/assets/css/plugins/select2/select2.min.css");
        $cs->registerCssFile($baseUrl."/assets/multiselect/jquery.multiselect.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/jquery.raty/jquery.raty.css");
        $cs->registerCssFile($baseUrl."/assets/datetimepicker/jquery.datetimepicker.css");
    }
}