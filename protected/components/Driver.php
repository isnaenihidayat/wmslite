<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Driver
{
    public static function assetsUrl()
    {
        return Yii::app()->baseUrl . '/assets';
    }

    public static function jsLang()
    {
        return array(
            'are_your_sure' => self::t("Are you sure"),
            'create_task' => self::t("Create Task"),
            'update_task' => self::t("Update Task"),
            'create_agent' => self::t("Create Agent"),
            'update_agent' => self::t("Update Agent"),
            'create_team' => self::t("Create Team"),
            'update_team' => self::t("Update Team"),
            'create_group' => self::t("Create Group"),
            'update_group' => self::t("Update Group"),
            'create_route' => self::t("Create Route"),
            'update_route' => self::t("Update Route"),
            'add_driver' => self::t("Add Driver"),
            'update_driver' => self::t("Update Driver"),
            'pickup_before' => self::t("Pickup before"),
            'delivery_before' => self::t("Delivery before"),
            'delivery_address' => self::t("Delivery Address"),
            'pickup_address' => self::t("Pickup Address"),
            'location_on_map' => self::t("Location on Map"),
            'trip_on_map' => self::t("Trip on Map"),
            'no_history' => self::t("No history"),
            'no_trip' => self::t("No trip driver"),
            'reason' => self::t("Reason"),
            'assign_agent' => self::t("Assign Agent"),
            're_assign_agent' => self::t("Re-assign Agent"),
            'details' => self::t("Details"),
            'name' => self::t("Name"),
            'task_id' => self::t("Task ID"),
            'undefine_result' => self::t("Undefined Result"),
            'connection_lost' => self::t("Connection Lost"),
            'task' => Driver::t("Task"),
            'online' => Driver::t("Online"),
            'offline' => Driver::t("Offline"),
            'not_available' => Driver::t("Not available"),
            'no_notification' => self::t("No notifications for today"),
            'currentlocation' => self::t("Current Location"),
            'autoassigning' => self::t("Auto assigning"),
            'account_expired' => self::t("Your account is expired")
        );
    }

    public static function dateNow()
    {
        return date('Y-m-d G:i:s');
    }

    public static function driverStatus()
    {
        return array(
            'active' => Yii::t("default", 'active'),
            'pending' => Yii::t("default", 'pending for approval'),
            'suspended' => Yii::t("default", 'suspended'),
            'blocked' => Yii::t("default", 'blocked'),
            //'expired'=>Yii::t("default",'expired')
        );
    }

    public static function Curl($uri = "", $post = "")
    {
        $error_no = '';
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resutl = curl_exec($ch);

        $error_no = curl_errno($ch);

        if ($error_no == 0) {
            return $resutl;
        } else return false;
        curl_close($ch);
    }

    public static function dump($data = '')
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public static function parseValidatorError($error = '')
    {
        $error_string = '';
        if (is_array($error) && count($error) >= 1) {
            foreach ($error as $val) {
                $error_string .= "$val\n";
            }
        }
        return $error_string;
    }

    public static function deliveryTimeOption()
    {
        $data[] = self::t("Please select");
        for ($i = 1; $i <= 5; $i++) {
            $data[$i] = self::t("after" . " " . $i . " " . "hour of purchase");
        }
        return $data;
    }

    public static function t($message = '')
    {
        return Yii::t("default", $message);
    }

    public static function q($data)
    {
        return Yii::app()->db->quoteValue($data);
    }

    public static function transportType()
    {
        return array(
            'truck' => self::t("Truck"),
            'car' => self::t("Car"),
            'bike' => self::t("Bike"),
            'bicycle' => self::t("Bicycle"),
            'scooter' => self::t("Scooter"),
            'walk' => self::t("Walk"),
        );
    }

    public static function prettyPrice($amount = '')
    {
        if (!empty($amount)) {
            return displayPrice(getCurrencyCode(), prettyFormat($amount));
        }
        return 0;
    }

    public static function islogin()
    {
        if (isset($_SESSION['wmslite'])) {
            if (is_numeric($_SESSION['wmslite']['user_id'])) {
                return true;
            }
        }
        return false;
    }

    public static function getLoginType()
    {
        if (self::islogin()) {
            return $_SESSION['wmslite']['module'];
        }
        return 1;
    }

    public static function getUserType()
    {
        /*if(isset($_SESSION['wmslite'])){
            if(is_numeric($_SESSION['wmslite']['admin'])){
                return true;
            }
        }
        return false;*/
        if (self::islogin()) {
            return $_SESSION['wmslite']['admin'];
        }
        return 0;
    }

    public static function getUserId()
    {
        if (self::islogin()) {
            return $_SESSION['wmslite']['user_id'];
        }
        return false;
    }

    public static function getUserName()
    {
        if (self::islogin()) {
            return $_SESSION['wmslite']['first_name'] . ' ' . $_SESSION['wmslite']['last_name'];
        }
        return false;
    }

    public static function getUserEmail($customer_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{customer}}
		WHERE
		customer_id=" . self::q($customer_id) . "		
		LIMIT 0,1
		";

        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getPlanID()
    {
        if (self::islogin()) {
            return $_SESSION['elcar']['plan_id'];
        }
        return false;
    }

    public static function getUserToken()
    {
        if (self::islogin()) {
            return $_SESSION['elcar']['token'];
        }
        return false;
    }

    public static function getUserStatus()
    {
        if (self::islogin()) {
            return $_SESSION['wmslite']['status'];
        }
        return false;
    }

    public static function uploadURL()
    {
        return Yii::app()->getBaseUrl(true) . "/upload";
    }

    public static function uploadPhotoURL()
    {
        return Yii::app()->getBaseUrl(true) . "/upload/photo";
    }

    public static function uploadPath()
    {
        return Yii::getPathOfAlias('webroot') . "/upload";
    }

    public static function moduleUrl()
    {
        return Yii::app()->getBaseUrl(true);
    }

    public static function Login($email = '', $password = '', $module = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{user}}
		WHERE
		email_address=" . self::q($email) . "
        LIMIT 0,1
        ";

        if ($res = $db->rst($stmt)) {

            /*foreach ($res as $val) {

                if ($val['admin'] < 1) {

                    if ($module == $val['module']) {
                        $hash=$data['password'];

                        if (CPasswordHelper::verifyPassword($password, $hash)){
                            return $val;
                        }
                    } else {
                        return false;
                    }
                    
                } else {

                }
            }*/

            $data = $res[0];
            $hash = $data['password'];

            if (!empty($module) && $data['module'] > 0 && $data['admin'] < 1) {
                if ($module !== $data['module']) return false;
            }

            if (CPasswordHelper::verifyPassword($password, $hash)) {
                return $data;
            }
        }
        return false;
    }

    public static function cleanText($text = '')
    {
        return stripslashes($text);
    }

    public static function serviceList()
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{services}}
		ORDER BY service_name ASC
		";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function deviceList()
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_device}}
		ORDER BY device_name ASC
		";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function doorList()
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_door}}
		ORDER BY door_name ASC
		";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getGroup($group_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{contact_group}}
		WHERE
		group_id=" . self::q($group_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function groupList($customer_id = '')
    {
        $and = '';
        $and .= " AND customer_id=" . self::q($customer_id) . "  ";

        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{contact_group}}		
		WHERE 1
		$and
		ORDER BY contact_name ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTeam($team_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_team}}
		WHERE
		team_id=" . self::q($team_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function teamList($customer_id = '', $status = 'publish')
    {
        $and = '';
        $and .= " AND customer_id=" . self::q($customer_id) . "  ";

        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_team}}		
		WHERE 1
		$and
		AND status ='$status'
		ORDER BY team_name ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTeamAll()
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_team}}		
		WHERE
		status='publish'		
		ORDER BY team_name ASC	
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function toList($data = '', $key = '', $value = '', $default_value = '')
    {
        $list = '';
        if (is_array($data) && count($data) >= 1) {
            if (!empty($default_value)) {
                $list[] = $default_value;
            }
            foreach ($data as $val) {
                $list[$val[$key]] = $val[$value];
            }
            return $list;
        }
        return false;
    }

    public static function getCompanyName()
    {
        $name = getOptionA('CompanyName');
        if (!empty($name)) {
            return $name;
        }
        return "PT eLogistik System Indonesia";
    }

    public static function LocInfo($loc_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{loc}} 		
		WHERE
		loc_name=" . self::q($loc_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetLocUsage($loc_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT loc_descr
		FROM
		vw_loc_idle		
		WHERE
        loc_descr=" . self::q($loc_id) . "
        LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function InInfo($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{inbound_header}} 		
		WHERE
		id=" . self::q($id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function OutInfo($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{outbound_header}} 		
		WHERE
		id=" . self::q($id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetIn($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT koli as `id`, koli as `text`
		FROM
		vw_inbound	
        WHERE
        status='successful' and flag=0 and loc <> ''
        AND (UPPER(koli) LIKE '%" . strtoupper($id) . "%')
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetInDemoMovement($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT koli as `id`, koli as `text`
		FROM
		vw_inbound	
        WHERE
        status='successful' and flag=0 and loc <> ''
        AND (UPPER(koli) LIKE '%" . strtoupper($id) . "%' )
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetHwbs($id = '')
    {

        $and = '';
        if (!empty($id)) {
            $and = "AND (UPPER(hawb) LIKE '%" . strtoupper($id) . "%')";
        }

        $db = new DbExt;
        $stmt = "
		SELECT hawb as `id`, hawb as `text`
		FROM
		vw_inbounds	
        WHERE
        status='successful' and flag=0 and loc <> ''
        $and
		GROUP BY hawb";

        //self::dump($stmt);

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function cekStatusIn($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(*) AS `CountLoc`
        FROM 
        vw_inbounds
        WHERE hawb='" . $id . "' AND STATUS='successful'
        AND flag=0 AND loc <> ''
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function hitungLot($hawb = '', $part = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT count(lotnumber) as qtytotal FROM vw_inbounds WHERE
        status='successful' and flag=0 and loc <> '' and hawb=" . self::q($hawb) . "
        AND partnumber=" . self::q($part) . "
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetParts($hawb = '', $part = '')
    {
        $and = '';
        if (!empty($part)) {
            $and = "AND (UPPER(partnumber) LIKE '%" . strtoupper($part) . "%')";
        }

        $db = new DbExt;
        $stmt = "
		SELECT partnumber as `id`, partnumber as `text`
		FROM
		vw_inbounds	
        WHERE
        status='successful' and flag=0 and loc <> '' and hawb=" . self::q($hawb) . "
        $and
		GROUP BY id_detail";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetLotParts($hawb = '', $part = '', $lot = '')
    {
        $and = '';
        if (!empty($lot)) {
            $and = "AND lotnumber=" . self::q($lot) . "";
        }

        $db = new DbExt;
        $stmt = "
		SELECT lotnumber as `id`, lotnumber as `text`
		FROM vw_inbounds WHERE
        status='successful' and flag=0 and loc <> '' and hawb=" . self::q($hawb) . "
        AND partnumber=" . self::q($part) . " $and";

        //self::dump($stmt);

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetLocParts($hawb = '', $part = '', $lot = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM vw_inbounds WHERE
        status='successful' and flag=0 and loc <> '' and hawb=" . self::q($hawb) . "
        AND partnumber=" . self::q($part) . " AND lotnumber=" . self::q($lot) . "";

        //self::dump($stmt);

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function cekIn($hawb = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{inbound_header}} 		
        WHERE
        hawb=" . self::q($hawb) . "
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function InInfos($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{inbound_header_s}} 		
		WHERE
		id=" . self::q($id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetInDet($hawb = '')
    {
        $db = new DbExt;

        $stmt = "
		SELECT *
		FROM
		{{inbound_details}} 		
        WHERE
        descr=" . self::q($hawb) . "
        ";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetInDetSchenker($hawb = '')
    {
        $db = new DbExt;

        $stmt = "
		SELECT *
		FROM
		{{inbound_details_schenker}} 		
        WHERE
        descr=" . self::q($hawb) . "
        ";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetInOne($hawb = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT max(id) as `id`
		FROM
        {{outbound_header}} 		
        LIMIT 0,1
        ";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetIdOut()
    {
        $db = new DbExt;
        $stmt = "
		SELECT max(id) as `id`
		FROM
        {{outbound_header_s}} 		
        LIMIT 0,1
        ";
        if ($res = $db->rst($stmt)) {
            return $res[0]['id'];
        }
        return false;
    }

    public static function getDescr($hawb = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT descr
		FROM
        {{inbound_header}}
        WHERE
        hawb=" . self::q($hawb) . " 		
        LIMIT 0,1
        ";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getLocIn($hawb = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT loc
		FROM
		{{inbound_details}} 		
        WHERE
        descr=" . self::q($hawb) . "
        LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0]['loc'];
        }
        return false;
    }

    public static function GetLoc($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT loc_name as `id`, loc_name as `text`
		FROM
		{{loc}}	
        WHERE
        (UPPER(loc_name) LIKE '%" . strtoupper($id) . "%')
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetLocs($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT loc_descr as `id`, loc_descr as `text`
		FROM
		{{loc_s}}	
        WHERE
        (UPPER(loc_descr) LIKE '%" . strtoupper($id) . "%')
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetPart($id = '', $hawb = '')
    {
        $and = '';
        if (!empty($hawb)) {
            $and .= " AND hawb=" . self::q($hawb) . "  ";
        }

        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{inbound_details_s}}	
        WHERE
        (UPPER(partnumber) LIKE '%" . strtoupper($id) . "%')
        $and
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function GetkKeycCount()
    {
        $db = new DbExt;
        $stmt = "
		SELECT option_value + 1 as keycount
		FROM
		el_option
        WHERE option_name='noship'
		";
        if ($res = $db->rst($stmt)) {
            return $res[0]['keycount'];
        }
        return false;
    }

    public static function GetPartDescr($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT descr
		FROM
		{{inbound_details_s}}	
        WHERE partnumber = '" . $id . "'
		";
        if ($res = $db->rst($stmt)) {
            return $res[0]['descr'];
        }
        return false;
    }

    public static function getDocFile($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT docfile
		FROM
		{{inbound_header_s}}	
        WHERE hawb = '" . $id . "'
		";
        if ($res = $db->rst($stmt)) {
            return $res[0]['docfile'];
        }
        return false;
    }

    public static function CekCountIn($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(*) AS `CountLoc`
        FROM 
        vw_inbound
        WHERE hawb='" . $id . "' AND loc <> ''
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function CekCountIns($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(*) AS `CountLoc`
        FROM 
        vw_inbounds
        WHERE hawb='" . $id . "' AND loc <> ''
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function CekInLoc($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(hawb) as `hitung`
        FROM 
        {{inbound_details}}
        WHERE hawb='" . $id . "' AND (loc = '' OR loc IS NULL)
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function CekInLocs($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(hawb) as `hitung`
        FROM 
        {{inbound_lots}}
        WHERE hawb='" . $id . "' AND loc = ''
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function GetHawbLot($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT hawb FROM vw_inbounds
        WHERE lotnumber='" . $id . "'
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function CekOutLoc($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(hawb) as `hitung`
        FROM 
        {{outbound_details}}
        WHERE id='" . $id . "' AND flag = '0'
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function CekOutLocs($id = '')
    {
        $db = new DbExt;
        $stmt = "
        SELECT COUNT(hawb) as `hitung`
        FROM 
        {{outbound_details_s}}
        WHERE id='" . $id . "' AND flag = '0'
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getOutID($hawb)
    {
        $db = new DbExt;
        $stmt = "
        SELECT *
        FROM 
        {{outbound_details}}
        WHERE descr='" . $id . "'
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function UserInfo($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{user}} 		
		WHERE
		user_id=" . self::q($id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function ApkInfo($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{apk}} 		
		WHERE
		id=" . self::q($id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function ApkInfos($id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM
		{{apk_s}} 		
		WHERE
		id=" . self::q($id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getInbound()
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_inbound_header 
        WHERE `status` = 'inprogress' 
        ORDER BY date_created
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getInboundById($data_id = '')
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_inbound_details 
        WHERE hawb = " . self::q($data_id) . " 
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getOutbound()
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_outbound_header 
        WHERE `status` = 'inprogress' 
        ORDER BY id DESC
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getOutbounds()
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_outbound_header_s 
        WHERE `status` = 'inprogress' 
        ORDER BY date_created
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getOutboundById($data_id = '')
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_outbound_details 
        WHERE id = " . self::q($data_id) . " 
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getOutboundsById($data_id = '')
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_outbound_details_s 
        WHERE id = " . self::q($data_id) . " 
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getOutboundByHawb($hawb = '')
    {
        $db  = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $stmt = "SELECT * FROM 
        el_outbound_details 
        WHERE hawb = " . self::q($hawb) . " 
        AND flag = '0'
        ";

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getUserByToken($token = '')
    {
        if (empty($token)) {
            return false;
        }
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{apk}}
		WHERE
		token=" . self::q($token) . "		
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getLocCurrent($hawb = '')
    {
        if (empty($hawb)) {
            return '';
        }
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{moving}}
		WHERE
		hawb_descr=" . self::q($hawb) . "		
        ORDER BY id DESC
        LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return '';
    }

    public static function getLocCurrent2($hawb = '', $sku = '')
    {
        if (empty($hawb)) {
            return '';
        }
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{moving}}
		WHERE
		hawb=" . self::q($hawb) . "		
		AND hawb_descr=" . self::q($sku) . "		
        ORDER BY id DESC
        LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return '';
    }

    public static function getLocCurrents($hawb = '')
    {
        if (empty($hawb)) {
            return '';
        }
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{moving_s}}
		WHERE
		lotnumber=" . self::q($hawb) . "		
        ORDER BY id DESC
        LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return '';
    }

    public static function insertLog($params)
    {
        $db = new DbExt;
        $db->insertData("{{moving}}", $params);
    }

    public static function insertLogs($params)
    {
        $db = new DbExt;
        $db->insertData("{{moving_s}}", $params);
    }

    public static function GetInDets($LotNumber = '')
    {
        $db = new DbExt;

        $stmt = "
		SELECT * FROM
		{{inbound_lots}}
        WHERE
        lotnumber=" . self::q($LotNumber) . "
        ";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function verifyLot($LotNumber = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{inbound_lots}}
		WHERE
		lotnumber=" . self::q($LotNumber) . "		
		LIMIT 0,1
		";

        if ($res = $db->rst($stmt)) {
            return false;
        }
        return true;
    }

    public static function getSearch($status, $q = '')
    {
        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        switch ($status) {
            case "inbound":
                $stmt = "
                SELECT * FROM
                vw_inbound	
                WHERE (UPPER(hawb) LIKE '%" . strtoupper($q) . "%') OR
                (UPPER(descr) LIKE '%" . strtoupper($q) . "%') OR
                (UPPER(po) LIKE '%" . strtoupper($q) . "%') OR
                (UPPER(koli) LIKE '%" . strtoupper($q) . "%')
                ORDER BY date_created DESC
                ";
                break;

            case "outbound":
                $stmt = "
                SELECT * FROM
                vw_outbound	
                WHERE (UPPER(head) LIKE '%" . strtoupper($q) . "%') OR
                (UPPER(hawb) LIKE '%" . strtoupper($q) . "%') OR
                (UPPER(po) LIKE '%" . strtoupper($q) . "%') OR
                (UPPER(destination) LIKE '%" . strtoupper($q) . "%')
                ORDER BY date_created DESC
                ";
                break;
        }

        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function formatSearch($status, $data = '')
    {
        if (is_array($data) && count($data) >= 1) {
            ob_start();
?>
            <?php if ($status == "inbound") : ?>
                <div class="feed-element task-map" data-id="<?php echo $data['id'] ?>">

                    <div class="media-body row">

                        <div class="col-md-9">
                            <small class="text-muted">
                                <i class="fa fa-tasks"></i> <?php echo Driver::t("HAWB") ?>=>
                                <?php if (!empty($data['hawb'])) : ?>
                                    <?php echo $data['hawb'] ?>
                                <?php endif; ?>
                            </small>
                            <br>

                            <small class="text-muted">
                                <i class="fa fa-edit"></i> <?php echo Driver::t("Description") ?>=>
                                <?php if (!empty($data['descr'])) : ?>
                                    <?php echo $data['descr'] ?>
                                <?php endif; ?>
                            </small>
                            <br>

                            <small class="text-muted">
                                <i class="fa fa-paste"></i> <?php echo Driver::t("PO") ?>=>
                                <?php if (!empty($data['po'])) : ?>
                                    <?php echo $data['po'] ?> <br>
                                <?php endif; ?>
                            </small>
                        </div>

                        <div class="col-md-2 pull-right">
                            <p class="label label-success pull-right">
                                <?php echo Driver::t($status) ?>
                            </p>

                            <div class="pull-right">
                                <a class="btn btn-xs btn-white details-inbound" href="javascript:;" data-id="<?php echo $data['id'] ?>" data-hawb="<?php echo $data['hawb'] ?>">
                                    <i class="fa fa-tasks"></i> <?php echo Driver::t("Details") ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="feed-element task-map" data-id="<?php echo $data['id'] ?>">

                    <div class="media-body row">

                        <div class="col-md-9">

                            <small class="text-muted">
                                <i class="fa fa-paste"></i> <?php echo Driver::t("PO") ?>=>
                                <?php if (!empty($data['po'])) : ?>
                                    <?php echo $data['po'] ?>
                                <?php endif; ?>
                            </small>
                            <br>

                            <small class="text-muted">
                                <i class="fa fa-edit"></i> <?php echo Driver::t("Destination") ?>=>
                                <?php if (!empty($data['destination'])) : ?>
                                    <?php echo $data['destination'] ?>
                                <?php endif; ?>
                            </small>
                            <br>

                            <small class="text-muted">
                                <i class="fa fa-folder"></i> <?php echo Driver::t("QTY") ?>=>
                                <?php if (!empty($data['qty'])) : ?>
                                    <?php echo $data['qty'] ?>
                                <?php endif; ?>
                            </small>
                            <br>

                        </div>

                        <div class="col-md-2 pull-right">
                            <p class="label label-primary pull-right">
                                <?php echo Driver::t($status) ?>
                            </p>

                            <div class="pull-right">
                                <a class="btn btn-xs btn-white details-outbound" href="javascript:;" data-id="<?php echo $data['id'] ?>">
                                    <i class="fa fa-tasks"></i> <?php echo Driver::t("Details") ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <?php
            $forms = ob_get_contents();
            ob_end_clean();
            return $forms;
        }
    }

    public static function driverList($customer_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver}}
		WHERE		
		customer_id =" . self::q($customer_id) . "
		ORDER BY first_name ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function contactList($customer_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT contact_id, CONCAT(first_name,' ',last_name) AS contact_name FROM
		{{contact}}
		WHERE		
		customer_id =" . self::q($customer_id) . "
		ORDER BY first_name ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getAllDriver($customer_id = '')
    {
        $and = '';
        $and .= " AND customer_id=" . self::q($customer_id) . "  ";

        $db = new DbExt;
        $stmt = "
		SELECT * FROM				
		{{driver}}		
		WHERE 1
		$and
		AND status='active'
		ORDER BY first_name ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getAllContact($customer_id = '', $sort_by = '')
    {
        $and = '';
        $and .= " AND customer_id=" . self::q($customer_id) . "  ";

        if (!empty($sort_by)) {
            $sort = $sort_by;
        } else $sort = 'first_name';

        $db = new DbExt;
        $stmt = "
		SELECT * FROM				
		{{contact}}		
		WHERE 1
		$and
		AND status='active'
		ORDER BY $sort ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function updateDriverTeam($driver = '', $team_id = '')
    {
        $db = new DbExt;
        if (!empty($driver)) {
            $driver = json_decode($driver, true);
            if (is_array($driver) && count($driver) >= 1) {
                foreach ($driver as $driver_id) {
                    $params['team_id'] = $team_id;
                    $db->updateData("{{driver}}", $params, 'driver_id', $driver_id);
                    unset($params);
                }
            }
        }
    }

    public static function updateGroupRoute($contact = '', $group_id = '')
    {
        $db = new DbExt;
        if (!empty($contact)) {
            $contact = json_decode($contact, true);
            if (is_array($contact) && count($contact) >= 1) {
                foreach ($contact as $contact_id) {
                    $params['group_id'] = $group_id;
                    $db->updateData("{{contact}}", $params, 'contact_id', $contact_id);
                    unset($params);
                }
            }
        }
    }

    public static function updateTeamDriver($driver_id = '', $team_id = '')
    {
        dump($driver_id);
        dump($team_id);
        if ($res = self::getTeam($team_id)) {
            dump($res);
            if (!empty($res['team_member'])) {
                $team_member = json_decode($res['team_member'], true);
                $team_member = array_flip($team_member);
                dump($team_member);
            }
        }
    }

    public static function getDriverByTeam($team_id = '')
    {
        $db = new DbExt;
        if (!empty($team_id)) {
            $stmt = "SELECT * FROM
			{{driver}}
			WHERE
			team_id=" . self::q($team_id) . "
			";
            if ($res = $db->rst($stmt)) {
                return $res;
            }
        }
        return false;
    }

    public static function getContactByGroup($group_id = '')
    {
        $db = new DbExt;
        if (!empty($group_id)) {
            $stmt = "SELECT * FROM
			{{contact}}
			WHERE
			group_id=" . self::q($group_id) . "
			";
            if ($res = $db->rst($stmt)) {
                return $res;
            }
        }
        return false;
    }

    public static function getTask($customer_id = '')
    {
        $and = '';
        $and = " AND customer_id=" . self::q($customer_id) . " ";

        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{driver_task}}
		WHERE 1		
		$and
		ORDER BY date_created ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTaskByDriverID($driver_id = '', $delivery_date = '')
    {
        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "SELECT * FROM
		{{driver_task_view}}
		WHERE
		driver_id=" . self::q($driver_id) . "
		AND
		delivery_date LIKE '" . $delivery_date . "%'
		ORDER BY delivery_date ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTaskId($task_id = '')
    {
        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
		SELECT * FROM
		{{driver_task_view}}
		WHERE
		task_id=" . self::q($task_id) . "		
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getReportTaskId($task_id = '')
    {
        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT
        rpt_task.*,
        rpt_task_history_pivot.`acknowledged`,
        rpt_task_history_pivot.`started`,
        rpt_task_history_pivot.`inprogress`,
        rpt_task_history_pivot.`successful`
        FROM rpt_task INNER JOIN rpt_task_history_pivot 
        ON (rpt_task.`task_id`=rpt_task_history_pivot.`task_id`)
        WHERE rpt_task.`task_id`=" . self::q($task_id) . "
        LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function deleteTask($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		DELETE FROM
		{{driver_task}}
		WHERE
		task_id=" . self::q($task_id) . "
		";
        if ($db->qry($stmt)) {

            //delete all history
            $stmt2 = "
			DELETE FROM
			{{task_history}}
			WHERE
			task_id=" . self::q($task_id) . "
			";
            $db->qry($stmt2);

            $stmt3 = "
			DELETE FROM
			{{driver_assignment}}
			WHERE
			task_id=" . self::q($task_id) . "
			";
            $db->qry($stmt3);

            return true;
        }
        return false;
    }

    public static function getTaskByStatus($customer_id = '', $status = '', $date = '')
    {

        //$where="WHERE 1";
        $where = " WHERE customer_id =" . self::q($customer_id) . " ";


        $where_status = '';

        $and_date = '';
        if (!empty($date)) {
            $and_date = " AND delivery_date LIKE '" . $date . "%' ";
        }

        switch ($status) {
            case "unassigned":
                $where_status = "AND status IN ('declined','unassigned')";
                break;

            case "assigned":
                $where_status = "AND status IN ('assigned','started','inprogress','acknowledged')";
                break;

            case "completed":
                $where_status = "AND status IN ('successful','failed','cancelled','canceled')";
                break;

            default:
                $where_status = "AND status =" . self::q($status) . "";
                break;
        }

        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
		SELECT * FROM
		{{driver_task_view}}		
		$where		
		$and_date
		$where_status
		ORDER BY stat_varchar, delivery_date ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            //dump($res);
            return $res;
        }
        return false;
    }

    public static function formatTask($data = '')
    {
        if (is_array($data) && count($data) >= 1) {
            //dump($data);
            $trans_type = self::t("D");
            if ($data['trans_type'] == "pickup") {
                $trans_type = self::t("P");
            }
            ob_start();
        ?>
            <div class="row box task-map" data-lat="<?php echo $data['task_lat'] ?>" data-lng="<?php echo $data['task_lng'] ?>" data-id="<?php echo $data['task_id'] ?>">

                <div class="col-md-2 center">
                    <div class="tag rounded <?php echo $data['trans_type']; ?>"><?php echo $trans_type; ?></div>
                    <div class="top10"><i class="ion-ios-location"></i></div>
                    <div class="top10"><i class="ion-ios-time-outline"></i></div>
                    <?php if ($data['driver_id'] > 0) : ?>
                        <div class="top10"><i class="ion-android-person"></i></div>
                    <?php endif; ?>
                </div> <!--row-->

                <div class="col-md-10">

                    <div class="row ">
                        <?php if ($data['task_id'] > 0) : ?>
                            <div class="col-md-6 small">
                                <?php echo Driver::t("Task ID") ?>. <b><?php echo $data['task_id'] ?></b></div>
                        <?php endif; ?>
                    </div>

                    <?php if (Driver::getUserType() == "admin") : ?>
                        <?php if (!empty($data['merchant_name'])) : ?>
                            <div class="row top10">
                                <div class="col-md-12">
                                    <?php
                                    echo Driver::t("Merchant name") . ": <span class=\"text-primary\">" .
                                        self::cleanString($data['merchant_name']) . "</span>";
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="row top10">
                        <div class="col-md-5"><?php echo $data['customer_name'] ?></div>
                        <div class="col-md-7 text-right">

                            <?php if ($data['status'] == "unassigned") : ?>
                                <a href="javascript:;" class="assign-agent inline orange-button-small rounded" data-id="<?php echo $data['task_id'] ?>">
                                    <?php echo self::t("Assign Driver") ?>
                                </a>
                            <?php else : ?>
                                <p class="rounded tag <?php echo $data['status'] ?>">
                                    <?php echo Driver::t($data['status']) ?>
                                </p>
                            <?php endif; ?>

                        </div> <!--col-->
                    </div> <!--row-->

                    <div class="row top5">
                        <div class="col-md-8">
                            <p class="task_address top10 concat-text"><?php echo $data['delivery_address'] ?></p>
                            <p class="task_time">
                                <?php echo date('G:i', strtotime($data['delivery_date'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <a href="javascript:;" class="task-details" data-id="<?php echo $data['task_id'] ?>">
                                <?php echo Driver::t("Details") ?>
                            </a>
                        </div>
                    </div> <!--row-->

                    <?php if ($data['driver_id'] > 0) : ?>
                        <p class="concat-text top10">
                            <?php echo $data['driver_name'] ?>
                        </p>
                    <?php endif; ?>

                </div> <!--row-->

                <?php if (!empty($data['assignment_status']) && $data['status'] == "unassigned") : ?>
                    <?php if ($data['assignment_status'] == "unable to auto assign") : ?>

                        <div class="col-md-7 top5 center autoassign-col-1-<?php echo $data['task_id'] ?>">
                            <p class="small-font text-danger"><?php echo Driver::t($data['assignment_status']) ?></p>
                        </div>

                        <div class="col-md-5 top5 text-right autoassign-col-2-<?php echo $data['task_id'] ?>">
                            <a href="javascript:retryAutoAssign('<?php echo $data['task_id'] ?>');" class="small-font">
                                <?php echo Driver::t("Retry") ?>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="col-md-12 top5 center">
                            <p class="small-font text-primary">
                                <?php echo Driver::t($data['assignment_status']) ?>...
                                <i class="small-font fa fa-circle-o-notch fa-spin fa-3x fa-fw"></i>
                            </p>
                        <?php endif; ?>
                        </div>
                    <?php endif; ?>

            </div> <!--row-->
        <?php
            $forms = ob_get_contents();
            ob_end_clean();
            return $forms;
        }
    }

    public static function formatTask1($data = '')
    {
        if (is_array($data) && count($data) >= 1) {
            ob_start();
        ?>
            <div class="feed-element task-map" data-lat="<?php echo $data['task_lat'] ?>" data-lng="<?php echo $data['task_lng'] ?>" data-id="<?php echo $data['task_id'] ?>">
                <a href="#" class="pull-left">
                    <img alt="image" class="img-circle" src="<?php echo Yii::app()->getBaseUrl(true) . "/assets/images/" . $data['trans_type'] . ".png" ?>">
                    <br>
                    <small class="text-muted">
                        <?php echo $data['trans_type'] ?>
                    </small>
                </a>

                <div class="media-body row">

                    <div class="col-md-9">
                        <?php if ($data['task_id'] > 0) : ?>
                            <small class="text-muted">
                                <i class="fa fa-tasks"></i> <?php echo Driver::t("Task ID") ?>. <?php echo $data['task_id'] ?>
                            </small>
                            <br>
                        <?php endif; ?>

                        <?php if (!empty($data['customer_name'])) : ?>
                            <strong>Customer: </strong> <?php echo $data['customer_name'] ?> <br>
                        <?php endif; ?>

                        <?php if ($data['driver_id'] > 0) : ?>
                            <small class="text-muted">
                                <i class="fa fa-user"></i> <?php echo $data['driver_name'] ?> <br>
                            </small>
                        <?php endif; ?>

                        <?php if (!empty($data['delivery_address'])) : ?>
                            <small class="text-muted">
                                <i class="fa fa-home"></i> <?php echo $data['delivery_address'] ?> <br>
                            </small>
                        <?php endif; ?>

                        <?php if (!empty($data['delivery_date'])) : ?>
                            <small class="text-muted">
                                <i class="fa fa-clock-o"></i> <?php echo date('G:i', strtotime($data['delivery_date'])) ?> <br>
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-2 pull-right">
                        <?php if ($data['status'] == "unassigned") : ?>
                            <a href="javascript:;" class="assign-agent label label-warning pull-right" data-id="<?php echo $data['task_id'] ?>">
                                <?php echo self::t("Assign Driver") ?>
                            </a>
                        <?php else : ?>
                            <p class="label label-<?php echo $data['status'] ?> pull-right">
                                <?php echo Driver::t($data['status']) ?>
                            </p>
                        <?php endif; ?>

                        <div class="pull-right">
                            <a class="btn btn-xs btn-white task-details" href="javascript:;" data-id="<?php echo $data['task_id'] ?>">
                                <i class="fa fa-tasks"></i> <?php echo Driver::t("Details") ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
            $forms = ob_get_contents();
            ob_end_clean();
            return $forms;
        }
    }

    public static function statusList()
    {
        //acknowledged
        return array(
            '' => self::t("Please select status"),
            'unassigned' => self::t("unassigned"),
            'assigned' => self::t("assigned"),
            'started' => self::t("started"),
            'inprogress' => self::t("inprogress"),
            'successful' => self::t("successful"),
            'failed' => self::t("failed"),
            'declined' => self::t("declined"),
            'cancelled' => self::t("cancelled"),
        );
    }

    public static function prettyStatus($from = '', $to = '')
    {
        if (!empty($from) && !empty($to)) {
            $prety = self::t("Status updated from");
            $prety .= " $from " . self::t("to") . " $to";
            return $prety;
        }
        return Driver::t("Status changed");
    }

    public static function getTaskHistory($task_id = '')
    {

        $db = new DbExt;
        $and = '';
        $or = '';
        if ($task_id > 0) {
            if (!empty($or)) {
                $or .= " OR task_id=" . self::q($task_id) . " ";
            } else {
                $or = "task_id=" . self::q($task_id) . " ";
            }
        }

        if (!empty($or)) {
            $and = " AND ( $or ) ";
        }

        $stmt = "SELECT * FROM
		{{task_history}}
		WHERE
		1
		$and
		GROUP BY `status`
		ORDER BY id ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getCountTrip($task_id = '')
    {
        $db = new DbExt;

        $stmt = "SELECT count(*) as total FROM
		{{driver_log}}
		WHERE
		task_id=" . self::q($task_id) . "
		GROUP BY driver_location_lat,driver_location_lng
		ORDER BY id ASC LIMIT 0, 10000
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTaskTrip($task_id = '')
    {

        $db = new DbExt;

        $stmt = "SELECT * FROM
		{{driver_log}}
		WHERE
		task_id=" . self::q($task_id) . "
		GROUP BY driver_location_lat,driver_location_lng
		ORDER BY id ASC LIMIT 0, 10000
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function hasModuleAddon($modulename = '')
    {
        if (Yii::app()->hasModule($modulename)) {
            $path_to_upload = Yii::getPathOfAlias('webroot') . "/protected/modules/$modulename";
            if (file_exists($path_to_upload)) {
                return true;
            }
        }
        return false;
    }

    public static function AdminStatusTpl()
    {
        //$team_list=Driver::teamList( 'merchant',  Yii::app()->functions->getMerchantID() );
        $team_list = Driver::teamList(self::getUserId());
        if ($team_list) {
            $team_list = Driver::toList(
                $team_list,
                'team_id',
                'team_name',
                Driver::t("Select a team")
            );
        }
        //dump($team_list);

        $all_driver = Driver::getAllDriver();
        ?>
        <div class="uk-form-row">
            <label class="uk-form-label"><?php echo t('Select Team') ?></label>
            <?php
            echo CHtml::dropDownList('team_id', '', (array)$team_list, array(
                'class' => "task_team_id"
            ))
            ?>
        </div>

        <div class="uk-form-row">
            <label class="uk-form-label"><?php echo t('Assign Agent') ?></label>
            <select name="driver_id" id="driver_id" class="driver_id">
                <?php if (is_array($all_driver) && count($all_driver) >= 1) : ?>
                    <option value=""><?php echo Driver::t("Select driver") ?></option>
                    <?php foreach ($all_driver as $val) : ?>
                        <option class="<?php echo "team_opion option_" . $val['team_id'] ?>" value="<?php echo $val['driver_id'] ?>">
                            <?php echo $val['first_name'] . " " . $val['last_name'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
<?php
    }

    public static function addressToLatLong($address = '')
    {
        $protocol = isset($_SERVER["https"]) ? 'https' : 'http';
        if ($protocol == "http") {
            $api = "http://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address);
        } else $api = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address);

        /*check if has provide api key*/
        $key = Yii::app()->functions->getOptionAdmin('drv_google_api');
        if (!empty($key)) {
            $api = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . urlencode($key);
        }

        if (!$json = @file_get_contents($api)) {
            $json = Driver::Curl($api, '');
        }

        /*dump($api);
        dump($json);*/

        if (!empty($json)) {
            $json = json_decode($json);
            if (isset($json->error_message)) {
                return false;
            } else {
                if ($json->status == "OK") {
                    $lat = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
                    $long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
                } else {
                    $lat = '';
                    $long = '';
                }
                return array(
                    'lat' => $lat,
                    'long' => $long
                );
            }
        }
        return false;
    }

    public static function LatLongToAddress($lat, $lng)
    {
        $protocol = isset($_SERVER["https"]) ? 'https' : 'http';
        if ($protocol == "http") {
            $api = "http://maps.googleapis.com/maps/api/geocode/json?latlng=" . urlencode($lat) . "," . urlencode($lng);
        } else $api = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . urlencode($lat) . "," . urlencode($lng);

        /*check if has provide api key*/
        $key = Yii::app()->functions->getOptionAdmin('google_api_key');
        if (!empty($key)) {
            $api = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . urlencode($lat) . "," . urlencode($lng) . "&key=" . urlencode($key);
        }

        if (!$json = @file_get_contents($api)) {
            $json = Driver::Curl($api, '');
        }

        if (!empty($json)) {
            $json = json_decode($json);
            if (isset($json->error_message)) {
                return false;
            } else {
                if ($json->status == "OK") {
                    $address = $json->{'results'}[0]->{'formatted_address'};
                } else {
                    $address = '';
                }
                return $address;
            }
        }
        return false;
    }

    public static function getDriverByStats(
        $customer_id = '',
        $stats = '',
        $transaction_date = '',
        $driver_status = 'active',
        $team_id = ''
    ) {

        $db = new DbExt;
        $todays_date = date('Y-m-d');
        //$time_now = time() - 600;
        $time_now = time() - 200;
        $and = '';

        /*dump($time_now);
        dump("->".strtotime("now"));*/

        $and = " AND customer_id=" . self::q($customer_id) . " ";

        switch ($stats) {
            case "active":
                $and .= " AND on_duty ='1' ";
                $and .= " AND last_online >='$time_now' ";
                $and .= " AND last_login like '" . $todays_date . "%'";
                break;

            case "offline":
                $date_now = date("now", strtotime('-6 minutes'));
                $and .= " AND last_online <='$time_now' ";
            default:

                break;
        }

        $and .= " AND status=" . self::q($driver_status) . "";

        if ($team_id > 0) {
            $and .= " AND team_id=" . self::q($team_id) . " ";
        }

        $stmt = "
		SELECT a.*,
		(
		  select count(*)
		  from
		  {{driver_task}}
		  where
		  driver_id=a.driver_id
		  and 
		  delivery_date like '" . $transaction_date . "%'
		) as total_task
		FROM
		{{driver}} a
		WHERE 1
		$and
		ORDER BY first_name ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            $data = '';
            foreach ($res as $val) {
                $val['is_online'] = 2;
                $last_login = date('Y-m-d', strtotime($val['last_login']));
                if ($last_login == $todays_date && $val['on_duty'] == 1) {
                    if ($val['last_online'] >= $time_now) {
                        $val['is_online'] = 1;
                    }
                }
                $data[] = $val;
            }
            return $data;
        }
        return false;
    }

    public static function driverAppLogin($username = '', $password = '')
    {
        $db = new DbExt;

        $stmt = "SELECT * FROM
		{{apk}}
		WHERE
		username=" . self::q($username) . "
		AND
		password=" . self::q(md5($password)) . "
		LIMIT 0,1
        ";

        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function generateRandomNumber($range = 10)
    {
        $chars = "0123456789";
        srand((float)microtime() * 1000000);
        $i = 0;
        $pass = '';
        while ($i <= $range) {
            $num = rand() % $range;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }

    public static function random_string($length = 10)
    {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

    public static function checkToken($token = '')
    {
        if (empty($token)) {
            return false;
        }

        $db = new DbExt;
        $stmt = "
		  SELECT count(*) as total
		  FROM
		  {{driver_task}}
		  WHERE
		  task_token=" . self::q($token) . "
		";
        if ($res = $db->rst($stmt)) {
            if ($res[0] > 0) {
                return false;
            } else return true;
        }

        return true;
    }

    public static function driverForgotPassword($email_address = '')
    {
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{driver}}
		WHERE
		email=" . self::q($email_address) . "		
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getDriverByToken($token = '')
    {
        if (empty($token)) {
            return false;
        }
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{driver}}
		WHERE
		token=" . self::q($token) . "		
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function driverStatusPretty($driver_name = '', $status = '')
    {
        $msg = '';
        switch ($status) {

            case "sign":
            case "signature":
                $msg = $driver_name . " " . self::t("added a signature");
                break;
                break;
            case "photo":
                $msg = $driver_name . " " . self::t("added a photo");
                break;
            case "notes":
                $msg = $driver_name . " " . self::t("added a notes");
                break;
            case "failed":
                $msg = $driver_name . " " . self::t("marked the task as failed");
                break;

            case "cancelled":
                $msg = $driver_name . " " . self::t("marked the task as cancelled");
                break;

            case "declined":
                $msg = $driver_name . " " . self::t("marked the task as declined");
                break;

            case "acknowledged":
                $msg = $driver_name . " " . self::t("accepted the task");
                break;

            case "started":
                $msg = $driver_name . " " . self::t("started this task");
                break;

            case "inprogress":
                $msg = $driver_name . " " . self::t("reached the destination");
                break;

            case "successful":
                $msg = $driver_name . " " . self::t("Completed the task successfully");
                break;

            default:
                $msg = self::t("Status changed");
                break;
        }
        return $msg;
    }

    public static function getDriverTaskHistory($task_id = '')
    {
        $db = new DbExt;
        $stmt = "SELECT * FROM
		{{task_history}}
		WHERE
		task_id=" . self::q($task_id) . "		
		AND status NOT IN ('assigned')
		ORDER BY id ASC
		";
        if ($res = $db->rst($stmt)) {
            $data = '';
            foreach ($res as $val) {
                $val['status_raw'] = $val['status'];
                $val['status'] = self::t($val['status']);
                $val['time'] = Yii::app()->functions->timeFormat($val['date_created'], true);
                $val['date'] = Yii::app()->functions->FormatDateTime($val['date_created'], false);
                $data[] = $val;
            }
            return $data;
        }
        return false;
    }

    public static function getDriverNoteHistory($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		  SELECT *
		  FROM
		  {{task_history}}
		  WHERE
		  task_id=" . self::q($task_id) . "
		  AND status IN ('notes')
		  ORDER BY id ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getDriverCountNote($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		  SELECT count(*) as total
		  FROM
		  {{task_history}}
		  WHERE
		  task_id=" . self::q($task_id) . "
		  AND status IN ('notes')
		  ORDER BY id ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getDriverTaskViewHistory($task_id = '', $status = '', $and = '')
    {
        $sqlquery = '';
        if (!empty($and)) {
            $sqlquery = " AND id='$and' ";
        } else {
            $sqlquery = " AND status IN ('$status') ";
        }

        $db = new DbExt;
        $stmt = "
		  SELECT *
		  FROM
		  {{task_history}}
		  WHERE
		  task_id=" . self::q($task_id) . "
		  $sqlquery
		  ORDER BY id ASC
		";
        if ($res = $db->rst($stmt)) {
            if (!empty($and)) {
                return $res[0];
            } elseif ($status == 'sign') {
                return $res[0];
            } else return $res;
        }
        return false;
    }

    public static function getDriverCountPhoto($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		  SELECT count(*) as total
		  FROM
		  {{task_history}}
		  WHERE
		  task_id=" . self::q($task_id) . "
		  AND status IN ('photo')
		  ORDER BY id ASC
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getDriverMapIcon($customer_id = '')
    {
        $val['driver'] = Yii::app()->getBaseUrl(true) . '/' . Yii::app()->functions->getOption("driver_icon", $customer_id);
        $val['customer'] = Yii::app()->getBaseUrl(true) . '/' . Yii::app()->functions->getOption("customer_icon", $customer_id);
        $val['merchant'] = Yii::app()->getBaseUrl(true) . '/' . Yii::app()->functions->getOption("merchant_icon", $customer_id);
        return $val;
    }

    public static function getDriverTaskCalendar($driver_id = '', $start = '', $end = '')
    {
        $db = new DbExt;
        $stmt = "SELECT 
		DISTINCT DATE_FORMAT(a.delivery_date,'%Y-%m-%d') as delivery_date		
		FROM
		{{driver_task}} a
		WHERE
		driver_id=" . self::q($driver_id) . "		
		AND
		delivery_date BETWEEN '$start' AND '$end'
		";
        $db->qry("SET SQL_BIG_SELECTS=1");
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTotalTaskByDate($driver_id = '', $date = '')
    {
        $db = new DbExt;
        $stmt = "
		  SELECT count(*) as total
		  FROM
		  {{driver_task}}
		  WHERE
		  delivery_date LIKE '" . $date . "%'
		  AND
		  driver_id=" . self::q($driver_id) . "		
		";
        if ($res = $db->rst($stmt)) {
            return $res[0]['total'];
        }
        return 0;
    }

    public static function availableLanguages()
    {
        $lang['en'] = 'English';
        /*$stmt="
        SELECT * FROM
        {{languages}}
        WHERE
        status in ('publish','published')
        ";
        $db_ext=new DbExt;
        if ($res=$db_ext->rst($stmt)){
            foreach ($res as $val) {
                $lang[$val['lang_id']]=$val['language_code'];
            }
        }*/
        return $lang;
    }

    public static function notificationListPickup()
    {
        $data['PICKUP']['REQUEST_RECEIVED'] = array(
            'REQUEST_RECEIVED_PUSH',
            'REQUEST_RECEIVED_SMS',
            'REQUEST_RECEIVED_EMAIL'
        );
        $data['PICKUP']['DRIVER_STARTED'] = array(
            'DRIVER_STARTED_PUSH',
            'DRIVER_STARTED_SMS',
            'DRIVER_STARTED_EMAIL'
        );
        $data['PICKUP']['DRIVER_ARRIVED'] = array(
            'DRIVER_ARRIVED_PUSH',
            'DRIVER_ARRIVED_SMS',
            'DRIVER_ARRIVED_EMAIL'
        );
        $data['PICKUP']['SUCCESSFUL'] = array(
            'SUCCESSFUL_PUSH',
            'SUCCESSFUL_SMS',
            'SUCCESSFUL_EMAIL'
        );
        $data['PICKUP']['FAILED'] = array(
            'FAILED_PUSH',
            'FAILED_SMS',
            'FAILED_EMAIL'
        );
        return $data;
    }

    public static function notificationListDelivery()
    {
        $data['DELIVERY']['REQUEST_RECEIVED'] = array(
            'REQUEST_RECEIVED_PUSH',
            'REQUEST_RECEIVED_SMS',
            'REQUEST_RECEIVED_EMAIL'
        );
        $data['DELIVERY']['DRIVER_STARTED'] = array(
            'DRIVER_STARTED_PUSH',
            'DRIVER_STARTED_SMS',
            'DRIVER_STARTED_EMAIL'
        );
        $data['DELIVERY']['DRIVER_ARRIVED'] = array(
            'DRIVER_ARRIVED_PUSH',
            'DRIVER_ARRIVED_SMS',
            'DRIVER_ARRIVED_EMAIL'
        );
        $data['DELIVERY']['SUCCESSFUL'] = array(
            'SUCCESSFUL_PUSH',
            'SUCCESSFUL_SMS',
            'SUCCESSFUL_EMAIL'
        );
        $data['DELIVERY']['FAILED'] = array(
            'FAILED_PUSH',
            'FAILED_SMS',
            'FAILED_EMAIL'
        );
        return $data;
    }

    public static function tagAvailableList()
    {
        return array(
            t('Available Tags'),
            'TaskID', 'CustomerName',
            'CustomerAddress', 'DeliveryDateTime',
            'PickUpDateTime', 'DriverName',
            'OrderNo', 'CompanyName', 'CompletedTime'
        );
    }

    public static function getNotifications($customer_id = '', $viewed = 2)
    {
        $date_now = date("Y-m-d");

        $and = " AND customer_id=" . self::q($customer_id) . "  ";

        $db_ext = new DbExt;
        $stmt = "
    	SELECT a.* FROM
    	{{task_history}} a
    	WHERE
    	notification_viewed='$viewed'
    	AND 
    	driver_id > 0
    	AND
    	date_created LIKE '" . $date_now . "%'
    	AND
    	task_id = (
    	  select task_id 
    	  from
    	  {{driver_task}}
    	  where 
    	  task_id=a.task_id
    	  $and    	  
    	  limit 0,1
    	)    	
    	LIMIT 0,3
    	";
        //dump($stmt);
        if ($res = $db_ext->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function base30_to_jpeg($base30_string, $output_file)
    {

        $data = str_replace('image/jsignature;base30,', '', $base30_string);
        $converter = new jSignature_Tools_Base30();
        $raw = $converter->Base64ToNative($data);
        //Calculate dimensions
        $width = 0;
        $height = 0;
        foreach ($raw as $line) {
            if (max($line['x']) > $width) $width = max($line['x']);
            if (max($line['y']) > $height) $height = max($line['y']);
        }

        // Create an image
        $im = imagecreatetruecolor($width + 20, $height + 20);

        // Save transparency for PNG
        imagesavealpha($im, true);
        // Fill background with transparency
        $trans_colour = imagecolorallocatealpha($im, 255, 255, 255, 127);
        imagefill($im, 0, 0, $trans_colour);
        // Set pen thickness
        imagesetthickness($im, 2);
        // Set pen color to black
        $black = imagecolorallocate($im, 0, 0, 0);
        // Loop through array pairs from each signature word
        for ($i = 0; $i < count($raw); $i++) {
            // Loop through each pair in a word
            for ($j = 0; $j < count($raw[$i]['x']); $j++) {
                // Make sure we are not on the last coordinate in the array
                if (!isset($raw[$i]['x'][$j]))
                    break;
                if (!isset($raw[$i]['x'][$j + 1]))
                    // Draw the dot for the coordinate
                    imagesetpixel($im, $raw[$i]['x'][$j], $raw[$i]['y'][$j], $black);
                else
                    // Draw the line for the coordinate pair
                    imageline($im, $raw[$i]['x'][$j], $raw[$i]['y'][$j], $raw[$i]['x'][$j + 1], $raw[$i]['y'][$j + 1], $black);
            }
        }

        //Create Image
        $ifp = fopen($output_file, "wb");
        imagepng($im, $output_file);
        fclose($ifp);
        imagedestroy($im);
        return $output_file;
    }

    public static function priceSettings()
    {
        $admin_decimal_separator = getOptionA('admin_decimal_separator');
        $admin_decimal_place = getOptionA('admin_decimal_place');
        $admin_currency_position = getOptionA('admin_currency_position');
        $admin_thousand_separator = getOptionA('admin_thousand_separator');

        return array(
            'decimal_place' => strlen($admin_decimal_place) > 0 ? $admin_decimal_place : 2,
            'currency_position' => !empty($admin_currency_position) ? $admin_currency_position : 'left',
            'currency_set' => getCurrencyCode(),
            'thousand_separator' => !empty($admin_thousand_separator) ? $admin_thousand_separator : '',
            'decimal_separator' => !empty($admin_decimal_separator) ? $admin_decimal_separator : '.',
        );
    }

    public static function getDriverNotifications($driver_id = '')
    {
        $db_ext = new DbExt;
        $stmt = "SELECT * FROM
		{{driver_pushlog}}
		WHERE
		driver_id=" . self::q($driver_id) . "
		AND
		status='process'
		AND
		is_read='2'
		ORDER BY date_created DESC
		LIMIT 0,10
		";
        if ($res = $db_ext->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function prettyDate($date = '', $show_time = true)
    {
        if (!empty($date)) {
            return Yii::app()->functions->translateDate(Yii::app()->functions->FormatDateTime($date, $show_time));
        }
        return '';
    }

    public static function sendDriverNotification($key = '', $info = '')
    {
        if (!is_array($info) && count($info) <= 0) {
            return false;
        }

        /*check if driver is online */
        $driver_send_push_to_online = getOption(Driver::getUserId(), 'driver_send_push_to_online');
        if ($driver_send_push_to_online == 1) {
            if (!$driver_inf = self::isDriverOnline($info['driver_id'])) {
                return;
            }
        }

        $db_ext = new DbExt;
        //dump($info);
        //PUSH
        $key_value = getOption(Driver::getUserId(), $key . "_PUSH");
        if ($key_value == 1 && $info['enabled_push'] == 1) {
            $push_message = getOption(Driver::getUserId(), $key . "_PUSH_TPL");
            $push_message = self::smarty('TaskID', $info['task_id'], $push_message);
            $push_message = self::smarty('CustomerName', $info['customer_name'], $push_message);
            $push_message = self::smarty('CustomerAddress', $info['delivery_address'], $push_message);
            $push_message = self::smarty('DeliveryDateTime', self::prettyDate($info['delivery_date']), $push_message);
            $push_message = self::smarty('PickUpDateTime', self::prettyDate($info['delivery_date']), $push_message);
            $push_message = self::smarty('DriverName', $info['driver_name'], $push_message);
            $push_message = self::smarty('CompanyName', getOptionA('website_title'), $push_message);
            //$push_message=self::smarty('CompletedTime',$info[''],$push_message);
            $params = array(
                'customer_id' => isset($info['customer_id']) ? $info['customer_id'] : Driver::getUserId(),
                'device_platform' => isset($info['device_platform']) ? $info['device_platform'] : '',
                'device_id' => isset($info['device_id']) ? $info['device_id'] : '',
                'push_title' => str_replace("_", ' ', $key),
                'push_message' => $push_message,
                'actions' => $key,
                'driver_id' => isset($info['driver_id']) ? $info['driver_id'] : '',
                'task_id' => isset($info['task_id']) ? $info['task_id'] : '',
                'date_created' => date('c'),
                'ip_address' => $_SERVER['REMOTE_ADDR']
            );
            $db_ext->insertData("{{driver_pushlog}}", $params);
            $push_id = Yii::app()->db->getLastInsertID();
            self::RunPush($push_id);
        }

        //SMS
        if (self::canCustomerSendSMS($info['customer_id'])) {
            $key_value = getOption(Driver::getUserId(), $key . "_SMS");
            if ($key_value == 1 && $info['driver_phone'] != "") {
                $sms_message = getOption(Driver::getUserId(), $key . "_SMS_TPL");
                $sms_message = self::smarty('TaskID', $info['task_id'], $sms_message);
                $sms_message = self::smarty('CustomerName', $info['customer_name'], $sms_message);
                $sms_message = self::smarty('CustomerAddress', $info['delivery_address'], $sms_message);
                $sms_message = self::smarty('DeliveryDateTime', self::prettyDate($info['delivery_date']), $sms_message);
                $sms_message = self::smarty('PickUpDateTime', self::prettyDate($info['delivery_date']), $sms_message);
                $sms_message = self::smarty('DriverName', $info['driver_name'], $sms_message);
                $sms_message = self::smarty('CompanyName', getOptionA('website_title'), $sms_message);
                if ($send_sms = Yii::app()->functions->sendSMS($info['driver_phone'], $sms_message)) {
                    $params = array(
                        'to_number' => $info['driver_phone'],
                        'sms_text' => $sms_message,
                        'msg' => isset($send_sms['msg']) ? $send_sms['msg'] : '',
                        'raw' => isset($send_sms['raw']) ? $send_sms['raw'] : '',
                        'provider' => $send_sms['sms_provider'],
                        'date_created' => date('c'),
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    );
                    //$db_ext->insertData("{{sms_logs}}",$params);
                }
            }
        }

        //EMAIL
        $key_value = getOption(Driver::getUserId(), $key . "_EMAIL");
        if ($key_value == 1 && $info['driver_email'] != "") {
            $email_message = getOption(Driver::getUserId(), $key . "_EMAIL_TPL");
            $email_message = self::smarty('TaskID', $info['task_id'], $email_message);
            $email_message = self::smarty('CustomerName', $info['customer_name'], $email_message);
            $email_message = self::smarty('CustomerAddress', $info['delivery_address'], $email_message);
            $email_message = self::smarty('DeliveryDateTime', self::prettyDate($info['delivery_date']), $email_message);
            $email_message = self::smarty('PickUpDateTime', self::prettyDate($info['delivery_date']), $email_message);
            $email_message = self::smarty('DriverName', $info['driver_name'], $email_message);
            $email_message = self::smarty('CompanyName', getOptionA('website_title'), $email_message);
            $resp_email = sendEmail($info['driver_email'], '', $key, $email_message);
        }
    }

    public static function smarty($search = '', $value = '', $subject = '')
    {
        return str_replace("[" . $search . "]", $value, $subject);
    }

    public static function sendNotificationCustomer($key = '', $info = '')
    {

        //return ;
        /*dump($key);
        dump($info);*/

        $db_ext = new DbExt;

        $key_is_enabled = getOption(Driver::getUserId(), $key . "_PUSH");
        //dump($key_is_enabled);

        $key_is_enabled = getOption(Driver::getUserId(), $key . "_EMAIL");
        if ($key_is_enabled == 1 && !empty($info['email_address'])) {
            $message = getOptionA($key . "_EMAIL_TPL");
            $message = self::smarty('TaskID', $info['task_id'], $message);
            $message = self::smarty('CustomerName', $info['customer_name'], $message);
            $message = self::smarty('CustomerAddress', $info['delivery_address'], $message);
            $message = self::smarty('DeliveryDateTime', self::prettyDate($info['delivery_date']), $message);
            $message = self::smarty('PickUpDateTime', self::prettyDate($info['delivery_date']), $message);
            $message = self::smarty('DriverName', $info['driver_name'], $message);
            $message = self::smarty('CompanyName', getOptionA('website_title'), $message);
            //dump($message);
            sendEmail(
                $info['email_address'],
                '',
                str_replace("_", " ", $key),
                $message
            );
        }

        $key_is_enabled = getOption(Driver::getUserId(), $key . "_SMS");

        /*plan check*/
        if (!self::planCheckCanSendSMS(self::getPlanID())) {
            $key_is_enabled = 2;
        }

        if ($key_is_enabled == 1 && $info['contact_number'] != "") {
            $message = getOption(Driver::getUserId(), $key . "_SMS_TPL");
            $message = self::smarty('TaskID', $info['task_id'], $message);
            $message = self::smarty('CustomerName', $info['customer_name'], $message);
            $message = self::smarty('CustomerAddress', $info['delivery_address'], $message);
            $message = self::smarty('DeliveryDateTime', self::prettyDate($info['delivery_date']), $message);
            $message = self::smarty('PickUpDateTime', self::prettyDate($info['delivery_date']), $message);
            $message = self::smarty('DriverName', $info['driver_name'], $message);
            $message = self::smarty('CompanyName', getOptionA('website_title'), $message);
            if ($send_sms = Yii::app()->functions->sendSMS($info['contact_number'], $message)) {
                $params = array(
                    'to_number' => $info['driver_phone'],
                    'sms_text' => $message,
                    'msg' => isset($send_sms['msg']) ? $send_sms['msg'] : '',
                    'raw' => isset($send_sms['raw']) ? $send_sms['raw'] : '',
                    'provider' => $send_sms['sms_provider'],
                    'date_created' => date('c'),
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                );
                $db_ext->insertData("{{sms_logs}}", $params);
            }
        }
    }

    public static function sendNotificationAdmin($info = '', $reason = '')
    {
        $admin_email = self::getUserEmail($info['customer_id']);
        if (!empty($admin_email['email_address'])) {
            $message = "Task ID " . $info['task_id'] . " has been declined " . $info['driver_name'] . ", with reason: " . $reason;
            sendEmail($admin_email['email_address'], '', 'Task id has been declined.', $message);
        }
    }

    public static function RunPush($push_id = '')
    {
        $db = new DbExt;
        $status = '';

        $ring_tone_filename = 'food_song';
        $api_key = Yii::app()->functions->getOptionAdmin('push_api_key');

        $driver_ios_push_mode = getOptionA('ios_mode');
        $driver_ios_pass_phrase = getOptionA('ios_password');
        $driver_ios_push_dev_cer = getOptionA('ios_dev_certificate');
        $driver_ios_push_prod_cer = getOptionA('ios_prod_certificate');

        $DriverIOSPush = new DriverIOSPush;
        $DriverIOSPush->pass_prase = $driver_ios_pass_phrase;
        $DriverIOSPush->dev_certificate = $driver_ios_push_dev_cer;
        $DriverIOSPush->prod_certificate = $driver_ios_push_prod_cer;

        $production = $driver_ios_push_mode == "production" ? true : false;

        $and = '';
        if (!empty($push_id)) {
            $and = " AND push_id=" . self::q($push_id) . " ";
        }

        $stmt = "
		SELECT * FROM
		{{driver_pushlog}}
		WHERE
		status='pending'
		$and
		ORDER BY date_created ASC
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            foreach ($res as $val) {
                $push_id = $val['push_id'];
                if (!empty($val['device_id'])) {
                    if (!empty($api_key)) {
                        $message = array(
                            'title' => $val['push_title'],
                            'message' => $val['push_message'],
                            'soundname' => $ring_tone_filename,
                            'count' => 1,
                            'data' => array(
                                'push_type' => $val['push_type'],
                                //						   'order_id'=>$val['order_id'],
                                'actions' => $val['actions'],
                            )
                        );

                        //dump($message);

                        if (strtolower($val['device_platform']) == "android") {
                            $resp = AndroidPush::sendPush($api_key, $val['device_id'], $message);
                            if (is_array($resp) && count($resp) >= 1) {
                                if ($resp['success'] > 0) {
                                    $status = "process";
                                } else {
                                    $status = $resp['results'][0]['error'];
                                }
                            } else $status = "uknown push response";
                        } elseif (strtolower($val['device_platform']) == "ios") {

                            $additional_data = array(
                                'push_type' => $val['push_type'],
                                //'order_id'=>$val['order_id'],
                                'actions' => $val['actions'],
                            );
                            if ($DriverIOSPush->push(
                                $val['push_message'],
                                $val['device_id'],
                                $production,
                                $additional_data
                            )) {
                                $status = "process";
                            } else $status = $DriverIOSPush->get_msg();
                        } else {
                            $status = "Uknown device";
                        }
                    } else $status = "API key is empty";
                } else $status = "Device id is empty";

                $params = array(
                    'status' => $status,
                    'date_process' => date('c'),
                    'json_response' => isset($resp) ? json_encode($resp) : '',
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                );
                $db->updateData("{{driver_pushlog}}", $params, 'push_id', $push_id);
            }
        } //else echo 'no record to process';
    }

    public static function cleanString($text = '')
    {
        if (!empty($text)) {
            return stripslashes($text);
        }
        return;
    }

    public static function updateLastOnline($driver_id = '')
    {
        $params = array(
            'last_online' => strtotime("now"),
            'ip_address' => $_SERVER['REMOTE_ADDR']
        );
        $db = new DbExt;
        $db->updateData("{{driver}}", $params, 'driver_id', $driver_id);
    }

    public static function isDriverOnline($driver_id = '')
    {
        $db = new DbExt;
        $todays_date = date('Y-m-d');
        $time_now = time() - 200;
        $and = '';

        $and .= " AND on_duty ='1' ";
        $and .= " AND last_online >='$time_now' ";
        $and .= " AND last_login like '" . $todays_date . "%'";

        $stmt = "SELECT * FROM
        {{driver}}
        WHERE driver_id=" . self::q($driver_id) . "
        $and
        LIMIT 0,1
        ";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getUnAssignedDriver($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_assignment}}
		WHERE
		status='pending'
		AND task_id=" . self::q($task_id) . "
		ORDER BY assignment_id ASC
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getUnAssignedDriver2($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_assignment}}
		WHERE
		status='pending'
		AND task_id=" . self::q($task_id) . "
		ORDER BY assignment_id ASC
		LIMIT 0,5
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getUnAssignedDriver3($task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_assignment}}
		WHERE		
		task_id=" . self::q($task_id) . "
		ORDER BY assignment_id ASC
		LIMIT 0,10
		";
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getTaskByDriverNTask($task_id = '', $driver_id = '')
    {
        $res = '';
        $res2 = '';

        $db = new DbExt;
        $stmt = "
		SELECT a.*,a.driver_id as driver_id_task
		 FROM
		{{driver_task}} a
		WHERE
		task_id=" . self::q($task_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            $res = $res[0];
            $stmt2 = "
			SELECT 
			b.driver_id,
			concat(b.first_name,' ',b.last_name) as driver_name,
			b.device_id,
			b.phone as driver_phone,
			b.email as driver_email,
			b.device_platform,
			b.enabled_push,
			b.location_lat as driver_lat,
			b.location_lng as driver_lng
			FROM {{driver}} b
			WHERE
			driver_id=" . self::q($driver_id) . "
			LIMIT 0,1
			";
            if ($res2 = $db->rst($stmt2)) {
                $res2 = $res2[0];
                //dump($res2);
            }
            $merge_data = array_merge((array) $res, (array) $res2);
            return $merge_data;
        }
        return false;
    }

    public static function getTaskByDriverIDWithAssigment($driver_id = '', $delivery_date = '')
    {
        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");

        $or = "
		OR task_id IN (
		  select task_id 
		  from
		  {{driver_assignment}}
		  where
		  task_id=a.task_id
		  and
		  driver_id=" . self::q($driver_id) . "
		  and
		  status='process'
		  and
		  task_status='unassigned'
		)
		";

        $stmt = "SELECT a.* FROM
		{{driver_task_view}} a
		WHERE
		driver_id=" . self::q($driver_id) . "
		AND
		delivery_date LIKE '" . $delivery_date . "%'	
		$or
		ORDER BY delivery_date ASC
		";

        if (isset($_GET['debug'])) {
            dump($stmt);
        }
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getAssignmentByDriverTaskID($driver_id = '', $task_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{driver_assignment}}
		WHERE
		driver_id=" . self::q($driver_id) . "
		AND task_id=" . self::q($task_id) . "		
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function generateReports(
        $chart_type = '',
        $time = '',
        $team = '',
        $driver = '',
        $chart_option = '',
        $start_date = '',
        $end_date = ''
    ) {

        $db = new DbExt;
        $and = '';
        switch ($time) {
            case "week":
                $start = date('Y-m-d', strtotime("-7 day"));
                $end = date("Y-m-d", strtotime("+1 day"));
                $and .= " AND delivery_date BETWEEN '$start' AND '$end' ";
                break;

            case "month":
                $start = date('Y-m-d', strtotime("-30 day"));
                $end = date("Y-m-d", strtotime("+1 day"));
                $and .= " AND delivery_date BETWEEN '$start' AND '$end' ";
                break;

            case "custom":
                $and .= " AND delivery_date BETWEEN '$start_date' AND '$end_date' ";
                break;

            default:
                break;
        }

        if ($team > 0) {
            $and .= " AND team_id=" . self::q($team) . " ";
        }
        if ($driver > 0) {
            $and .= " AND driver_id=" . self::q($driver) . " ";
        }

        $and .= " AND driver_id <>'' ";

        $user_type = self::getUserType();
        if ($user_type == "merchant") {
            $user_id = self::getUserId();
            $and = " AND user_type='merchant' AND user_id=" . self::q($user_id) . " ";
        }


        $group = "GROUP BY DATE_FORMAT(delivery_date,'%Y-%m-%d'),status";
        if ($chart_option == "agent") {
            $group = "GROUP BY driver_name,status";
        }

        if ($chart_type == "task_completion") {
            $stmt = "
			SELECT DATE_FORMAT(a.delivery_date,'%Y-%m-%d') as delivery_date ,a.status,
			count(*) as total,
			(
			  select concat(first_name,' ',last_name)
			  from
			  {{driver}}
			  where
			  driver_id=a.driver_id
			) as driver_name
			FROM {{driver_task}} a
			WHERE customer_id=" . self::q(self::getUserId()) . "
			$and
			$group
			ORDER BY delivery_date ASC
			";
        } else {
            $stmt = "
			";
        }
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function generateTrack($time = '', $team = '', $driver = '', $start_date = '', $end_date = '')
    {
        $db = new DbExt;
        $and = '';
        switch ($time) {
            case "day":
                $start = date('Y-m-d', strtotime("-1 day"));
                $end = date("Y-m-d", strtotime("+1 day"));
                $and .= " AND delivery_date BETWEEN '$start' AND '$end' ";
                break;

            case "week":
                $start = date('Y-m-d', strtotime("-7 day"));
                $end = date("Y-m-d", strtotime("+1 day"));
                $and .= " AND delivery_date BETWEEN '$start' AND '$end' ";
                break;

            case "month":
                $start = date('Y-m-d', strtotime("-30 day"));
                $end = date("Y-m-d", strtotime("+1 day"));
                $and .= " AND delivery_date BETWEEN '$start' AND '$end' ";
                break;

            case "custom":
                $and .= " AND delivery_date BETWEEN '$start_date' AND '$end_date' ";
                break;

            default:
                break;
        }

        if ($team > 0) {
            $and .= " AND team_id=" . self::q($team) . " ";
        }
        if ($driver > 0) {
            $and .= " AND driver_id=" . self::q($driver) . " ";
        }

        $and .= " AND driver_id <>'' ";

        $stmt = "
		  SELECT *
		  FROM {{driver_task}}
		  WHERE customer_id=" . self::q(self::getUserId()) . "
		  $and
		  ORDER BY delivery_date ASC
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return false;
    }

    public static function getPlansByID($plan_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT * FROM
		{{plan}}
		WHERE
		plan_id=" . Driver::q($plan_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getPlansPrice($plan_id = '')
    {
        if ($res = self::getPlansByID($plan_id)) {
            $price = $res['price'];
            if ($res['promo_price'] > 0.0001) {
                $price = $res['promo_price'];
            }
            return $price;
        }
        return 0;
    }

    public static function planCheckCanAddDriver($customer_id = '', $plan_id = '')
    {
        $db = new DbExt;
        $stmt = "SELECT count(*) as total_driver,
		(
		select allowed_driver
		from {{plan}}
		where
		plan_id=" . self::q($plan_id) . "
		) as allowed_driver
		FROM {{driver}} 
		WHERE
		customer_id=" . self::q($customer_id) . "
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            $res = $res[0];

            if ($res['allowed_driver'] == "unlimited") {
                return true;
            }

            if ($res['allowed_driver'] > $res['total_driver']) {
                return true;
            }
        }
        return false;
    }

    public static function planCheckCAnAddTask($customer_id = '', $plan_id = '')
    {
        $db = new DbExt;
        $stmt = "SELECT count(*) as total_task,
		(
		select allowed_task
		from {{plan}}
		where
		plan_id=" . self::q($plan_id) . "
		) as allowed_task
		FROM {{driver_task}} 
		WHERE
		customer_id=" . self::q($customer_id) . "
		";
        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            $res = $res[0];

            if ($res['allowed_task'] == "unlimited") {
                return true;
            }

            if ($res['allowed_task'] > $res['total_task']) {
                return true;
            }
        }
        return false;
    }

    public static function planCheckCanSendSMS($plan_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM {{plan}}
		WHERE
		plan_id=" . self::q($plan_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            $res = $res[0];
            if ($res['with_sms'] == 1) {
                return true;
            }
        }
        return false;
    }

    public static function canCustomerSendSMS($customer_id = '')
    {
        $db = new DbExt;
        $stmt = "
		SELECT *
		FROM {{customer}}
		WHERE
		customer_id=" . self::q($customer_id) . "
		LIMIT 0,1
		";
        if ($res = $db->rst($stmt)) {
            $res = $res[0];
            if ($res['with_sms'] == 1) {
                return true;
            }
        }
        return false;
    }

    public static function getMobileTranslation()
    {
        $language_list = getOptionA('language_list');
        if (!empty($language_list)) {
            $language_list = json_decode($language_list, true);
        }

        $final_lang = '';
        $path = Yii::getPathOfAlias('webroot') . "/protected/messages";
        if (is_array($language_list) && count($language_list) >= 1) {
            foreach ($language_list as $val) {
                $lang_path = $path . "/$val/mobile.php";
                if (file_exists($lang_path)) {
                    $temp_lang = '';
                    $temp_lang = require_once($lang_path);
                    foreach ($temp_lang as $key => $val_lang) {
                        $final_lang[$key][$val] = $val_lang;
                    }
                }
            }
        }
        return $final_lang;
    }

    public static function TranslateStatus($key, $lng)
    {
        $newKey = "";
        if ($lng == 'id') {
            switch ($key) {
                case "acknowledged":
                    $newKey = "Diterima";
                    break;
                case "unassigned":
                    $newKey = "Belum ditugaskan";
                    break;
                case "assigned":
                    $newKey = "Ditugaskan";
                    break;
                case "started":
                    $newKey = "Mulai";
                    break;
                case "inprogress":
                    $newKey = "Dalam Proses";
                    break;
                case "failed":
                    $newKey = "Gagal";
                    break;
                case "declined":
                    $newKey = "Menolak";
                    break;
                case "cancelled":
                    $newKey = "Dibatalkan";
                    break;
                case "successful":
                    $newKey = "Selesai";
                    break;
            }
        } else {
            $newKey = $key;
        }

        return $newKey;
    }

    public static function getGPSSetting()
    {
        $params = '';

        $stationaryRadius = getOptionA('stationaryRadius');
        if (!empty($stationaryRadius)) {
            $params['stationaryRadius'] = $stationaryRadius;
        }
        $distanceFilter = getOptionA('distanceFilter');
        if (!empty($distanceFilter)) {
            $params['distanceFilter'] = $distanceFilter;
        }
        $desiredAccuracy = getOptionA('desiredAccuracy');
        if (!empty($desiredAccuracy)) {
            $params['desiredAccuracy'] = $desiredAccuracy;
        }
        $locationProvider = getOptionA('locationProvider');
        if (!empty($locationProvider)) {
            $params['locationProvider'] = $locationProvider;
        }
        $interval = getOptionA('interval');
        if (!empty($interval)) {
            $params['interval'] = $interval;
        }
        $fastestInterval = getOptionA('fastestInterval');
        if (!empty($fastestInterval)) {
            $params['fastestInterval'] = $fastestInterval;
        }
        $activitiesInterval = getOptionA('activitiesInterval');
        if (!empty($activitiesInterval)) {
            $params['activitiesInterval'] = $activitiesInterval;
        }
        $notificationTitle = getOptionA('notificationTitle');
        if (!empty($notificationTitle)) {
            $params['notificationTitle'] = $notificationTitle;
        }
        $notificationText = getOptionA('notificationText');
        if (!empty($notificationText)) {
            $params['notificationText'] = $notificationText;
        }
        $debug = getOptionA('debug');
        if (!empty($debug)) {
            $params['debug'] = $debug;
        }
        $StopDetection = getOptionA('StopDetection');
        if (!empty($StopDetection)) {
            $params['StopDetection'] = $StopDetection;
        }
        return $params;
    }

    public static function checkDriverUserExist($username = '', $driver_id = '')
    {
        $db = new DbExt;
        $and = "";
        if (is_numeric($driver_id)) {
            $and = " AND driver_id!=" . self::q($driver_id) . " ";
        }
        $stmt = "
		SELECT * FROM
		{{driver}}
		WHERE
		phone=" . self::q($username) . "
		$and
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function checkViewerUserExist($username = '', $viewer_id = '')
    {
        $db = new DbExt;
        $and = "";
        if (is_numeric($viewer_id)) {
            $and = " AND viewer_id!=" . self::q($viewer_id) . " ";
        }
        $stmt = "
		SELECT * FROM
		{{viewer}}
		WHERE
		username=" . self::q($username) . "
		$and
		";
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return false;
    }

    public static function getBetweenTime($start, $finish)
    {
        $selisih_waktu = $finish - $start;

        $jumlah_hari = floor($selisih_waktu / 86400);

        $sisa = $selisih_waktu % 86400;
        $jumlah_jam = floor($sisa / 3600);

        $sisa = $sisa % 3600;
        $jumlah_menit = floor($sisa / 60);

        $sisa = $sisa % 60;
        $jumlah_detik = floor($sisa / 1);

        $betweenTime = $jumlah_hari . ' Day ' . $jumlah_jam . ' Hour ' . $jumlah_menit . ' Minute ' . $jumlah_detik . ' Second';

        return $betweenTime;
    }

    public static function getBetweenDate($start, $finish)
    {
        $selisih_waktu = $finish - $start;

        return $selisih_waktu;
    }

    public static function getBetweenValue($selisih_waktu)
    {
        $jumlah_hari = floor($selisih_waktu / 86400);

        $sisa = $selisih_waktu % 86400;
        $jumlah_jam = floor($sisa / 3600);

        $sisa = $sisa % 3600;
        $jumlah_menit = floor($sisa / 60);

        $sisa = $sisa % 60;
        $jumlah_detik = floor($sisa / 1);

        $betweenTime = $jumlah_hari . ' Day ' . $jumlah_jam . ' Hour ' . $jumlah_menit . ' Minute ' . $jumlah_detik . ' Second';

        return $betweenTime;
    }

    public static function getDay($start, $finish)
    {
        $selisih_waktu = $finish - $start;

        $jumlah_hari = floor($selisih_waktu / 86400);

        return $jumlah_hari;
    }

    public static function getHour($start, $finish)
    {
        $selisih_waktu = $finish - $start;

        $sisa = $selisih_waktu % 86400;
        $jumlah_jam = floor($sisa / 3600);

        return $jumlah_jam;
    }

    public static function getMinute($start, $finish)
    {
        $selisih_waktu = $finish - $start;

        $jumlah_hari = floor($selisih_waktu / 86400);

        $sisa = $selisih_waktu % 86400;
        $jumlah_jam = floor($sisa / 3600);

        $sisa = $sisa % 3600;
        $jumlah_menit = floor($sisa / 60);

        return $jumlah_menit;
    }

    public static function getSecond($start, $finish)
    {
        $selisih_waktu = $finish - $start;

        $jumlah_hari = floor($selisih_waktu / 86400);

        $sisa = $selisih_waktu % 86400;
        $jumlah_jam = floor($sisa / 3600);

        $sisa = $sisa % 3600;
        $jumlah_menit = floor($sisa / 60);

        $sisa = $sisa % 60;
        $jumlah_detik = floor($sisa / 1);

        return $jumlah_detik;
    }

    public static function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public static function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $kilometers = $miles * 1.609344;
        return $kilometers;
        //return $miles;
    }

    public static function get_distance($lat1, $lat2, $long1, $long2)
    {
        /* These are two points in New York City */
        $point1 = array('lat' => $lat1, 'long' => $long1);
        $point2 = array('lat' => $lat2, 'long' => $long2);

        $distance = self::getDistanceBetweenPoints($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
        return $distance;
    }

    public static function getPathLength($coords)
    {
        $dist = 0;
        $last = "";

        for ($i = 0, $l = count($coords); $i < $l; $i++) {
            if ($last) {
                $dist += self::get_distance(
                    $coords[$i]['driver_location_lat'],
                    $coords[$i]['driver_location_lng'],
                    $last[0]['driver_location_lat'],
                    $last[0]['driver_location_lng']
                );
            }
            $last = array(
                'driver_location_lat' => $coords[$i]['driver_location_lat'],
                'driver_location_lng' => $coords[$i]['driver_location_lng']
            );
        }

        return $dist;
    }

    public static function createNewTask($token, $ip)
    {
        if (!$token = Self::getDriverByToken($token)) {
            return false;
        }
        $driver_id = $token['driver_id'];
        $driver_name = $token['first_name'] . " " . $token['last_name'];

        $DbExt = new DbExt;

        $params = array(
            'task_description' => '@Auto Generate Task',
            'trans_type' => 'delivery',
            'delivery_date' => date('c'),
            'team_id' => $token['team_id'],
            'driver_id' => $driver_id,
            'date_created' => date('c'),
            'ip_address' => $ip,
            'customer_id' => $token['customer_id'],
            'task_token' => Driver::random_string(10)
        );

        if (!empty($params['delivery_date'])) {
            $params['delivery_date'] = date("Y-m-d G:i", strtotime($params['delivery_date']));
        }

        if ($params['driver_id'] > 0) {
            $params['status'] = 'acknowledged';
        }

        if ($DbExt->insertData("{{driver_task}}", $params)) {
            $task_id = Yii::app()->db->getLastInsertID();

            // send notification to driver
            if ($info = Driver::getTaskId($task_id)) {

                $params_history = '';
                $params_history['ip_address'] = $_SERVER['REMOTE_ADDR'];
                $params_history['date_created'] = date('c');
                $params_history['task_id'] = $task_id;
                $params_history['driver_id'] = $driver_id;
                $params_history['driver_location_lat'] = isset($token['location_lat']) ? $token['location_lat'] : '';
                $params_history['driver_location_lng'] = isset($token['location_lng']) ? $token['location_lng'] : '';
                $remarks = Self::driverStatusPretty($driver_name, 'acknowledged');
                $params_history['status'] = 'acknowledged';
                $params_history['remarks'] = $remarks;
                // insert history
                $DbExt->insertData("{{task_history}}", $params_history);

                Self::sendDriverNotification('ASSIGN_TASK', $info);
            }

            return true;
        }

        return false;
    }

    public static function getContact($contact_id)
    {
        if ($contact_id > 0) {
            $db = new DbExt;
            $stmt = "
            SELECT * FROM
            {{contact}}
            WHERE
            contact_id=" . self::q($contact_id) . "
            LIMIT 0,1
            ";
            if ($res = $db->rst($stmt)) {
                return $res[0];
            }
            return false;
        }
        return false;
    }

    public static function getTaskOTAbyDate($key, $key2, $day, $month, $year)
    {
        if (empty($key)) {
            return '';
        }
        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT Arrived FROM
		  rpt_ota
		WHERE
		task_description=" . self::q($key) . "
		AND customer_name=" . self::q($key2) . "
		AND `day`=" . self::q($day) . " AND `month`=" . self::q($month) . " 
		AND `year`=" . self::q($year) . "
		LIMIT 0,1
		";
        /*dump($stmt);*/
        if ($res = $db->rst($stmt)) {
            return $res[0]['Arrived'];
        }
        return '';
    }

    public static function getTaskOTA($key, $key2, $key3)
    {
        if (empty($key)) {
            return '';
        }

        $and = '';
        if (!empty($key3)) {
            $and .= "AND Target=" . self::q($key3) . "  ";
        }

        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT * FROM
		  rpt_ota
		WHERE
		task_description=" . self::q($key) . "
		AND customer_name=" . self::q($key2) . "
		$and
		";
        /*dump($stmt);*/
        if ($res = $db->rst($stmt)) {
            return $res;
        }
        return '';
    }

    public static function getSuccessTask($key, $key2)
    {
        if (empty($key)) {
            return '';
        }

        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT 
          COUNT(task_id) AS total_success
        FROM {{driver_task_view}}
        WHERE driver_id=$key2 AND `status`='successful'
        $key
		";

        //dump($stmt);
        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return '';
    }

    public static function getSuccessTask1($key, $key2)
    {
        if (empty($key)) {
            return '';
        }

        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT 
          a.*,
          (SELECT `acknowledged` FROM rpt_task_history_pivot WHERE task_id=a.`task_id`) AS `task_acknowledged`,
          (SELECT `successful` FROM rpt_task_history_pivot WHERE task_id=a.`task_id`) AS `task_success`
        FROM {{driver_task_view}} a
        WHERE driver_id=$key2 AND `status`='successful'
        $key
		";

        //dump($stmt);
        $total_success = 0;
        $selisih = 0;
        if ($res = $db->rst($stmt)) {
            foreach ($res as $val) {
                if (!empty($val['task_acknowledged']) && !empty($val['task_success'])) {
                    $selisih = Self::getMinute(strtotime($val['task_acknowledged']), strtotime($val['task_success']));
                    if ($selisih > 5) {
                        $total_success++;
                    }
                }
            }

            return $total_success;
        }
        return '';
    }

    public static function getSuccessTask2($key, $key2)
    {
        if (empty($key)) {
            return '';
        }

        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT 
          *
        FROM {{driver_task_view}}
        WHERE driver_id=$key2 AND `status`='successful'
        $key
		";

        //dump($stmt);
        $total_success = 0;
        if ($res = $db->rst($stmt)) {

            foreach ($res as $val) {

                if ($history_details = self::getTaskHistory($val['task_id'])) {
                    $datetime = strtotime($res['date_created']);
                    $starttask = '';

                    foreach ($history_details as $val2) {
                        /* get diff time */
                        $betweentime = '';
                        if ($val2['status'] == "notes" || $val2['status'] == "photo" || $val2['status'] == "sign") {
                            //nothing to do..
                        } else {
                            if ($val2['status'] == "started") {
                                $starttask = strtotime($val2['date_created']);
                            }
                            if (!empty($datetime)) {
                                $create_history = strtotime($val2['date_created']);

                                if ($val2['status'] == "successful") {
                                    $betweentime = Self::getMinute($starttask, $create_history);
                                } else {
                                    $betweentime = Self::getMinute($datetime, $create_history);
                                }
                            }

                            $datetime = strtotime($val2['date_created']);
                        }

                        if ($betweentime > 5) {
                            $total_success++;
                        }
                    }
                }
            }

            return $total_success;
        }
        return '';
    }

    public static function getAllTask($key, $key2)
    {
        if (empty($key)) {
            return '';
        }

        $db = new DbExt;
        $db->qry("SET SQL_BIG_SELECTS=1");
        $stmt = "
        SELECT 
          COUNT(task_id) AS total_assigned
        FROM {{driver_task_view}}
        WHERE driver_id=$key2 
        $key
		";

        if ($res = $db->rst($stmt)) {
            return $res[0];
        }
        return '';
    }

    public static function getAngka($key)
    {
        if (empty($key)) {
            return false;
        }

        $angkaarray = array(1 => 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

        if ($key > 26) {
            $num_key = round($key / 26, 0);
            return $angkaarray[$key] . $angkaarray[$num_key];
        } elseif ($key <= 26) {
            return $angkaarray[$key];
        }
    }

    public static function getColumnLetter($number)
    {
        $prefix = '';
        $suffix = '';
        $prefNum = intval($number / 26);
        if ($number > 25) {
            $prefix = self::getColumnLetter($prefNum - 1);
        }
        $suffix = chr(fmod($number, 26) + 65);
        return $prefix . $suffix;
    }

    public static function cellColor($objPHPExcel, $cells, $color)
    {
        $objPHPExcel->setActiveSheetIndex(0)->getStyle($cells)->getFill()->applyFromArray(array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => $color
            )
        ));
    }

    public static function generateRandom($length = 64)
    {
        $keyspace = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        if ($length < 1) throw new \RangeException("Length must be a positive integer");

        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public static function sendEmail($to, $subject, $body_html, $body_plain = null, $attachment1 = null, $attachment2 = null)
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'srv169.niagahoster.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'no-reply@elog.id';                     //SMTP username
            $mail->Password   = 'f1R3Spir!T825';                               //SMTP password
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('no-reply@elog.id', 'Notification of WMSLite System');
            foreach($to as $r){
                $mail->addAddress($r['email'], $r['name']);     //Add a recipient
            }
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            if($attachment1 != NULL ){
                $mail->addAttachment($attachment1);
            }

            if($attachment2 != NULL ){
                $mail->addAttachment($attachment2);
            }

            //Content
            //$mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body_html;
            $mail->AltBody = $body_plain;

            $mail->send();
            //echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }

    public static function sendEmailTest()
    {
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'srv29.niagahoster.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'no-reply@elog.id';                     //SMTP username
            $mail->Password   = 'f1R3Spir!T825';                               //SMTP password
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('no-reply@elog.id', 'Notification of WMSLite System');
            $mail->addAddress('azh.azuharu@gmail.com', 'Fahmi');     //Add a recipient
            
            // $mail->addAddress('ellen@example.com');               //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments

            
            //$mail->addAttachment(Yii::app()->getBaseUrl(true)."/assets/images/category.png");    //Optional name

            //Content
            //$mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Test Subject at ' . date('Y-m-d H:i:s');
            $mail->Body    = 'Test Body at ' . date('Y-m-d H:i:s');
            $mail->AltBody = 'Test Alt Body at ' . date('Y-m-d H:i:s');

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}/* end class*/