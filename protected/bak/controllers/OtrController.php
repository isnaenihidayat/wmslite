<?php
if (!isset($_SESSION)) {
    session_start();
}

class OtrController extends CController
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

    public function actionLogin()
    {
        ScriptManager::scripts();

        if (Functions::islogin()) {
            $this->redirect(Yii::app()->createUrl('/dashboard'));
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
        // die(var_dump($_SERVER['DOCUMENT_ROOT'], Yii::app()->getBasePath()));
        // Driver::sendEmailTest();
        // die();

        // $sh =  Yii::app()->db->createCommand()
        //     ->select('*')
        //     ->from('el_inbound_header')
        //     ->where('hawb=:hawb', array(':hawb' => 'PPN-TEST1'))
        //     ->queryRow();

        // $sh_detail =  Yii::app()->db->createCommand()
        //     ->select('d.descr AS hawb, h.descr, h.delivery_id, h.po, h.ata, h.sppb_date, h.locator, d.loc')
        //     ->from('el_inbound_details d')
        //     ->where('d.hawb=:hawb', array(':hawb' => 'PPN-TEST1'))
        //     ->leftJoin('el_inbound_header h', 'h.hawb=d.hawb')
        //     ->queryAll();

        // $basePath = Yii::app()->getBasePath();
        // //remove protected
        // $baseDir = str_replace('protected', '', $basePath);
        // $attachment1 = $baseDir . "/upload/" . $sh['docfile'];

        // $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'en');
        // $html2pdf->setDefaultFont('helvetica');
        // $html2pdf->writeHTML($this->renderPartial('demo_movement_detail_pdf', ['detail' => $sh_detail], true));
        // $output = $baseDir . "/inbdetail/" . $sh['hawb'] . '.pdf';
        // $html2pdf->output($output, 'FI');
        // $attachment2 = $output;
        // die();

        ScriptManager::scripts();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('dashboard', array());
    }

    public function actionProduct_category()
    {

        ScriptManager::scripts();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('product_category', array());
    }

    public function actionProduct_category_insert()
    {
        $params = [
            'name' => $_POST['name'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['wmslite']['user_id']
        ];

        $db = new DbExt;
        if ($db->insertData("{{product_category}}", $params)) {
            Yii::app()->user->setFlash('success', "Product category has been successfully added");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when adding product category");
        }

        $this->redirect(Yii::app()->createUrl('/otr/product_category'));
    }

    public function actionProduct_category_update()
    {
        $params = [
            'name' => $_POST['name_edit'],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['wmslite']['user_id']
        ];

        $db = new DbExt;
        if ($db->updateData("{{product_category}}", $params, 'id', $_POST['id_edit'])) {
            Yii::app()->user->setFlash('success', "Product category has been successfully updated");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when updating product category");
        }

        $this->redirect(Yii::app()->createUrl('/otr/product_category'));
    }

    public function actionProduct_category_delete()
    {

        $db = new DbExt;
        $stmt = "
        DELETE FROM
        {{product_category}}
        WHERE
        id=" . Driver::q($_POST['id_delete']) . "
        ";

        $DbExt = new DbExt;
        $delete = $DbExt->qry($stmt);

        if ($delete) {
            Yii::app()->user->setFlash('success', "Product category has been successfully deleted");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when deleting product category");
        }

        $this->redirect(Yii::app()->createUrl('/otr/product_category'));
    }

    public function actionProduct_category_get()
    {
        $product = Yii::app()->db->createCommand()
            ->select('id, name')
            ->from('el_product_category')
            ->where('id=:id', array(':id' => $_POST['id']))
            ->queryRow();

        echo json_encode($product);
    }

    public function actionDemo_movement()
    {

        ScriptManager::scripts();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('demo_movement', array());
    }

    public function actionDemo_movement_insert()
    {
        $params = [
            'ref' => $_POST['ref'],
            'requested_by' => $_POST['requested_by'],
            'from_loc' => 'Current Location',
            'to_loc' => $_POST['to_loc'],
            'status' => 'Requested',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['wmslite']['user_id']
        ];

        if (empty($_POST['hawb'])) {
            Yii::app()->user->setFlash('error', "Please provide HAWB");
            $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
        }

        $insert = Yii::app()->db->createCommand()->insert('el_demo_movement', $params);
        $id_demo_movement = Yii::app()->db->getLastInsertID();

        if ($insert) {

            $i = 0;
            foreach ($_POST['hawb'] as $r) {
                Yii::app()->db->createCommand()->insert('el_demo_movement_detail', [
                    'demo_movement_id' => $id_demo_movement,
                    'hawb' => $r,
                    'loc' => $_POST['to_loc'], // barang keluar, di detail value ini sama seperti loc di header
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['wmslite']['user_id']
                ]);

                //update loc di inbound detail, dilakukan saat demo mov successful

                $i++;
            }

            Yii::app()->user->setFlash('success', "Demo movement has been successfully added");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when adding demo movement");
        }

        $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
    }

    public function actionDemo_movement_update()
    {
        $params = [
            'ref' => $_POST['ref_edit'],
            'requested_by' => $_POST['requested_by_edit'],
            'to_loc' => $_POST['to_loc_edit'],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['wmslite']['user_id']
        ];

        $db = new DbExt;
        if ($db->updateData("{{demo_movement}}", $params, 'id', $_POST['id_edit'])) {

            Yii::app()->db->createCommand()->delete('el_demo_movement_detail', 'demo_movement_id=:id', array(':id' => $_POST['id_edit']));

            $i = 0;
            foreach ($_POST['hawb_edit'] as $r) {
                Yii::app()->db->createCommand()->insert('el_demo_movement_detail', [
                    'demo_movement_id' => $_POST['id_edit'],
                    'hawb' => $r,
                    'loc' => $_POST['to_loc_edit'][$i],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['wmslite']['user_id']
                ]);
                $i++;
            }

            Yii::app()->user->setFlash('success', "Demo movement has been successfully updated");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when updating demo movement");
        }

        $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
    }

    public function actionDemo_movement_return()
    {
        $params = [
            'ref' => $_POST['ref_return'],
            'requested_by' => $_POST['requested_by_return'],
            'from_loc' => $_POST['from_loc_return'],
            'to_loc' => 'Warehouse',
            'status' => 'Requested',
            'is_return' => 1,
            'returned_from' => $_POST['id_return'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['wmslite']['user_id']
        ];

        if (empty($_POST['hawb_return'])) {
            Yii::app()->user->setFlash('error', "No HAWB found");
            $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
        }

        //check apakah sdh pernah di return
        $returned = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement')
            ->where('returned_from=:r', array(':r' => $_POST['id_return']))
            ->queryAll();
        if (!empty($returned)) {
            Yii::app()->user->setFlash('error', "This refs already returned");
            $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
        }


        //check ada hawb blm dipilih
        $blm_isi = 0;
        foreach ($_POST['to_loc_return'] as $r) {
            if ($r == '- Choose Return Location -') {
                $blm_isi++;
            }
        }
        if ($blm_isi > 0) {
            Yii::app()->user->setFlash('error', "All return location must be chosen");
            $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
        }

        $insert = Yii::app()->db->createCommand()->insert('el_demo_movement', $params);
        $id_demo_movement = Yii::app()->db->getLastInsertID();

        if ($insert) {

            $i = 0;
            foreach ($_POST['hawb_return'] as $r) {
                Yii::app()->db->createCommand()->insert('el_demo_movement_detail', [
                    'demo_movement_id' => $id_demo_movement,
                    'hawb' => $r,
                    'loc' => $_POST['to_loc_return'][$i],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['wmslite']['user_id']
                ]);
                $i++;
            }

            Yii::app()->user->setFlash('success', "Demo movement (return) has been successfully added");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when adding demo movement");
        }

        $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
    }

    public function actionDemo_movement_update_return()
    {
        $params = [
            'ref' => $_POST['ref_return_edit'],
            'requested_by' => $_POST['requested_by_return_edit'],
            'from_loc' => $_POST['from_loc_return_edit'],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['wmslite']['user_id']
        ];

        $db = new DbExt;
        if ($db->updateData("{{demo_movement}}", $params, 'id', $_POST['id_edit_return'])) {

            Yii::app()->db->createCommand()->delete('el_demo_movement_detail', 'demo_movement_id=:id', array(':id' => $_POST['id_edit_return']));

            $i = 0;
            foreach ($_POST['hawb_edit_return'] as $r) {
                Yii::app()->db->createCommand()->insert('el_demo_movement_detail', [
                    'demo_movement_id' => $_POST['id_edit_return'],
                    'hawb' => $r,
                    'loc' => $_POST['to_loc_edit_return'][$i],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['wmslite']['user_id']
                ]);
                $i++;
            }

            Yii::app()->user->setFlash('success', "Return Demo movement has been successfully updated");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when updating return demo movement");
        }

        $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
    }

    public function actionDemo_movement_delete()
    {

        $db = new DbExt;
        $stmt = "
        DELETE FROM
        {{demo_movement}}
        WHERE
        id=" . Driver::q($_POST['id_delete']) . "
        ";

        $DbExt = new DbExt;
        $delete = $DbExt->qry($stmt);

        Yii::app()->db->createCommand()->delete('el_demo_movement_detail', 'demo_movement_id=:id', [
            ':id' => $_POST['id_delete']
        ]);

        if ($delete) {
            Yii::app()->user->setFlash('success', "Demo movement has been successfully deleted");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when deleting demo movement");
        }

        $this->redirect(Yii::app()->createUrl('/otr/demo_movement'));
    }

    public function actionDemo_movement_get()
    {
        $demo = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement')
            ->where('id=:id', array(':id' => $_POST['id']))
            ->queryRow();

        echo json_encode($demo);
    }

    public function actionDemo_movement_get_with_detail()
    {
        $demo = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement')
            ->where('id=:id', array(':id' => $_POST['id']))
            ->queryRow();

        //requested loc nya ambil dari inbound
        if ($demo['status'] == 'Requested') {
            $demo_detail = Yii::app()->db->createCommand()
                ->select('d.id, d.demo_movement_id, d.hawb, d.flag, d.scan_time, (SELECT loc FROM el_inbound_details WHERE descr=d.hawb) AS loc')
                ->from('el_demo_movement_detail d')
                ->where('d.demo_movement_id=:id', array(':id' => $demo['id']))
                ->queryAll();
        } else {
            //successful loc nya ambil dari demo m detail
            $demo_detail = Yii::app()->db->createCommand()
                ->select('d.*')
                ->from('el_demo_movement_detail d')
                ->where('d.demo_movement_id=:id', array(':id' => $demo['id']))
                ->queryAll();
        }

        echo json_encode([
            'demo' => $demo,
            'demo_detail' => $demo_detail,
        ]);
    }

    public function actionRecipient()
    {

        ScriptManager::scripts();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('recipient', array());
    }

    public function actionRecipient_insert()
    {
        $params = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $_SESSION['wmslite']['user_id']
        ];

        $db = new DbExt;
        if ($db->insertData("{{recipient}}", $params)) {
            Yii::app()->user->setFlash('success', "Recipient has been successfully added");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when adding recipient");
        }

        $this->redirect(Yii::app()->createUrl('/otr/recipient'));
    }

    public function actionRecipient_update()
    {
        $params = [
            'name' => $_POST['name_edit'],
            'email' => $_POST['email_edit'],
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['wmslite']['user_id']
        ];

        $db = new DbExt;
        if ($db->updateData("{{recipient}}", $params, 'id', $_POST['id_edit'])) {
            Yii::app()->user->setFlash('success', "Recipient has been successfully updated");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when updating recipient");
        }

        $this->redirect(Yii::app()->createUrl('/otr/recipient'));
    }

    public function actionRecipient_delete()
    {

        $db = new DbExt;
        $stmt = "
        DELETE FROM
        {{recipient}}
        WHERE
        id=" . Driver::q($_POST['id_delete']) . "
        ";

        $DbExt = new DbExt;
        $delete = $DbExt->qry($stmt);

        if ($delete) {
            Yii::app()->user->setFlash('success', "Recipient has been successfully deleted");
        } else {
            Yii::app()->user->setFlash('error', "Error occured when deleting recipient");
        }

        $this->redirect(Yii::app()->createUrl('/otr/recipient'));
    }

    public function actionRecipient_get()
    {
        $product = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_recipient')
            ->where('id=:id', array(':id' => $_POST['id']))
            ->queryRow();

        echo json_encode($product);
    }

    public function actionMonitoring()
    {

        ScriptManager::scripts();

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('monitoring', array());
    }

    public function actionDashboard()
    {
        ScriptManager::scripts();

        $search_result = null;
        $shipment_search = null;
        $outbound_search = null;

        $keyword = isset($_GET['search']) ? $_GET['search'] : null;

        if (!empty($keyword)) {
            $search_result = 1;
            // $shipment_search = Yii::app()->db->createCommand()
            //     ->select('ih.id AS id_ih, od.hawb, od.descr, ih.descr AS descr_detail, od.loc, oh.po AS po_out, ih.po AS po_in, ship_method, ata, sppb_date,
            //     locator, od.loc, oh.destination, ih.status AS status_in, od.date_updated,
            //     (SELECT first_name FROM el_user WHERE user_id=od.updated_by) AS updated_by_nama_out')
            //     ->from('{{outbound_details}} od')
            //     ->leftJoin('{{inbound_header}} ih', 'ih.hawb=od.hawb')
            //     ->leftJoin('{{outbound_header}} oh', 'oh.id=od.id')
            //     //->where('hawb=:id', array(':id' => $keyword))
            //     ->where(array('like', 'od.hawb', '%'.$keyword.'%'))
            //     ->andWhere(array('flag', 1))
            //     // WHERE `name` LIKE '%Qiang' AND `name` LIKE '%Xue'
            //     //where(array('like', 'name', array('%Qiang', '%Xue')))
            //     ->queryAll();

            $sql = "SELECT ih.id AS id_ih, ih.hawb, id.descr, ih.descr AS descr_detail, 
            id.loc AS loc_inbound, oh.po AS po_out, ih.po AS po_in, ship_method, ata, sppb_date,
            locator, od.loc, 
            
            (
                CASE
                WHEN demo_movement_id IS NULL THEN id.loc
                ELSE
                    (SELECT loc FROM el_demo_movement_detail dmd WHERE dmd.hawb=id.descr)
                END
            ) AS loc_inb_dm,
            
            oh.destination, oh.delivery_id, oh.transporter, ih.status AS status_in, oh.date_updated,
            (SELECT first_name FROM el_user WHERE user_id=oh.updated_by) AS updated_by_nama_out
            FROM el_inbound_header ih
            LEFT JOIN el_inbound_details id ON ih.hawb=id.hawb
            LEFT JOIN el_outbound_details od ON od.descr=id.descr AND od.hawb=id.hawb
            LEFT JOIN el_outbound_header oh ON oh.id=od.id
            WHERE ih.delivery_id=id.sso_delivery_id AND ih.hawb LIKE '%" . $keyword . "%'
            OR id.descr LIKE '%" . $keyword . "%'
            OR ih.locator LIKE '%" . $keyword . "%'
            OR ih.po LIKE '%" . $keyword . "%'";

            $shipment_search = Yii::app()->db->createCommand($sql)->queryAll();
        }

        $this->body_class = "top-navigation fixed-nav dashboard";
        $this->render('dashboard', array(
            'search_result' => $search_result,
            'shipment_search' => $shipment_search,
            'outbound_search' => $outbound_search,
        ));
    }

    public function actionpickingList()
    {
        ScriptManager::scriptsOption();

        $this->body_class = "white-bg";
        $this->render('picking-list', array());
    }

    public function actionpickingListSchenker()
    {
        ScriptManager::scriptsOption();

        $this->body_class = "white-bg";
        $this->render('picking-list-schenker', array());
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

    public function actionDummyData()
    {
        $action = $_GET['action'];
        $hawb = $_GET['hawb'];

        // $only = [
        //     '4850113495',
        //     'ELP-06025206',
        // ];

        // if (!in_array($hawb, $only)) {
        //     die('dummy not available');
        // }

        if ($action == 'add') {
        } else if ($action == 'ubah_ke_warehouse_in_transit') {

            $command = Yii::app()->db->createCommand();
            $command->update('el_inbound_header', array(
                'status' => 'Warehouse in Transit',
                'date_updated' => date('Y-m-d H:i:s'),
            ), 'hawb=:id', array(':id' => $hawb));

            echo 'Done';
        } else if ($action == 'ubah_ke_successful') {

            $command = Yii::app()->db->createCommand();
            $command->update('el_inbound_header', array(
                'status' => 'successful',
                'date_updated' => date('Y-m-d H:i:s'),
            ), 'hawb=:id', array(':id' => $hawb));

            echo 'Done';
        } else if ($action == 'delete_outbound') {

            $el_outbound_details =  Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_outbound_details')
                ->where('hawb=:id', array(':id' => $hawb))
                ->queryRow();

            if (!empty($el_outbound_details)) {
                $command = Yii::app()->db->createCommand();
                $command->delete('el_outbound_header', 'id=:id', array(':id' => $el_outbound_details['id']));

                $command = Yii::app()->db->createCommand();
                $command->delete('el_outbound_details', 'id=:id', array(':id' => $el_outbound_details['id']));

                // $command = Yii::app()->db->createCommand();
                // $command->delete('el_inbound_details', 'hawb=:id', array(':id' => $hawb));

                $command = Yii::app()->db->createCommand();
                $command->update('el_inbound_details', array(
                    'flag' => 0,
                    'date_updated' => date('Y-m-d H:i:s'),
                ), 'hawb=:id', array(':id' => $hawb));

                echo 'Done';
            }
        }
    }

    //untuk ngisi id inbound details
    public function actionMatchingInboundOutbound()
    {
        //ambil semua outbound yang id_outbound = null
        $sql = "SELECT * FROM el_outbound_details WHERE id_inbound_details IS NULL LIMIT 10000";
        $outbound_details = Yii::app()->db->createCommand($sql)->queryAll();

        $counter = 0;
        foreach ($outbound_details as $r) {
            $sql = "SELECT COUNT(*) AS total FROM el_inbound_details WHERE hawb='" . $r['hawb'] . "' AND descr='" . $r['descr'] . "'";
            $jml = Yii::app()->db->createCommand($sql)->queryRow();

            if ($jml['total'] == 1) {

                $counter++;

                //update id_inbound_details di outbound details
                $sql = "SELECT * FROM el_inbound_details WHERE hawb='" . $r['hawb'] . "' AND descr='" . $r['descr'] . "'";
                $inbound = Yii::app()->db->createCommand($sql)->queryRow();

                if ($inbound['qty'] == 1) {
                    $command = Yii::app()->db->createCommand();
                    $command->update('el_outbound_details', array(
                        'id_inbound_details' => $inbound['id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ), 'hawb=:hawb AND descr=:descr', array(':hawb' => $r['hawb'], ':descr' => $r['descr']));
                }
            }
        }

        echo 'diubah ' . $counter;
    }

    //untuk update qty out
    public function actionMatchingInboundOutboundQty()
    {
        $sql = "SELECT * FROM el_inbound_header WHERE warehouse='marunda'";
        $iheader = Yii::app()->db->createCommand($sql)->queryAll();

        foreach ($iheader as $ih) {
            $sql = "SELECT * FROM el_inbound_details WHERE hawb='" . $ih['hawb'] . "' AND sso_delivery_id='" . $ih['delivery_id'] . "'";
            $idetail = Yii::app()->db->createCommand($sql)->queryAll();

            foreach ($idetail as $id) {

                if (!empty($id['lottable03'])) {
                    $sql = "SELECT SUM(qty) AS total_qty FROM el_outbound_details WHERE hawb='" . $id['hawb'] . "' AND descr='" . $id['descr'] . "' AND lottable03='" . $id['lottable03'] . "'";
                    $qtyOutbound = Yii::app()->db->createCommand($sql)->queryRow();
                } else {
                    $sql = "SELECT SUM(qty) AS total_qty FROM el_outbound_details WHERE hawb='" . $id['hawb'] . "' AND descr='" . $id['descr'] . "'";
                    $qtyOutbound = Yii::app()->db->createCommand($sql)->queryRow();
                }

                //update qtyOutInboundDetails
                $command = Yii::app()->db->createCommand();
                $command->update('el_inbound_details', array(
                    'qty_out' => $qtyOutbound['total_qty'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ), 'id=:id', array(':id' => $id['id']));
                //), 'hawb=:hawb AND descr=:descr', array(':hawb' => $id['hawb'], ':descr' => $id['descr']));

            }
        }
    }

    public function actionCekOutboundGaAdaInbound()
    {
        $sql = "SELECT * FROM el_outbound_details";
        $od = Yii::app()->db->createCommand($sql)->queryAll();

        foreach ($od as $r) {
            $sql = "SELECT * FROM el_inbound_details WHERE hawb='" . $r['hawb'] . "' AND descr='" . $r['descr'] . "'";
            $ib = Yii::app()->db->createCommand($sql)->queryAll();

            if(empty($ib)){
                echo $r['hawb'] .' '. $r['descr'] . '<br>';
            }
        }
    }
}
