<?php
if (!isset($_SESSION)) {
    session_start();
}

class OtrOutboundController extends CController
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
        $this->render('/otr-outbound/index', array());
    }

    public function actionCreate()
    {
        ScriptManagerNew::scripts();

        $this->pageTitle = 'WMSLite - Create Outbound';
        $this->body_class = "top-navigation";
        $this->render('/otr-outbound/create', array());
    }

    public function actionStore()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $upload_dir = Driver::uploadPath();
            $uploader = new FileUpload('file');
            $ext = $uploader->getExtension(); // Get the extension of the uploaded file
            $uploader->newFileName = "outbound-" . Functions::generateCode(20) . "." . $ext;
            //Handle the upload
            $result = $uploader->handleUpload($upload_dir);

            if (!$result) {
                Yii::app()->user->setFlash('danger', $uploader->getErrorMsg());
                $this->redirect(Yii::app()->createUrl('/otr-outbound/create'));
            } else {
                $params = [
                    'destination' => $_POST['destination'],
                    'po' => $_POST['po'],
                    'delivery_id' => $_POST['delivery_id'],
                    'transporter' => $_POST['transporter'],
                    'docfile' => $uploader->newFileName,
                    'qty' => 0,
                    'status' => 'draft',
                    'date_created' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['wmslite']['user_id'],
                ];

                Yii::app()->db->createCommand()->insert('el_outbound_header', $params);
                $id = Yii::app()->db->getLastInsertID();

                Yii::app()->user->setFlash('success', "Outbound successfully created, please add detail item");
                $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
            }
        } else {
            $this->redirect(Yii::app()->createUrl('/otr-outbound/create'));
        }
    }

    public function actionEdit($id)
    {
        ScriptManagerNew::scripts();

        $header = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_outbound_header')
            ->where('id=:id', array(':id' => $id))
            ->queryRow();

        if (empty($header)) {
            throw new CHttpException(404, 'Data cannot be found.');
        }

        $details = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_outbound_details')
            ->where('id=:id', array(':id' => $id))
            ->queryAll();

        $this->pageTitle = 'WMSLite - Edit Outbound';
        $this->body_class = "top-navigation";
        $this->render('/otr-outbound/edit', array('header' => $header, 'details' => $details));
    }

    public function actionUpdate($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // die(var_dump($id, $_POST));

            $docfile = '';
            if ($_FILES['file']['name'] != '') {
                $upload_dir = Driver::uploadPath();
                $uploader = new FileUpload('file');
                $ext = $uploader->getExtension(); // Get the extension of the uploaded file
                $uploader->newFileName = "outbound-" . Functions::generateCode(20) . "." . $ext;
                //Handle the upload
                $result = $uploader->handleUpload($upload_dir);
                $docfile = $uploader->newFileName;
            }

            $params = [
                'destination' => $_POST['destination'],
                'po' => $_POST['po'],
                'delivery_id' => $_POST['delivery_id'],
                'transporter' => $_POST['transporter'],
                'status' => 'draft',
                'date_updated' => date('Y-m-d H:i:s'),
                'updated_by' => $_SESSION['wmslite']['user_id'],
            ];

            if (!empty($docfile)) {
                $params['docfile'] = $docfile;
            }

            Yii::app()->db->createCommand()->update('el_outbound_header', $params, 'id=:id', array(':id' => $id));

            Yii::app()->user->setFlash('success', "Outbound successfully updated");
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
        } else {
            $this->redirect(Yii::app()->createUrl('/otr-outbound/create'));
        }
    }

    public function actionUpdateItem($idk)
    {
        //die(var_dump($_POST, $idk));
        try {
            $transaction = Yii::app()->db->beginTransaction();
            //cek status header
            $header = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_outbound_header')
                ->where('id=:id', array(':id' => $_POST['id']))
                ->queryRow();

            $outbound_detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_outbound_details')
                ->where('idk=:idk', array(':idk' => $idk))
                ->queryRow();

            if (empty($header)) {
                throw new CHttpException(404, 'Data cannot be found.');
            }

            if ($header['status'] == 'successful') {
                Yii::app()->user->setFlash('danger', "Cant edit item anymore!");
                $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $_POST['id'])));
            }

            //cek inbound item
            $inbound_detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_details')
                ->where('id=:id', array(':id' => $_POST['id_inbound_details']))
                ->queryRow();

            if (empty($inbound_detail)) {
                throw new CHttpException(404, 'Cant check qty available at inbound');
            }

            if ($outbound_detail['qty'] > $_POST['qty_edit']) {
                //berkurang
                $qtyout = $inbound_detail['qty_out'] - ($outbound_detail['qty'] - $_POST['qty_edit']);
            } else if ($outbound_detail['qty'] < $_POST['qty_edit']) {
                //bertambah
                $qtyout = $inbound_detail['qty_out'] + ($_POST['qty_edit'] - $outbound_detail['qty']);
            } else {
                $qtyout = $outbound_detail['qty'];
            }

            if ($_POST['qty_edit'] == 0) {
                Yii::app()->user->setFlash('danger', "Please delete item instead");
                $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $_POST['id'])));
            }

            //die(var_dump($outbound_detail['qty'],  $_POST['qty_edit'], $qtyout));


            if ($inbound_detail['qty'] >= $qtyout) {
                Yii::app()->db->createCommand()->update('el_outbound_details', [
                    'qty' => $_POST['qty_edit'],
                ], 'idk=:idk', array(':idk' => $idk));

                //update qty out di inbounds
                Yii::app()->db->createCommand()->update('el_inbound_details', [
                    'qty_out' => $qtyout,
                ], 'id=:id', array(':id' => $inbound_detail['id']));

                $transaction->commit();

                Yii::app()->user->setFlash('success', "Item updated!");
                $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $_POST['id'])));
            } else {
                Yii::app()->user->setFlash('danger', "No qty available");
                $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $_POST['id'])));
            }
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::app()->user->setFlash('danger', "Failed when processing, transaction rollback");
        }
    }

    public function actionGetOutbound()
    {
        $model = new Outbound();
        $list = $model->get_datatables();

        //die(var_dump($list));
        $data = array();
        $no   = isset($_POST['start']) ? $_POST['start'] : 1;

        foreach ($list as $r) {

            $created_by = '';
            if (!empty($r['created_by'])) {
                $created_by = ' by ' . $r['created_by_nama'];
            }

            $updated_by = '';
            if (!empty($r['updated_by'])) {
                $updated_by = ' by ' . $r['updated_by_nama'];
            }

            $date_created = Yii::app()->functions->prettyDate($r['date_created'], true);
            $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

            $date_updated = Yii::app()->functions->prettyDate($r['date_updated'], true);
            $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

            $rstat = '';
            switch ($r['status']) {
                case "acknowledged":
                case "successful":
                case "Warehouse in Transit":
                    $rstat = 'primary';
                    break;
                case "started":
                    $rstat = 'info';
                    break;
                case "assigned":
                    $rstat = 'warning';
                    break;
                case "inprogress":
                    $rstat = 'success';
                    break;
                case "failed":
                case "canceled":
                case "cancelled":
                case "declined":
                case "suspended":
                case "blocked":
                    $rstat = 'danger';
                    break;
            }
            $status = "<span class=\"label label-" . $rstat . " \">" . Driver::t($r['status']) . "</span>";

            $id = $r['id'];

            $action = "";

            $admin_id = Driver::getUserType();
            if ($admin_id == 1) {
                $action = '<a class="btn btn-sm btn-success" href="' . Yii::app()->createUrl('otr-outbound/edit', array('id' => $r['id'])) . '">' . Driver::t('Edit') . '</a> ';
            }


            $action .= '<a class="btn btn-sm btn-info" href="" onclick="window.open(\'' . Yii::app()->createUrl('otr-outbound/print', array('id' => $r['id'])) . '\', \'_blank\').focus()">' . Driver::t('Print') . '</a> ';

            //yg inbound nya dari schenker, tidak bisa di delete

            if (($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3') && $r['status'] != 'successful') {
                $action .= '<a class="btn btn-sm btn-danger del-outbound" data-id="' . $r['id'] . ' href="javascript:;">' . Driver::t('Delete') . '</a>';
            }


            // preparing an array
            $row   = array();
            $row[] = $r['id'];
            $row[] = $r['qty'];
            $row[] = $r['po'];
            $row[] = $r['destination'];
            $row[] = $r['delivery_id'];
            $row[] = $r['transporter'];
            $row[] = $date_created;
            $row[] = $r['scan_time'];
            $row[] = $date_updated;
            $row[] = $status;
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

    public function actionGetHawb()
    {
        if (isset($_GET['idHawb'])) {


            // $sql = "SELECT h.hawb AS id, h.hawb AS text
            //         FROM el_inbound_header h
            //         LEFT JOIN el_inbound_details s ON s.hawb=h.hawb
            //         WHERE h.status='successful' AND s.flag=0 AND h.hawb LIKE '%" . $this->data['idHawb'] . "%' GROUP BY h.hawb";

            // multiqty
            $sql = "SELECT h.hawb AS id, h.hawb AS text
                    FROM el_inbound_header h
                    LEFT JOIN el_inbound_details s ON s.hawb=h.hawb
                    WHERE h.status='successful' AND s.flag=0 AND h.hawb LIKE '%" . $_GET['idHawb'] . "%' AND s.qty > s.qty_out GROUP BY h.hawb";

            $result = Yii::app()->db->createCommand($sql)->queryAll();

            // if ($result) {
            //     $this->details = $result;
            // } else $this->msg = Driver::t("Record not found");
        }

        echo CJSON::encode($result);
        Yii::app()->end();
    }

    public function actionStoreItem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
        }

        if(empty($_POST['hawb'])){
            Yii::app()->user->setFlash('danger', "Please choose HAWB");
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
        }

        //die(var_dump($_POST, $id));
        try {
            $transaction = Yii::app()->db->beginTransaction();

            $sql = "SELECT d.id, d.hawb, d.descr AS sub_hawb,
                (SELECT h.descr FROM el_inbound_header h WHERE h.hawb=d.hawb AND h.delivery_id=d.sso_delivery_id) AS descr, 
                (SELECT h.warehouse FROM el_inbound_header h WHERE h.hawb=d.hawb AND h.delivery_id=d.sso_delivery_id) AS warehouse,
                d.qty, d.qty_out, d.loc, d.lottable03
                FROM el_inbound_details d 
                WHERE d.flag=0 AND d.hawb='" . $_POST['hawb'] . "'";

            $result = Yii::app()->db->createCommand($sql)->queryAll();

            foreach ($result as $r) {
                $qty = $r['qty'] - $r['qty_out'];
                if ($qty > 0) {
                    $params = [
                        'hawb' => $r['hawb'],
                        'descr' => $r['sub_hawb'],
                        'loc' => $r['loc'],
                        'id' => $id,
                        'flag' => 0,
                        'qty' => $qty,
                        'scan_time' => NULL,
                        'lottable03' => $r['lottable03'],
                        'id_inbound_details' => $r['id'],
                        'date_created' => date('Y-m-d H:i:s'),
                        'created_by' => $_SESSION['wmslite']['user_id'],
                    ];

                    Yii::app()->db->createCommand()->insert('el_outbound_details', $params);

                    //update qty out di inbounds
                    Yii::app()->db->createCommand()->update('el_inbound_details', [
                        'qty_out' => $r['qty_out'] + $params['qty'],
                    ], 'id=:id', array(':id' => $r['id']));
                }
            }

            $transaction->commit();

            Yii::app()->user->setFlash('success', "Item successfully added");
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::app()->user->setFlash('danger', "Failed when processing, transaction rollback");
        }

        $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
    }

    public function actionDeleteAllItem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
        }

        $header = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_outbound_header')
            ->where('id=:id', array(':id' => $id))
            ->queryRow();

        if (empty($header)) {
            throw new CHttpException(404, 'Data cannot be found.');
        }


        if ($header['status'] == 'successful' || $header['status'] == 'inprogress') {
            Yii::app()->user->setFlash('danger', "Can't delete item on successful outbound!");
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
        } else {

            $outbound_details = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_outbound_details')
                ->where('id=:id', array(':id' => $id))
                ->queryAll();

            foreach ($outbound_details as $r) {

                $inbound_detail = Yii::app()->db->createCommand()
                    ->select('*')
                    ->from('el_inbound_details')
                    ->where('id=:id', array(':id' => $r['id_inbound_details']))
                    ->queryRow();

                if (empty($inbound_detail)) {
                    throw new CHttpException(404, 'Data inbound not found.');
                }

                $this->_deleteItem($inbound_detail, $r);
            }


            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $id)));
        }
    }

    public function actionDeleteItem($idk)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $_POST['id'])));
        }

        //die(var_dump($_POST, $idk));


        $outbound_detail = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_outbound_details')
            ->where('idk=:idk', array(':idk' => $idk))
            ->queryRow();

        if (empty($outbound_detail)) {
            throw new CHttpException(404, 'Data item cannot be found.');
        }

        $header = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_outbound_header')
            ->where('id=:id', array(':id' => $outbound_detail['id']))
            ->queryRow();

        if (empty($header)) {
            throw new CHttpException(404, 'Data cannot be found.');
        }

        if ($header['status'] == 'successful' || $header['status'] == 'inprogress') {
            Yii::app()->user->setFlash('danger', "Can't delete item on successful outbound!");
            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $outbound_detail['id'])));
        } else {

            $inbound_detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_details')
                ->where('id=:id', array(':id' => $_POST['id_inbound_details']))
                ->queryRow();

            if (empty($inbound_detail)) {
                throw new CHttpException(404, 'Data inbound not found.');
            }

            $this->_deleteItem($inbound_detail, $outbound_detail);

            $this->redirect(Yii::app()->createUrl('/otr-outbound/edit', array('id' => $_POST['id'])));
        }
    }

    private function _deleteItem($inbound_detail, $outbound_detail)
    {
        try {
            $transaction = Yii::app()->db->beginTransaction();

            //update qty out di inbounds
            Yii::app()->db->createCommand()->update('el_inbound_details', [
                'qty_out' => $inbound_detail['qty_out'] - $outbound_detail['qty'],
            ], 'id=:id', array(':id' => $inbound_detail['id']));

            Yii::app()->db->createCommand()->delete('el_outbound_details', 'idk=:idk', [':idk' => $outbound_detail['idk']]);

            $transaction->commit();
            Yii::app()->user->setFlash('success', "Item cleared");
        } catch (Exception $e) {
            $transaction->rollback();
            Yii::app()->user->setFlash('danger', "Failed when processing, transaction rollback");
        }
    }

    public function actionPrint($id)
    {
        // ScriptManagerNew::scripts();
        $cs = Yii::app()->getClientScript();
        $cs->registerCssFile(Yii::app()->baseUrl . "/assets/css/bootstrap.min.css");

        $this->pageTitle = 'WMSLite - Print';
        $this->body_class = "top-navigation";

        $sql = "SELECT SQL_CALC_FOUND_ROWS y.*, ih.descr FROM (
                SELECT x.*, id.sso_delivery_id AS sso_delivery_id FROM (
                SELECT
                oh.id AS id,
                od.descr AS hawb,
                od.hawb AS hawb0,
                od.loc AS loc,
                oh.po AS po,
                oh.qty AS qty,
                oh.destination AS destination,
                oh.delivery_id AS delivery_id,
                oh.transporter AS transporter,
                oh.checker AS checker,
                oh.date_created AS date_created,
                od.scan_time AS scan_time,
                oh.date_updated AS date_updated,
                oh.created_by AS created_by,
                oh.updated_by AS updated_by,
                u1.first_name AS created_by_nama,
                u2.first_name AS updated_by_nama
                FROM el_outbound_header oh 
                INNER JOIN el_outbound_details od ON od.id=oh.id
                LEFT JOIN el_user u1 ON u1.user_id=oh.created_by
                LEFT JOIN el_user u2 ON u2.user_id=oh.updated_by
                WHERE oh.id=" . Driver::q($id) . "
                ) AS x LEFT JOIN el_inbound_details id ON x.hawb=id.descr AND x.hawb0=id.hawb
                ) AS y LEFT JOIN el_inbound_header ih ON y.hawb0=ih.hawb AND y.sso_delivery_id=ih.delivery_id";
        $details = Yii::app()->db->createCommand($sql)->queryAll();

        $this->render('/otr-outbound/print', array('details' => $details));
    }

    public function actionMarunda($po)
    {
        ScriptManagerNew::scripts();

        $this->pageTitle = 'WMSLite - Marunda';
        $this->body_class = "top-navigation";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/shipment/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
  "shipmentOrder": [
    {
      "shipmentOrderHeader": [
        {
          "storerKey": "IDGEHC",
          "externOrderKey": "' . $po . '"
        }
      ]
    }
  ]
}',
            CURLOPT_HTTPHEADER => array(
                'ngwSystemId: PRDO2_wmwhse153',
                'Content-Type: application/json',
                'Authorization: Basic MFFQSnRwcUIxUlhnakpVejpZIUxhY3xFdVxILlpqRXx4U3Vud3I9VEdQLGJyQip4eQ=='
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            $result = '';
        } else {
            $error_msg = '';
            $result = json_decode($response);
        }

        $this->render('/otr-outbound/marunda', array('result' => $result, 'error_msg' => $error_msg));
    }
}
