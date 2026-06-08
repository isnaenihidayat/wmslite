<?php
if (!isset($_SESSION)) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

class AjaxController extends CController
{
    public $code = 2;
    public $msg;
    public $details;
    public $on_update;
    public $data;

    public function __construct()
    {
        $this->data = $_POST;
    }

    public function init()
    {
        // set website timezone
        /*$website_timezone=Yii::app()->functions->getOptionAdmin("website_timezone" );
        if (!empty($website_timezone)){
            Yii::app()->timeZone=$website_timezone;
        }*/

        if (isset($this->data['language'])) {
            Yii::app()->language = $this->data['language'];
        }
        if (isset($_GET['language'])) {
            Yii::app()->language = $_GET['language'];
        }
        unset($this->data['language']);
    }

    function dump($data = '')
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    private function jsonResponse()
    {
        $resp = array('code' => $this->code, 'msg' => $this->msg, 'details' => $this->details, 'on_update' => $this->on_update);
        echo CJSON::encode($resp);
        Yii::app()->end();
    }

    private function otableNodata()
    {
        if (isset($_GET['sEcho'])) {
            $feed_data['sEcho'] = $_GET['sEcho'];
        } else $feed_data['sEcho'] = 1;

        $feed_data['iTotalRecords'] = 0;
        $feed_data['iTotalDisplayRecords'] = 0;
        $feed_data['aaData'] = array();
        echo json_encode($feed_data);
        die();
    }

    private function otableOutput($feed_data = '')
    {
        echo json_encode($feed_data);
        die();
    }

    public function actionLogin()
    {
        $req = array(
            'email_address' => Driver::t("Email Address is required"),
            'password' => Driver::t("Password is required"),
        );
        $Validator = new Validator;
        $Validator->required($req, $this->data);

        if ($Validator->validate()) {
            if ($res = Driver::Login(trim($this->data['email_address']), trim($this->data['password']), trim($this->data['type_module']))) {

                if ($res['status'] <> 'active' && $res['admin'] < 1) {
                    $this->msg = Driver::t("Sorry but your account has to be confirmed by an Administrator before you can login.");
                    $this->jsonResponse();
                    Yii::app()->end();
                }

                $params['last_login'] = Driver::dateNow();

                $db = new DbExt;
                $db->updateData("{{user}}", $params, 'user_id', $res['user_id']);

                $_SESSION['wmslite'] = $res;
                $_SESSION['wmslite']['email_address'] = trim($this->data['email_address']);
                $this->code = 1;
                $this->msg = Driver::t("Login Successful");

                if (trim($this->data['type_module']) < 2) {
                    $this->details = Yii::app()->createUrl('/otr/dashboard');
                } else $this->details = Yii::app()->createUrl('/service/dashboard');
            } else $this->msg = Driver::t("Login failed. either email and password is invalid");
        } else $this->msg = $Validator->getErrorAsHTML();
        $this->jsonResponse();
    }

    public function actionDeleteRecords()
    {
        if (isset($this->data['tbl']) && isset($this->data['whereid'])) {
            $wherefield = $this->data['whereid'];
            $tbl = $this->data['tbl'];

            $stmt = "
			DELETE FROM
			{{{$tbl}}}
			WHERE
			$wherefield=" . Driver::q($this->data['id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry($stmt);
            $this->code = 1;
            $this->msg = Driver::t("Successful");
        } else $this->msg = Driver::t("Missing parameters");
        $this->jsonResponse();
    }

