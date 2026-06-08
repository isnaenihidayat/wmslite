<?php
if (!isset($_SESSION)) {session_start();}

class FrontController extends CController
{
    public $layout = 'layout';
    public $body_class = '';
    public $body_page = '';

    public function init()
    {
        // set website timezone
        $website_timezone = Yii::app()->functions->getOptionAdmin("website_timezone" );
        if (!empty($website_timezone)) {
            Yii::app()->timeZone = $website_timezone;
        }

        if (isset($_GET['lang'])) {
            Yii::app()->language = $_GET['lang'];
        }
    }

    public function beforeAction($action)
    {
        $action_name = $action->id;
        $accept_controller = array('login', 'ajax', 'resetpassword');
        if (!Functions::islogin()) {
            if (!in_array($action_name, $accept_controller)) {
                $this->redirect(Yii::app()->createUrl('/login'));
            }
        }

        /*check user status*/
        $status = Driver::getUserStatus();

        $baseUrl = Yii::app()->baseUrl."";
        $cs = Yii::app()->getClientScript();
        $jslang = json_encode(Driver::jsLang());
        $cs->registerScript(
            'jslang',
            "var jslang = $jslang;",
            CClientScript::POS_HEAD
        );

        $js_lang_validator = Yii::app()->functions->jsLanguageValidator();
        $js_lang = Yii::app()->functions->jsLanguageAdmin();

        $cs->registerScript(
            'jsLanguageValidator',
            'var jsLanguageValidator = '.json_encode($js_lang_validator).';',
            CClientScript::POS_HEAD
        );

        $cs->registerScript(
            'js_lang',
            'var js_lang = '.json_encode($js_lang).';',
            CClientScript::POS_HEAD
        );

        $cs->registerScript(
            'account_status',
            "var account_status = '$status';",
            CClientScript::POS_HEAD
        );

        $language=Yii::app()->language;
        $cs->registerScript(
            'language',
            "var language = '$language';",
            CClientScript::POS_HEAD
        );

        if (in_array($action_name, $accept_controller)) {
            $cs->registerCssFile($baseUrl."/assets/css/ui.css");
            $cs->registerCssFile($baseUrl."/assets/css/loginstyle.css");
        }

        return true;
    }

    public function actionLogin()
    {
        ScriptManager::scriptsLogin();

        if (Functions::islogin()) {
            
            if (Driver::getLoginType() < 2) {
                $this->redirect(Yii::app()->createUrl('/otr/dashboard'));
            } else $this->redirect(Yii::app()->createUrl('/service/dashboard'));

            Yii::app()->end();
        }

        $this->body_class = "account";
        $this->body_page = 'data-page="login"';
        $this->render('login');
    }

    public function actionLogout()
    {
        unset($_SESSION['wmslite']);
        $this->redirect(Yii::app()->createUrl('/login'));
    }

    public function actionIndex()
    {
        ScriptManager::scripts();

        if (Functions::islogin()) {
            
            if (Driver::getLoginType() < 2) {
                $this->redirect(Yii::app()->createUrl('/otr/dashboard'));
            } else $this->redirect(Yii::app()->createUrl('/service/dashboard'));

            Yii::app()->end();
        }
    }

    public function actionQRPrintOne()
    {
        ScriptManager::scriptsOption();
        
        $this->body_class = "white-bg";
        $this->render('qrcodeprint', array());
    }

    public function actionQRPrintAll()
    {
        ScriptManager::scriptsOption();
        
        $this->body_class = "white-bg";
        $this->render('qrcode_print', array());
    }


}