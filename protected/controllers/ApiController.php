<?php

error_reporting(0);

class ApiController extends CController
{
    public $data;
    public $code = 2;
    public $msg = '';
    public $details = '';

    public function __construct()
    {
        $this->data = $_GET;

        $website_timezone = Yii::app()->functions->getOptionAdmin("website_timezone");
        if (!empty($website_timezone)) {
            Yii::app()->timeZone = $website_timezone;
        }

        if (isset($_GET['lang_id'])) {
            Yii::app()->language = $_GET['lang_id'];
        }
    }

    public function beforeAction($action)
    {
        /*check if there is api has key*/
        $action = Yii::app()->controller->action->id;
        $continue = true;
        if ($action == "getLanguageSettings" || $action == "GetAppSettings") {
            $continue = false;
        }
        if ($continue) {
            $key = getOptionA('mobile_api_key');
            if (!empty($key)) {
                if (!isset($this->data['api_key'])) {
                    $this->data['api_key'] = '';
                }
                if (trim($key) != trim($this->data['api_key'])) {
                    $this->msg = $this->t("api hash key is not valid");
                    $this->output();
                    Yii::app()->end();
                }
            }
        }
        return true;
    }

    public function actionIndex()
    {
        echo 'Api is working';
    }

    private function q($data = '')
    {
        return Yii::app()->db->quoteValue($data);
    }

    private function t($message = '')
    {
        return Yii::t("default", $message);
    }

    private function output()
    {
        $resp = array(
            'code' => $this->code,
            'msg' => $this->msg,
            'details' => $this->details,
            'request' => json_encode($this->data)
        );
        if (isset($this->data['debug'])) {
            dump($resp);
        }

        if (!isset($_GET['callback'])) {
            $_GET['callback'] = '';
        }

        if (isset($_GET['json']) && $_GET['json'] == TRUE) {
            echo CJSON::encode($resp);
        } else echo $_GET['callback'] . '(' . CJSON::encode($resp) . ')';
        Yii::app()->end();
    }

    public function actionGetAppSettings()
    {
        $translation = Driver::getMobileTranslation();
        $this->code = 1;
        $this->msg = "OK";
        $this->details = array(
            'translation' => $translation,
            'notification_sound_url' => Driver::moduleUrl() . "/sound/food_song.mp3"
        );
        $this->output();
    }

    public function actionLogin()
    {
        if (!empty($this->data['username']) && !empty($this->data['password'])) {
            if ($res = Driver::driverAppLogin($this->data['username'], $this->data['password'])) {
                $token = md5(Driver::generateRandomNumber(5) . $this->data['username']);
                $params = array(
                    'last_login' => date('Y-m-d H:i:s'),
                    'token' => $token,
                    //'last_online'=>strtotime("now"),
                    //device_id'=>isset($this->data['device_id'])?$this->data['device_id']:'',
                    //device_platform'=>isset($this->data['device_platform'])?$this->data['device_platform']:'Android'
                );
                if (!empty($res['token'])) {
                    unset($params['token']);
                    $token = $res['token'];
                }
                $db = new DbExt;
                if ($db->updateData("{{apk}}", $params, 'id', $res['id'])) {
                    $this->code = 1;
                    $this->msg = self::t("Login Successful");

                    $this->details = array(
                        'username' => $this->data['username'],
                        'password' => $this->data['password'],
                        'remember' => isset($this->data['remember']) ? $this->data['remember'] : '',
                        'token' => $token
                    );
                } else $this->msg = self::t("Login failed. please try again later");
            } else $this->msg = self::t("Login failed. either username or password is incorrect");
        } else $this->msg = self::t("Please fill in your username and password");
        $this->output();
    }

