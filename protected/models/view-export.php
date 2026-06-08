<?php
$db=new DbExt;

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

if(isset($_SESSION['kt_export_stmt'])){
    $stmt=$_SESSION['kt_export_stmt'];

    $pos=strpos($stmt,"LIMIT");
    $stmt=substr($stmt,0,$pos);

    //dump($stmt);
    $feed_data='';
    $filename=isset($_GET['filename'])?$_GET['filename']:'';

    switch ($filename) {

        case "customer_signup":

            $header=array(
                t("ID"),
                t("Name"),
                t("Mobile number"),
                t("Email address"),
                t("Plan"),
                t("Status"),
                t("Date"),
            );

            if($res=$db->rst($stmt)){
                foreach ($res as $val) {
                    $feed_data[]=array(
                        $val['customer_id'],
                        $val['first_name']." ".$val['last_name'],
                        $val['mobile_number'],
                        $val['email_address'],
                        $val['plan_name'],
                        $val['status'],
                        AdminFunctions::prettyDate($val['date_created'])
                    );
                }
            }
            $filename = $filename.'-'. date('Ymd') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "sales_report":

            $header=array(
                t("Date"),
                t("Trans Type"),
                t("Payment Provider"),
                t("Memo"),
                t("Total"),
                t("Transaction Ref")
            );

            if($res=$db->rst($stmt)){
                foreach ($res as $val) {
                    $feed_data[]=array(
                        AdminFunctions::prettyDate($val['date_created']),
                        $val['transaction_type'],
                        AdminFunctions::prettyGateway($val['payment_provider']),
                        $val['memo'],
                        prettyPrice($val['total_paid']),
                        $val['transaction_ref'],
                    );
                }
            }
            $filename = $filename.'-'. date('Ymd') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "sms_logs":

            $header=array(
                t("Date"),
                t("Mobile number"),
                t("SMS"),
                t("Provider"),
                t("Status"),
            );

            if($res=$db->rst($stmt)){
                foreach ($res as $val) {
                    $feed_data[]=array(
                        AdminFunctions::prettyDate($val['date_created']),
                        $val['to_number'],
                        $val['sms_text'],
                        $val['provider'],
                        $val['msg'],
                    );
                }
            }
            $filename = $filename.'-'. date('Ymd') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "email_logs":

            $header=array(
                t("Date"),
                t("Email address"),
                t("Subject"),
                t("Content"),
                t("Status"),
            );

            if($res=$db->rst($stmt)){
                foreach ($res as $val) {
                    $feed_data[]=array(
                        AdminFunctions::prettyDate($val['date_created']),
                        $val['email_address'],
                        $val['subject'],
                        $val['content'],
                        $val['status']
                    );
                }
            }
            $filename = $filename.'-'. date('Ymd') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "push_logs":

            $header=array(
                t("Date"),
                t("Device"),
                t("Device ID"),
                t("Push Title"),
                t("Content"),
                t("Type"),
                t("Status"),
            );

            if($res=$db->rst($stmt)){
                foreach ($res as $val) {
                    $feed_data[]=array(
                        AdminFunctions::prettyDate($val['date_created']),
                        $val['device_platform'],
                        $val['device_id'],
                        $val['push_title'],
                        $val['push_message'],
                        $val['push_type'],
                        $val['status']
                    );
                }
            }
            $filename = $filename.'-'. date('Ymd') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "report":
            $header=array(
                t("Task ID"),
                t("Status"),
                t("Description"),
                t("Type"),
                t("Customer Name"),
                t("Contact Number"),
                t("Email Address"),
                t("Delivery Date"),
                t("Delivery Address"),
                t("Date Created"),
                t("Driver Name"),
                t("Accept Date"),
                t("Accept Time"),
                t("Start Date"),
                t("Start Time"),
                t("Arrived Date"),
                t("Arrived Time"),
                t("Successful Date"),
                t("Successful Time"),
                t("Duration"),
                t("Mileage")
            );

            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $team = $_GET['team_selection'];
            $driver = $_GET['driver_selection'];
            $and='';
            $start='';
            $end='';
            $query='';

            switch ($_GET['time_selection'])
            {
                case 'week':
                    $start= date('Y-m-d', strtotime("-7 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";
                    break;

                case 'month':
                    $start= date('Y-m-d', strtotime("-30 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";
                    break;

                case 'custom':
                    $and.= " AND delivery_date BETWEEN '$start_date' AND '$end_date' ";
                    break;

                default:
                    break;
            }

            if ($team>0){
                $and.=" AND team_id=".Driver::q($team)." ";
            }
            if($driver>0){
                $and.=" AND driver_id=".Driver::q($driver)." ";
            }

            $and.=" AND driver_id <>'' ";

            $stmt="
            SELECT
              rpt_task.task_id,
              rpt_task.status,
              rpt_task.task_description,
              rpt_task.trans_type,
              rpt_task.customer_name,
              rpt_task.contact_number,
              rpt_task.email_address,
              rpt_task.delivery_date,
              rpt_task.delivery_address,
              rpt_task.date_created,
              rpt_task.driver_name,
              rpt_task.mileage,
              rpt_task_history_pivot.`acknowledged`,
              rpt_task_history_pivot.`started`,
              rpt_task_history_pivot.`inprogress`,
              rpt_task_history_pivot.`successful`
            FROM rpt_task INNER JOIN rpt_task_history_pivot 
            ON (rpt_task.`task_id`=rpt_task_history_pivot.`task_id`)
            WHERE rpt_task.`customer_id`=".Driver::q(ViewerFunctions::getUserId())."
            $and
            ";

            $totalWaktu='';

            if($result=$db->rst($stmt)){
                foreach ($result as $val) {

                    $starttask=strtotime($val['started']);
                    $endtask=strtotime($val['successful']);

                    $betweentime=Driver::getBetweenTime($starttask, $endtask);

                    $val['duration']=$betweentime;
                    //$val['mileage']='';

                    $val['accept_date']=Yii::app()->functions->FormatDateTime($val['acknowledged'],false);
                    $val['accept_time']=Yii::app()->functions->timeFormat($val['acknowledged']);
                    $val['start_date']=Yii::app()->functions->FormatDateTime($val['started'],false);
                    $val['start_time']=Yii::app()->functions->timeFormat($val['started']);
                    $val['arrived_date']=Yii::app()->functions->FormatDateTime($val['inprogress'],false);
                    $val['arrived_time']=Yii::app()->functions->timeFormat($val['inprogress']);
                    $val['successful_date']=Yii::app()->functions->FormatDateTime($val['successful'],false);
                    $val['successful_time']=Yii::app()->functions->timeFormat($val['successful']);

                    /*$trip_details=''; $trip_data='';
                    if($trip_details = Driver::getTaskTrip($val['task_id'])){
                        $totalMileage=0;
                        for($r = 0; $r < count($trip_details)-1; ++$r) {
                            $totalMileage += (round(Driver::get_distance($trip_details[$r]['driver_location_lat'],$trip_details[$r+1]['driver_location_lat'],$trip_details[$r]['driver_location_lng'],$trip_details[$r+1]['driver_location_lng']),2));
                        }
                        $val['mileage']=$totalMileage.' Km';
                    }*/

                    /*$trip_details=''; $trip_data='';
                    $trip='';
                    $countTrip = Driver::getCountTrip($val['task_id']);
                    if ($countTrip > 0) {
                        $totalMileage=0;

                        for($r = 0; $r < count($countTrip)-1; ++$r) {
                            $trip[$r]=array(
                                'lat'=>'',
                                'lng'=>''
                            );
                            $trip[$r+1]=array(
                                'lat'=>'',
                                'lng'=>''
                            );
                            $totalMileage += (round(Driver::get_distance($trip[$r]['lat'],$trip[$r+1]['lat'],
                                                                         $trip[$r]['lng'],$trip[$r+1]['lng']),2));
                        }
                    }*/

                    $feed_data[]=array(
                        $val['task_id'],
                        $val['status'],
                        $val['task_description'],
                        $val['trans_type'],
                        $val['customer_name'],
                        $val['contact_number'],
                        $val['email_address'],
                        Yii::app()->functions->FormatDateTime($val['delivery_date']),
                        $val['delivery_address'],
                        Yii::app()->functions->FormatDateTime($val['date_created']),
                        $val['driver_name'],
                        $val['accept_date'],
                        $val['accept_time'],
                        $val['start_date'],
                        $val['start_time'],
                        $val['arrived_date'],
                        $val['arrived_time'],
                        $val['successful_date'],
                        $val['successful_time'],
                        $val['duration'],
                        $val['mileage'].' Km'
                    );
                }

                $feed_data[]=array(
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Total',
                    Driver::getBetweenValue($totalWaktu),
                    ''
                );
            }

            $filename = $filename.'-'. date('YmdHis') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "ota_report1":
            $header=array(
                t("No"),
                t("Dist Rep"),
                t("Sub Outlet Name"),
                t("Target")
            );

            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $team = $_GET['team_selection'];
            $driver = $_GET['driver_selection'];
            $and='';
            $start='';
            $end='';
            $query='';

            switch ($_GET['time_selection'])
            {
                case 'week':
                    $start= date('Y-m-d', strtotime("-7 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";
                    break;

                case 'month':
                    $start= date('Y-m-d', strtotime("-30 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";
                    break;

                case 'custom':
                    $and.= " AND delivery_date BETWEEN '$start_date' AND '$end_date' ";
                    break;

                default:
                    break;
            }

            $stmt="
            SELECT
              customer_id, task_description, customer_name, Target
            FROM rpt_ota
            WHERE `customer_id`=".Driver::q(ViewerFunctions::getUserId())."
            $and
            GROUP BY customer_id, task_description, customer_name
            ORDER BY customer_id, task_description, customer_name
            ";

            $starttask=strtotime($start);
            $endtask=strtotime($end);

            $countDate = Driver::getDay($starttask, $endtask);
            for($r = 0; $r < $countDate; ++$r){
                $insertDate=date('d-M-Y', strtotime("-".(($countDate-1) - $r)." day") );
                array_push($header, $insertDate);
            }

            if($result=$db->rst($stmt)){
                $No = 1;
                $iNum = 0;
                foreach ($result as $val) {
                    $feed_data[$iNum]=array(
                        $No++,
                        $val['task_description'],
                        $val['customer_name'],
                        Yii::app()->functions->timeFormat($val['Target'], true)
                    );

                    for($r = 0; $r < $countDate; ++$r){
                        $day = date('d', strtotime("-".(($countDate-1) - $r)." day"));
                        $month = date('m', strtotime("-".(($countDate-1) - $r)." day"));
                        $year = date('Y', strtotime("-".(($countDate-1) - $r)." day"));

                        $getOTA = Driver::getTaskOTAbyDate($val['task_description'], $val['customer_name'],
                            $day, $month, $year);
                        array_push($feed_data[$iNum], Yii::app()->functions->timeFormat($getOTA['Arrived'], true));
                    }

                    $iNum++;
                }
                /*dump($feed_data);*/
            }

            $filename = $filename.'-'. date('YmdHis') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        case "ota_report":
            Yii::import('application.extensions.PHPExcel.PHPExcel');
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();

            // Set document properties
            $objPHPExcel->getProperties()->setCreator("eLogistik System Indonesia")
                ->setLastModifiedBy('eLogistik System Indonesia')
                ->setTitle("Report OTA");


            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'No')
                ->setCellValue('B1', 'Dist Rep')
                ->setCellValue('C1', 'Sub Outlet Name')
                ->setCellValue('D1', 'Target');

            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $team = $_GET['team_selection'];
            $driver = $_GET['driver_selection'];
            $and='';
            $start='';
            $end='';
            $query='';
            $starttask='';
            $endtask='';
            $coloumExcel='';

            switch ($_GET['time_selection'])
            {
                case 'week':
                    $start= date('Y-m-d', strtotime("-7 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";

                    $starttask=strtotime($start);
                    $endtask=strtotime($end);
                    break;

                case 'month':
                    $start= date('Y-m-d', strtotime("-30 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";

                    $starttask=strtotime($start);
                    $endtask=strtotime($end);
                    break;

                case 'custom':
                    $and.= " AND delivery_date BETWEEN '$start_date' AND '$end_date' ";

                    $starttask=strtotime($start_date);
                    $endtask=strtotime($end_date);
                    break;

                default:
                    break;
            }

            $stmt="
            SELECT
              customer_id, task_description, customer_name, Target, delivery_date
            FROM rpt_ota
            WHERE `customer_id`=".Driver::q(ViewerFunctions::getUserId())."
            $and
            GROUP BY customer_id, task_description, customer_name, Target
            ORDER BY customer_id, task_description, Target
            ";

            $countDate = Driver::getDay($starttask, $endtask);

            $limit = $countDate;
            if($_GET['time_selection']!='custom') {
                for ($i = 0, $j = 'E'; $i < ($limit+1); $i++, $j++) {
                    $insertDate = date('d-M-Y', strtotime("-" . (($limit - 1) - $i) . " day"));
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($j . '1', $insertDate);
                    $coloumExcel = $j;
                }
            } else {
                for ($i = 0, $j = 'E'; $i < ($limit+1); $i++, $j++) {
                    $insertDate = date("d-M-Y", strtotime($start_date ."+".$i." days"));
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue($j . '1', $insertDate);
                }
            }

            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue(Driver::getColumnLetter($countDate+5).'1', 'Average')
                ->setCellValue(Driver::getColumnLetter($countDate+6).'1', 'Performance')
                ->setCellValue(Driver::getColumnLetter($countDate+7).'1', 'Difference (minute)')
                ->setCellValue(Driver::getColumnLetter($countDate+9).'1', 'Delivery')
                ->setCellValue(Driver::getColumnLetter($countDate+10).'1', 'Achievement')
                ->setCellValue(Driver::getColumnLetter($countDate+11).'1', '%');

            // Add some data
            if($result=$db->rst($stmt)){
                $No = 1;
                $iNum = 0;
                $GroupRoute = '';
                $startMerge = '';
                $endMerge = '';

                foreach ($result as $val) {
                    $times = array();
                    $delivery = array();
                    $prestasi = array();

                    if (!empty($GroupRoute) && $GroupRoute!=$val['task_description']) {

                        if(empty($endMerge)){
                            $endMerge = ($No);
                        }

                        $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('A'.($No+1), '')
                            ->mergeCells('A'.($No+1).':'.Driver::getColumnLetter($countDate+11).($No+1))
                            ->mergeCells('B'.$startMerge.':B'.$endMerge)
                            ->getStyle('B'.$startMerge.':B'.$endMerge)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                        $objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$startMerge.':B'.$endMerge)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                        $startMerge = '';
                        $endMerge = '';

                        $No++;
                    }

                    if(empty($startMerge)){
                        $startMerge = ($No+1);
                    }

                    $GroupRoute = $val['task_description'];
                    $TargetOta = $val['Target'];
                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A'.($No+1), $No)
                        ->setCellValue('B'.($No+1), $val['task_description'])
                        ->setCellValue('C'.($No+1), $val['customer_name'])
                        ->setCellValue('D'.($No+1), Yii::app()->functions->timeFormat($TargetOta));

                    if ($res = Driver::getTaskOTA($val['task_description'], $val['customer_name'], $TargetOta)) {
                        foreach ($res as $value) {

                            $getOTA = $value['Arrived'];

                            if($_GET['time_selection']!='custom'){
                                for($r = 0; $r < $countDate; ++$r){
                                    $day = date('d', strtotime("-".(($countDate-1) - $r)." day"));
                                    $month = date('m', strtotime("-".(($countDate-1) - $r)." day"));
                                    $year = date('Y', strtotime("-".(($countDate-1) - $r)." day"));

                                    if($value['day']==$day && $value['month']==$month && $value['year']==$year){
                                        $objPHPExcel->setActiveSheetIndex(0)
                                            ->setCellValue(Driver::getColumnLetter($r+4).($No+1),
                                                Yii::app()->functions->timeFormat($getOTA));

                                        if(!empty($getOTA)){
                                            array_push($times, $getOTA);

                                            if (strtotime($value['Arrived1']) > strtotime($val['delivery_date'])){
                                                Driver::cellColor($objPHPExcel,Driver::getColumnLetter($r+4).($No+1), 'f28a8c');
                                            } else {
                                                Driver::cellColor($objPHPExcel,Driver::getColumnLetter($r+4).($No+1), '93fc8f');
                                                array_push($prestasi, $getOTA);
                                            }
                                        }
                                    }
                                }
                            } else {
                                for($r = 0; $r < ($countDate+1); ++$r){
                                    $day = date("d", strtotime($start_date ."+".$r." days"));
                                    $month = date("m", strtotime($start_date ."+".$r." days"));
                                    $year = date("Y", strtotime($start_date ."+".$r." days"));

                                    if($value['day']==$day && $value['month']==$month && $value['year']==$year){
                                        $objPHPExcel->setActiveSheetIndex(0)
                                            ->setCellValue(Driver::getColumnLetter($r+4).($No+1),
                                                Yii::app()->functions->timeFormat($getOTA));

                                        if(!empty($getOTA)){
                                            array_push($times, $getOTA);

                                            if (strtotime($value['Arrived1']) > strtotime($val['delivery_date'])){
                                                Driver::cellColor($objPHPExcel,Driver::getColumnLetter($r+4).($No+1), 'f28a8c');
                                            } else {
                                                Driver::cellColor($objPHPExcel,Driver::getColumnLetter($r+4).($No+1), '93fc8f');
                                                array_push($prestasi, $getOTA);
                                            }
                                        }
                                    }
                                }

                            }
                        }
                    }

                    $totaltime = '';
                    foreach($times as $time){
                        $timestamp = strtotime($time);
                        $totaltime += $timestamp;
                    }

                    $average_time = ($totaltime/count($times));
                    $prosentase = round(((count($prestasi)/count($times)) * 100),0);
                    //$performace = date('G:i', $TargetOta)/date('G:i', $average_time);
                    $difference = Driver::getMinute(strtotime($TargetOta), $average_time);

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue(Driver::getColumnLetter($countDate+5).($No+1), date('G:i',$average_time))
                        ->setCellValue(Driver::getColumnLetter($countDate+6).($No+1), '=D'.($No+1).'/'.Driver::getColumnLetter($countDate+5).($No+1))
                        ->setCellValue(Driver::getColumnLetter($countDate+7).($No+1), $difference)
                        ->setCellValue(Driver::getColumnLetter($countDate+9).($No+1), count($times))
                        ->setCellValue(Driver::getColumnLetter($countDate+10).($No+1), count($prestasi))
                        ->setCellValue(Driver::getColumnLetter($countDate+11).($No+1), $prosentase);

                    if ($difference < 0){
                        Driver::cellColor($objPHPExcel,Driver::getColumnLetter($countDate+7).($No+1), '93fc8f');
                    } else Driver::cellColor($objPHPExcel,Driver::getColumnLetter($countDate+7).($No+1), 'f28a8c');

                    $objPHPExcel->setActiveSheetIndex(0)
                        ->getStyle(Driver::getColumnLetter($countDate+6).($No+1))
                        ->getNumberFormat()
                        ->applyFromArray([
                            "code" => PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE
                        ]);

                    $No++;
                }

                // Set Border
                $styleArray = array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        )
                    )
                );
                $objPHPExcel->getActiveSheet()
                    ->getStyle('A1:'.Driver::getColumnLetter($countDate+7).($No))
                    ->applyFromArray($styleArray);
                $objPHPExcel->getActiveSheet()
                    ->getStyle(Driver::getColumnLetter($countDate+9).'1:'.Driver::getColumnLetter($countDate+11).($No))
                    ->applyFromArray($styleArray);
                unset($styleArray);

                // Set Auto Size
                foreach(range('A', Driver::getColumnLetter($countDate+11)) as $columnID)
                {
                    $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }
            }

            // Rename worksheet
            // $objPHPExcel->getActiveSheet()->setTitle('Simple');

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);

            // Redirect output to a client’s web browser (Excel5)
            $filename = $filename.'-'. date('YmdHis') .'.xls';
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');

            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            break;

        case "report1":
            $header=array(
                t("Task ID"),
                t("Status"),
                t("Description"),
                t("Type"),
                t("Customer Name"),
                t("Contact Number"),
                t("Email Address"),
                t("Delivery Date"),
                t("Delivery Address"),
                t("Date Created"),
                t("Driver Name"),
                t("Accept Date"),
                t("Accept Time"),
                t("Start Date"),
                t("Start Time"),
                t("Arrived Date"),
                t("Arrived Time"),
                t("Successful Date"),
                t("Successful Time"),
                t("Duration"),
                t("Mileage")
            );

            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
            $team = $_GET['team_selection'];
            $driver = $_GET['driver_selection'];
            $and='';
            $start='';
            $end='';
            $query='';

            switch ($_GET['time_selection'])
            {
                case 'week':
                    $start= date('Y-m-d', strtotime("-7 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";
                    break;

                case 'month':
                    $start= date('Y-m-d', strtotime("-30 day") );
                    $end=date("Y-m-d", strtotime("+1 day"));
                    $and.= " AND delivery_date BETWEEN '$start' AND '$end' ";
                    break;

                case 'custom':
                    $and.= " AND delivery_date BETWEEN '$start_date' AND '$end_date' ";
                    break;

                default:
                    break;
            }

            if ($team>0){
                $and.=" AND team_id=".Driver::q($team)." ";
            }
            if($driver>0){
                $and.=" AND driver_id=".Driver::q($driver)." ";
            }

            $and.=" AND driver_id <>'' ";

            $stmt="
			SELECT task_id,
			(
			  select concat(first_name,' ',last_name)
			  from
			  {{driver}}
			  where
			  driver_id=a.driver_id
			) as driver_name,task_description,
			trans_type,delivery_date,delivery_address
			FROM {{driver_task}} a
			WHERE customer_id=".Driver::q(ViewerFunctions::getUserId())."
			$and
			ORDER BY delivery_date ASC
			";

            $totalWaktu='';

            if($result=$db->rst($stmt)){
                foreach ($result as $val) {
                    if ( $res=Driver::getTaskId($val['task_id'])){

                        $res['accept']='';
                        $res['start']='';
                        $res['arrived']='';
                        $res['successful']='';
                        $res['duration']='';
                        $res['mileage']='';

                        $history_details='';
                        if($history_details = Driver::getTaskHistory($res['task_id'])){
                            $datecreate = strtotime($res['date_created']);
                            $datetime = strtotime($res['date_created']);
                            $starttask='';

                            foreach ($history_details as $valh){
                                /* get diff time */
                                $betweentime='';

                                switch ($valh['status'])
                                {
                                    case "acknowledged":
                                        $starttask=strtotime($valh['date_created']);
                                        $res['accept_date']=Yii::app()->functions->FormatDateTime($valh['date_created'],false);
                                        $res['accept_time']=Yii::app()->functions->timeFormat($valh['date_created']);
                                        break;

                                    case "started":
                                        $res['start_date']=Yii::app()->functions->FormatDateTime($valh['date_created'],false);
                                        $res['start_time']=Yii::app()->functions->timeFormat($valh['date_created']);
                                        break;

                                    case "inprogress":
                                        $res['arrived_date']=Yii::app()->functions->FormatDateTime($valh['date_created'],false);
                                        $res['arrived_time']=Yii::app()->functions->timeFormat($valh['date_created']);
                                        break;

                                    case "successful":
                                        $res['successful_date']=Yii::app()->functions->FormatDateTime($valh['date_created'],false);
                                        $res['successful_time']=Yii::app()->functions->timeFormat($valh['date_created']);
                                        break;

                                    default:
                                        break;
                                }

                                if(!empty($datetime)){
                                    $create_history=strtotime($valh['date_created']);

                                    if($valh['status']=="successful"){
                                        if(!empty($starttask)){
                                            $betweentime=Driver::getBetweenTime($starttask,$create_history);
                                            $totalWaktu += Driver::getBetweenDate($starttask,$create_history);
                                        } else {
                                            $betweentime=Driver::getBetweenTime($datecreate,$create_history);
                                            $totalWaktu += Driver::getDay($starttask,$create_history);
                                        }

                                        $res['duration']=$betweentime;
                                    } else {
                                        $betweentime=Driver::getBetweenTime($datetime,$create_history);
                                    }
                                }

                                $datetime=strtotime($valh['date_created']);

                                $valh['between_time']=$betweentime;
                            }

                            $trip_details=''; $trip_data='';
                            $startlong = ''; $finishlong = '';
                            if($trip_details = Driver::getTaskTrip($res['task_id'])){
                                $totalMileage=0;
                                for($r = 0; $r < count($trip_details)-1; ++$r) {
                                    $totalMileage += (round(Driver::get_distance($trip_details[$r]['driver_location_lat'],$trip_details[$r+1]['driver_location_lat'],$trip_details[$r]['driver_location_lng'],$trip_details[$r+1]['driver_location_lng']),2));
                                }
                                $res['mileage']=$totalMileage.' Km';
                            }
                            $res['mileage']='';

                        }

                        $feed_data[]=array(
                            $res['task_id'],
                            $res['status'],
                            $res['task_description'],
                            $res['trans_type'],
                            $res['customer_name'],
                            $res['contact_number'],
                            $res['email_address'],
                            Yii::app()->functions->FormatDateTime($res['delivery_date']),
                            $res['delivery_address'],
                            Yii::app()->functions->FormatDateTime($res['date_created']),
                            $res['driver_name'],
                            $res['accept_date'],
                            $res['accept_time'],
                            $res['start_date'],
                            $res['start_time'],
                            $res['arrived_date'],
                            $res['arrived_time'],
                            $res['successful_date'],
                            $res['successful_time'],
                            $res['duration'],
                            $res['mileage']
                        );
                    }
                }

                $feed_data[]=array(
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'Total',
                    Driver::getBetweenValue($totalWaktu),
                    ''
                );
            }

            $filename = $filename.'-'. date('YmdHis') .'.csv';
            $excel  = new ExcelFormat($filename);
            $excel->addHeaders($header);
            $excel->setData($feed_data);
            $excel->prepareExcel();
            Yii::app()->end();
            break;

        default:
            break;
    }
}