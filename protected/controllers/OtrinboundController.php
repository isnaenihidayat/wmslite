<?php
if (!isset($_SESSION)) {
    session_start();
}

class OtrinboundController extends CController
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

        $this->pageTitle = 'WMSLite - Inbound (New)';
        $this->body_class = "top-navigation";
        $this->render('/otr-inbound/index', array());
    }

    public function actionGetData()
    {
        $model = new OtrInbound();
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

            $locator = '<span style="width: 150px; word-wrap: break-word; display: inline-block;">' . $r['locator'] . '</span>';

            $id = $r['id'];
            $admin_id = Driver::getUserType();

            $action = '';

            //if ($r['status']!=="successful") {
            if ($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3') {
                $action = "<a class=\"btn btn-sm btn-success new-inbound\" data-id=\"" . $id . "\" href=\"javascript:;\">" . Driver::t("Edit") . "</a> ";
            }


            $action .= "<a class=\"btn btn-sm btn-primary details-inbound\" data-id=\"" . $id . "\" data-hawb=\"" . $r['hawb'] .  "\" data-delivery=\"" . $r['delivery_id'] . "\" href=\"javascript:;\">" . Driver::t("Details") . "</a> ";

            //$cek = Driver::CekCountIn($r['hawb']);
            // disable delete sementara
            if (($admin_id == 1 || $_SESSION['wmslite']['type'] == '1' || $_SESSION['wmslite']['type'] == '3')) {
                $action .= "<a class=\"btn btn-sm btn-danger del-inbound\" data-hawb=\"" . $r['hawb'] . "\" data-delivery=\"" . $r['delivery_id'] . "\" href=\"javascript:;\">" . Driver::t("Delete") . "</a> ";
            }
            //$action.="<a class=\"btn btn-default new-inbound\" data-id=\"".$id."\" href=\"javascript:;\">".Driver::t("Edit")."</a>";



            $action .= '<a class="btn btn-sm btn-primary" data-toggle="modal" data-target="#detailModal" data-id="' . $r['id'] . '" data-receiptkey="' . $r['receiptKey'] . '" href="#">Detail</a>';


            // preparing an array
            $row   = array();
            $row[] = $r['id'];
            $row[] = $r['hawb'];
            $row[] = $r['descr'];
            $row[] = $r['product_category_name'];
            $row[] = $r['modality'];
            $row[] = $r['delivery_id'];
            $row[] = $r['po'];
            $row[] = $locator;
            $row[] = $r['etd'];
            $row[] = $r['eta'];
            $row[] = $r['ata'];
            $row[] = $r['sppb_date'];
            $row[] = $date_created;
            $row[] = $date_updated;
            $row[] = $status;
            $row[] = $r['totalQtyReceived'];
            $row[] = $r['itemInDetail'];
            $row[] = $r['totalPick'];
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

    public function actionGetPick()
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

    public function actionGetAvailableLot()
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
}