    public function actionForgotPassword()
    {
        if (empty($this->data['email'])) {
            $this->msg = self::t("Email address is required");
            $this->output();
            Yii::app()->end();
        }
        $db = new DbExt;
        if ($res = Driver::driverForgotPassword($this->data['email'])) {
            $driver_id = $res['driver_id'];
            $code = Driver::generateRandomNumber(5);
            $params = array('forgot_pass_code' => $code);
            if ($db->updateData('{{driver}}', $params, 'driver_id', $driver_id)) {
                $this->code = 1;
                $this->msg = self::t("We have send the a password change code to your email");

                $tpl = EmailTemplate::forgotPasswordRequest();
                $tpl = smarty('first_name', $res['first_name'], $tpl);
                $tpl = smarty('code', $code, $tpl);
                $subject = 'Forgot Password';
                if (sendEmail($res['email'], '', $subject, $tpl)) {
                    $this->details = "send email ok";
                } else $this->msg = "send email failed";
            } else $this->msg = self::t("Something went wrong please try again later");
        } else $this->msg = self::t("Email address not found");
        $this->output();
    }

    public function actionChangePassword()
    {
        $Validator = new Validator;
        $req = array(
            'email_address' => self::t("Email address is required"),
            'code' => self::t("Code is required"),
            'newpass' => self::t("New Password is required")
        );
        $Validator->required($req, $this->data);
        if ($Validator->validate()) {
            if ($res = Driver::driverForgotPassword($this->data['email_address'])) {
                if ($res['forgot_pass_code'] == $this->data['code']) {
                    $params = array(
                        'password' => md5($this->data['newpass']),
                        'date_modified' => date('c'),
                        'forgot_pass_code' => Driver::generateRandomNumber(5)
                    );
                    $db = new DbExt;
                    if ($db->updateData("{{driver}}", $params, 'driver_id', $res['driver_id'])) {
                        $this->code = 1;
                        $this->msg = self::t("Password successfully changed");
                    } else $this->msg = self::t("Something went wrong please try again later");
                } else $this->msg = self::t("Invalid password code");
            } else $this->msg = self::t("Email address not found");
        } else $this->msg = Driver::parseValidatorError($Validator->getError());
        $this->output();
    }

    public function actionLogout()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if (!$token = Driver::getUserByToken($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }
        $id = $token['id'];
        $params = array(
            'last_online' => time() - 300
        );

        $db = new DbExt;
        $db->updateData('{{apk}}', $params, 'id', $id);
        $this->code = 1;
        $this->msg = "OK";
        $this->output();
    }

    public function actiongetReceipt()
    {
        //cek status header
        $res = Driver::cekIn($this->data['receiptkey']);

        if ($res[0]['status'] == 'successful') {
            $this->msg = self::t("Close");
            $this->output();
            Yii::app()->end();
        }

        if ($res = Driver::GetInDet($this->data['receiptkey'])) {
            $this->code = 1;
            $this->msg = "OK";
            $this->details = $res;
        } else $this->msg = self::t("No task for the day");


        $this->output();
    }

    public function actionupdateInbound()
    {

        $hawb = isset($this->data['resultTable']) ? $this->data['resultTable'] : '';

        $idreplid = isset($this->data['idreplid']) ? $this->data['idreplid'] : '';
        $tmpLoc = isset($this->data['tmpLoc']) ? $this->data['tmpLoc'] : '';

        if (!empty($idreplid)) {

            $db = new DbExt;
            for ($i = 0; $i < count($idreplid); $i++) {
                $params = array(
                    'loc' => $tmpLoc[$i + 1]
                );

                $db->updateData("{{inbound_details}}", $params, 'descr', $idreplid[$i + 1]);
            }

            //cek status header
            $res = Driver::cekIn($hawb);

            if ($res[0]['qty'] >= count($idreplid) + 1) {
                $params = array(
                    'status' => 'successful'
                );

                $db->updateData("{{inbound_header}}", $params, 'hawb', $hawb);
            }

            $this->code = 1;
            $this->msg = "OK";
        }

        $this->output();
    }

    public function actiongetShipment()
    {
        //cek status header
        $res = Driver::cekIn($this->data['outboundkey']);

        if ($res[0]['status'] == 'successful') {
            $this->msg = self::t("Close");
            $this->output();
            Yii::app()->end();
        }

        if ($res = Driver::GetInDet($this->data['receiptkey'])) {
            $this->code = 1;
            $this->msg = "OK";
            $this->details = $res;
        } else $this->msg = self::t("No task for the day");


        $this->output();
    }

