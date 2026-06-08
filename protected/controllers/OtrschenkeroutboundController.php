<?php
if (!isset($_SESSION)) {
    session_start();
}

class OtrschenkeroutboundController extends CController
{
    public $layout = 'layout';
    public $body_class = '';
    public $body_page = '';

    public function init()
    {
        // set website timezone
        $website_timezone = Yii::app()->functions->getOptionAdmin("website_timezone");
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


        if (Driver::getLoginType() == 2 && Driver::getUserType() < 1) {
            $this->redirect(Yii::app()->createUrl('/service/dashboard'));
        }

        /*check user status*/
        $status = Driver::getUserStatus();

        $baseUrl = Yii::app()->baseUrl . "";
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
            'var jsLanguageValidator = ' . json_encode($js_lang_validator) . ';',
            CClientScript::POS_HEAD
        );

        $cs->registerScript(
            'js_lang',
            'var js_lang = ' . json_encode($js_lang) . ';',
            CClientScript::POS_HEAD
        );

        $cs->registerScript(
            'account_status',
            "var account_status = '$status';",
            CClientScript::POS_HEAD
        );

        $language = Yii::app()->language;
        $cs->registerScript(
            'language',
            "var language = '$language';",
            CClientScript::POS_HEAD
        );

        if (in_array($action_name, $accept_controller)) {
            $cs->registerCssFile($baseUrl . "/assets/css/ui.css");
            $cs->registerCssFile($baseUrl . "/assets/css/loginstyle.css");
        }

        return true;
    }

    public function actionIndex()
    {
        ScriptManagerNew::scripts();

        $this->pageTitle = 'WMSLite - Outbound';
        $this->body_class = "top-navigation";
        $this->render('/otr-schenker-outbound/index', array());
    }

    public function actionUpload()
    {
        //die(var_dump($_FILES, $_POST));
        $upload_dir = Driver::uploadPath();
        $uploader = new FileUpload('uploadfile');
        $ext = $uploader->getExtension(); // Get the extension of the uploaded file
        $prefix = date('Ymd') . '_outbound';
        $uploader->newFileName = $prefix . "_" . Functions::generateCode(20) . "." . $ext;

        $result = $uploader->handleUpload($upload_dir);
        if (!$result) {
            Yii::app()->user->setFlash('danger', "Error when upload document: " . $uploader->getErrorMsg());
            $this->redirect(Yii::app()->createUrl('/otrschenkeroutbound/index'));
        } else {

            $orderKey = $_POST['orderKey'];

            $cek = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_schenker_outbound_files')
                ->where('orderKey=:orderKey', array(':orderKey' => $orderKey))
                ->queryRow();

            if (empty($cek)) {
                //insert
                Yii::app()->db->createCommand()->insert('el_schenker_outbound_files', [
                    'orderKey' => $orderKey,
                    'docfile' => $uploader->newFileName,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                //update
                Yii::app()->db->createCommand()->update('el_schenker_outbound_files', [
                    'orderKey' => $orderKey,
                    'docfile' => $uploader->newFileName,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], 'orderKey=:orderKey', array(':orderKey' => $orderKey));

                if(file_exists('upload/'.$cek['docfile'])){
                    unlink('upload/'.$cek['docfile']);
                }
            }

            Yii::app()->user->setFlash('success', "Document uploaded successfully");
            $this->redirect(Yii::app()->createUrl('/otrschenkeroutbound/index'));
        }

    }

    public function actionGetData()
    {
        $model = new SchenkerOutbound();
        $list = $model->get_datatables();

        //die(var_dump($list));
        $data = array();
        $no   = isset($_POST['start']) ? $_POST['start'] : 1;

        foreach ($list as $r) {


            $action = '<a class="btn btn-sm btn-primary" data-toggle="modal" data-target="#detailModal" data-id="' . $r['id'] . '" data-orderkey="' . $r['orderKey'] . '" href="#">Detail</a>';


            // preparing an array
            $row   = array();
            $row[] = $r['id'];
            $row[] = $r['externOrderKey'];
            $row[] = $r['ccompany'];
            $row[] = $r['susr1'];
            $row[] = $r['carrierName'];
            $row[] = $r['actualShipDate'];
            $row[] = '-';
            $row[] = $r['status'];
            $row[] = $action;

            $data[] = $row;
        }

        $draw = isset($_POST['draw']) ? $_POST['draw'] : 0;

        $output = array(
            "draw"            => $draw,
            "recordsTotal"    => $model->count_all(),
            "recordsFiltered" => $model->count_filtered(),
            "data"            => $data,
        );

        echo json_encode($output);
    }

    public function actionGetDetail()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_schenker_outbound_detail')
                ->where('header_id=:id', array(':id' => $_POST['id']))
                ->queryAll();

            echo '<table class="table table-bordered table-hover">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>HAWB</th>';
            echo '<th>SKU</th>';
            echo '<th>Qty</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';


            $no = 1;
            foreach ($detail as $r) {
                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td>' . $r['lottable07'] . '</td>';
                echo '<td>' . $r['sku'] . '</td>';
                echo '<td>' . $r['shippedQty'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }
    }

    public function actionGetLot()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_schenker_outbound_pick')
                ->where('orderKey=:id', array(':id' => $_POST['orderKey']))
                ->queryAll();

            echo '<table class="table table-bordered table-hover">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>OrderKey</th>';
            echo '<th>Lot</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';


            $no = 1;
            foreach ($detail as $r) {
                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td>' . $r['orderKey'] . '</td>';
                echo '<td>' . $r['lot'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }
    }

    public function actionGetDocfile()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $file = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_schenker_outbound_files')
                ->where('orderKey=:id', array(':id' => $_POST['orderKey']))
                ->queryRow();

            if(!empty($file) && file_exists('upload/'.$file['docfile'])){
                echo '<a href="'.Yii::app()->createUrl('upload').'/'.$file['docfile'].'" target="_blank"><span class="glyphicon glyphicon-save-file"></span> Download</a>';
            }
        }
    }
}
