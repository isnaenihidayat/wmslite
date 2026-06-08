<?php
if (!isset($_SESSION)) {session_start();}

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

class ServiceController extends CController
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

        if (Driver::getLoginType() == 1 && Driver::getUserType() < 1) {
            $this->redirect(Yii::app()->createUrl('/otr/dashboard'));
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

        return true;
    }

    public function actionIndex()
    {

        ScriptManager::servicescript();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('dashboard', array());
    }

    public function actionDashboard()
    {
        ScriptManager::servicescript();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('dashboard', array());
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

    public function actionQRPrintAlls()
    {
        ScriptManager::scriptsOption();
        
        $this->body_class = "white-bg";
        $this->render('qrcode_prints', array());
    }

    public function actionpickingList()
    {
        ScriptManager::scriptsOption();

        $this->body_class = "white-bg";
        $this->render('picking-list', array());
    }

    public function actiondeliveryOrder()
    {
        ScriptManager::scriptsOption();

        $this->body_class = "white-bg";
        $this->render('delivery-order', array());
    }

}