    public function actiongetInbound()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if ($res = Driver::getInbound()) {

            $this->code = 1;
            $this->msg = "OK";
            $data = '';

            foreach ($res as $val) {
                $val['date_created'] = Yii::app()->functions->prettyDate($val['date_created']);
                $data[] = $val;
            }
            $this->details = $data;
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actiongetInById()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $data_id = $this->data['data_id'];

        if ($res = Driver::getInboundById($data_id)) {

            $this->code = 1;
            $this->msg = "OK";
            $this->details = array(
                'header' => $this->data['data_id'],
                'isi' => $res
            );
        } else $this->msg = self::t("No task for the day");

        $this->output();
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

    public function actiongetAllLoc()
    {
        $this->data = $_GET;

        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }


        $loc = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_loc')
            ->queryAll();

        echo json_encode($loc);
        Yii::app()->end();
    }

    public function actionUpdateLoc()
    {

        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if (!$token = Driver::getUserByToken($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $hawb = $this->data['hawb'];

        //cek allLoc
        $hawb_header = Driver::GetInDet($this->data['hawb']);

        if (!empty($hawb_header['loc'])) {
            $this->msg = self::t("Cannot double scan!!");
            $this->output();
            Yii::app()->end();
        }

        $params = array(
            'loc' => $this->data['loc'],
        );
        $params['scan_time'] = Driver::dateNow();

        $db = new DbExt;
        $db->updateData("{{inbound_details}}", $params, 'descr', $hawb);

        $cek = Driver::CekInLoc($hawb_header['hawb']);
        if ($cek['hitung'] < 1) {
            $params = array(
                'status' => 'successful',
                'checker' => $token['name']
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
                    ->where('hawb=:hawb', array(':hawb' => $hawb_header['hawb']))
                    ->queryRow();

                $sh_detail =  Yii::app()->db->createCommand()
                    ->select('d.descr AS hawb, h.descr, h.delivery_id, h.po, h.ata, h.sppb_date, h.locator, d.loc')
                    ->from('el_inbound_details d')
                    ->where('d.hawb=:hawb', array(':hawb' => $hawb_header['hawb']))
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
                $attachment1 = $baseDir . "upload/" . $sh['docfile'];


                // $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('L', 'A4', 'en');
                // $html2pdf->setDefaultFont('helvetica');
                // $html2pdf->writeHTML($this->renderPartial('otr/demo_movement_detail_pdf', ['detail' => $sh_detail], true));
                // $output = $baseDir."/inbdetail/" . $sh['hawb'] . '.pdf';
                // $html2pdf->output($output, 'F');
                // $attachment2 = $output;

                Driver::sendEmail($recipient, $subject, $body_html, $body_text, $attachment1);
            }
        }

        $LocCurrent = Driver::getLocCurrent2($hawb_header['hawb'], $this->data['hawb']);

        $paramdetails = array(
            'hawb' => $hawb_header['hawb'],
            'hawb_descr' => $this->data['hawb'],
            'loc_before' => $LocCurrent['loc_after'],
            'loc_after' => $this->data['loc'],
            'users' => $token['name']
        );
        $paramdetails['date_created'] = Driver::dateNow();

        Driver::insertLog($paramdetails);

        $this->code = 1;
        $this->msg = self::t("done");
        $this->details = array(
            'hitung' => $cek['hitung'],
            'header' => $hawb_header['hawb'],
            'LocCurrent' => $LocCurrent['loc_after']
        );

        $this->output();
    }

    public function actiongetOutbound()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if ($res = Driver::getOutbound()) {

            $this->code = 1;
            $this->msg = "OK";
            $data = '';

            foreach ($res as $val) {
                $val['date_created'] = Yii::app()->functions->prettyDate($val['date_created']);
                $data[] = $val;
            }
            $this->details = $data;
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actionOutDetail()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $id = $this->data['id'];

        if ($res = Driver::getOutboundById($id)) {

            $this->code = 1;
            $this->msg = "OK";
            $this->details = $res;
            $this->details = array(
                'id' => $this->data['id'],
                'list' => $res
            );
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actionOutDetailNew()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $id = $this->data['id'];

        if ($res = Driver::getOutboundById($id)) {

            $this->code = 1;
            $this->msg = "OK";
            $this->details = $res;
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actionsendOut()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if (!$token = Driver::getUserByToken($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $hawb = $this->data['hawb'];
        $id = $this->data['id'];
        // $id_detail = $this->data['id_detail']; // tambahan untuk validasi qr, ternyata ga bisa, harus ubah db

        // if (isset($_GET['id_detail'])) {
        //     $validasi = Yii::app()->db->createCommand()
        //         ->select('*y')
        //         ->from('el_demo_movement_detail')
        //         ->where('id=:id', array(':id' => $id_detail))
        //         ->where('descr=:hawb', array(':hawb' => $hawb))
        //         ->queryAll();

        //     if (empty($validasi)) {
        //         $this->msg = self::t("Wrong QR Code scanned");
        //         $this->output();
        //         Yii::app()->end();
        //     }
        // }


        //update flag dan scan time di detail
        $command = Yii::app()->db->createCommand();
        $command->update('el_outbound_details', array(
            'flag' => 1,
            'scan_time' => Driver::dateNow(),
            'updated_by' => $token['id'],
            'date_updated' => date('Y-m-d H:i:s'),
        ), 'id=:id AND descr=:hawb', array(':id' => $id, ':hawb' => $hawb));

        $status = 'inprogress';
        //cek allLoc
        $hawb_header = Driver::GetInDet($this->data['hawb']);


        $LocCurrent = Driver::getLocCurrent2($hawb_header['hawb'], $this->data['hawb']);

        $paramdetails = array(
            'hawb' => $hawb_header['hawb'],
            'hawb_descr' => $hawb,
            'loc_before' => $LocCurrent['loc_after'],
            'loc_after' => 'out',
            'users' => $token['name']
        );
        $paramdetails['date_created'] = Driver::dateNow();

        Driver::insertLog($paramdetails);

        $cek = Driver::CekOutLoc($id);
        if ($cek['hitung'] < 1) {
            $command = Yii::app()->db->createCommand();
            $command->update('el_outbound_header', array(
                'status' => 'successful',
                'checker' => $token['name'],
                'updated_by' => $token['id'],
                'date_updated' => date('Y-m-d H:i:s'),
            ), 'id=:id', array(':id' => $id));

            $status = 'successful';
        }

        $this->code = 1;
        $this->msg = self::t("Successful");
        $this->details = array(
            'id' => $id,
            'status' => $status
        );

        $this->output();
    }

    public function actiongetDemoMovement()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $demo = Yii::app()->db->createCommand()
            ->select('d.*, (select count(*) from el_demo_movement_detail dd where dd.demo_movement_id=d.id) AS qty')
            ->from('el_demo_movement d')
            ->where('d.status=:id', array(':id' => 'Requested'))
            ->order('d.id DESC')
            ->queryAll();

        if ($demo) {

            $this->code = 1;
            $this->msg = "OK";

            $this->details = $demo;
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actiongetDemoMovementDetail()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $id = $this->data['id'];

        $demoDetail = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement_detail')
            ->where('demo_movement_id=:id', array(':id' => $id))
            ->queryAll();

        if (!empty($demoDetail)) {

            $this->code = 1;
            $this->msg = "OK";
            $this->details = $demoDetail;
        } else $this->msg = self::t("No Demo Movement Detail Found");

        $this->output();
    }

    public function actionvalidateDemoMovement()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if (!$token = Driver::getUserByToken($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $id = $this->data['id']; //id di details
        $hawb = $this->data['hawb']; //hawb di details

        $demoDetail = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement_detail')
            ->where('id=:id', array(':id' => $id))
            ->andWhere('hawb=:hawb', array(':hawb' => $hawb))
            ->queryRow();

        if (empty($demoDetail)) {
            $this->msg = self::t("Wrong QR Code, please scan the correct QR Code");
            $this->output();
            Yii::app()->end();
        }

        //update flag dan scan time di detail
        $command = Yii::app()->db->createCommand();
        $command->update('el_demo_movement_detail', array(
            'flag' => 1,
            'scan_time' => Driver::dateNow(),
            'updated_by' => $token['id'],
            'updated_at' => date('Y-m-d H:i:s'),
        ), 'id=:id AND hawb=:hawb', array(':id' => $id, ':hawb' => $hawb));

        // start el moving
        //ambil hawb headernya, untuk diinsert ke moving

        //ambil data header
        $header = Yii::app()->db->createCommand()
            ->select('*')
            ->from('el_demo_movement')
            ->where('id=:id', array(':id' => $demoDetail['demo_movement_id']))
            //->order('id DESC')
            ->queryRow();

        $hawb_header = Driver::GetInDet($this->data['hawb']);

        $LocCurrent = Driver::getLocCurrent2($hawb_header['hawb'], $hawb);

        $paramdetails = array(
            'hawb' => $hawb_header['hawb'],
            'hawb_descr' => $hawb,
            'loc_before' => $LocCurrent['loc_after'],
            'loc_after' => $demoDetail['loc'],
            'users' => $token['name']
        );
        $paramdetails['date_created'] = Driver::dateNow();

        Driver::insertLog($paramdetails);
        // end el moving


        //hitung demo movement detail yg flag 0, by id
        $cek = Yii::app()->db->createCommand()
            ->select('count(*) AS hitung')
            ->from('el_demo_movement_detail')
            ->where('demo_movement_id=:id', array(':id' => $demoDetail['demo_movement_id']))
            ->andWhere('flag=:flag', array(':flag' => 0))
            ->queryRow();

        if ($cek['hitung'] < 1) {

            $command = Yii::app()->db->createCommand();
            $command->update('el_demo_movement', array(
                'status' => 'Successful',
                'checker' => $token['name'],
                'updated_by' => $token['id'],
                'updated_at' => date('Y-m-d H:i:s'),
            ), 'id=:id', array(':id' => $demoDetail['demo_movement_id']));


            //kasi demo_movement_id untuk setiap barang di inbound_details
            $inbound_detail = Yii::app()->db->createCommand()
                ->select('*')
                ->from('el_demo_movement_detail')
                ->where('demo_movement_id=:demo_movement_id', array(':demo_movement_id' => $demoDetail['demo_movement_id']))
                ->queryAll();

            if ($header['is_return'] == 1) {
                foreach ($inbound_detail as $r) {

                    //ambil loc di demo
                    $dmd = Yii::app()->db->createCommand()
                        ->select('loc')
                        ->from('el_demo_movement_detail')
                        ->where('hawb=:id', array(':id' => $r['hawb']))
                        ->order('id DESC')
                        ->limit(1)
                        ->queryRow();

                    //update demo_movement_id DAN loc
                    Yii::app()->db->createCommand()->update('el_inbound_details', array(
                        'demo_movement_id' => NULL,
                        'updated_by' => $token['id'],
                        'loc' => $dmd['loc'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ), 'descr=:descr', array(':descr' => $r['hawb']));
                }
            } else {
                foreach ($inbound_detail as $r) {
                    Yii::app()->db->createCommand()->update('el_inbound_details', array(
                        'demo_movement_id' => $demoDetail['demo_movement_id'],
                        'updated_by' => $token['id'],
                        'date_updated' => date('Y-m-d H:i:s'),
                    ), 'descr=:descr', array(':descr' => $r['hawb']));
                }
            }

            foreach ($inbound_detail as $r) {
                Yii::app()->db->createCommand()->update('el_inbound_details', array(
                    'demo_movement_id' => $demo_id,
                    'updated_by' => $token['id'],
                    'date_updated' => date('Y-m-d H:i:s'),
                ), 'descr=:descr', array(':descr' => $r['hawb']));
            }
        }

        $this->code = 1;
        $this->msg = self::t("Validate Demo Movement Successful");
        $this->details = array(
            'id' => $id,
            'status' => 'Successful',
        );

        $this->output();
    }


    //// batas otr/service


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

    public function actionUpdateLocs()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if (!$token = Driver::getUserByToken($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $lot = $this->data['lot'];

        //cek allLoc
        $hawb = Driver::GetInDets($this->data['lot']);

        if (!empty($hawb_header['loc'])) {
            $this->msg = self::t("Cannot double scan!!");
            $this->output();
            Yii::app()->end();
        }

        $params = array(
            'loc' => $this->data['loc'],
        );
        $params['scan_time'] = Driver::dateNow();

        $db = new DbExt;
        $db->updateData("{{inbound_lots}}", $params, 'lotnumber', $lot);

        $cek = Driver::CekInLocs($hawb['hawb']);
        if ($cek['hitung'] < 1) {
            $params = array(
                'status' => 'successful',
                'checker' => $token['name']
            );
            $db->updateData("{{inbound_header_s}}", $params, 'hawb', $hawb['hawb']);
        }

        $LocCurrent = Driver::getLocCurrents($this->data['lot']);

        $paramdetails = array(
            'hawb' => $hawb['hawb'],
            'partnumber' => $hawb['partnumber'],
            'lotnumber' => $this->data['lot'],
            'loc_before' => $LocCurrent['loc_after'],
            'loc_after' => $this->data['loc'],
            'users' => $token['name']
        );
        $paramdetails['date_created'] = Driver::dateNow();

        Driver::insertLogs($paramdetails);

        $this->code = 1;
        $this->msg = self::t("done");
        $this->details = array(
            'hitung' => $cek['hitung'],
            'header' => $hawb['hawb'],
            'LocCurrent' => $LocCurrent['loc_after']
        );

        $this->output();
    }

    public function actiongetOutbounds()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if ($res = Driver::getOutbounds()) {

            $this->code = 1;
            $this->msg = "OK";
            $data = '';

            foreach ($res as $val) {
                $val['date_created'] = Yii::app()->functions->prettyDate($val['date_created']);
                $data[] = $val;
            }
            $this->details = $data;
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actionOutDetails()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $id = $this->data['id'];

        if ($res = Driver::getOutboundsById($id)) {

            $this->code = 1;
            $this->msg = "OK";
            $this->details = $res;
            $this->details = array(
                'id' => $this->data['id'],
                'list' => $res
            );
        } else $this->msg = self::t("No task for the day");

        $this->output();
    }

    public function actionsendOuts()
    {
        if (!isset($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        if (!$token = Driver::getUserByToken($this->data['token'])) {
            $this->msg = self::t("Token not valid");
            $this->output();
            Yii::app()->end();
        }

        $lotnumber = $this->data['lotnumber'];

        $params = array(
            'flag' => '1'
        );
        $params['scan_time'] = Driver::dateNow();

        $db = new DbExt;
        $db->updateData("{{outbound_details_s}}", $params, 'lotnumber', $lotnumber);

        $status = 'inprogress';
        //cek allLoc
        $hawb = Driver::GetInDets($this->data['lotnumber']);
        $id = $this->data['id'];

        $LocCurrent = Driver::getLocCurrents($this->data['lotnumber']);

        $paramdetails = array(
            'hawb' => $hawb['hawb'],
            'partnumber' => $hawb['partnumber'],
            'lotnumber' => $lotnumber,
            'loc_before' => $LocCurrent['loc_after'],
            'loc_after' => 'out',
            'users' => $token['name']
        );
        $paramdetails['date_created'] = Driver::dateNow();

        Driver::insertLogs($paramdetails);

        $cek = Driver::CekOutLocs($id);
        if ($cek['hitung'] < 1) {
            $status = 'successful';

            $params = array(
                'status' => 'successful',
                'checker' => $token['name']
            );
            $db->updateData("{{outbound_header_s}}", $params, 'id', $id);
        }

        $this->code = 1;
        $this->msg = self::t("Login Successful");
        $this->details = array(
            'id' => $id,
            'status' => $status
        );

        $this->output();
    }
} /*end class*/