    public function actiondelInbound()
    {

        //sementara di disabled
        // $this->code=2;
        // $this->msg=Driver::t("Function disabled");
        // $this->jsonResponse();


        if (isset($this->data['hawb'])) {

            $stmt1 = "
			DELETE FROM
			{{inbound_header}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            AND delivery_id=" . Driver::q($this->data['deliveryId']) . "
            ";

            $stmt2 = "
			DELETE FROM
			{{inbound_details}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            AND sso_delivery_id=" . Driver::q($this->data['deliveryId']) . "
            ";

            $DbExt = new DbExt;

            $DbExt->qry($stmt1);
            $DbExt->qry($stmt2);

            $this->code = 1;
            $this->msg = Driver::t("Successful");
        } else $this->msg = Driver::t("Missing parameters");
        $this->jsonResponse();
    }

    public function actiondelShipment()
    {
        //sementara di disabled
        // $this->code=2;
        // $this->msg=Driver::t("Function disabled");
        // $this->jsonResponse();


        if (isset($this->data['hawb'])) {

            $stmt1 = "
			DELETE FROM
			{{inbound_header}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            AND delivery_id=" . Driver::q($this->data['deliveryId']) . "
            ";

            $stmt2 = "
			DELETE FROM
			{{inbound_details}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            AND sso_delivery_id=" . Driver::q($this->data['deliveryId']) . "
            ";

            $DbExt = new DbExt;

            $DbExt->qry($stmt1);
            $DbExt->qry($stmt2);

            $this->code = 1;
            $this->msg = Driver::t("Successful");
        } else $this->msg = Driver::t("Missing parameters");
        $this->jsonResponse();
    }

    public function actiondelOutbound()
    {

        if (isset($this->data['id'])) {

            //yg di out
            $details = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_outbound_details')
                ->where('id=:id', array(':id' => $this->data['id']))
                ->queryAll();

            foreach ($details as $r) {
                //kembalikan flag 1 di inbound details
                Yii::app()->db->createCommand()->update('el_inbound_details', [
                    'flag' => 0,
                    'date_updated' => date('Y-m-d H:i:s'),
                ], 'descr=:descr AND hawb=:hawb', array(':descr' => $r['descr'], 'hawb' => $r['hawb']));
            }

            $stmt1 = "
                    DELETE FROM
                    {{outbound_header}}
                    WHERE id=" . $this->data['id'] . "
                    ";

            $stmt2 = "
                    DELETE FROM
                    {{outbound_details}}
                    WHERE id=" . Driver::q($this->data['id']) . "
                    ";

            $DbExt = new DbExt;

            $DbExt->qry($stmt1);
            $DbExt->qry($stmt2);

            $this->code = 1;
            $this->msg = Driver::t("Successful");
        } else $this->msg = Driver::t("Missing parameters");
        $this->jsonResponse();
    }

    public function actiondelOutboundSchenker()
    {

        if (isset($this->data['id'])) {

            $stmt1 = "
                    DELETE FROM
                    {{outbound_header}}
                    WHERE id=" . $this->data['id'] . "
                    ";

            $stmt2 = "
                    DELETE FROM
                    {{outbound_details_schenker}}
                    WHERE id_header=" . Driver::q($this->data['id']) . "
                    ";

            $DbExt = new DbExt;

            $DbExt->qry($stmt1);
            $DbExt->qry($stmt2);

            $this->code = 1;
            $this->msg = Driver::t("Successful");
        } else $this->msg = Driver::t("Missing parameters");
        $this->jsonResponse();
    }

    public function actiondelInbounds()
    {
        if (isset($this->data['hawb'])) {

            $stmt1 = "
			DELETE FROM
			{{inbound_header_s}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            ";

            $stmt2 = "
			DELETE FROM
			{{inbound_details_s}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            ";

            $stmt3 = "
			DELETE FROM
			{{inbound_lots}}
			WHERE hawb=" . Driver::q($this->data['hawb']) . "
            ";

            $DbExt = new DbExt;

            $DbExt->qry($stmt1);
            $DbExt->qry($stmt2);
            $DbExt->qry($stmt3);

            $this->code = 1;
            $this->msg = Driver::t("Successful");
        } else $this->msg = Driver::t("Missing parameters");
        $this->jsonResponse();
    }

    public function actionapproveUser()
    {
        $db = new DbExt;
        if (isset($this->data['id'])) {
            $params['status'] = 'active';
            if ($db->updateData("{{user}}", $params, 'user_id', $this->data['id'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'approveUser';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actiondeclineUser()
    {
        $db = new DbExt;
        if (isset($this->data['id'])) {
            $params['status'] = 'decline';
            if ($db->updateData("{{user}}", $params, 'user_id', $this->data['id'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'declineUser';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actionblockUser()
    {
        $db = new DbExt;
        if (isset($this->data['id'])) {
            $params['status'] = 'block';
            if ($db->updateData("{{user}}", $params, 'user_id', $this->data['id'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'blockUser';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actionuserList()
    {
        if ($_GET['module'] == '1') {
            $aColumns = array(
                'user_id',
                'first_name',
                'last_name',
                'mobile_number',
                'email_address',
                'type',
                'admin',
                'status'
            );
        } else {
            $aColumns = array(
                'user_id',
                'first_name',
                'last_name',
                'mobile_number',
                'email_address',
                'status'
            );
        }

        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';
        if (isset($_GET['module'])) {
            $and .= " AND module IN (0, " . Driver::q($_GET['module']) . ")";
        }

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{user}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $valstat = '';
                switch ($val['status']) {
                    case "active":
                        $valstat = 'primary';
                        break;
                    case "pending":
                        $valstat = 'info';
                        break;
                    case "decline":
                    case "block":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['user_id'];

                $admin_id = Self::userType();
                $selfid = Self::userId();

                $action = '';
                if ($admin_id == 1) {
                    $action = "<a class=\"btn btn-success new-user\" data-id=\"" . $id . "\" data-admin=\"" . $admin_id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";

                    if ($val['admin'] < 1) {

                        switch ($val['status']) {
                            case 'active':
                                $action .= "<a class=\"btn btn-default block-user\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Block") . "</a> ";
                                break;
                            case 'decline':
                            case 'block':
                                $action .= "<a class=\"btn btn-info approve-user\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Approve") . "</a> ";
                                break;
                            case 'pending':
                                $action .= "<a class=\"btn btn-info approve-user\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Approve") . "</a> ";
                                $action .= "<a class=\"btn btn-warning decline-user\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Decline") . "</a> ";
                                break;
                        }

                        $p = "id=$id" . "&tbl=user&whereid=user_id";
                        $action .= "<a data-data=\"$p\" class=\"btn btn-danger table-delete\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                    }
                } else {
                    if ($val['user_id'] === $selfid) {
                        $action = "<a class=\"btn btn-success new-user\" data-id=\"" . $id . "\" data-admin=\"" . $val['admin'] . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                    }
                }

                /*if ($val['admin'] === 1) {

                    switch ($val['status'])
                    {
                        case 'active':
                            $action .= "<a class=\"btn btn-default block-user\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Block")."</a> ";
                            break;
                        case 'decline':
                        case 'block':
                            $action .= "<a class=\"btn btn-info approve-user\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Approve")."</a> ";
                            break;
                        case 'pending':
                            $action .= "<a class=\"btn btn-info approve-user\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Approve")."</a> ";
                            $action .= "<a class=\"btn btn-warning decline-user\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Decline")."</a> ";
                            break;
                    }

                    $p="id=$id"."&tbl=user&whereid=user_id";
                    $action .= "<a data-data=\"$p\" class=\"btn btn-danger table-delete\" href=\"javascript:;\">".Driver::t("Delete")."</a> ";
                }*/
                if ($_GET['module'] == '1') {

                    switch ($val['type']) {
                        case '1':
                            $type = 'Warehouse';
                            break;
                        case '2':
                            $type = 'Custom';
                            break;
                        case '3':
                            $type = 'Read Only';
                            break;

                        default:
                            $type = '-';
                            break;
                    }

                    $admin = $val['admin'] == 1 ? 'Admin' : 'User';

                    $feed_data['aaData'][] = array(
                        $val['first_name'] . " " . $val['last_name'],
                        $val['mobile_number'],
                        $val['email_address'],
                        $type,
                        $admin,
                        $status,
                        $action
                    );
                } else {
                    $feed_data['aaData'][] = array(
                        $val['first_name'] . " " . $val['last_name'],
                        $val['mobile_number'],
                        $val['email_address'],
                        $status,
                        $action
                    );
                }
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddUser()
    {
        $params = array(
            'first_name' => $this->data['first_name'],
            'last_name' => $this->data['last_name'],
            'mobile_number' => $this->data['phone'],
            'email_address' => $this->data['email'],
            'password' => $this->data['password'],
            'module' => $this->data['module'],
        );

        if ($_SESSION['wmslite']['admin'] == '1') {
            $params['type'] = isset($this->data['type']) ? $this->data['type'] : 1;
            $params['admin'] = isset($this->data['type']) ? $this->data['type'] : 0;
        }

        if (!isset($this->data['idUser'])) {
            $this->data['idUser'] = '';
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (isset($params['password'])) {
            if (!empty($params['password'])) {
                $params['password'] = CPasswordHelper::hashPassword($params['password']);
            } else unset($params['password']);
        }

        $params['date_created'] = Driver::dateNow();

        $db = new DbExt;
        if (!empty($this->data['idUser'])) {
            if ($db->updateData("{{user}}", $params, 'user_id', $this->data['idUser'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-user-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            if ($db->insertData("{{user}}", $params)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-user-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actiongetUserInfo()
    {
        if (isset($this->data['idUser'])) {
            if ($res = Driver::UserInfo($this->data['idUser'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actionapkList()
    {
        $aColumns = array(
            'id',
            'name',
            'username',
            'password',
            'last_login',
            'token'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{apk}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['id'];

                $admin_id = Self::userType();

                $action = '';
                $action = "<a class=\"btn btn-success new-apk\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";

                if ($admin_id == 1) {
                    $p = "id=$id" . "&tbl=apk&whereid=id";
                    $action .= "<a data-data=\"$p\" class=\"btn btn-danger table-delete\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                } else {
                    $action = "<a class=\"btn btn-success new-apk\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['name'],
                    $val['username'],
                    $val['last_login'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddApk()
    {
        $params = array(
            'name' => $this->data['name'],
            'username' => $this->data['username'],
            'password' => $this->data['password'],
        );
        if (!isset($this->data['idApk'])) {
            $this->data['idApk'] = '';
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (isset($params['password'])) {
            if (!empty($params['password'])) {
                $params['password'] = md5($params['password']);
            } else unset($params['password']);
        }

        $db = new DbExt;
        if (!empty($this->data['idApk'])) {
            if ($db->updateData("{{apk}}", $params, 'id', $this->data['idApk'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-apk-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            if ($db->insertData("{{apk}}", $params)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-apk-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actiongetApkInfo()
    {
        if (isset($this->data['idApk'])) {
            if ($res = Driver::ApkInfo($this->data['idApk'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idApk'];
        $this->jsonResponse();
    }


    public function actioninlist()
    {
        $aColumns = array(
            'id',
            'hawb',
            'descr',
            'product_category_name',
            'modality',
            'delivery_id',
            'po',
            'locator',
            'etd',
            'eta',
            'ata',
            'sppb_date',
            'date_created',
            'date_updated',
            'status',
            'totalQtyReceived',
            'itemInDetail',
            'totalPick'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        if (!empty($_GET['warehouse'])) {
            $and = " AND warehouse='" . $_GET['warehouse'] . "' ";
        }

        // $stmt = "SELECT SQL_CALC_FOUND_ROWS h.*, c.name as product_category_name,
        // (SELECT first_name FROM {{user}} WHERE user_id=h.created_by) AS created_by_nama,
        // (SELECT first_name FROM {{user}} WHERE user_id=h.updated_by) AS updated_by_nama
        // FROM
        // {{inbound_header}} h
        // LEFT JOIN {{product_category}} c ON c.id=h.product_category_id
        // WHERE 1 AND (status='Warehouse in Transit' OR status='successful' OR status='inprogress')
        $stmt = "SELECT SQL_CALC_FOUND_ROWS * FROM view_schenker_inbound_combine WHERE 1 
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $created_by = '';
                if (!empty($val['created_by'])) {
                    $created_by = ' by ' . $val['created_by_nama'];
                }

                $updated_by = '';
                if (!empty($val['updated_by'])) {
                    $updated_by = ' by ' . $val['updated_by_nama'];
                }

                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

                $date_updated = Yii::app()->functions->prettyDate($val['date_updated'], true);
                $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

                $valstat = '';
                switch ($val['status']) {
                    case "acknowledged":
                    case "successful":
                    case "Warehouse in Transit":
                        $valstat = 'primary';
                        break;
                    case "started":
                        $valstat = 'info';
                        break;
                    case "assigned":
                        $valstat = 'warning';
                        break;
                    case "inprogress":
                        $valstat = 'success';
                        break;
                    case "failed":
                    case "canceled":
                    case "cancelled":
                    case "declined":
                    case "suspended":
                    case "blocked":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['id'];
                $admin_id = Self::userType();

                $action = '';

                //if ($val['status']!=="successful") {
                if ($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3') {
                    $action = "<a class=\"btn btn-sm btn-success new-inbound\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }


                $action .= '<a class="btn btn-sm btn-primary details-inbound" data-id="' . $id . '" data-hawb="' . $val['hawb'] .  '" data-delivery="' . $val['delivery_id'] . '" data-id_sc="' . $val['id_sc'] . '" data-receiptkey="' . $val['receiptKey'] . '" href="javascript:;">' . Driver::t("Details") . '</a> ';

                //$cek = Driver::CekCountIn($val['hawb']);
                // disable delete sementara
                if (($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3')) {
                    $action .= "<a class=\"btn btn-sm btn-danger del-inbound\" data-hawb=\"" . $val['hawb'] . "\" data-delivery=\"" . $val['delivery_id'] . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                }
                //$action.="<a class=\"btn btn-default new-inbound\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Edit")."</a>";

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['hawb'],
                    $val['descr'],
                    $val['product_category_name'],
                    $val['modality'],
                    $val['delivery_id'],
                    $val['po'],
                    '<span style="width: 150px; word-wrap: break-word; display: inline-block;">' . $val['locator'] . '</span>',
                    $val['etd'],
                    $val['eta'],
                    $val['ata'],
                    $val['sppb_date'],
                    $date_created,
                    $date_updated,
                    $status,
                    $val['totalQtyReceived'],
                    $val['itemInDetail'],
                    $val['totalPick'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionGetDetailSc()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_schenker_inbound_detail')
                ->where('header_id=:id', array(':id' => $_POST['id']))
                ->queryAll();

            echo '<table class="table table-bordered table-hover">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>HAWB</th>';
            echo '<th>SKU</th>';
            echo '<th>toLot</th>';
            echo '<th>qtyReceived</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';


            $no = 1;
            foreach ($detail as $r) {
                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td>' . $r['lottable07'] . '</td>';
                echo '<td>' . $r['sku'] . '</td>';
                echo '<td>' . $r['toLot'] . '</td>';
                echo '<td>' . $r['qtyReceived'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }
    }

    public function actionGetPickSc()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $sql = "SELECT * FROM el_schenker_outbound_pick p WHERE p.lot IN (SELECT toLot FROM el_schenker_inbound_detail WHERE receiptKey='" . $_POST['receiptKey'] . "');";
            $detail = Yii::app()->db->createCommand($sql)->queryAll();

            echo '<table class="table table-bordered table-hover">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>orderKey</th>';
            echo '<th>lot</th>';
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

    public function actionGetAvailableLotSc()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $sql = "SELECT toLot FROM el_schenker_inbound_detail
	WHERE receiptKey='" . $_POST['receiptKey'] . "'
	AND NOT EXISTS (SELECT lot FROM el_schenker_outbound_pick WHERE lot=toLot);";
            $detail = Yii::app()->db->createCommand($sql)->queryAll();

            echo '<table class="table table-bordered table-hover">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>lot</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';


            $no = 1;
            foreach ($detail as $r) {
                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td>' . $r['toLot'] . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        }
    }

    public function actionshlist()
    {
        $aColumns = array(
            'h.id',
            'hawb',
            'descr',
            'c.name',
            'modality',
            'delivery_id',
            'qty',
            'po',
            'ship_method',
            'etd',
            'eta',
            'ata',
            'sppb_date',
            'date_created',
            'date_updated',
            'status'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS h.id, h.hawb, h.descr, c.name AS product_category_name, h.modality, h.delivery_id, h.qty, h.po, h.ship_method, h.etd, h.eta, h.ata, h.sppb_date, h.date_created, h.date_updated, h.status, h.warehouse,
        (SELECT COUNT(*)
        FROM 
        el_inbound_details d
        WHERE d.hawb=h.hawb AND d.loc <> '') AS countloc,
        (SELECT first_name FROM {{user}} WHERE user_id=h.created_by) AS created_by_nama,
        (SELECT first_name FROM {{user}} WHERE user_id=h.updated_by) AS updated_by_nama
		FROM
		{{inbound_header}} h
        LEFT JOIN {{product_category}} c ON c.id=h.product_category_id
		WHERE 1 AND from_shipment = 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $created_by = '';
                if (!empty($val['created_by'])) {
                    $created_by = ' by ' . $val['created_by_nama'];
                }

                $updated_by = '';
                if (!empty($val['updated_by'])) {
                    $updated_by = ' by ' . $val['updated_by_nama'];
                }

                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

                $date_updated = Yii::app()->functions->prettyDate($val['date_updated'], true);
                $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

                $valstat = '';
                switch ($val['status']) {
                    case "acknowledged":
                    case "successful":
                    case "Warehouse in Transit":
                        $valstat = 'primary';
                        break;
                    case "started":
                        $valstat = 'info';
                        break;
                    case "assigned":
                        $valstat = 'warning';
                        break;
                    case "inprogress":
                        $valstat = 'success';
                        break;
                    case "failed":
                    case "canceled":
                    case "cancelled":
                    case "declined":
                    case "suspended":
                    case "blocked":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['id'];
                $warehouse = $val['warehouse'];
                $admin_id = Self::userType();

                $action = '';

                //if ($val['status']!=="successful") {
                if ($admin_id == 1 || $_SESSION['wmslite']['type'] == 2) {
                    $action = "<a class=\"btn btn-sm btn-success new-shipment\" data-id=\"" . $id . "\" data-warehouse=\"" . $warehouse . "\" data-method=\"edit\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                    $action .= "<a class=\"btn btn-sm btn-warning new-shipment\" data-id=\"" . $id . "\" data-warehouse=\"" . $warehouse . "\" data-method=\"update\" href=\"javascript:;\">" . Driver::t("Update") . "</a> ";
                }
                $action .= "<a class=\"btn btn-sm btn-primary details-shipment\" data-id=\"" . $id . "\" data-hawb=\"" . $val['hawb'] .  "\" data-delivery=\"" . $val['delivery_id'] . "\" href=\"javascript:;\">" . Driver::t("Details") . "</a> ";

                if ($val['status'] == 'Warehouse in Transit') {
                    $action .= "<a class=\"btn btn-sm btn-primary push-outbound\" data-id=\"" . $id . "\" data-hawb=\"" . $val['hawb'] .  "\" data-delivery=\"" . $val['delivery_id'] . "\" href=\"javascript:;\">" . Driver::t("Push Outbound") . "</a> ";
                }

                //$cek = Driver::CekCountIn($val['hawb']);
                $cek = $val['countloc'];
                // disable delete sementara
                if (($admin_id == 1 || $_SESSION['wmslite']['type'] == 2) && $cek < 1) {
                    $action .= "<a class=\"btn btn-sm btn-danger del-shipment\" data-hawb=\"" . $val['hawb'] . "\" data-delivery=\"" . $val['delivery_id'] . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                }
                //$action.="<a class=\"btn btn-default new-shipment\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Edit")."</a>";

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['hawb'],
                    $val['descr'],
                    $val['product_category_name'],
                    $val['modality'],
                    $val['delivery_id'],
                    $val['qty'],
                    $val['po'],
                    $val['ship_method'],
                    $val['etd'],
                    $val['eta'],
                    $val['ata'],
                    $val['sppb_date'],
                    $date_created,
                    $date_updated,
                    $status,
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddIn()
    {
        $params = array(
            'hawb' => isset($this->data['hawb']) ? $this->data['hawb'] : '',
            'descr' => isset($this->data['hawb_descr']) ? $this->data['hawb_descr'] : '',
            'product_category_id' => !empty($this->data['product_category_in']) ? $this->data['product_category_in'] : NULL,
            'modality' => isset($this->data['modality_in']) ? $this->data['modality_in'] : NULL,
            'delivery_id' => isset($this->data['delivery_id_in']) ? $this->data['delivery_id_in'] : NULL,
            'qty' => isset($this->data['qty']) ? $this->data['qty'] : '',
            'po' => isset($this->data['po_number']) ? $this->data['po_number'] : '',
            'locator' => isset($this->data['locator_number']) ? $this->data['locator_number'] : '',
            'docfile' => isset($this->data['filename']) ? $this->data['filename'] : '',
            'warehouse' => isset($this->data['warehouse_in']) ? $this->data['warehouse_in'] : ''
        );

        if (!isset($this->data['idInb'])) {
            $this->data['idInb'] = '';
        }

        if ($params['warehouse'] == '') {
            $this->msg = Driver::t("Please refresh the page and try again");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if ($params['qty'] == '' && $params['warehouse'] == 'arcadia') {
            $this->msg = Driver::t("Please provide qty for Arcadia warehouse");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if ($params['qty'] == '0' && $params['warehouse'] == 'arcadia') {
            $this->msg = Driver::t("Please provide qty for Arcadia warehouse");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['idInb'])) {
            unset($params['hawb']);
            unset($params['qty']);

            $params['updated_by'] = $_SESSION['wmslite']['user_id'];

            //die(var_dump($params, $this->data));
            $params['date_updated'] = date('Y-m-d H:i:s');
            $update = $db->updateData("{{inbound_header}}", $params, 'id', $this->data['idInb']);
            //if ($update === true) {
            $this->code = 1;
            $this->msg = $this->data['hawb'];
            $this->on_update = true;
            $this->details = 'new-in-modal';
            //}
            // else {
            //     $this->msg=Driver::t("failed cannot insert record");
            // };
        } else {
            //check hawb is unique
            $hawb = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header')
                ->where('hawb=:r', array(':r' => $_POST['hawb']))
                ->andWhere('delivery_id=:d', array(':d' => $_POST['delivery_id_in']))
                ->queryAll();
            if (!empty($hawb)) {
                $this->msg = Driver::t("HAWB already exist");
                $this->jsonResponse();
                Yii::app()->end();
            }

            $params['created_by'] = $_SESSION['wmslite']['user_id'];
            $params['date_created'] = Driver::dateNow();

            $insert = Yii::app()->db->createCommand()->insert('el_inbound_header', $params);
            $id = Yii::app()->db->getLastInsertID();

            if ($insert) {

                //untuk arcadia
                if ($params['warehouse'] == 'arcadia') {
                    $qty = $this->data['qty'];
                    //lakukan loop untuk insert detail
                    for ($i = 0; $i < $qty; $i++) {
                        $paramdetails = array(
                            'hawb' => $this->data['hawb'],
                            'descr' => $this->data['hawb'] . '-' . ($i + 1),
                            'sso_delivery_id' => $params['delivery_id'],
                            'loc' => NULL,
                            'scan_time' => NULL,
                            'created_by' => $_SESSION['wmslite']['user_id'],
                            'date_created' => date('Y-m-d H:i:s'),
                            'qty' => 1,
                        );
                        Yii::app()->db->createCommand()->insert('el_inbound_details', $paramdetails);

                        $paramdetails = array(
                            'hawb' => $this->data['hawb'],
                            'hawb_descr' => $this->data['hawb'] . '-' . ($i + 1),
                            'loc_after' => 'stage',
                            'users' => 'system'
                        );
                        $paramdetails['date_created'] = Driver::dateNow();

                        Driver::insertLog($paramdetails);
                    }
                }

                //marunda, update sso_delivery_id
                if (empty($params['delivery_id']) && $params['warehouse'] == 'marunda') {
                    $command = Yii::app()->db->createCommand();
                    $command->update('el_inbound_header', array(
                        'delivery_id' => 'W' . str_pad($id, 8, "0", STR_PAD_LEFT),
                        'date_updated' => date('Y-m-d H:i:s'),
                    ), 'id=:id', array(':id' => $id));
                }

                $this->code = 1;
                $this->msg = 'HAWB ' . $this->data['hawb'] . ' submitted successfully';
                $this->details = 'new-in-modal';
            } else {
                $this->msg = Driver::t("failed cannot insert record");
            }
        }

        $this->jsonResponse();
    }

    public function actionaddSh()
    {
        $params = array(
            'hawb' => isset($this->data['hawb_sh']) ? trim($this->data['hawb_sh']) : '',
            'descr' => isset($this->data['hawb_descr_sh']) ? trim($this->data['hawb_descr_sh']) : '',
            'product_category_id' => !empty($this->data['product_category_sh']) ? trim($this->data['product_category_sh']) : NULL,
            'modality' => isset($this->data['modality_sh']) ? trim($this->data['modality_sh']) : NULL,
            'delivery_id' => isset($this->data['delivery_id_sh']) ? trim($this->data['delivery_id_sh']) : NULL,
            'qty' => isset($this->data['qty_sh']) ? trim($this->data['qty_sh']) : '0',
            'po' => isset($this->data['po_number_sh']) ? trim($this->data['po_number_sh']) : '',
            'docfile' => isset($this->data['filename_sh']) ? trim($this->data['filename_sh']) : '',
            'ship_method' => isset($this->data['ship_method_sh']) ? trim($this->data['ship_method_sh']) : '',
            'etd' => !empty($this->data['etd_sh']) ? trim($this->data['etd_sh']) : null,
            'eta' => !empty($this->data['eta_sh']) ? trim($this->data['eta_sh']) : null,
            'ata' => !empty($this->data['ata_sh']) ? trim($this->data['ata_sh']) : null,
            'pib_number' => isset($this->data['pib_number_sh']) ? trim($this->data['pib_number_sh']) : null,
            'sppb_date' => isset($this->data['sppb_date_sh']) ? trim($this->data['sppb_date_sh']) : null,
            'locator' => isset($this->data['locator_number_sh']) ? trim($this->data['locator_number_sh']) : '',
            'status' => 'Air/Ocean Intransit',
            'from_shipment' => 1,
            'warehouse' => isset($this->data['warehouse_sh']) ? $this->data['warehouse_sh'] : '',
        );

        if ($params['warehouse'] == '') {
            $this->msg = Driver::t("Please refresh the page and try again");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (!isset($this->data['idInb_sh'])) {
            $this->data['idInb_sh'] = '';
        }

        if ($params['etd'] == '0000-00-00') {
            $params['etd'] = NULL;
        }

        if ($params['eta'] == '0000-00-00') {
            $params['eta'] = NULL;
        }

        if ($params['sppb_date'] == '0000-00-00') {
            $params['sppb_date'] = NULL;
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        //die(var_dump($params));

        $db = new DbExt;
        if (!empty($this->data['idInb_sh'])) {

            //cek status skg, jika successful, jangan sampai ganti status lain
            $sh =  Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header')
                ->where('id=:id', array(':id' => $this->data['idInb_sh']))
                ->queryRow();

            $successful = false;
            if (!empty($sh)) {
                if ($sh['status'] == 'successful') {
                    $successful = true;
                }
            }

            //unset($params['hawb']); //masih bisa diedit
            unset($params['qty']);

            //ata dan pib harus diisi bersamaan
            if (($params['ata'] != '' && $params['pib_number'] == '') || ($params['pib_number'] != '' && $params['ata'] == '')) {
                $this->code = 2;
                $this->msg = 'ATA dan PIB Number harus diisi';
                $this->jsonResponse();
            }

            if (!empty($params['ata']) && !empty($params['pib_number'])) {
                $params['status'] = 'Custom Process';
            } else {
                unset($params['ata']);
                unset($params['pib_number']);
            }

            //sppb date dan locator harus diisi bersamaan
            if ((!empty($params['sppb_date']) && empty($params['locator'])) || (empty($params['locator']) && !empty($params['sppb_date']))) {
                $this->code = 2;
                $this->msg = 'SPPB date dan Oracle Locator harus diisi';
                $this->jsonResponse();
            }

            if (!empty($params['sppb_date']) && !empty($params['locator'])) {
                $params['status'] = 'Warehouse in Transit';
            } else {
                unset($params['sppb_date']);
                unset($params['locator']);
            }

            //kalau sdh successful tetap successful
            if ($successful) {
                $params['status'] = 'successful';
            }

            //die(var_dump($params, $this->data));
            $params['updated_by'] = $_SESSION['wmslite']['user_id'];
            $params['date_updated'] = date('Y-m-d H:i:s');
            $db->updateData("{{inbound_header}}", $params, 'id', $this->data['idInb_sh']);

            //update details
            if ($this->data['hawb_sh'] != $this->data['hawb_old_sh']) {
                $command = Yii::app()->db->createCommand();
                $details = $command->select('*')->from('{{inbound_details}}')->where('hawb=:id', array(':id' => $this->data['hawb_old_sh']))->queryAll();
                //update details
                foreach ($details as $r) {
                    $temp_id = explode('-', $r['descr']);

                    $command = Yii::app()->db->createCommand();
                    $command->update('{{inbound_details}}', array(
                        'hawb' => $this->data['hawb_sh'],
                        'descr' => $this->data['hawb_sh'] . '-' . $temp_id[1],
                        'updated_by' => $_SESSION['wmslite']['user_id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ), 'descr=:id', array(':id' => $r['descr']));
                }
            }

            //if ($update === true) {
            $this->code = 1;
            $this->msg = $this->data['hawb_sh'];
            $this->on_update = true;
            $this->details = 'new-sh-modal';
            //}
            // else {
            //     $this->msg=Driver::t("failed cannot insert record");
            // };
        } else {
            $params['created_by'] = $_SESSION['wmslite']['user_id'];
            $params['date_created'] = Driver::dateNow();

            unset($params['ata']);
            unset($params['pib_number']);
            unset($params['sppb_date']);
            unset($params['locator']);

            //check data sebelum insert
            if ($params['warehouse'] == 'arcadia') {
                $command = Yii::app()->db->createCommand();
                $header = $command->select('*')->from('el_inbound_header')->where('hawb=:id', array(':id' => $this->data['hawb_sh']))->queryAll();

                if (!empty($header) && $params['warehouse'] == 'arcadia') {
                    $this->code = 2;
                    $this->msg = 'Data HAWB: ' . $this->data['hawb_sh'] . ' sudah tersimpan di sistem';
                    $this->jsonResponse();
                }
            } else {
                $builder = Yii::app()->db->createCommand()
                    ->select('*')
                    ->from('el_inbound_header')
                    ->where('hawb=:r', array(':r' => $this->data['hawb_sh']));
                $delivery_id = $this->data['delivery_id_sh'];
                if (trim($delivery_id) != '') {
                    $builder->andWhere('delivery_id=:r2', array(':r2' => $delivery_id));
                } else {
                    $builder->andWhere("(delivery_id IS NULL OR delivery_id = '')");
                }
                $header = $builder->queryRow();

                if (!empty($header) && $params['warehouse'] == 'marunda') {
                    $this->code = 2;
                    $this->msg = 'Data HAWB & Delivery ID: ' . $this->data['hawb_sh'] . ' sudah tersimpan di sistem';
                    $this->jsonResponse();
                }
            }







            $insert = Yii::app()->db->createCommand()->insert('el_inbound_header', $params);
            $id = Yii::app()->db->getLastInsertID();

            if ($insert) {

                //untuk arcadia
                if ($params['warehouse'] == 'arcadia') {
                    $qty = $this->data['qty_sh'];
                    //lakukan loop untuk insert detail
                    for ($i = 0; $i < $qty; ++$i) {
                        $paramdetails = array(
                            'hawb' => $this->data['hawb_sh'],
                            'descr' => $this->data['hawb_sh'] . '-' . ($i + 1),
                            'sso_delivery_id' => $params['delivery_id'],
                            'created_by' => $_SESSION['wmslite']['user_id'],
                            'date_created' => date('Y-m-d H:i:s'),
                            'qty' => 1,
                        );
                        $db->insertData("{{inbound_details}}", $paramdetails);

                        $paramdetails = array(
                            'hawb' => $this->data['hawb_sh'],
                            'hawb_descr' => $this->data['hawb_sh'] . '-' . ($i + 1),
                            'loc_after' => 'stage',
                            'users' => 'system'
                        );
                        $paramdetails['date_created'] = Driver::dateNow();

                        Driver::insertLog($paramdetails);
                    }
                }

                //marunda, update sso_delivery_id
                if (empty($params['delivery_id']) && $params['warehouse'] == 'marunda') {
                    $command = Yii::app()->db->createCommand();
                    $command->update('el_inbound_header', array(
                        'delivery_id' => 'W' . str_pad($id, 8, "0", STR_PAD_LEFT),
                        'date_updated' => date('Y-m-d H:i:s'),
                    ), 'id=:id', array(':id' => $id));
                }

                $this->code = 1;
                $this->msg = 'HAWB ' . $this->data['hawb_sh'] . ' submitted successfully';
                $this->details = 'new-sh-modal';
            } else {
                $this->msg = Driver::t("failed cannot insert record");
            }
        }

        $this->jsonResponse();
    }

    public function actionsendLoc()
    {
        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['HawbLoc'])) {

            $params['loc'] = $this->data['Loc_select'];
            if ($db->updateData("{{inbound_details}}", $params, 'descr', $this->data['HawbLoc'])) {

                $hawb_header = Driver::GetInDet($this->data['HawbLoc']);

                $cek = Driver::CekInLoc($hawb_header['hawb']);
                if ($cek['hitung'] < 1) {
                    $params = array(
                        'status' => 'successful',
                        'checker' => 'system'
                    );

                    $db->updateData("{{inbound_header}}", $params, 'hawb', $hawb_header['hawb']);

                    $send_email = true;
                    if ($send_email) {
                        $recipient = Yii::app()->db->createCommand()
                            ->select('*')
                            ->from('el_recipient')
                            ->queryAll();

                        $sh =  Yii::app()->db->createCommand()
                            ->select('*')
                            ->from('el_inbound_header')
                            ->where('hawb=:hawb', array(':hawb' => $this->data['Hawb']))
                            ->queryRow();

                        $sh_detail =  Yii::app()->db->createCommand()
                            ->select('d.descr AS hawb, h.descr, h.delivery_id, h.po, h.ata, h.sppb_date, h.locator, d.loc')
                            ->from('el_inbound_details d')
                            ->where('d.hawb=:hawb', array(':hawb' => $this->data['Hawb']))
                            ->leftJoin('el_inbound_header h', 'h.hawb=d.hawb')
                            ->queryAll();

                        $subject = 'Receiving Notification at GE Warehouse';
                        $body_html = "Dear PJM Team,<br><br>";
                        $body_html .= "For your information that GE Warehouse received your package with information below:<br><br>";

                        // $body_html .= "HAWB: " . $sh['hawb'] . "<br>";
                        // $body_html .= "Description: " . $sh['descr'] . "<br>";
                        // $body_html .= "SSO Delivery ID: " . $sh['delivery_id'] . "<br>";
                        // $body_html .= "PO Number: " . $sh['hawb'] . "<br>";
                        // $body_html .= "ATA: " . $sh['ata'] . "<br>";
                        // $body_html .= "SPPB Date: " . $sh['sppb_date'] . "<br>";
                        // $body_html .= "Quantity Package: " . $sh['qty'] . "<br>";
                        // $body_html .= "Oracle Locator: " . $sh['locator'] . "<br><br>";
                        // $body_html .= "Location List<br><br>";


                        $body_html .= '<table cellspacing="0" cellpadding="2" border="1" style="text-align: center; width="400px;">';
                        $body_html .= '<tbody>';
                        $body_html .= '<tr>';
                        $body_html .= '<th style="border: 1px solid">HAWB</th>';
                        $body_html .= '<th style="border: 1px solid">Description</th>';
                        $body_html .= '<th style="border: 1px solid">SSO Delivery ID</th>';
                        $body_html .= '<th style="border: 1px solid">PO Number</th>';
                        $body_html .= '<th style="border: 1px solid">ATA</th>';
                        $body_html .= '<th style="border: 1px solid">SPPB Date</th>';
                        $body_html .= '<th style="border: 1px solid">Oracle Locator</th>';
                        $body_html .= '<th style="border: 1px solid">Location</th>';
                        $body_html .= '</tr>';

                        foreach ($sh_detail as $r) :
                            $body_html .= '<tr>';
                            $body_html .= '<td style="border: 1px solid">' . $r['hawb'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['descr'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['delivery_id'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['po'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['ata'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['sppb_date'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['locator'] . '</td>';
                            $body_html .= '<td style="border: 1px solid">' . $r['loc'] . '</td>';
                            $body_html .= '</tr>';
                        endforeach;

                        $body_html .= '</tbody>';
                        $body_html .= '</table>';


                        $body_html .= "Please do necessary action for next action/delivery.<br><br>Regards<br>GE HEALTHCARE / WMS LITE<br>";


                        $body_text = "Dear PJM Team,\n\n";
                        $body_text .= "For your information that GE Warehouse received your package with information below:\n\n";

                        // $body_text .= "HAWB: " . $sh['hawb'] . "\n";
                        // $body_text .= "Description: " . $sh['descr'] . "\n";
                        // $body_text .= "SSO Delivery ID: " . $sh['delivery_id'] . "\n";
                        // $body_text .= "PO Number: " . $sh['hawb'] . "\n";
                        // $body_text .= "ATA: " . $sh['ata'] . "\n";
                        // $body_text .= "SPPB Date: " . $sh['sppb_date'] . "\n";
                        // $body_text .= "Quantity Package: " . $sh['qty'] . "\n";
                        // $body_text .= "Oracle Locator: " . $sh['locator'] . "\n\n";

                        $body_text .= "Please do necessary action for next action/delivery.\n\nRegards\nGE HEALTHCARE / WMS LITE\n";

                        $basePath = Yii::app()->getBasePath();
                        //remove protected
                        $baseDir = str_replace('protected', '', $basePath);
                        $attachment1 = $baseDir . "/upload/" . $sh['docfile'];


                        // $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'en');
                        // $html2pdf->setDefaultFont('helvetica');
                        // $html2pdf->writeHTML($this->renderPartial('otr/demo_movement_detail_pdf', ['detail' => $sh_detail], true));
                        // $output = $baseDir."/inbdetail/" . $sh['hawb'] . '.pdf';
                        // $html2pdf->output($output, 'F');
                        // $attachment2 = $output;

                        Driver::sendEmail($recipient, $subject, $body_html, $body_text, $attachment1);
                    }
                }

                $LocCurrent = Driver::getLocCurrent2($hawb_header['hawb'], $this->data['HawbLoc']);

                $paramdetails = array(
                    'hawb' => $hawb_header['hawb'],
                    'hawb_descr' => $this->data['HawbLoc'],
                    'loc_before' => $LocCurrent['loc_after'],
                    'loc_after' => $this->data['Loc_select'],
                    'users' => 'system'
                );
                $paramdetails['date_created'] = Driver::dateNow();

                Driver::insertLog($paramdetails);

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'send-loc-modal';
            }
        } else $this->msg = Driver::t("failed cannot insert record");

        $this->jsonResponse();
    }

    public function actiongetInInfo()
    {
        if (isset($this->data['idInb'])) {
            if ($res = Driver::InInfo($this->data['idInb'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actiongetShInfo()
    {
        if (isset($this->data['idInb_sh'])) {
            if ($res = Driver::InInfo($this->data['idInb_sh'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actiongetInList()
    {

        if (isset($this->data['idHawb']) && isset($this->data['deliveryId'])) {

            // $stmt = "SELECT s.*, h.docfile FROM el_inbound_details s 
            // LEFT JOIN el_inbound_header h ON h.hawb=s.hawb
            // WHERE s.hawb=" . Driver::q($this->data['idHawb']) . "
            // ";

            $builder = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header')
                ->where('hawb=:r', array(':r' => $this->data['idHawb']));
            $delivery_id = $this->data['deliveryId'];
            if (trim($delivery_id) != '') {
                $builder->andWhere('delivery_id=:r2', array(':r2' => $this->data['deliveryId']));
            } else {
                $builder->andWhere("(delivery_id IS NULL OR delivery_id = '')");
            }
            $header = $builder->queryRow();

            $docfile = $header['docfile'];
            $filehtml = '';
            if (!empty($docfile)) {
                $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
            }

            // $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            // FROM
            // vw_inbound
            // WHERE
            // hawb=" . Driver::q($this->data['idHawb']) . "
            // AND delivery_id=" . Driver::q($this->data['deliveryId']) . "
            // ";

            if ($header['warehouse'] == 'arcadia') {

                $delivery_id = $this->data['deliveryId'];
                if (trim($delivery_id) == '') {
                    $sqld = "AND (h.delivery_id='' OR h.delivery_id IS NULL)";
                } else {
                    $sqld = "AND h.delivery_id=" . Driver::q($this->data['deliveryId']);
                }

                $stmt = "SELECT SQL_CALC_FOUND_ROWS d.*, d.descr AS sku_descr, h.descr, d.qty AS koli
                        FROM
                        el_inbound_details d LEFT JOIN
                        el_inbound_header h ON h.hawb=d.hawb AND h.warehouse='arcadia'
                        WHERE
                        h.hawb=" . Driver::q($this->data['idHawb']) . "
                        $sqld
                        ";
            } else {
                $stmt = "SELECT SQL_CALC_FOUND_ROWS d.*, d.descr AS sku_descr, h.descr, d.qty AS koli
                        FROM
                        el_inbound_details d LEFT JOIN
                        el_inbound_header h ON h.hawb=d.hawb AND h.warehouse='marunda'
                        WHERE
                        h.delivery_id=d.sso_delivery_id AND
                        h.hawb=" . Driver::q($this->data['idHawb']) . "
                        AND h.delivery_id=" . Driver::q($this->data['deliveryId']) . "
                        ";
            }



            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {

                if (empty($res)) {
                    $this->code = 1;
                    $this->msg = Driver::t("Data not available");
                    $this->details = array(
                        'docfile' => $filehtml
                    );
                    $this->jsonResponse();
                    Yii::app()->end();
                }

                foreach ($res as $val) {

                    $iLoc = "";
                    $loc = '<td>';
                    if (!empty($val['loc'])) {
                        $iLoc = "<span class=\"label label-default\">" . $val['loc'] . "</span>";
                        $loc .= $iLoc;
                    }
                    $loc .= "</td>";

                    $admin_id = Self::userType();

                    $and = '';
                    if ($admin_id == 1 && empty($iLoc)) {
                        $and = "<button class=\"btn btn-success btn-xs putaway\" data-id=\"" . $val['koli'] . "\"> Send to Loc</button>";
                    }

                    // ".$loc." // after koli

                    $html .= "
                    <tr>
                    <td class=\"text-center\"><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td class=\"text-center\"><a href=\"#\">" . $val['sku_descr'] . "</a></td>

                    <td class=\"text-center\">" . $val['descr'] . "</td>
                    <td class=\"text-center\">" . $val['loc'] . "</td>
                    <td class=\"text-center\">" . $val['scan_time'] . "</td>
                    <td class=\"text-center\">" . $val['qty'] . "</td>
                    <td class=\"text-center\">
                        <button class=\"btn btn-white btn-xs printQrcode\" data-id=\"" . $val['koli'] . "\"> Print Qrcode</button>
                        <button class=\"btn btn-primary btn-xs details\" data-id=\"" . $val['koli'] . "\"> Edit Koli</button>
                    </td>
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html,
                    'docfile' => $filehtml
                );
            } else {
                $this->code = 1;
                $this->msg = Driver::t("Data not available");
                $this->details = array(
                    'docfile' => $filehtml
                );
                $this->jsonResponse();
                Yii::app()->end();
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetShList()
    {
        if (isset($this->data['idHawb_sh'])) {

            // $header = Yii::app()->db->createCommand()
            //     ->select('*')
            //     ->from('el_inbound_header')
            //     ->where('id=:r', array(':r' => $this->data['idHawb_sh']))
            //     ->queryRow();

            $builder = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header')
                ->where('hawb=:r', array(':r' => $this->data['idHawb_sh']));
            $delivery_id = $this->data['deliveryId'];
            if (trim($delivery_id) != '') {
                $builder->andWhere('delivery_id=:r2', array(':r2' => $this->data['deliveryId']));
            } else {
                $builder->andWhere("(delivery_id IS NULL OR delivery_id = '')");
            }
            $header = $builder->queryRow();


            $docfile = $header['docfile'];
            if (!empty($docfile)) {
                $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
            }

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            vw_inbound
            WHERE
            hawb=" . Driver::q($header['hawb']) . "
            AND delivery_id=" . Driver::q($header['delivery_id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $iLoc = "";
                    $loc = '<td>';
                    if (!empty($val['loc'])) {
                        $iLoc = "<span class=\"label label-default\">" . $val['loc'] . "</span>";
                        $loc .= $iLoc;
                    }
                    $loc .= "</td>";

                    $admin_id = Self::userType();

                    $and = '';
                    if ($admin_id == 1 && empty($iLoc)) {
                        $and = "<button class=\"btn btn-success btn-xs putaway\" data-id=\"" . $val['koli'] . "\"> Send to Loc</button>";
                    }

                    // ".$loc." // after koli

                    $html .= "
                    <tr>
                    <td class=\"text-center\"><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td class=\"text-center\"><a href=\"#\">" . $val['koli'] . "</a></td>

                    <td class=\"text-center\">" . $val['weight'] . "</td>
                    <td class=\"text-center\">" . $val['long'] . "</td>
                    <td class=\"text-center\">" . $val['wide'] . "</td>
                    <td class=\"text-center\">" . $val['high'] . "</td>
                    <td class=\"text-center\">
                        <button class=\"btn btn-primary btn-xs detailssh\" data-id=\"" . $val['koli'] . "\"> Edit Koli</button>
                    </td>
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html,
                    'docfile' => $filehtml
                );
            } else {
                $this->code = 1;
                $this->msg = Driver::t("Data not available");
                $this->details = array(
                    'docfile' => $filehtml
                );
            }
        } else {
            $this->msg = Driver::t("Missing parameters") . $this->data['idHawb_sh'];
        }
        $this->jsonResponse();
    }

    public function actiongetInsList()
    {
        if (isset($this->data['idHawb'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            vw_inbound
            WHERE
            hawb=" . Driver::q($this->data['idHawb']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $docfile = '';
            $filehtml = '';
            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $docfile = $val['docfile'];

                    $html .= "
                    <tr>
                    <td><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td><a href=\"#\">" . $val['koli'] . "</a></td>
                    <td>" . $val['weight'] . "</td>
                    <td>" . $val['long'] . "</td>
                    <td>" . $val['wide'] . "</td>
                    <td>" . $val['high'] . "</td>
                    <td class=\"text-right\">
                        <button class=\"btn btn-primary btn-xs details\" data-id=\"" . $val['koli'] . "\"> Edit Koli</button>
                    </td>
                    </tr>
                    ";
                }

                if (!empty($docfile)) {
                    $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html,
                    'docfile' => $filehtml
                );
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetShsList()
    {
        if (isset($this->data['idHawb_sh'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            vw_inbound
            WHERE
            hawb=" . Driver::q($this->data['idHawb_sh']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $docfile = '';
            $filehtml = '';
            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $docfile = $val['docfile'];

                    $html .= "
                    <tr>
                    <td><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td><a href=\"#\">" . $val['koli'] . "</a></td>
                    <td>" . $val['weight'] . "</td>
                    <td>" . $val['long'] . "</td>
                    <td>" . $val['wide'] . "</td>
                    <td>" . $val['high'] . "</td>
                    <td class=\"text-right\">
                        <button class=\"btn btn-primary btn-xs detailssh\" data-id=\"" . $val['koli'] . "\"> Edit Koli</button>
                    </td>
                    </tr>
                    ";
                }

                if (!empty($docfile)) {
                    $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html,
                    'docfile' => $filehtml
                );
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetQRCode()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = self::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['idHawb'])) {
            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_details}}
            WHERE
            descr=" . Driver::q($this->data['idHawb']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $idHawb = $this->data['idHawb'];

            #parameter inputan
            //isi QRCode saat discan
            $isi_teks = $idHawb;
            //direktori dan nama logo
            $logopath = Yii::app()->getBaseUrl(true) . '/assets/images/dk_.jpg';
            //namafile setelah jadi qrcode
            $namafile = $idHawb . ".png";
            //kualitas dan ukuran qrcode
            $quality = 'H';
            $ukuran = 8;
            $padding = 0;

            QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);
            /*$filepath = $path_to_upload.'/'.$namafile;
            $QR = imagecreatefrompng($filepath);

            $logo = imagecreatefromstring(file_get_contents($logopath));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);

            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);

            //besar logo
            $logo_qr_width = $QR_width/2.5;
            $scale = $logo_width/$logo_qr_width;
            $logo_qr_height = $logo_height/$scale;

            //posisi logo
            imagecopyresampled($QR, $logo, $QR_width/3.3, $QR_height/3.3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);

            imagepng($QR, $filepath);*/

            $html = "";

            if (strpos($namafile, ' ') !== false) {
                $namafile = str_replace(' ', '%20', $namafile);
            }

            if (strpos($namafile, '#') !== false) {
                $namafile = str_replace('#', '%23', $namafile);
            }

            $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;
            $html .= "<tr><td><img id=\"myQRCode\" src=\"" . $urlpath . "\"></td></tr>";
            //$html.="<tr><td class=\"text-center\"><h3 id=\"labelQr\">".$this->data['idHawb']."</h3></td></tr>";
            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = $html;
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actionoutlist()
    {
        $aColumns = array(
            'id',
            'qty',
            'po',
            'destination',
            'delivery_id',
            'transporter',
            'date_created',
            'scan_time',
            'date_updated',
            'status'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS h.*,
        (SELECT first_name FROM {{user}} WHERE user_id=h.created_by) AS created_by_nama,
        (SELECT first_name FROM {{user}} WHERE user_id=h.updated_by) AS updated_by_nama,
        (
        SELECT warehouse FROM el_inbound_header WHERE hawb=(SELECT hawb FROM el_outbound_details WHERE id=h.id LIMIT 1) LIMIT 1
        ) AS warehouse
		FROM
		{{outbound_header}} h
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $created_by = '';
                if (!empty($val['created_by'])) {
                    $created_by = ' by ' . $val['created_by_nama'];
                }

                $updated_by = '';
                if (!empty($val['updated_by'])) {
                    $updated_by = ' by ' . $val['updated_by_nama'];
                }

                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

                $date_updated = Yii::app()->functions->prettyDate($val['date_updated'], true);
                $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

                $valstat = '';
                switch ($val['status']) {
                    case "acknowledged":
                    case "successful":
                    case "Warehouse in Transit":
                        $valstat = 'primary';
                        break;
                    case "started":
                        $valstat = 'info';
                        break;
                    case "assigned":
                        $valstat = 'warning';
                        break;
                    case "inprogress":
                        $valstat = 'success';
                        break;
                    case "failed":
                    case "canceled":
                    case "cancelled":
                    case "declined":
                    case "suspended":
                    case "blocked":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['id'];

                $action = "";

                $admin_id = Self::userType();
                if ($admin_id == 1) {
                    $action = "<a class=\"btn btn-success new-outbound\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }


                //$action="<a class=\"btn btn-success new-outbound\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Edit")."</a>";
                $action .= "<a class=\"btn btn-info PickPrint\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Print") . "</a> ";
                $action .= "<a class=\"btn btn-primary details-outbound\" data-id=\"" . $id . "\" data-po=\"" . $val['po'] . "\" href=\"javascript:;\">" . Driver::t("Details") . "</a> ";

                //yg inbound nya dari schenker, tidak bisa di delete

                if (($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3') && $val['status'] != 'successful' && $val['warehouse'] == 'arcadia') {
                    $action .= "<a class=\"btn btn-danger del-outbound\" data-id=\"" . $val['id'] . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['qty'],
                    $val['po'],
                    $val['destination'],
                    $val['delivery_id'],
                    $val['transporter'],
                    $date_created,
                    $val['scan_time'],
                    $date_updated,
                    $status,
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionoutlistschenker()
    {
        $aColumns = array(
            'id',
            'qty',
            'po',
            'destination',
            'delivery_id',
            'transporter',
            'date_created',
            'date_updated',
            'status'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS h.*,
        (SELECT first_name FROM {{user}} WHERE user_id=h.created_by) AS created_by_nama,
        (SELECT first_name FROM {{user}} WHERE user_id=h.updated_by) AS updated_by_nama
		FROM
		{{outbound_header}} h
		WHERE warehouse='schenker'
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $created_by = '';
                if (!empty($val['created_by'])) {
                    $created_by = ' by ' . $val['created_by_nama'];
                }

                $updated_by = '';
                if (!empty($val['updated_by'])) {
                    $updated_by = ' by ' . $val['updated_by_nama'];
                }

                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

                $date_updated = Yii::app()->functions->prettyDate($val['date_updated'], true);
                $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

                $valstat = '';
                switch ($val['status']) {
                    case "acknowledged":
                    case "successful":
                    case "Warehouse in Transit":
                        $valstat = 'primary';
                        break;
                    case "started":
                        $valstat = 'info';
                        break;
                    case "assigned":
                        $valstat = 'warning';
                        break;
                    case "inprogress":
                        $valstat = 'success';
                        break;
                    case "failed":
                    case "canceled":
                    case "cancelled":
                    case "declined":
                    case "suspended":
                    case "blocked":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['id'];

                $action = "";

                $admin_id = Self::userType();
                if ($admin_id == 1) {
                    $action = "<a class=\"btn btn-sm btn-success new-outbound-schenker\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }


                //$action="<a class=\"btn btn-success new-outbound\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Edit")."</a>";
                $action .= "<a class=\"btn btn-sm btn-info PickPrintSchenker\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Print") . "</a> ";
                $action .= "<a class=\"btn btn-sm btn-primary details-outbound-schenker\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Details") . "</a> ";

                if (($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3') && $val['status'] != 'successful') {
                    $action .= "<a class=\"btn btn-sm btn-danger del-outbound-schenker\" data-id=\"" . $val['id'] . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['qty'],
                    $val['po'],
                    $val['destination'],
                    $val['delivery_id'],
                    $val['transporter'],
                    $date_created,
                    $date_updated,
                    $status,
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddOut()
    {

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        //check gon/po is unique
        $po = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_outbound_header')
            ->where('po=:r', array(':r' => $_POST['po_number_o']))
            ->queryAll();
        if (!empty($po) && empty($_POST['idOut'])) {
            $this->msg = Driver::t("GON already exist");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $params = array(
            'po' => isset($this->data['po_number_o']) ? $this->data['po_number_o'] : '',
            'destination' => isset($this->data['destination']) ? $this->data['destination'] : '',
            'delivery_id' => isset($this->data['delivery_id']) ? $this->data['delivery_id'] : '',
            'transporter' => isset($this->data['transporter']) ? $this->data['transporter'] : '',
            'checker' => !empty($this->data['checker']) ? $this->data['checker'] : NULL,
        );

        if (!empty($this->data['filename_ob'])) {
            $params['docfile'] = $this->data['filename_ob'];
        }

        $db = new DbExt;

        if (!empty($this->data['idOut'])) {

            $params['updated_by'] = $_SESSION['wmslite']['user_id'];
            $params['date_updated'] = Driver::dateNow();

            if ($db->updateData("{{outbound_header}}", $params, 'id', $this->data['idOut'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-out-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            //$hawb = isset($this->data['hawb']) ? json_encode($this->data['hawb']) : '';

            $id_detail = $_POST['id_detail'];
            if (empty($id_detail)) {
                $this->msg = Driver::t("Please provide details");
                $this->jsonResponse();
            }

            $qty = $_POST['qty'];
            $id_detail = $_POST['id_detail'];
            $sub_hawb = $_POST['sub_hawb'];
            $lottable03 = $_POST['lottable03'];
            //die(var_dump($_POST['id_detail']));

            if (!empty($id_detail)) {
                //$hawb = json_decode($hawb, true);

                //validate qty
                $counter = 0;
                $qty_total = 0;
                foreach ($id_detail as $r) {

                    //pastikan id nya ada di inbound dan flag=0
                    $cek =  Yii::app()->db->createCommand()
                        ->select('*')
                        ->from('el_inbound_details')
                        ->where('id=:id', array(':id' => $r))
                        ->andWhere('flag=0')
                        ->queryRow();

                    if (empty($cek)) {
                        $this->msg = Driver::t("Item not found, please check again");
                        $this->jsonResponse();
                    }

                    //pastikan qty nya tidak kosong
                    if (empty($qty[$counter])) {
                        $this->msg = Driver::t("Qty can not empty");
                        $this->jsonResponse();
                    }


                    //cek apakah qty masih ada
                    if (($qty[$counter] > $cek['qty'] - $cek['qty_out'])) {
                        $this->msg = Driver::t("Item not available, please check stock");
                        $this->jsonResponse();
                    }

                    // if ($qty[$counter] > $cek['qty']) {
                    //     $this->msg = Driver::t("Qty cant more than stock");
                    //     $this->jsonResponse();
                    // }

                    $qty_total += $qty[$counter];

                    $counter++;
                }

                //all item clean, ready to process

                $params['qty'] = $qty_total;
                $params['status'] = 'inprogress';
                $params['created_by'] = $_SESSION['wmslite']['user_id'];
                $params['date_created'] = Driver::dateNow();

                $insert = Yii::app()->db->createCommand()->insert('el_outbound_header', $params);
                $id = Yii::app()->db->getLastInsertID();

                if ($insert) {

                    //update qty_out di inbound_details
                    $counter = 0;
                    foreach ($id_detail as $r) {
                        $inbound_detail =  Yii::app()->db->createCommand()
                            ->select('*')
                            ->from('el_inbound_details')
                            ->where('id=:id', array(':id' => $r))
                            ->andWhere('flag=0')
                            ->queryRow();

                        $paramdetails = array(
                            'hawb' => $inbound_detail['hawb'],
                            'descr' => $inbound_detail['descr'],
                            'loc' => $inbound_detail['loc'],
                            'id' => $id,
                            'qty' => $qty[$counter], // input qty yg di out
                            'created_by' => $_SESSION['wmslite']['user_id'],
                            'date_created' => Driver::dateNow(),
                            'lottable03' => isset($lottable03[$counter]) ? $lottable03[$counter] : NULL,
                            'id_inbound_details' => $id_detail[$counter], // input id_inbound_detail nya
                        );
                        //insert outbound_detail
                        Yii::app()->db->createCommand()->insert('el_outbound_details', $paramdetails);

                        //update qty_out
                        $command = Yii::app()->db->createCommand();
                        $command->update('el_inbound_details', array(
                            'qty_out' => $inbound_detail['qty_out'] + $qty[$counter],
                            'date_updated' => date('Y-m-d H:i:s'),
                        ), 'id=:id', array(':id' => $r));


                        $dtls2 =  Yii::app()->db->createCommand()
                            ->select('*')
                            ->from('el_inbound_details')
                            ->where('id=:id', array(':id' => $r))
                            ->andWhere('flag=0')
                            ->queryRow();
                        if ($dtls2['qty'] == $dtls2['qty_out']) {
                            $command = Yii::app()->db->createCommand();
                            $command->update('el_inbound_details', array(
                                'flag' => 1,
                                'date_updated' => date('Y-m-d H:i:s'),
                            ), 'id=:id', array(':id' => $r));
                        }

                        //update moving
                        // 20240409 peer moving blm detail
                        $LocCurrent = Driver::getLocCurrent2($inbound_detail['hawb'], $inbound_detail['descr']);
                        $moving = array(
                            'hawb' => $inbound_detail['hawb'],
                            'hawb_descr' => $inbound_detail['descr'],
                            'loc_before' => isset($LocCurrent['loc_after']) ? $LocCurrent['loc_after'] : '',
                            'loc_after' => 'stage',
                            'users' => 'system'
                        );
                        $moving['date_created'] = Driver::dateNow();

                        Driver::insertLog($moving);

                        $counter++;
                    }

                    $this->code = 1;
                    $this->msg = Driver::t("Successful");
                    $this->details = 'new-out-modal';
                    $this->jsonResponse();
                } else $this->msg = Driver::t("failed cannot insert record");
            }
        }

        $this->jsonResponse();
    }

    public function actionaddOutSchenker()
    {

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $params = array(
            'po' => isset($this->data['po_number_o_schenker']) ? $this->data['po_number_o_schenker'] : '',
            'destination' => isset($this->data['destination_schenker']) ? $this->data['destination_schenker'] : '',
            'delivery_id' => isset($this->data['delivery_id_schenker']) ? $this->data['delivery_id_schenker'] : '',
            'transporter' => isset($this->data['transporter_schenker']) ? $this->data['transporter_schenker'] : '',
            'checker' => !empty($this->data['checker_schenker']) ? $this->data['checker_schenker'] : NULL,
            'warehouse' => 'schenker'
        );

        if (!empty($this->data['filename_ob'])) {
            $params['docfile'] = $this->data['filename_ob'];
        }

        $db = new DbExt;

        if (!empty($this->data['idOutSchenker'])) {

            $params['updated_by'] = $_SESSION['wmslite']['user_id'];
            $params['date_updated'] = Driver::dateNow();

            if ($db->updateData("{{outbound_header}}", $params, 'id', $this->data['idOutSchenker'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-out-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            $hawb = isset($this->data['hawb']) ? json_encode($this->data['hawb']) : '';
            $part = isset($this->data['part']) ? json_encode($this->data['part']) : '';
            $sku = isset($this->data['sku']) ? json_encode($this->data['sku']) : '';
            $qty = isset($this->data['qty']) ? json_encode($this->data['qty']) : '';

            if (!empty($hawb)) {
                $hawb = json_decode($hawb, true);
                $part = json_decode($part, true);
                $sku = json_decode($sku, true);
                $qty = json_decode($qty, true);

                $params['created_by'] = $_SESSION['wmslite']['user_id'];
                $params['date_created'] = Driver::dateNow();
                $params['warehouse'] = 'schenker';
                $params['qty'] = count($hawb);

                $insert = Yii::app()->db->createCommand()->insert('el_outbound_header', $params);
                $id = Yii::app()->db->getLastInsertID();

                if ($insert) {

                    $counter = 0;
                    foreach ($sku as $r) {

                        $det = Yii::app()->db->createCommand()
                            ->select('*')
                            ->from('el_inbound_details_schenker')
                            ->where('sku=:sku', array(':sku' => $r))
                            ->limit($qty[$counter])
                            ->order('id ASC')
                            ->queryAll();

                        if (!empty($det)) {

                            //loop ke sku_index, untuk di out
                            foreach ($det as $d) {
                                Yii::app()->db->createCommand()->insert('el_outbound_details_schenker', [
                                    'id_header' => $id,
                                    'hawb' => $hawb[$counter],
                                    'sku' => $d['sku'],
                                    'sku_index' => $d['sku_index'],
                                    'sso_delivery_id' => $d['sso_delivery_id'],
                                    'invs_sku_descr' => $d['invs_sku_descr'],
                                    'invs_lot' => $d['invs_lot'],
                                    'invs_loc' => $d['invs_loc'],
                                    'invs_id' => $d['invs_id'],
                                    'invs_status' => $d['invs_status'],
                                    'invl_locator' => $d['invl_locator'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                ]);

                                //update flag loc
                                $command = Yii::app()->db->createCommand();
                                $command->update('el_inbound_details_schenker', array(
                                    'flag' => 1,
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ), 'sku_index=:id', array(':id' => $d['sku_index']));

                                $LocCurrent = Driver::getLocCurrent($d['invs_id']);
                                $paramdetails = array(
                                    'hawb' => $hawb[$counter],
                                    'hawb_descr' => $d['sku_index'],
                                    'loc_before' => !isset($LocCurrent['loc_after']) ? '' : $LocCurrent['loc_after'],
                                    'loc_after' => 'stage',
                                    'users' => 'system'
                                );
                                $paramdetails['date_created'] = Driver::dateNow();

                                Driver::insertLog($paramdetails);
                            }
                        }

                        $counter++;
                    }

                    $this->code = 1;
                    $this->msg = Driver::t("Successful");
                    $this->details = 'new-out-modal';
                } else $this->msg = Driver::t("failed cannot insert record");
            }
        }

        $this->jsonResponse();
    }

    public function actiongetOutInfo()
    {
        if (isset($this->data['idOut'])) {
            if ($res = Driver::OutInfo($this->data['idOut'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actiongetOutInfoSchenker()
    {
        if (isset($this->data['idOutSchenker'])) {
            if ($res = Driver::OutInfo($this->data['idOutSchenker'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actionloclist()
    {
        $aColumns = array(
            'loc_id',
            'loc_name',
            'loc_descr'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{loc}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['loc_name'];

                $action = "<a class=\"btn btn-success new-loc\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a>";

                //$LocUsage = Driver::GetLocUsage(trim($val['loc_descr']));
                //self::dump($LocUsage['loc_descr']);

                // if (!empty($LocUsage['loc_descr'])) {

                //     $loc_id = $val['loc_id'];
                //     $p="loc_id=$loc_id"."&tbl=loc&whereid=loc_id";
                //     $action .= "<a data-data=\"$p\" class=\"btn btn-danger table-delete\" href=\"javascript:;\">".Driver::t("Delete")."</a>";

                // }

                $feed_data['aaData'][] = array(
                    $val['loc_id'],
                    $val['loc_name'],
                    $val['loc_descr'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetOutList()
    {
        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{outbound_details}}
            WHERE
            id=" . Driver::q($this->data['id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $docfile = "";
            $filehtml = '';
            $i = 1;

            $header = Yii::app()->db->createCommand()
                ->select('docfile, status')
                ->from('{{outbound_header}}')
                ->where('id=:id', array(':id' => $this->data['id']))
                ->queryRow();

            $docfile = $header['docfile'] ? $header['docfile'] : '';


            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $loc = '';
                    if (!empty($val['loc'])) {
                        $loc = "<td>" . $val['loc'] . "</td>";
                    }

                    $html .= "
                    <tr>
                    <td><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td>" . $val['hawb'] . "</td>
                    <td>" . $val['descr'] . "</td>
                    <td>" . $val['qty'] . "</td>
                    <td>" . $val['notes'] . "</td>
                    </tr>
                    ";
                }

                if (!empty($docfile)) {
                    $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
                }

                $sql = "SELECT COUNT(*) AS total FROM el_outbound_details_schenker s WHERE s.id_outbound_header=" . $this->data['id'];
                $schenker = Yii::app()->db->createCommand($sql)->queryRow();

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                //$this->details=$html;
                $this->details = array(
                    'html' => $html,
                    'docfile' => $filehtml,
                    'status' => $header['status'],
                    //'is_schenker' => $schenker['total'] > 0 ? 1 : 0,
                    'is_schenker' => $header['status'] == 'inprogress' ? 1 : 0,
                    'total_schenker' => $schenker['total']
                );
            } else {
                $this->code = 1;
                $this->msg = Driver::t("No Data Available");
                $response = [
                    // 'is_schenker' => 0
                    'is_schenker' => $header['status'] == 'inprogress' ? 1 : 0,
                ];
                $this->details = $response;
            }
        } else {
            $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        }
        $this->jsonResponse();
    }


    public function actiongetOutListSchenker()
    {
        if (isset($this->data['id'])) {

            $sql = "SELECT s.invs_id, s.hawb, s.sku, COUNT(s.sku) AS qty, s.invs_loc, s.invs_sku_descr, CONCAT(s.invs_id, ' - ', s.invs_sku_descr) AS text FROM
                 el_outbound_details_schenker s 
                 WHERE s.id_header='" . $this->data['id'] . "' GROUP BY s.sku";

            $details = Yii::app()->db->createCommand($sql)->queryAll();

            $html = "";
            $docfile = "";
            $filehtml = '';
            $i = 1;
            foreach ($details as $r) {
                $html .= "<tr>";
                $html .= '<td><span class="label label-primary">' . $i++ . '</span></td>';
                $html .= "<td>" . $r['hawb'] . "</td>";
                $html .= "<td>" . $r['sku'] . "</td>";
                $html .= "<td>" . $r['invs_id'] . "</td>";
                $html .= "<td>" . $r['qty'] . "</td>";
                $html .= "<td>" . $r['invs_sku_descr'] . "</td>";
                $html .= "</tr>";
            }

            $docfile_ = Yii::app()->db->createCommand()
                ->select('docfile')
                ->from('{{outbound_header}}')
                ->where('id=:id', array(':id' => $this->data['id']))
                ->queryRow();

            $docfile = $docfile_['docfile'] ? $docfile_['docfile'] : '';

            if (!empty($docfile)) {
                $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
            }

            $this->code = 1;
            $this->msg = Driver::t("Successful");
            //$this->details=$html;
            $this->details = array(
                'html' => $html,
                'docfile' => $filehtml
            );
        } else {
            $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        }
        $this->jsonResponse();
    }

    public function actiongetPicList()
    {
        if (isset($this->data['id'])) {

            // $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            // FROM
            // vw_outbound
            // WHERE
            // id=" . Driver::q($this->data['id']) . "
            // ";

            $stmt = "SELECT SQL_CALC_FOUND_ROWS y.*, ih.descr FROM (
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
                WHERE oh.id=" . Driver::q($this->data['id']) . "
                ) AS x LEFT JOIN el_inbound_details id ON x.hawb=id.descr AND x.hawb0=id.hawb
                ) AS y LEFT JOIN el_inbound_header ih ON y.hawb0=ih.hawb AND y.sso_delivery_id=ih.delivery_id
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {


                    $html .= "
                    <tr>
                    <td>" . $val['hawb'] . "</td>
                    <td>" . $val['descr'] . "</td>
                    <td>" . $val['qty'] . "</td>
                    <td>" . $val['po'] . "</td>
                    <td>" . $val['loc'] . "</td>
                    <td>" . $val['destination'] . "</td>
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetPicListSchenker()
    {
        if (isset($this->data['id'])) {

            $sql = "SELECT s.invs_id, s.hawb, s.sku, COUNT(s.sku) AS qty, s.invs_loc, s.invs_sku_descr, CONCAT(s.invs_id, ' - ', s.invs_sku_descr) AS text FROM
            el_outbound_details_schenker s 
            WHERE s.id_header='" . $this->data['id'] . "' GROUP BY s.sku";

            $data = Yii::app()->db->createCommand($sql)->queryAll();

            $html = "";
            $i = 1;
            if ($data) {
                foreach ($data as $val) {
                    $html .= "
                    <tr>
                    <td>" . $val['hawb'] . "</td>
                    <td>" . $val['sku'] . "</td>
                    <td>" . $val['qty'] . "</td>
                    <td>" . $val['invs_id'] . "</td>
                    <td>" . $val['invs_loc'] . "</td>
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetHawb()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {


            // $sql = "SELECT h.hawb AS id, h.hawb AS text
            //         FROM el_inbound_header h
            //         LEFT JOIN el_inbound_details s ON s.hawb=h.hawb
            //         WHERE h.status='successful' AND s.flag=0 AND h.hawb LIKE '%" . $this->data['idHawb'] . "%' GROUP BY h.hawb";

            // multiqty
            $sql = "SELECT h.hawb AS id, h.hawb AS text
                    FROM el_inbound_header h
                    LEFT JOIN el_inbound_details s ON s.hawb=h.hawb
                    WHERE h.status='successful' AND s.flag=0 AND h.hawb LIKE '%" . $this->data['idHawb'] . "%' AND s.qty > s.qty_out GROUP BY h.hawb";

            $result = Yii::app()->db->createCommand($sql)->queryAll();

            if ($result) {
                $this->details = $result;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetHawbMoving()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {

            $sql = "SELECT CONCAT(h.hawb, '###', s.descr) AS id, CONCAT(h.hawb, ' - ', s.descr) AS text
            FROM el_inbound_header h
            LEFT JOIN el_inbound_details s ON s.hawb=h.hawb
            WHERE h.status='successful' AND s.flag=0 AND (s.descr LIKE '%" . $this->data['idHawb'] . "%' OR h.hawb LIKE '%" . $this->data['idHawb'] . "%') ";
            $result = Yii::app()->db->createCommand($sql)->queryAll();

            //die(var_dump($sql));

            if (!empty($result)) {
                $this->details = $result;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    // public function actiongetHawb()
    // {
    //     $this->data = $_GET;
    //     if (isset($this->data['idHawb'])) {
    //         if ($res = Driver::GetIn($this->data['idHawb'])) {

    //             $this->details = $res;
    //         } else $this->msg = Driver::t("Record not found");
    //     } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

    //     echo CJSON::encode($this->details);
    //     Yii::app()->end();
    // }

    public function actiongetHawbSchenker()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {

            $result =  Yii::app()->db->createCommand()
                //->select('h.hawb, h.descr, s.invs_sku_descr, sku, s.sso_delivery_id')
                ->select('h.hawb AS id, h.hawb AS text')
                ->from('el_inbound_header h')
                ->leftJoin('el_inbound_details_schenker s', 's.sso_delivery_id=h.delivery_id')
                //->where('hawb:hawb', array(':hawb' => $this->data['idHawb']))
                ->where(array('like', 'hawb', '%' . $this->data['idHawb'] . '%'))
                ->andWhere("h.warehouse='schenker'")
                ->andWhere("h.status='successful'")
                ->andWhere("s.flag=0")
                ->andWhere("s.invs_loc <> ''")
                ->group('h.hawb')
                ->queryAll();

            if ($result) {
                $this->details = $result;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetPartSchenker()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {

            $result =  Yii::app()->db->createCommand()
                //->select('h.hawb, h.descr, s.invs_sku_descr, sku, s.sso_delivery_id')
                ->select('s.invs_id AS id, CONCAT(s.invs_id, " - ", s.invs_sku_descr) AS text')
                ->from('el_inbound_header h')
                ->leftJoin('el_inbound_details_schenker s', 's.sso_delivery_id=h.delivery_id')
                ->where('hawb=:hawb', array(':hawb' => $this->data['idHawb']))
                ->andWhere("h.status='successful'")
                ->andWhere("s.flag=0")
                ->andWhere("s.invs_loc <> ''")
                ->queryAll();

            if ($result) {
                $this->details = $result;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetSkuSchenker()
    {
        $this->data = $_POST;
        if (isset($this->data['idHawb'])) {

            $result =  Yii::app()->db->createCommand()
                //->select('h.hawb, h.descr, s.invs_sku_descr, sku, s.sso_delivery_id')
                ->select('s.sku, count(s.sku) AS qty')
                ->from('el_inbound_header h')
                ->leftJoin('el_inbound_details_schenker s', 's.sso_delivery_id=h.delivery_id')
                ->where('hawb=:hawb', array(':hawb' => $this->data['idHawb']))
                ->andWhere("h.status='successful'")
                ->andWhere("s.flag=0")
                ->andWhere("s.invs_loc <> ''")
                ->group('s.sku')
                ->queryAll();

            if ($result) {

                $output = '';
                foreach ($result as $r) {
                    //$output .= '<option value="'.$r['sku'].'">'.$r['sku'].'</option>';
                    $output .= '<div class="cont"><div><input type="checkbox" class="mulinput" value="' . $r['sku'] . '"></div><div>&nbsp;' . $r['sku'] . ' (' . $r['qty'] . ')</div></div>';
                }

                // echo $output;
                // die();

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $output;

                $this->jsonResponse();
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetQtyDetailSchenker()
    {
        //die(var_dump($_POST['id']));
        $this->details = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_inbound_details_schenker')
            ->where('invs_id=:id', array(':id' => $_POST['id']))
            ->queryRow();
        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    /**
     * yg muncul di sini list hawb yg belum masuk ke demo movement
     */
    public function actiongetHawbAddDemoMovement()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {
            if ($res = Driver::GetInDemoMovement($this->data['idHawb'])) {

                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetLoc()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {
            if ($res = Driver::GetLoc($this->data['idHawb'])) {

                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actionGetHawbDetail()
    {
        if (isset($this->data['idHawb'])) {

            $sql = "SELECT * FROM el_inbound_header WHERE hawb='" . $this->data['idHawb'] . "'";
            $header = Yii::app()->db->createCommand($sql)->queryRow();

            if (!empty($header)) {

                // if($header['warehouse'] == 'marunda'){

                // }else{
                //     //arcadia

                // }


                // $sql = "SELECT d.id, d.hawb, d.descr AS sub_hawb, h.descr, d.qty, d.loc, h.warehouse 
                // FROM el_inbound_details d 
                // LEFT JOIN el_inbound_header h ON h.hawb=d.hawb AND d.sso_delivery_id=h.delivery_id 
                // WHERE d.flag=0 AND d.hawb='" . $this->data['idHawb'] . "'";


                $sql = "SELECT d.id, d.hawb, d.descr AS sub_hawb,
                (SELECT h.descr FROM el_inbound_header h WHERE h.hawb=d.hawb AND h.delivery_id=d.sso_delivery_id) AS descr, 
                (SELECT h.warehouse FROM el_inbound_header h WHERE h.hawb=d.hawb AND h.delivery_id=d.sso_delivery_id) AS warehouse,
                d.qty, d.qty_out, d.loc, d.lottable03
                FROM el_inbound_details d 
                WHERE d.flag=0 AND d.hawb='" . $this->data['idHawb'] . "'";

                $result = Yii::app()->db->createCommand($sql)->queryAll();

                if (!empty($result)) {

                    $html = '';
                    foreach ($result as $r) {

                        if ($r['warehouse'] == 'marunda') {
                            $readonly = '';
                        } else {
                            $readonly = '';
                            //$readonly = 'readonly';
                        }

                        $html .= '<tr class="item-row">';
                        $html .= '<td><input type="hidden" value="' . $r['id'] . '" id="id_detail[]" name="id_detail[]"><input type="hidden" value="' . $r['hawb'] . '" id="hawb[]" name="hawb[]"><input type="hidden" value="' . $r['lottable03'] . '" id="lottable03[]" name="lottable03[]">' . $r['hawb'] . '</td>';
                        $html .= '<td><input type="hidden" value="' . $r['sub_hawb'] . '" id="sub_hawb[]" name="sub_hawb[]">' . $r['sub_hawb'] . '</td>';
                        $html .= '<td><input type="hidden" value="' . $r['descr'] . '" id="descr[]" name="descr[]">' . $r['descr'] . '</td>';
                        $html .= '<td><input type="hidden" value="' . $r['loc'] . '" id="loc[]" name="loc[]">' . $r['loc'] . '</td>';
                        $html .= '<td><input type="text" value="' . ($r['qty'] - $r['qty_out']) . '" id="qty[]" name="qty[]" ' . $readonly . '></td>';
                        $html .= '<td><a class="delinv" href="javascript:;" title="Remove row">X</a></td></tr>';
                    }
                    //die(var_dump($html));
                    echo $html;
                    die();

                    // $this->details = $html;
                    // Yii::app()->end();
                }
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        $this->jsonResponse();
    }

    public function actiongetHawbDetail1()
    {
        if (isset($this->data['idHawb'])) {

            if (strpos($this->data['idHawb'], '###')) {
                $idHawb = explode('###', $this->data['idHawb']);
                $hawb = $idHawb[0];
                $subhawb = $idHawb[1];

                $result = Yii::app()->db->createCommand()
                    ->select('*')
                    ->from('el_inbound_details')
                    ->where('hawb=:hawb', array(':hawb' => $hawb))
                    ->andWhere('descr=:descr', array(':descr' => $subhawb))
                    ->queryRow();

                if (!empty($result)) {
                    $this->code = 1;
                    $this->msg = Driver::t("Successful");
                    $this->details = $result;
                }
            } else {
                $this->msg = Driver::t("Data tidak lengkap");
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        $this->jsonResponse();
    }

    public function actiongetHawbDetailDemo()
    {
        if (isset($this->data['idHawb'])) {

            if ($res = Driver::GetInDet($this->data['idHawb'], '')) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        $this->jsonResponse();
    }

    public function actiongetHawbDetailDemoEdit()
    {
        if (isset($this->data['idHawb'])) {

            if ($res = Driver::GetInDet($this->data['idHawb'], '')) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        $this->jsonResponse();
    }

    public function actionaddLoc()
    {
        $params = array(
            'loc_name' => $this->data['loc_name'],
            'loc_descr' => $this->data['loc_descr']
        );
        if (!isset($this->data['idLoc'])) {
            $this->data['idLoc'] = '';
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['idLoc'])) {
            if ($db->updateData("{{loc}}", $params, 'loc_name', $this->data['idLoc'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-loc-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            if ($db->insertData("{{loc}}", $params)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-loc-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actionupdateKoli()
    {
        $params = array(
            'weight' => isset($this->data['weight']) ? $this->data['weight'] : '',
            'long' => isset($this->data['long']) ? $this->data['long'] : '',
            'wide' => isset($this->data['wide']) ? $this->data['wide'] : '',
            'high' => isset($this->data['high']) ? $this->data['high'] : '',
            'updated_by' => $_SESSION['wmslite']['user_id'],
            'date_updated' => date('Y-m-d H:i:s'),
        );

        if (!isset($this->data['Hawbdetail'])) {
            $this->msg = Driver::t("failed cannot insert record");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['Hawbdetail'])) {
            $db->updateData("{{inbound_details}}", $params, 'descr', $this->data['Hawbdetail']);
            //if ($db->updateData("{{inbound_details}}", $params,'descr', $this->data['Hawbdetail'])) {
            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = 'edit-detail-modal';
            //} else $this->msg=Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actionupdateKoliSh()
    {
        $params = array(
            'weight' => isset($this->data['weight_sh']) ? $this->data['weight_sh'] : '',
            'long' => isset($this->data['long_sh']) ? $this->data['long_sh'] : '',
            'wide' => isset($this->data['wide_sh']) ? $this->data['wide_sh'] : '',
            'high' => isset($this->data['high_sh']) ? $this->data['high_sh'] : '',
            'updated_by' => $_SESSION['wmslite']['user_id'],
            'date_updated' => date('Y-m-d H:i:s'),
        );

        if (!isset($this->data['Hawbdetail_sh'])) {
            $this->msg = Driver::t("failed cannot insert record");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['Hawbdetail_sh'])) {
            $db->updateData("{{inbound_details}}", $params, 'descr', $this->data['Hawbdetail_sh']);
            //if ($db->updateData("{{inbound_details}}", $params,'descr', $this->data['Hawbdetail_sh'])) {
            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = 'edit-detail-modalsh';
            //} else $this->msg=Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actiongetKoliDetail()
    {
        if (isset($this->data['idHawb'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_details}}
            WHERE
            descr=" . Driver::q($this->data['idHawb']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $docfile = '';
            $filehtml = '';
            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res[0];
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetKoliDetailSh()
    {
        if (isset($this->data['idHawb_sh'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_details}}
            WHERE
            descr=" . Driver::q($this->data['idHawb_sh']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $docfile = '';
            $filehtml = '';
            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res[0];
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb_sh'];
        $this->jsonResponse();
    }

    public function actiongetLocInfo()
    {
        if (isset($this->data['idLoc'])) {
            if ($res = Driver::LocInfo($this->data['idLoc'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actiongetReportsIn()
    {
        $aColumns = array(
            'hawb',
            'descr',
            'po',
            'locator',
            'koli',
            'loc',
            'pib_number',
            'sppb_date',
            'date_created',
            'date_updated',
            'checker',
            'scan_time'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        $and = "
        AND DATE(scan_time) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
        ";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		vw_inbound
		WHERE status IN ('Warehouse in Transit', 'successful')
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $created_by = '';
                if (!empty($val['created_by'])) {
                    $created_by = ' by ' . $val['created_by_nama'];
                }

                $updated_by = '';
                if (!empty($val['updated_by'])) {
                    $updated_by = ' by ' . $val['updated_by_nama'];
                }

                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

                $date_updated = Yii::app()->functions->prettyDate($val['date_updated'], true);
                $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

                $scan_time = Yii::app()->functions->prettyDate($val['scan_time'], true);
                $scan_time = Yii::app()->functions->translateDate($scan_time);

                $feed_data['aaData'][] = array(
                    $val['hawb'],
                    $val['descr'],
                    $val['po'],
                    $val['locator'],
                    $val['koli'],
                    $val['loc'],
                    $val['pib_number'],
                    $val['sppb_date'],
                    $date_created,
                    $date_updated,
                    $val['checker'],
                    $scan_time
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    // public function actiongetReportsSh()
    // {
    //     $aColumns = array(
    //         'hawb',
    //         'descr',
    //         'po',
    //         'locator',
    //         'koli',
    //         'loc',
    //         'date_created',
    //         'checker',
    //         'scan_time'
    //     );
    //     $t=AjaxDataTables::AjaxData($aColumns);
    //     if (isset($_GET['debug'])){
    //         Self::dump($t);
    //     }

    //     if (is_array($t) && count($t)>=1){
    //         $sWhere=$t['sWhere'];
    //         $sOrder=$t['sOrder'];
    //         $sLimit=$t['sLimit'];
    //     }

    //     $start_date = isset($_GET['start_date'])?$_GET['start_date']:'';
    //     $end_date = isset($_GET['end_date'])?$_GET['end_date']:'';

    //     $and="
    //     AND DATE(date_created) BETWEEN '".$start_date."' AND '".$end_date."'
    //     ";

    //     $stmt="SELECT SQL_CALC_FOUND_ROWS *
    // 	FROM
    // 	vw_inbound
    // 	WHERE 1
    // 	$and
    // 	$sWhere
    // 	$sOrder
    // 	$sLimit
    // 	";
    //     if (isset($_GET['debug'])){
    //         Self::dump($stmt);
    //     }

    //     $DbExt=new DbExt;
    //     $DbExt->qry("SET SQL_BIG_SELECTS=1");

    //     if ( $res=$DbExt->rst($stmt)){

    //         $iTotalRecords=0;
    //         $stmtc="SELECT FOUND_ROWS() as total_records";
    //         if ( $resc=$DbExt->rst($stmtc)){
    //             $iTotalRecords=$resc[0]['total_records'];
    //         }

    //         $feed_data['sEcho']=intval($_GET['sEcho']);
    //         $feed_data['iTotalRecords']=$iTotalRecords;
    //         $feed_data['iTotalDisplayRecords']=$iTotalRecords;

    //         foreach ($res as $val) {
    //             $date_created=Yii::app()->functions->prettyDate($val['date_created'],true);
    //             $date_created=Yii::app()->functions->translateDate($date_created);

    //             $scan_time=Yii::app()->functions->prettyDate($val['scan_time'],true);
    //             $scan_time=Yii::app()->functions->translateDate($scan_time);

    //             $feed_data['aaData'][]=array(
    //                 $val['hawb'],
    //                 $val['descr'],
    //                 $val['po'],
    //                 $val['locator'],
    //                 $val['koli'],
    //                 $val['loc'],
    //                 $date_created,
    //                 $val['checker'],
    //                 $scan_time
    //             );
    //         }
    //         if (isset($_GET['debug'])){
    //             Self::dump($feed_data);
    //         }
    //         $this->otableOutput($feed_data);
    //     }
    //     $this->otableNodata();
    // }

    public function actiongetReportsSh()
    {
        $aColumns = array(
            'hawb',
            'descr',
            'product_category_name',
            'modality',
            'delivery_id',
            'qty',
            'po',
            'ship_method',
            'etd',
            'eta',
            'pib_number',
            'sppb_date',
            'ata',
            'date_created',
            'date_updated',
            'status',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        $and = "
        AND DATE(date_created) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
        ";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS h.*, c.name as product_category_name,
        (SELECT first_name FROM {{user}} WHERE user_id=h.created_by) AS created_by_nama,
        (SELECT first_name FROM {{user}} WHERE user_id=h.updated_by) AS updated_by_nama
		FROM
		{{inbound_header}} h
        LEFT JOIN {{product_category}} c ON c.id=h.product_category_id
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $created_by = '';
                if (!empty($val['created_by'])) {
                    $created_by = ' by ' . $val['created_by_nama'];
                }

                $updated_by = '';
                if (!empty($val['updated_by'])) {
                    $updated_by = ' by ' . $val['updated_by_nama'];
                }

                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created) . $created_by;

                $date_updated = Yii::app()->functions->prettyDate($val['date_updated'], true);
                $date_updated = Yii::app()->functions->translateDate($date_updated) . $updated_by;

                $feed_data['aaData'][] = array(
                    $val['hawb'],
                    $val['descr'],
                    $val['product_category_name'],
                    $val['modality'],
                    $val['delivery_id'],
                    $val['qty'],
                    $val['po'],
                    $val['ship_method'],
                    $val['etd'],
                    $val['eta'],
                    $val['pib_number'],
                    $val['sppb_date'],
                    $val['ata'],
                    $date_created,
                    $date_updated,
                    $val['status'],
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetReportsIns()
    {
        $aColumns = array(
            'hawb',
            'descr',
            'po',
            'partnumber',
            'loc',
            'date_created',
            'checker',
            'scan_time'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        $and = "
        AND DATE(date_created) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
        ";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		vw_inbounds
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created);

                $scan_time = Yii::app()->functions->prettyDate($val['scan_time'], true);
                $scan_time = Yii::app()->functions->translateDate($scan_time);

                $feed_data['aaData'][] = array(
                    $val['hawb'],
                    $val['descr'],
                    $val['po'],
                    $val['partnumber'],
                    $val['loc'],
                    $date_created,
                    $val['checker'],
                    $scan_time
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetReportsOut()
    {

        // $aColumns = array(
        //     'hawb',
        //     'loc',
        //     'po',
        //     'destination',
        //     'delivery_id',
        //     'transporter',
        //     // 'pib_number',
        //     // 'sppb_date',
        //     'date_created',
        //     'date_updated',
        //     // 'checker',
        //     // 'scan_time',
        // );


        $aColumns = array(
            'ccompany',
            'actualShipDate2',
            'buyerPO',
            'externalOrderKey2',
            'sku',
            'externOrderKey',
            'lottable07',
            'lottable08',
            'shippedQty',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        $and = "
        AND DATE(actualShipDate2) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
        ";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		vw_outbound_sch
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $feed_data['aaData'][] = array(
                    $val['ccompany'],
                    $val['actualShipDate2'],
                    $val['buyerPO'],
                    $val['externalOrderKey2'],
                    $val['sku'],
                    $val['externOrderKey'],
                    $val['lottable07'],
                    $val['lottable08'],
                    $val['shippedQty'],
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetReportsOuts()
    {
        $aColumns = array(
            'hawb',
            'partnumber',
            'lotnumber',
            'loc',
            'po',
            'destination',
            'checker',
            'date_created',
            'scan_time'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        $and = "
        AND DATE(date_created) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
        ";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		vw_outbounds
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created);

                $scan_time = Yii::app()->functions->prettyDate($val['scan_time'], true);
                $scan_time = Yii::app()->functions->translateDate($scan_time);

                $feed_data['aaData'][] = array(
                    $val['hawb'],
                    $val['partnumber'],
                    $val['lotnumber'],
                    $val['loc'],
                    $val['po'],
                    $val['destination'],
                    $date_created,
                    $val['checker'],
                    $scan_time
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetReportsInv()
    {
        $aColumns = array(
            'lottable07',
            'sku',
            'descr',
            'po',
            'locator',
            'toLot',
            'date_created',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = "";

        // $stmt = "SELECT SQL_CALC_FOUND_ROWS *
        // FROM
        // vw_inventory
        // WHERE 1
        // $and
        // $sWhere
        // $sOrder
        // $sLimit
        // ";

        //report inventory harus diganti kondisinya
        //bisa sebagian keluar untuk marunda
        // where qty_out < qty

        // $stmt = "SELECT SQL_CALC_FOUND_ROWS * FROM (
        //     SELECT d.hawb, d.descr, d.loc, 
        //     (
        //         CASE
        //         WHEN demo_movement_id IS NULL THEN d.loc
        //         ELSE
        //             (SELECT loc FROM el_demo_movement_detail dmd WHERE dmd.hawb=d.descr)
        //         END
        //     ) AS loc_inb_dm,
        //     h.po AS PO, h.descr AS description, h.locator, (d.qty - d.qty_out) AS qty
        //     FROM el_inbound_details d LEFT JOIN el_inbound_header h ON h.hawb=d.hawb
        //     WHERE (d.loc <> '0' AND d.loc <> '') 
        //     AND h.delivery_id=d.sso_delivery_id 
        //     -- not in: barang outbound
        //     -- AND d.descr NOT IN (select descr from el_outbound_details where hawb=d.hawb AND descr=d.descr)
        //     AND d.qty_out < d.qty
        //     ) AS x WHERE 1

        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

        // $and = "
        // AND DATE(date_created) BETWEEN '" . $start_date . "' AND '" . $end_date . "'
        // ";

        //SELECT SQL_CALC_FOUND_ROWS * FROM (
        // SELECT id.id, lottable07, sku, descr, po, locator, c.receiptKey, toLot, c.date_created  FROM el_schenker_inbound_detail id
        // INNER JOIN view_schenker_inbound_combine c ON c.receiptKey=id.receiptKey
        // 	WHERE NOT EXISTS (SELECT lot FROM el_schenker_outbound_pick WHERE lot=id.toLot)) AS x WHERE 1

        $stmt = "SELECT SQL_CALC_FOUND_ROWS * FROM (

SELECT id.id, lottable07, sku, descr, po, locator, c.receiptKey, toLot, c.date_created  FROM el_schenker_inbound_detail id
INNER JOIN view_schenker_inbound_combine c ON c.receiptKey=id.receiptKey
	WHERE NOT EXISTS (SELECT lot FROM el_schenker_outbound_pick WHERE lot=id.toLot)
	
	UNION ALL
	
	SELECT d.id, d.hawb AS lottable07, d.descr AS sku, h.descr AS description, h.po AS PO, d.loc, NULL AS receiptKey, NULL AS toLot, d.date_created 
	-- h.locator, (d.qty - d.qty_out) AS qty
        FROM el_inbound_details d LEFT JOIN el_inbound_header h ON h.hawb=d.hawb
        WHERE (d.loc <> '0' AND d.loc <> '') 
        AND h.delivery_id=d.sso_delivery_id 
        AND d.qty_out < d.qty
				AND h.warehouse='arcadia'
				
				) AS x WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";


        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;



            foreach ($res as $val) {
                $feed_data['aaData'][] = array(
                    $val['lottable07'],
                    $val['sku'],
                    $val['descr'],
                    $val['po'],
                    $val['locator'],
                    $val['toLot'],
                    $val['date_created'],
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetReportsInvs()
    {
        $aColumns = array(
            'hawb',
            'partnumber',
            'lotnumber',
            'descr',
            'po',
            'loc',
            'date_created',
            'scan_time',
            'aging'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = "";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		vw_inventorys
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $feed_data['aaData'][] = array(
                    $val['hawb'],
                    $val['partnumber'],
                    $val['lotnumber'],
                    $val['descr'],
                    $val['po'],
                    $val['loc'],
                    $val['date_created'],
                    $val['scan_time'],
                    $val['aging']
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetReportsInvs2()
    {
        $aColumns = array(
            'partnumber',
            'descr',
            'quantity'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            Self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = "";

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		vw_inventorys_summary
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            Self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $feed_data['aaData'][] = array(
                    $val['partnumber'],
                    $val['descr'],
                    $val['quantity']
                );
            }
            if (isset($_GET['debug'])) {
                Self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetQRAll()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = self::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['id'])) {

            //isset($this->data['drv_default_location'])?$this->data['drv_default_location']:''
            $and = '';
            if (isset($this->data['idSub'])) {
                $and .= "AND descr=" . Driver::q($this->data['idSub']);
            }

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_details}}
            WHERE
            hawb=" . Driver::q($this->data['id']) . "
            $and
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 0;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    #parameter inputan
                    //isi QRCode saat discan
                    $isi_teks = $val['descr'];
                    //namafile setelah jadi qrcode
                    $namafile = $isi_teks . ".png";
                    //kualitas dan ukuran qrcode
                    $quality = 'H';
                    $ukuran = 8;
                    $padding = 0;

                    QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);

                    if (strpos($namafile, ' ') !== false) {
                        $namafile = str_replace(' ', '%20', $namafile);
                    }

                    if (strpos($namafile, '#') !== false) {
                        $namafile = str_replace('#', '%23', $namafile);
                    }

                    $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;

                    $html .= "
                    <tr id='tr" . $i++ . "'>
                        <td style=\"text-align:center;\">
                            <div class='cover_qrcode'>
                                <div style='height: 35.3mm !important;margin: 0 auto;width: 100%;'>
                                    <img src=\"" . $urlpath . "\" style='height: 100%;width: 40%;'><br><h4 id=\"mySmallModalLabel\" class=\"modal-title\">" . $isi_teks . "</h4>
                                </div>
                            </div>
                        </td>
                    </tr>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetQROne()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = self::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_details}}
            WHERE
            descr=" . Driver::q($this->data['id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 0;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    #parameter inputan
                    //isi QRCode saat discan
                    $isi_teks = $val['descr'];
                    //namafile setelah jadi qrcode
                    $namafile = $isi_teks . ".png";
                    //kualitas dan ukuran qrcode
                    $quality = 'H';
                    $ukuran = 8;
                    $padding = 0;

                    QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);

                    if (strpos($namafile, ' ') !== false) {
                        $namafile = str_replace(' ', '%20', $namafile);
                    }

                    if (strpos($namafile, '#') !== false) {
                        $namafile = str_replace('#', '%23', $namafile);
                    }

                    $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;

                    $html .= "
                    <tr id='tr" . $i++ . "'>
                        <td style=\"text-align:center; \">
                            <div class='cover_qrcode'>
                                <div style='height: 35.3mm !important;margin: 0 auto;width: 100%;'>
                                    <img id='imgQrcode' src='$urlpath' style='height: 100%;width: 40%;'><br><h4 id=\"mySmallModalLabel\" class=\"modal-title\">" . $isi_teks . "</h4>
                                </div>
                            </div>
                        </td>
                    </tr>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actionmovList()
    {
        $aColumns = array(
            'id',
            'hawb',
            'hawb_descr',
            'loc_before',
            'loc_after',
            'date_created',
            'users'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{moving}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created);

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['hawb'],
                    $val['hawb_descr'],
                    $val['loc_before'],
                    $val['loc_after'],
                    $date_created,
                    $val['users']
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionmovLoc()
    {
        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['hawbselect'])) {

            $idHawb = explode('###', $this->data['hawbselect']);
            $hawb = $idHawb[0];
            $subhawb = $idHawb[1];

            $fullName = Driver::getUserName();

            $params = array(
                'hawb' => $hawb,
                'hawb_descr' => $subhawb,
                'loc_before' => $this->data['locname'],
                'loc_after' => $this->data['Locselect'],
                'users' => $fullName
            );

            $params['date_created'] = Driver::dateNow();

            Driver::insertLog($params);

            $params = array(
                'loc' => $this->data['Locselect']
            );

            Yii::app()->db->createCommand()->update('el_inbound_details', $params, 'descr=:descr AND hawb=:hawb', array(':descr' => $subhawb, 'hawb' => $hawb));

            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = 'mov-loc-modal';
        } else $this->msg = Driver::t("failed cannot insert record");

        $this->jsonResponse();
    }

    private function userType()
    {
        return Driver::getUserType();
    }

    private function userId()
    {
        return Driver::getUserId();
    }

    public function actiongeneralSettings()
    {

        Yii::app()->functions->updateOption(
            'drv_default_location',
            isset($this->data['drv_default_location']) ? $this->data['drv_default_location'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'drv_map_style',
            isset($this->data['drv_map_style']) ? $this->data['drv_map_style'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'drv_delivery_time',
            isset($this->data['drv_delivery_time']) ? $this->data['drv_delivery_time'] : '',
            Driver::getUserId()
        );

        if (!empty($this->data['drv_default_location'])) {
            $country_list = require_once('CountryCode.php');
            $country_name = '';
            if (array_key_exists($this->data['drv_default_location'], (array)$country_list)) {
                $country_name = $country_list[$this->data['drv_default_location']];
            } else $country_name = $this->data['drv_default_location'];
            if ($res = Driver::addressToLatLong($country_name)) {
                Yii::app()->functions->updateOption("drv_default_location_lat", $res['lat'], Driver::getUserId());
                Yii::app()->functions->updateOption("drv_default_location_lng", $res['long'], Driver::getUserId());
            }
        }

        Yii::app()->functions->updateOption(
            'driver_send_push_to_online',
            isset($this->data['driver_send_push_to_online']) ? $this->data['driver_send_push_to_online'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_include_offline_driver_map',
            isset($this->data['driver_include_offline_driver_map']) ? $this->data['driver_include_offline_driver_map'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_disabled_auto_refresh',
            isset($this->data['driver_disabled_auto_refresh']) ? $this->data['driver_disabled_auto_refresh'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_enabled_notes',
            isset($this->data['driver_enabled_notes']) ? $this->data['driver_enabled_notes'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_enabled_signature',
            isset($this->data['driver_enabled_signature']) ? $this->data['driver_enabled_signature'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_enabled_photo',
            isset($this->data['driver_enabled_photo']) ? $this->data['driver_enabled_photo'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_enabled_multi_trip',
            isset($this->data['driver_enabled_multi_trip']) ? $this->data['driver_enabled_multi_trip'] : '',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'driver_icon',
            isset($this->data['driver_icon']) ? $this->data['driver_icon'] : 'assets/images/car.png',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'customer_icon',
            isset($this->data['customer_icon']) ? $this->data['customer_icon'] : 'assets/images/racing-flag.png',
            Driver::getUserId()
        );

        Yii::app()->functions->updateOption(
            'merchant_icon',
            isset($this->data['merchant_icon']) ? $this->data['merchant_icon'] : 'assets/images/restaurant-pin-32.png',
            Driver::getUserId()
        );

        $this->code = 1;
        $this->msg = Yii::t("default", "Setting saved");
        $this->jsonResponse();
    }

    public function actionForgotPassword()
    {
        if ($res = AdminFunctions::getCustomerByEmail($this->data['email_address'])) {
            if (AdminFunctions::sendResetPassword($res)) {
                $this->code = 1;
                $this->msg = "OK";
                $this->details = Yii::app()->createUrl('/app/resetpassword', array(
                    'hash' => $res['token']
                ));
            } else $this->msg = t("Sorry but we cannot process your request");
        } else $this->msg = t("Sorry but email address you supplied does not exists in our records");
        $this->jsonResponse();
    }

    public function actionresetPassword()
    {
        if ($this->data['password'] != $this->data['cpassword']) {
            $this->msg = t("Confirm password does not macth with your new password");
            $this->jsonResponse();
            Yii::app()->end();
        }
        if (isset($this->data['hash'])) {
            if ($res = FrontFunctions::getCustomerByToken($this->data['hash'])) {

                if ($this->data['verification_code'] != $res['verification_code']) {
                    $this->msg = t("Your verification code is incorrect");
                    $this->jsonResponse();
                    Yii::app()->end();
                }

                $customer_id = $res['customer_id'];
                $params['password'] = CPasswordHelper::hashPassword($this->data['password']);
                $params['date_modified'] = AdminFunctions::dateNow();

                $db = new DbExt;
                if ($db->updateData("{{customer}}", $params, 'customer_id', $customer_id)) {
                    $this->code = 1;
                    $this->msg = t("Your password has been reset");
                    $this->details = Yii::app()->createUrl('/app/login');
                } else $this->msg = t("failed cannot update record");
            } else $this->msg = t("Hash does not exist or your record does not exist in our records");
        } else $this->msg = t("Hash is missing");
        $this->jsonResponse();
    }

    public function actionsendPush()
    {
        if ($res = Driver::driverInfo($this->data['driver_id_push'])) {
            $params = array(
                'customer_id' => $res['customer_id'],
                'device_platform' => $res['device_platform'],
                'device_id' => $res['device_id'],
                'push_title' => $this->data['x_push_title'],
                'push_message' => $this->data['x_push_message'],
                'push_type' => "campaign",
                'actions' => "private",
                'driver_id' => $res['driver_id'],
                'date_created' => AdminFunctions::dateNow()
            );
            $db_ext = new DbExt;
            if ($db_ext->insertData("{{driver_pushlog}}", $params)) {
                $push_id = Yii::app()->db->getLastInsertID();
                Driver::RunPush($push_id);
                $this->code = 1;
                $this->msg = t("Successful");
            } else $this->msg = t("Something went wrong cannot insert records");
        } else $this->msg = t("Driver info not found");
        $this->jsonResponse();
    }

    public function actionuploadFile()
    {
        $upload_dir = Driver::uploadPath();
        $uploader = new FileUpload('uploadfile');
        $ext = $uploader->getExtension(); // Get the extension of the uploaded file
        $prefix = isset($_GET['prefix']) ? $_GET['prefix'] : '';
        $uploader->newFileName = $prefix . "-" . Functions::generateCode(20) . "." . $ext;
        //Handle the upload
        $result = $uploader->handleUpload($upload_dir);
        if (!$result) {
            exit(json_encode(array('success' => 2, 'msg' => $uploader->getErrorMsg())));
        }
        echo json_encode(array('success' => 1, 'filename' => $uploader->newFileName));
    }

    public function actiongetsearch()
    {
        if (isset($this->data['q'])) {

            $data = '';
            $html = '';
            $status_list = array('inbound', 'outbound');
            foreach ($status_list as $status) {
                if ($res = Driver::getSearch($status, $this->data['q'])) {
                    $html = '';

                    foreach ($res as $val) {
                        $html .= Driver::formatSearch($status, $val);
                    }

                    $data .= $html;
                }
            }

            $this->code = 1;
            $this->msg = Driver::t("Success..");
            $this->details = $data;
        } else $this->msg = Driver::t("Missing parameters") . $this->data['q'];

        $this->jsonResponse();
    }

    //Service
    public function actionloclists()
    {
        $aColumns = array(
            'loc_id',
            'loc_name',
            'loc_descr'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{loc_s}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['loc_name'];

                $action = "<a class=\"btn btn-success new-loc\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a>";

                $LocUsage = Driver::GetLocUsage(trim($val['loc_descr']));
                //self::dump($LocUsage['loc_descr']);

                if (!empty($LocUsage['loc_descr'])) {

                    $loc_id = $val['loc_id'];
                    $p = "loc_id=$loc_id" . "&tbl=loc&whereid=loc_id";
                    $action .= "<a data-data=\"$p\" class=\"btn btn-danger table-delete\" href=\"javascript:;\">" . Driver::t("Delete") . "</a>";
                }

                $feed_data['aaData'][] = array(
                    $val['loc_id'],
                    $val['loc_name'],
                    $val['loc_descr'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddLocs()
    {
        $params = array(
            'loc_name' => $this->data['loc_name'],
            'loc_descr' => $this->data['loc_descr']
        );
        if (!isset($this->data['idLoc'])) {
            $this->data['idLoc'] = '';
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['idLoc'])) {
            if ($db->updateData("{{loc_s}}", $params, 'loc_name', $this->data['idLoc'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-loc-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            if ($db->insertData("{{loc_s}}", $params)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-loc-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actionmovLists()
    {
        $aColumns = array(
            'id',
            'hawb',
            'partnumber',
            'lotnumber',
            'loc_before',
            'loc_after',
            'date_created',
            'users'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{moving_s}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created);

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['hawb'],
                    $val['partnumber'],
                    $val['lotnumber'],
                    $val['loc_before'],
                    $val['loc_after'],
                    $date_created,
                    $val['users']
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actioninlists()
    {
        $aColumns = array(
            'id',
            'hawb',
            'po',
            'checker',
            'date_created',
            'status'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            self::dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{inbound_header_s}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            self::dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created);

                $valstat = '';
                switch ($val['status']) {
                    case "acknowledged":
                    case "successful":
                    case "Warehouse in Transit":
                        $valstat = 'primary';
                        break;
                    case "started":
                        $valstat = 'info';
                        break;
                    case "assigned":
                        $valstat = 'warning';
                        break;
                    case "inprogress":
                        $valstat = 'success';
                        break;
                    case "failed":
                    case "canceled":
                    case "cancelled":
                    case "declined":
                    case "suspended":
                    case "blocked":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['id'];
                $admin_id = Self::userType();

                $action = '';

                //if ($val['status']!=="successful") {
                if ($admin_id == 1) {
                    $action = "<a class=\"btn btn-success new-inbound\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit Header") . "</a> ";
                }
                $action .= "<a class=\"btn btn-primary details-inbound\" data-id=\"" . $id . "\" data-hawb=\"" . $val['hawb'] . "\" href=\"javascript:;\">" . Driver::t("Details") . "</a> ";

                $cek = Driver::CekCountIns($val['hawb']);
                if ($admin_id == 1 && $cek['CountLoc'] < 1) {
                    $action .= "<a class=\"btn btn-danger del-inbound\" data-hawb=\"" . $val['hawb'] . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                }
                /*$action.="<a class=\"btn btn-default new-inbound\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Edit")."</a>";*/

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['hawb'],
                    $val['po'],
                    $val['checker'],
                    $date_created,
                    $status,
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                self::dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actiongetInLists()
    {
        if (isset($this->data['idHawb'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_details_s}}
            WHERE
            hawb=" . Driver::q($this->data['idHawb']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $docfile = '';
            $filehtml = '';
            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {

                foreach ($res as $val) {

                    $html .= "
                    <tr>
                    <td><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td><a class=\"details\" href=\"javascript:;\" data-id=\"" . $val['id'] . "\" data-partnumber=\"" . $val['partnumber'] . "\" >" . $val['partnumber'] . "</a></td>
                    <td>" . $val['descr'] . "</td>
                    <td>" . $val['qty'] . "</td>
                    </tr>
                    ";
                }

                $docfile = Driver::getDocFile($this->data['idHawb']);

                if (!empty($docfile)) {
                    $filehtml = "<a id='btnDocFile' data-id='" . $docfile . "'><button class='btn-success btn'><i class='fa fa-download icon-only'></i></button>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html,
                    'docfile' => $filehtml
                );
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetInInfos()
    {
        if (isset($this->data['idInb'])) {
            if ($res = Driver::InInfos($this->data['idInb'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'head' => $res
                );
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idView'];
        $this->jsonResponse();
    }

    public function actiongetHawbs()
    {
        $this->data = $_GET;
        //if(isset($this->data['idHawb'])){
        $idHawb = '';
        if (isset($this->data['idHawb'])) {
            $idHawb = $this->data['idHawb'];
        }
        if ($res = Driver::GetHwbs($idHawb)) {

            $this->details = $res;
        } else $this->msg = Driver::t("Record not found");
        //} else $this->msg=Driver::t("Missing parameters").$this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetParts()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {

            $idPart = '';
            if (isset($this->data['idPart'])) {
                $idPart = $this->data['idPart'];
            }

            if ($res = Driver::GetParts($this->data['idHawb'], $idPart)) {

                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetLotPart()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb']) || isset($this->data['idPart'])) {

            $lotNumber = '';
            if (isset($this->data['idLot'])) {
                $lotNumber = $this->data['idLot'];
            }

            if ($res = Driver::GetLotParts($this->data['idHawb'], $this->data['idPart'], $lotNumber)) {

                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetLocPart()
    {
        if (isset($this->data['idHawb']) || isset($this->data['idPart']) || isset($this->data['idLot'])) {

            if ($res = Driver::GetLocParts($this->data['idHawb'], $this->data['idPart'], $this->data['idLot'])) {

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res[0]['loc'];
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        $this->jsonResponse();
    }

    public function actiongetLocParts()
    {
        if (isset($this->data['idHawb']) || isset($this->data['idPart']) || isset($this->data['idLot'])) {

            if ($res = Driver::GetLocParts($this->data['idHawb'], $this->data['idPart'], $this->data['idLot'])) {

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res[0]['loc'];
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        $this->jsonResponse();
    }

    public function actionmovLocs()
    {
        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['hawbselect'])) {

            $fullName = Driver::getUserName();

            $params = array(
                'hawb' => $this->data['hawbselect'],
                'partnumber' => $this->data['partselect'],
                'lotnumber' => $this->data['lotselect'],
                'loc_before' => $this->data['locname'],
                'loc_after' => $this->data['Locselect'],
                'users' => $fullName
            );

            $params['date_created'] = Driver::dateNow();

            Driver::insertLogs($params);

            $params = array(
                'loc' => $this->data['Locselect']
            );

            $db->updateData("{{inbound_lots}}", $params, 'lotnumber', $this->data['lotselect']);

            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = 'mov-loc-modal';
        } else $this->msg = Driver::t("failed cannot insert record");

        $this->jsonResponse();
    }

    public function actionaddIns()
    {
        $params = array(
            'hawb' => isset($this->data['hawb']) ? $this->data['hawb'] : '',
            'po' => isset($this->data['po_number']) ? $this->data['po_number'] : '',
            'locator' => isset($this->data['locator_number']) ? $this->data['locator_number'] : '',
            'docfile' => isset($this->data['filename']) ? $this->data['filename'] : ''
        );
        if (!isset($this->data['idInb'])) {
            $this->data['idInb'] = '';
        }



        //add operation, check existing hawb
        if (empty($this->data['idInb'])) {
            $s =  Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header_s')
                ->where('hawb=:hawb', array(':hawb' => $this->data['hawb']))
                ->queryRow();

            if ($s) {
                $this->msg = Driver::t("Duplicate HAWB");
                $this->jsonResponse();
                Yii::app()->end();
            }
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $params['date_created'] = Driver::dateNow();

        $sono = isset($this->data['sono']) ? json_encode($this->data['sono']) : '';
        $partno = isset($this->data['partno']) ? json_encode($this->data['partno']) : '';
        $descr = isset($this->data['descr']) ? json_encode($this->data['descr']) : '';
        $qty = isset($this->data['qty']) ? json_encode($this->data['qty']) : '';

        //pengecekan agar detail tidak kosong 
        if (empty($partno) && empty($this->data['idInb'])) {
            $this->msg = Driver::t("Sorry but details don't to empty!!");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['idInb'])) {
            unset($params['hawb']);
            if ($db->updateData("{{inbound_header_s}}", $params, 'id', $this->data['idInb'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-in-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            if ($db->insertData("{{inbound_header_s}}", $params)) {

                if (!empty($partno)) {
                    $sono = json_decode($sono, true);
                    $partno = json_decode($partno, true);
                    $descr = json_decode($descr, true);
                    $qty = json_decode($qty, true);

                    //lakukan loop untuk insert detail
                    for ($i = 0; $i < count($partno); ++$i) {

                        $paramdetails = array(
                            'hawb' => $this->data['hawb'],
                            'partnumber' => $partno[$i],
                            'sonumber' => $sono[$i],
                            'descr' => $descr[$i],
                            'qty' => $qty[$i]
                        );
                        $db->insertData("{{inbound_details_s}}", $paramdetails);
                        $id_detail = Yii::app()->db->getLastInsertID();

                        //lakukan loop untuk insert lot
                        $qtylot = $qty[$i];
                        for ($n = 0; $n < $qtylot; ++$n) {

                            /*$checklot = false;
                            $lotnumber= '';
                            while($checklot == true) {

                                $lotnumber = Functions::generateCodes(10);
                                $result = Driver::verifyLot($lotnumber);
                                self::dump($lotnumber);

                                $checklot = $result; //recheck lot double
                            }*/

                            $lotnumber = Functions::generateCodes(10);

                            $paramlot = array(
                                'hawb' => $this->data['hawb'],
                                'partnumber' => $partno[$i],
                                'lotnumber' => $lotnumber,
                                'id_detail' => $id_detail
                            );

                            $db->insertData("{{inbound_lots}}", $paramlot);

                            $paramlot = array(
                                'hawb' => $this->data['hawb'],
                                'partnumber' => $partno[$i],
                                'lotnumber' => $lotnumber,
                                'loc_after' => 'stage',
                                'users' => 'system',
                                'id_detail' => $id_detail
                            );
                            $paramlot['date_created'] = Driver::dateNow();

                            Driver::insertLogs($paramlot);
                        }
                    }
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-in-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actiongetLotList()
    {
        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_lots}}
            WHERE
            id_detail=" . Driver::q($this->data['id']) . "
            AND hawb=" . Driver::q($this->data['hawb']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $docfile = '';
            $filehtml = '';
            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $iLoc = "";
                    $loc = '<td>';
                    if (!empty($val['loc'])) {
                        $iLoc = "<span class=\"label label-default\">" . $val['loc'] . "</span>";
                        $loc .= $iLoc;
                    }
                    $loc .= "</td>";

                    $admin_id = Self::userType();

                    $and = '';
                    if ($admin_id == 1 && empty($iLoc)) {
                        $and = "<button class=\"btn btn-success btn-xs putaway\" data-id=\"" . $val['lotnumber'] . "\"> Send to Loc</button>";
                    }

                    $html .= "
                    <tr>
                    <td><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td><a href=\"#\">" . $val['lotnumber'] . "</a></td>
                    " . $loc . "
                    <td class=\"text-right\">
                        <button class=\"btn btn-white btn-xs printQrcode\" data-id=\"" . $val['lotnumber'] . "\"> Print Qrcode</button>
                        " . $and . "
                    </td>
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html
                );
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetpart()
    {

        if (isset($this->data['partno'])) {
            if ($res = Driver::GetPart($this->data['partno'])) {

                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['partno'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actiongetpartdesc()
    {

        if (isset($this->data['partnumber'])) {
            if ($res = Driver::GetPartDescr($this->data['partnumber'])) {

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['partnumber'];
        $this->jsonResponse();
    }

    public function actiongetLocs()
    {
        $this->data = $_GET;
        if (isset($this->data['idHawb'])) {
            if ($res = Driver::GetLocs($this->data['idHawb'])) {

                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];

        echo CJSON::encode($this->details);
        Yii::app()->end();
    }

    public function actionsendLocs()
    {
        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $db = new DbExt;
        if (!empty($this->data['HawbLoc'])) {

            $params['loc'] = $this->data['Loc_select'];
            if ($db->updateData("{{inbound_lots}}", $params, 'lotnumber', $this->data['HawbLoc'])) {

                $hawb = Driver::GetHawbLot($this->data['HawbLoc']);
                $cek = Driver::CekInLocs($hawb['hawb']);
                if ($cek['hitung'] < 1) {
                    $params = array(
                        'status' => 'successful',
                        'checker' => 'system'
                    );
                    $db->updateData("{{inbound_header_s}}", $params, 'hawb', $hawb['hawb']);
                }

                $LocCurrent = Driver::getLocCurrents($this->data['HawbLoc']);
                $paramdetails = array(
                    'hawb' => $hawb['hawb'],
                    'partnumber' => $LocCurrent['partnumber'],
                    'lotnumber' => $this->data['HawbLoc'],
                    'loc_before' => $LocCurrent['loc_after'],
                    'loc_after' => $this->data['Loc_select'],
                    'users' => 'system'
                );
                $paramdetails['date_created'] = Driver::dateNow();

                Driver::insertLogs($paramdetails);

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'send-loc-modal';
            }
        } else $this->msg = Driver::t("failed cannot insert record");

        $this->jsonResponse();
    }

    public function actionoutlists()
    {
        $aColumns = array(
            'id',
            'qty',
            'destination',
            'checker',
            'date_created',
            'status'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{outbound_header_s}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {
                $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                $date_created = Yii::app()->functions->translateDate($date_created);

                $valstat = '';
                switch ($val['status']) {
                    case "acknowledged":
                    case "successful":
                    case "Warehouse in Transit":
                        $valstat = 'primary';
                        break;
                    case "started":
                        $valstat = 'info';
                        break;
                    case "assigned":
                        $valstat = 'warning';
                        break;
                    case "inprogress":
                        $valstat = 'success';
                        break;
                    case "failed":
                    case "canceled":
                    case "cancelled":
                    case "declined":
                    case "suspended":
                    case "blocked":
                        $valstat = 'danger';
                        break;
                }
                $status = "<span class=\"label label-" . $valstat . " \">" . Driver::t($val['status']) . "</span>";

                $id = $val['id'];

                if ($val['status'] == 'successful') {
                    $action = "<a class=\"btn btn-success DOPrint\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("DO Print") . "</a> ";
                } else {
                    $action = "<a class=\"btn btn-info PickPrint\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Pick Print") . "</a> ";
                }

                $action .= "<a class=\"btn btn-primary details-outbound\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Details") . "</a>";

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['qty'],
                    $val['destination'],
                    $val['checker'],
                    $date_created,
                    $status,
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddOuts()
    {

        $hawb = isset($this->data['hawb']) ? json_encode($this->data['hawb']) : '';

        $db = new DbExt;
        if (!empty($hawb)) {
            $hawb = json_decode($hawb, true);

            $startmonth = date("j");
            $keycount = Driver::GetkKeycCount();

            if ($startmonth == 5 && $keycount > 1) { //jika di tgl 1 dan nomor lebih dari 1
                $keycount = 1;
            }

            $newnoship = str_pad($keycount, 4, '0', STR_PAD_LEFT);

            $noship = 'GE/' . date("m") . date("y") . '/' . $newnoship;

            $params = array(
                'date_created' => Driver::dateNow(),
                'qty' => count($hawb),
                'delivery' => isset($this->data['delivery']) ? $this->data['delivery'] : '',
                'cp' => isset($this->data['cp']) ? $this->data['cp'] : '',
                'destination' => isset($this->data['destination']) ? $this->data['destination'] : '',
                'noship' => $noship
            );

            //update keycount
            $paramsoption['option_value'] = $keycount;
            $db->updateData('el_option', $paramsoption, 'option_name', 'noship');

            if (!Driver::islogin()) {
                $this->msg = Driver::t("Sorry but your session has expired");
                $this->jsonResponse();
                Yii::app()->end();
            }

            if (!empty($this->data['idOut'])) {

                if ($db->updateData("{{outbound_header}}", $params, 'id', $this->data['idOut'])) {
                    $this->code = 1;
                    $this->msg = Driver::t("Successful");
                    $this->details = 'new-out-modal';
                } else $this->msg = Driver::t("failed cannot insert record");
            } else {

                if ($db->insertData("{{outbound_header_s}}", $params)) {

                    $id = Driver::GetIdOut();

                    $part = isset($this->data['part']) ? json_encode($this->data['part']) : '';
                    $lot = isset($this->data['lot']) ? json_encode($this->data['lot']) : '';
                    $loc = isset($this->data['loc']) ? json_encode($this->data['loc']) : '';
                    $po = isset($this->data['po']) ? json_encode($this->data['po']) : '';

                    $part = json_decode($part, true);
                    $lot = json_decode($lot, true);
                    $loc = json_decode($loc, true);
                    $po = json_decode($po, true);

                    for ($i = 0; $i < count($hawb); ++$i) {

                        $paramdetails = array(
                            'hawb' => $hawb[$i],
                            'partnumber' => $part[$i],
                            'lotnumber' => $lot[$i],
                            'loc' => $loc[$i],
                            'po' => $po[$i],
                            'id' => $id
                        );
                        $db->insertData("{{outbound_details_s}}", $paramdetails);

                        //update flag loc
                        $paramsupdate['flag'] = '1';
                        $db->updateData("{{inbound_lots}}", $paramsupdate, 'lotnumber', $lot[$i]);

                        $LocCurrent = Driver::getLocCurrents($lot[$i]);
                        $paramdetails = array(
                            'hawb' => $hawb[$i],
                            'partnumber' => $part[$i],
                            'lotnumber' => $lot[$i],
                            'loc_before' => $LocCurrent['loc_after'],
                            'loc_after' => 'stage',
                            'users' => 'system'
                        );
                        $paramdetails['date_created'] = Driver::dateNow();

                        Driver::insertLogs($paramdetails);

                        //cek hawb inbound
                        $cekin = Driver::cekStatusIn($hawb[$i]);
                        if ($cekin['CountLoc'] < 1) {
                            $paramx['status'] = 'Outbounded';
                            $db->updateData("{{inbound_header_s}}", $paramx, 'hawb', $hawb[$i]);
                        }
                    }

                    $this->code = 1;
                    $this->msg = Driver::t("Successful");
                    $this->details = 'new-out-modal';
                } else $this->msg = Driver::t("failed cannot insert record");
            }
        }

        $this->jsonResponse();
    }

    public function actionapkLists()
    {
        $aColumns = array(
            'id',
            'name',
            'username',
            'password',
            'last_login',
            'token'
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{apk_s}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['id'];

                $admin_id = Self::userType();

                $action = '';
                $action = "<a class=\"btn btn-success new-apk\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";

                if ($admin_id == 1) {
                    $p = "id=$id" . "&tbl=apk&whereid=id";
                    $action .= "<a data-data=\"$p\" class=\"btn btn-danger table-delete\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                } else {
                    $action = "<a class=\"btn btn-success new-apk\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['name'],
                    $val['username'],
                    $val['last_login'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionaddApks()
    {
        $params = array(
            'name' => $this->data['name'],
            'username' => $this->data['username'],
            'password' => $this->data['password'],
        );
        if (!isset($this->data['idApk'])) {
            $this->data['idApk'] = '';
        }

        if (!Driver::islogin()) {
            $this->msg = Driver::t("Sorry but your session has expired");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (isset($params['password'])) {
            if (!empty($params['password'])) {
                $params['password'] = md5($params['password']);
            } else unset($params['password']);
        }

        $db = new DbExt;
        if (!empty($this->data['idApk'])) {
            if ($db->updateData("{{apk_s}}", $params, 'id', $this->data['idApk'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-apk-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        } else {
            if ($db->insertData("{{apk_s}}", $params)) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = 'new-apk-modal';
            } else $this->msg = Driver::t("failed cannot insert record");
        }

        $this->jsonResponse();
    }

    public function actiongetApkInfos()
    {
        if (isset($this->data['idApk'])) {
            if ($res = Driver::ApkInfos($this->data['idApk'])) {
                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $res;
            } else $this->msg = Driver::t("Record not found");
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idApk'];
        $this->jsonResponse();
    }

    public function actiongetOutLists()
    {
        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{outbound_details_s}}
            WHERE
            id=" . Driver::q($this->data['id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $loc = '';
                    if (!empty($val['loc'])) {
                        $loc = "<td>" . $val['loc'] . "</td>";
                    }

                    $html .= "
                    <tr>
                    <td><span class=\"label label-primary\">" . $i++ . "</span></td>
                    <td>" . $val['hawb'] . "</td>
                    <td>" . $val['partnumber'] . "</td>
                    <td>" . $val['lotnumber'] . "</td>
                    " . $loc . "
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetPicLists()
    {
        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            vw_outbounds
            WHERE
            id=" . Driver::q($this->data['id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 1;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $html .= "
                    <tr>
                    <td>" . $val['hawb'] . "</td>
                    <td>" . $val['partnumber'] . "</td>
                    <td>" . $val['lotnumber'] . "</td>
                    <td>" . $val['po'] . "</td>
                    <td>" . $val['loc'] . "</td>
                    <td>" . $val['destination'] . "</td>
                    </tr>
                    ";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetDO()
    {
        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS
            hawb,partnumber, COUNT(lotnumber) AS `total`, po,
            date_created, destination, delivery, cp, noship
            FROM
            vw_outbounds
            WHERE
            id=" . Driver::q($this->data['id']) . "
            GROUP BY hawb, partnumber, po";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 1;

            $date_created = '';
            $address = '';
            $cp = '';
            $po = '';
            $shipment = '';

            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    $dPart = Driver::GetPart($val['partnumber'], $val['hawb']);

                    $html .= "
                    <tr>
                    <td>" . $i++ . "</td>
                    <td>" . $val['partnumber'] . "</td>
                    <td>" . $val['total'] . "</td>
                    <td>" . $dPart[0]['descr'] . "</td>
                    <td>" . $dPart[0]['sonumber'] . "</td>
                    <td>" . $val['po'] . "</td>
                    </tr>
                    ";

                    $date_created = Yii::app()->functions->prettyDate($val['date_created'], true);
                    $date_created = Yii::app()->functions->translateDate($date_created);
                    $address = $val['destination'] . ', ' . $val['delivery'];
                    $cp = $val['cp'];
                    $noship = $val['noship'];
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = array(
                    'html' => $html,
                    'header' => array(
                        'date' => $date_created,
                        'address' => $address,
                        'cp' => $cp,
                        'noship' => $noship
                    )
                );
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetLotQRCode()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = Driver::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['idLot'])) {

            $idHawb = $this->data['idLot'];

            #parameter inputan
            //isi QRCode saat discan
            $isi_teks = $idHawb;
            //direktori dan nama logo
            $logopath = Yii::app()->getBaseUrl(true) . '/assets/images/dk_.jpg';
            //namafile setelah jadi qrcode
            $namafile = $idHawb . ".png";
            //kualitas dan ukuran qrcode
            $quality = 'H';
            $ukuran = 8;
            $padding = 0;

            QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);

            $html = "";

            if (strpos($namafile, ' ') !== false) {
                $namafile = str_replace(' ', '%20', $namafile);
            }

            if (strpos($namafile, '#') !== false) {
                $namafile = str_replace('#', '%23', $namafile);
            }

            $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;
            $html .= "<tr><td><img id=\"myQRCode\" src=\"" . $urlpath . "\"></td></tr>";

            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = $html;
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetQROnes()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = self::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['id'])) {

            $html = "";
            $i = 0;

            #parameter inputan
            //isi QRCode saat discan
            $isi_teks = $this->data['id'];
            //namafile setelah jadi qrcode
            $namafile = $isi_teks . ".png";
            //kualitas dan ukuran qrcode
            $quality = 'H';
            $ukuran = 8;
            $padding = 0;


            QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);

            if (strpos($namafile, ' ') !== false) {
                $namafile = str_replace(' ', '%20', $namafile);
            }

            if (strpos($namafile, '#') !== false) {
                $namafile = str_replace('#', '%23', $namafile);
            }

            $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;

            $html .= "
                <tr id='tr" . $i++ . "'>
                    <td style=\"text-align:center; \">
                        <div class='cover_qrcode'>
                            <div style='height: 35.3mm !important;margin: 0 auto;width: 100%;'>
                                <img id='imgQrcode' src=" . $urlpath . " style='height: 100%;width: 40%;'><br><h4 id=\"mySmallModalLabel\" class=\"modal-title\">" . $isi_teks . "</h4>
                            </div>
                        </div>
                    </td>
                </tr>";


            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = $html;
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetQRAlls()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = self::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['id'])) {

            //isset($this->data['drv_default_location'])?$this->data['drv_default_location']:''
            $and = '';
            if (isset($this->data['idSub'])) {
                $and .= "AND descr=" . Driver::q($this->data['idSub']);
            }

            if (isset($this->data['hawb'])) {
                $and .= "AND hawb=" . Driver::q($this->data['hawb']);
            }

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_lots}}
            WHERE
            partnumber=" . Driver::q($this->data['id']) . "
            $and
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 0;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    #parameter inputan
                    //isi QRCode saat discan
                    $isi_teks = $val['lotnumber'];
                    //namafile setelah jadi qrcode
                    $namafile = $isi_teks . ".png";
                    //kualitas dan ukuran qrcode
                    $quality = 'H';
                    $ukuran = 8;
                    $padding = 0;

                    QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);

                    if (strpos($namafile, ' ') !== false) {
                        $namafile = str_replace(' ', '%20', $namafile);
                    }

                    if (strpos($namafile, '#') !== false) {
                        $namafile = str_replace('#', '%23', $namafile);
                    }

                    $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;
                    /*<h5 id=\"mySmallModalLabel\" class=\"modal-title\">HAWB: ".$val['hawb']."</h5>
                    <h5 id=\"mySmallModalLabel\" class=\"modal-title\">Part: ".$val['partnumber']."</h5>*/
                    $html .= "
                    <tr id='tr" . $i++ . "'>
                        <td style=\"text-align:center;\">
                            <div class='cover_qrcode'>
                                <div style='height: 30.3mm !important;margin: 0 auto;width: 100%;'>
                                    <img src=\"" . $urlpath . "\" style='height: 100%;'><br>
                                    <h5 id=\"mySmallModalLabel\" class=\"modal-title\">" . $val['hawb'] . "</h5>
                                    <h5 id=\"mySmallModalLabel\" class=\"modal-title\">" . $val['partnumber'] . "</h5>
                                    <h4 id=\"mySmallModalLabel\" class=\"modal-title\">" . $isi_teks . "</h4>
                                </div>
                            </div>
                        </td>
                    </tr>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiongetQRAll_s()
    {
        include "phpqrcode/qrlib.php"; //<-- LOKASI FILE UTAMA PLUGINNYA

        $path_to_upload = Yii::getPathOfAlias('webroot') . "/upload";
        if (!file_exists($path_to_upload)) {
            if (!@mkdir($path_to_upload, 0777)) {
                $this->msg = self::t("Failed cannot create folder" . " " . $path_to_upload);
                Yii::app()->end();
            }
        }

        if (isset($this->data['id'])) {

            $stmt = "SELECT SQL_CALC_FOUND_ROWS *
            FROM
            {{inbound_lots}}
            WHERE
            hawb=" . Driver::q($this->data['id']) . "
            ";

            $DbExt = new DbExt;
            $DbExt->qry("SET SQL_BIG_SELECTS=1");

            $html = "";
            $i = 0;
            if ($res = $DbExt->rst($stmt)) {
                foreach ($res as $val) {

                    #parameter inputan
                    //isi QRCode saat discan
                    $isi_teks = $val['lotnumber'];
                    //namafile setelah jadi qrcode
                    $namafile = $isi_teks . ".png";
                    //kualitas dan ukuran qrcode
                    $quality = 'H';
                    $ukuran = 8;
                    $padding = 0;

                    QRCode::png($isi_teks, $path_to_upload . '/' . $namafile, $quality, $ukuran, $padding);

                    if (strpos($namafile, ' ') !== false) {
                        $namafile = str_replace(' ', '%20', $namafile);
                    }

                    if (strpos($namafile, '#') !== false) {
                        $namafile = str_replace('#', '%23', $namafile);
                    }

                    $urlpath = Yii::app()->getBaseUrl(true) . '/upload/' . $namafile;
                    /*<h5 id=\"mySmallModalLabel\" class=\"modal-title\">HAWB: ".$val['hawb']."</h5>
                    <h5 id=\"mySmallModalLabel\" class=\"modal-title\">Part: ".$val['partnumber']."</h5>*/
                    $html .= "
                    <tr id='tr" . $i++ . "'>
                        <td style=\"text-align:center;\">
                            <div class='cover_qrcode'>
                                <div style='height: 30.3mm !important;margin: 0 auto;width: 100%;'>
                                    <img src=\"" . $urlpath . "\" style='height: 100%;'><br>
                                    <h5 id=\"mySmallModalLabel\" class=\"modal-title\">" . $val['hawb'] . "</h5>
                                    <h5 id=\"mySmallModalLabel\" class=\"modal-title\">" . $val['partnumber'] . "</h5>
                                    <h4 id=\"mySmallModalLabel\" class=\"modal-title\">" . $isi_teks . "</h4>
                                </div>
                            </div>
                        </td>
                    </tr>";
                }

                $this->code = 1;
                $this->msg = Driver::t("Successful");
                $this->details = $html;
            }
        } else $this->msg = Driver::t("Missing parameters") . $this->data['idHawb'];
        $this->jsonResponse();
    }

    public function actiontryInsert()
    {
        $hawb   = $this->data['hawb'];
        $part   = $this->data['part'];
        $po     = $this->data['po'];
        $qtylot = $this->data['qtylot'];

        # cek ketersedian lot
        $totalLot = Driver::hitungLot($hawb, $part);
        if ($qtylot > $totalLot['qtytotal']) {
            $this->msg = Driver::t("Quantity is Less that entered.!!");
            $this->jsonResponse();
            Yii::app()->end();
        }

        # Ambil lot sesuai request
        $stmt = "SELECT * FROM vw_inbounds WHERE
        status='successful' and flag=0 and loc <> '' and hawb=" . Driver::q($hawb) . "
        AND partnumber=" . Driver::q($part) . " LIMIT 0, " . $qtylot . "";

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        $html = '';
        if ($res = $DbExt->rst($stmt)) {
            foreach ($res as $val) {

                $html .= '<tr class="item-row">
                <td><input type="hidden" value="' . $hawb . '" id="hawb[]" name="hawb[]">' . $hawb . '</td>
                <td><input type="hidden" value="' . $part . '" id="part[]" name="part[]">' . $part . '</td>
                <td><input type="hidden" value="' . $val['lotnumber'] . '" id="lot[]" name="lot[]">' . $val['lotnumber'] . '</td>
                <td><input type="hidden" value="' . $val['loc'] . '" id="loc[]" name="loc[]">' . $val['loc'] . '</td>
                <td><input type="hidden" value="' . $po . '" id="po[]" name="po[]">' . $po .  '</td>
                <td><a class="delinv" href="javascript:;" title="Remove row">X</a></td></tr>';
            }

            $this->code = 1;
            $this->msg = Driver::t("Successful");
            $this->details = $html;
        } else $this->msg = Driver::t("Error bro..");

        $this->jsonResponse();
    }

    public function actiontryInsertSchenker()
    {


        $hawb   = $this->data['hawb'];
        //$part   = $this->data['part'];
        $skus   = $this->data['sku'];
        //$po     = $this->data['po'];
        $qtylot = $this->data['qtylot'];
        $all_qty = $this->data['all_qty'];

        if (empty($skus)) {
            $this->msg = Driver::t("No SKU selected");
            $this->jsonResponse();
            Yii::app()->end();
        }

        if (empty($qtylot) && $all_qty != 'yes') {
            $this->msg = Driver::t("No Qty provided");
            $this->jsonResponse();
            Yii::app()->end();
        }

        $html = '';

        //loop through SKU
        $skus = explode(',', $skus);
        foreach ($skus as $r) {
            # cek ketersedian lot
            $sql = "SELECT s.invs_id, s.sku, COUNT(s.sku) AS qty, s.invs_loc, s.invs_sku_descr, CONCAT(s.invs_id, ' - ', s.invs_sku_descr) AS text FROM
            el_inbound_header h LEFT JOIN el_inbound_details_schenker s ON s.sso_delivery_id=h.delivery_id 
            WHERE h.status='successful' AND s.flag=0 AND s.invs_loc <> '' AND h.hawb='" . $hawb . "' AND s.sku='" . $r . "' GROUP BY s.sku";

            $sku = Yii::app()->db->createCommand($sql)->queryRow();

            if ($all_qty == 'yes') {
                $qty = $sku['qty'];
            } else {
                if ($qtylot > $sku['qty']) {
                    $this->msg = Driver::t("The qty available is not appropriate " . $r);
                    $this->jsonResponse();
                    Yii::app()->end();
                }

                $qty = $qtylot;
            }

            $html .= '<tr class="item-row">
            <td><input type="hidden" value="' . $hawb . '" id="hawb[]" name="hawb[]">' . $hawb . '</td>
            <td><input type="hidden" value="' . $sku['sku'] . '" id="sku[]" name="sku[]">' . $sku['sku'] . '</td>
            <td><input type="hidden" value="' . $qty . '" id="qty[]" name="qty[]">' . $qty . '</td>
            <td><input type="hidden" value="' . $sku['invs_id'] . '" id="part[]" name="part[]">' . $sku['invs_id'] . '</td>
            <td><input type="hidden" value="' . $sku['invs_sku_descr'] . '" id="sku_descr[]" name="sku_descr[]">' . $sku['invs_sku_descr'] . '</td>
            <td><a class="delinvsch" href="javascript:;" title="Remove row">X</a></td></tr>';
        }

        $this->code = 1;
        $this->msg = Driver::t("Successful");
        $this->details = $html;

        $this->jsonResponse();
    }

    public function actionProduct_Category_List()
    {
        $aColumns = array(
            'id',
            'name',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS *
		FROM
		{{product_category}}
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['id'];

                $admin_id = Self::userType();

                $action = '';
                $action = "<a class=\"btn btn-success product_category_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";

                if ($admin_id == 1) {
                    $action .= "<a class=\"btn btn-danger product_category_delete\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                } else {
                    $action = "<a class=\"btn btn-success product_category_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['name'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionDemo_Movement_List()
    {
        $aColumns = array(
            'd.id',
            'd.ref',
            'd.requested_by',
            'd.from_loc',
            'd.to_loc',
            'd.status',
            'created_at',
            'created_by',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS d.id, d.ref, d.requested_by, d.from_loc, d.to_loc, 
        (SELECT id FROM el_demo_movement WHERE returned_from=d.id) AS sdh_return,
        d.status, d.is_return, d.created_at, CONCAT(u.first_name, ' ', u.last_name) AS created_by
		FROM
		el_demo_movement d
        LEFT JOIN el_user u ON u.user_id=d.created_by 
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['id'];

                $admin_id = Self::userType();

                $action = '';

                if ($admin_id == 1) {
                    if ($val['status'] == 'Requested') {
                        if ($val['is_return'] == 0) {
                            $action .= "<a class=\"btn btn-success demo_movement_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                        } else {
                            $action .= "<a class=\"btn btn-success demo_movement_edit_return\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                        }
                    }
                    $action .= "<a class=\"btn btn-primary demo_movement_detail\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Detail") . "</a> ";

                    if ($val['from_loc'] == 'Current Location' && $val['is_return'] == 0 && $val['status'] == 'Successful') {
                        //belum return, tampil tombol
                        if (empty($val['sdh_return'])) {
                            $action .= "<a class=\"btn btn-warning demo_movement_return\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Return") . "</a> ";
                        }
                    }
                    if ($val['status'] == 'Requested') {
                        $action .= "<a class=\"btn btn-danger demo_movement_delete\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                    }
                } else {
                    $action .= "<a class=\"btn btn-danger demo_movement_detail\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                }

                if ($val['status'] == 'Requested') {
                    $status = '<span class="label label-default ">' . $val['status'] . '</span>';
                } else if ($val['status'] == 'Successful') {
                    $status = '<span class="label label-primary ">' . $val['status'] . '</span>';
                } else {
                    $status = '<span class="label label-default ">' . $val['status'] . '</span>';
                }

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['ref'],
                    $val['requested_by'],
                    $val['from_loc'],
                    $val['to_loc'],
                    $status,
                    $val['created_at'],
                    $val['created_by'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionDemo_Movement_List_Detail()
    {
        $aColumns = array(
            'hawb',
            'from_loc',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        //cek status demo nya, masih requested -> ambil dari inbound_details
        //sdh successful -> ambil dari 

        $header = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement')
            ->where('id=:id', array(':id' => $_GET['id']))
            ->queryRow();

        if ($header['is_return'] == 0) {
            if ($header['status'] == 'Requested') {
                $stmt = "SELECT SQL_CALC_FOUND_ROWS d.id, d.demo_movement_id, d.hawb, d.flag, d.scan_time, 
                        (SELECT loc FROM el_inbound_details WHERE descr=d.hawb) AS loc
                        FROM
                        el_demo_movement_detail d";
            } else {
                $stmt = "SELECT SQL_CALC_FOUND_ROWS d.id, d.demo_movement_id, d.hawb, d.flag, d.scan_time, 
                        d.loc
                        FROM
                        el_demo_movement_detail d";
            }
        } else {
            if ($header['status'] == 'Requested') {
                $stmt = "SELECT SQL_CALC_FOUND_ROWS d.id, d.demo_movement_id, d.hawb, d.flag, d.scan_time, 
                    (SELECT loc FROM el_demo_movement_detail WHERE demo_movement_id=" . $header['returned_from'] . " AND hawb=d.hawb) AS loc
                    FROM
                    el_demo_movement_detail d";
            } else {
                $stmt = "SELECT SQL_CALC_FOUND_ROWS d.id, d.demo_movement_id, d.hawb, d.flag, d.scan_time, 
                        d.loc
                        FROM
                        el_demo_movement_detail d";
            }
        }


        $stmt .= " WHERE d.demo_movement_id = " . (int)$_GET['id'] . "
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['id'];

                $admin_id = Self::userType();

                $action = '';
                //$action = "<a class=\"btn btn-success product_category_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";

                if ($admin_id == 1) {
                    $action .= "<a class=\"btn btn-danger product_category_delete\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                } else {
                    // $action = "<a class=\"btn btn-success product_category_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['hawb'],
                    $val['loc'],
                    //$action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionRecipient_List()
    {
        $aColumns = array(
            'id',
            'name',
            'email',
            'created_at',
            'created_by',
        );
        $t = AjaxDataTables::AjaxData($aColumns);
        if (isset($_GET['debug'])) {
            dump($t);
        }

        if (is_array($t) && count($t) >= 1) {
            $sWhere = $t['sWhere'];
            $sOrder = $t['sOrder'];
            $sLimit = $t['sLimit'];
        }

        $and = '';

        $stmt = "SELECT SQL_CALC_FOUND_ROWS d.id, d.name, d.email, d.created_at, CONCAT(u.first_name, ' ', u.last_name) AS created_by
		FROM
		{{recipient}} d
        LEFT JOIN {{user}} u ON u.user_id=d.created_by 
		WHERE 1
		$and
		$sWhere
		$sOrder
		$sLimit
		";
        if (isset($_GET['debug'])) {
            dump($stmt);
        }

        $DbExt = new DbExt;
        $DbExt->qry("SET SQL_BIG_SELECTS=1");

        if ($res = $DbExt->rst($stmt)) {

            $iTotalRecords = 0;
            $stmtc = "SELECT FOUND_ROWS() as total_records";
            if ($resc = $DbExt->rst($stmtc)) {
                $iTotalRecords = $resc[0]['total_records'];
            }

            $feed_data['sEcho'] = intval($_GET['sEcho']);
            $feed_data['iTotalRecords'] = $iTotalRecords;
            $feed_data['iTotalDisplayRecords'] = $iTotalRecords;

            foreach ($res as $val) {

                $id = $val['id'];

                $admin_id = Self::userType();

                $action = '';
                $action = "<a class=\"btn btn-success recipient_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";

                if ($admin_id == 1) {
                    $action .= "<a class=\"btn btn-danger recipient_delete\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
                } else {
                    $action = "<a class=\"btn btn-success recipient_edit\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
                }

                $feed_data['aaData'][] = array(
                    $val['id'],
                    $val['name'],
                    $val['email'],
                    $val['created_at'],
                    $val['created_by'],
                    $action
                );
            }
            if (isset($_GET['debug'])) {
                dump($feed_data);
            }
            $this->otableOutput($feed_data);
        }
        $this->otableNodata();
    }

    public function actionValidateDeliveryId()
    {
        $delivery_id = $_POST['delivery_id'];
        $hawb_sh = $_POST['hawb_sh'];

        //posisi insert
        if (empty($_POST['hawb_sh'])) {
            $cek =  Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header')
                ->where('delivery_id=:sso', array(':sso' => $delivery_id))
                ->queryRow();
        } else { //posisi update
            $cek =  Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_inbound_header')
                ->where('delivery_id=:sso', array(':sso' => $delivery_id))
                ->andWhere('hawb<>:id', array(':id' => $hawb_sh))
                ->queryRow();
        }

        if (empty($cek)) {
            echo json_encode([
                'status' => true,
                'message' => 'SSO Delivery ID is ready to go'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'SSO Delivery ID already registered in system, please provide another'
            ]);
        }
    }
}/* end class*/
