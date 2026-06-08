<?php

class SchenkerCommand extends CConsoleCommand
{

    //php yiic.php sync inbound
    public $connection;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        // $this->connection = new CDbConnection('mysql:host=localhost;dbname=wmslite', 'root', '');
        $this->connection = new CDbConnection('mysql:host=localhost;dbname=wmslite', 'root', 'hS8ld9934#mj^d');
        $this->connection->active = true;
    }

    public function actionOutbound()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'all_outbound'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'notes' => 'Sedang proses'
            ], 'sync_name=:sync_name', array(':sync_name' => 'all_outbound'));

            //ambil offset terakhit
            $offset = $sync_status['offset'] ?? 0;
            //$offset = 0;



            echo 'processing offset: ' . $offset . PHP_EOL;

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
                                "pagesize": "100",
                                "offset": "' . $offset . '",
                                "orderBy1": "asc",
                                "sort1": "orderKey",
                                "recordCount": "99999999999"
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
                die(var_dump($error_msg));
                return $error_msg;
            } else {
                $result = json_decode($response);

                if (!empty($result->result->shipmentOrder[0]->shipmentOrderHeader)) {

                    $headers = $result->result->shipmentOrder[0]->shipmentOrderHeader;
                    if (count($headers) == 100) {
                        $offset = $offset + 100;
                    }

                    foreach ($headers as $h) {

                        //if (!empty($h->actualShipDate)) {

                        echo 'processing header orderKey: ' . $h->orderKey . PHP_EOL;

                        //cek sdh ada di db
                        $cek = $this->connection->createCommand()
                            ->select('*')
                            ->from('el_schenker_outbound_header')
                            ->where('orderKey=:orderKey', array(':orderKey' => $h->orderKey))
                            ->queryRow();

                        if (empty($cek)) {
                            // echo 'insert header '. $h->orderKey. PHP_EOL;
                            #1. INSERT data Schenker ke el_schenker_outbound_header
                            $this->connection->createCommand()->insert('el_schenker_outbound_header', [
                                'ccompany' => $h->ccompany ?? '',
                                'status' => $h->status ?? '',
                                'serialKey' => $h->serialKey ?? '',
                                'orderKey' => $h->orderKey ?? '',
                                'externOrderKey' => $h->externOrderKey ?? '',
                                'orderDate' => $h->orderDate ?? '',
                                'priority' => $h->priority ?? '',
                                'carrierName' => $h->carrierName ?? '',
                                'buyerPO' => $h->buyerPO ?? '',
                                'type' => $h->type ?? '',
                                'actualShipDate' => $h->actualShipDate ?? '',
                                'externalOrderKey2' => $h->externalOrderKey2 ?? '',
                                'storerKey' => $h->storerKey ?? '',
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);

                            $header_id = $this->connection->getLastInsertID();


                            #2. INSERT data Schenker ke el_schenker_outbound_detail
                            $details = $h->shipmentOrderDetail;
                            foreach ($details as $d) {
                                if (!empty(trim($d->sku))) {
                                    if (!empty(trim($d->lottable07))) {

                                        echo 'insert detail ' . $d->lottable07 . PHP_EOL;
                                        $this->connection->createCommand()->insert('el_schenker_outbound_detail', [
                                            'header_id' => $header_id,
                                            'susr1' => $d->susr1 ?? '',
                                            'orderLineNumber' => $d->orderLineNumber ?? '',
                                            'externLineNo' => $d->externLineNo ?? '',
                                            'storerKey' => $d->storerKey ?? '',
                                            'sku' => $d->sku ?? '',
                                            'packKey' => $d->packKey ?? '',
                                            'uom' => $d->uom ?? '',
                                            'openQty' => $d->openQty ?? '',
                                            'orderKey' => $d->orderKey ?? '',
                                            'orderDetailSysId' => $d->orderDetailSysId ?? '',
                                            'externOrderKey' => $d->externOrderKey ?? '',
                                            'status' => $d->status ?? '',
                                            'lottable07' => $d->lottable07 ?? '',
                                            'lottable08' => $d->lottable08 ?? '',
                                            'shippedQty' => $d->shippedQty ?? '',
                                            'created_at' => date('Y-m-d H:i:s'),
                                        ]);
                                    }
                                }
                            }
                        }
                        //}
                    }
                }
            }

            $notes =  'Sync done' . PHP_EOL;
            echo $notes . PHP_EOL;

            //update log done all_outbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
                'offset' => $offset,
            ], 'sync_name=:sync_name', array(':sync_name' => 'all_outbound'));
        } else {
            $notes =  'Masih ada process sync all_outbound yang sedang berjalan' . PHP_EOL;
            echo $notes . PHP_EOL;
        }
    }

    public function actionPick()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'all_pick'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'notes' => 'Sedang proses'
            ], 'sync_name=:sync_name', array(':sync_name' => 'all_pick'));

            $sql = "SELECT externOrderKey 
                    FROM el_schenker_outbound_header h
                    WHERE NOT EXISTS (SELECT 1 FROM el_schenker_outbound_pick p WHERE p.orderKey=h.orderKey AND p.lot <> '' AND p.lot IS NOT NULL)";
            $to_sync = $this->connection->createCommand($sql)->queryAll();

            foreach ($to_sync as $r) {

                //test
                // if($r['externOrderKey'] != '5363333'){
                //     break;
                // }

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/shipment/listpicks',
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
                                "externOrderKey": "' . $r['externOrderKey'] . '"
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
                    die(var_dump($error_msg));
                    return $error_msg;
                } else {
                    $result = json_decode($response);

                    if (!empty($result->result->shipmentOrder[0]->shipmentOrderHeader)) {

                        //die(var_dump($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->ccompany));

                        $headers = $result->result->shipmentOrder[0]->shipmentOrderHeader;
                        foreach ($headers as $h) {

                            if (!empty($h->actualShipDate)) {
                                //cek sdh ada di db
                                // $cek = $this->connection->createCommand()
                                //     ->select('*')
                                //     ->from('el_schenker_outbound_pick')
                                //     ->where('orderKey=:orderKey', array(':orderKey' => $h->orderKey))
                                //     ->queryAll();

                                //delete existing
                                $this->connection->createCommand()->delete('el_schenker_outbound_pick', 'orderKey=:id', array(
                                    ':id' => $h->orderKey
                                ));

                                $shipmentOrderDetail = $h->shipmentOrderDetail;
                                foreach ($shipmentOrderDetail as $s) {
                                    foreach ($s->PickDetail as $p) {
                                        $this->connection->createCommand()->insert('el_schenker_outbound_pick', [
                                            'orderKey' => empty($p->orderKey) ? NULL : $p->orderKey,
                                            'lot' => empty($p->lot) ? NULL : $p->lot,
                                            'created_at' => date('Y-m-d H:i:s'),
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }







            $notes =  'Sync done' . PHP_EOL;
            echo $notes . PHP_EOL;

            //update log done all_pick
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
            ], 'sync_name=:sync_name', array(':sync_name' => 'all_pick'));
        } else {
            $notes =  'Masih ada process sync all_pick yang sedang berjalan' . PHP_EOL;
            echo $notes . PHP_EOL;
        }
    }

    public function actionInbound()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'all_inbound'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'notes' => 'Sedang proses'
            ], 'sync_name=:sync_name', array(':sync_name' => 'all_inbound'));

            //ambil offset terakhit
            $offset = $sync_status['offset'] ?? 0;
            $offset_start = $offset;
            //$offset = 0;



            echo 'processing offset: ' . $offset . PHP_EOL;

            // if(($offset - $offset_start) > 50){
            //     break;
            // }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/receipt/list',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                        "advancedShipNotice": [
                        {
                            "advancedShipNoticeHeader": [
                            {
                                "storerKey": "IDGEHC",
                                "pagesize": "100",
                                "offset": "' . $offset . '",
                                "orderBy1": "asc",
                                "sort1": "receiptKey",
                                "recordCount": "99999999999"
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
                die(var_dump($error_msg));
                return $error_msg;
            } else {
                $result = json_decode($response);

                if (!empty($result->result->advancedShipNotice[0]->advancedShipNoticeHeader)) {

                    $headers = $result->result->advancedShipNotice[0]->advancedShipNoticeHeader;
                    if (count($headers) == 100) {
                        $offset = $offset + 100;
                    }

                    foreach ($headers as $h) {

                        //if (!empty($h->actualShipDate)) {

                        echo 'processing header receiptKey: ' . $h->receiptKey . PHP_EOL;

                        //cek sdh ada di db
                        $cek = $this->connection->createCommand()
                            ->select('*')
                            ->from('el_schenker_inbound_header')
                            ->where('receiptKey=:receiptKey', array(':receiptKey' => $h->receiptKey))
                            ->queryRow();

                        if (empty($cek)) {
                            // echo 'insert header '. $h->receiptKey. PHP_EOL;
                            #1. INSERT data Schenker ke el_schenker_inbound_header
                            $this->connection->createCommand()->insert('el_schenker_inbound_header', [
                                'susr1' => $h->susr1 ?? '',
                                'susr2' => $h->susr2 ?? '',
                                'status' => $h->status ?? '',
                                'serialKey' => $h->serialKey ?? '',
                                'receiptKey' => $h->receiptKey ?? '',
                                'externReceiptKey' => $h->externReceiptKey ?? '',
                                'receiptDate' => $h->receiptDate ?? '',
                                'carrierName' => $h->carrierName ?? '',
                                'type' => $h->type ?? '',
                                'actualShipDate' => $h->actualShipDate ?? '',
                                'adviceDate' => $h->adviceDate ?? '',
                                'externReceiptKey2' => $h->externReceiptKey2 ?? '',
                                'storerKey' => $h->storerKey ?? '',
                                'created_at' => date('Y-m-d H:i:s'),
                            ]);

                            $header_id = $this->connection->getLastInsertID();


                            #2. INSERT data Schenker ke el_schenker_inbound_detail
                            $details = $h->advancedShipNoticeDetail;
                            foreach ($details as $d) {

                                $dateReceived = $d->dateReceived;
                                if (!empty($dateReceived)) {
                                    $date = DateTime::createFromFormat('d/m/Y H:i:s', $dateReceived);
                                    $dateReceived = $date->format('Y-m-d H:i:s');
                                }

                                if (($d->status == 11 || $d->status == 9) && $d->qtyReceived != "0.0" && !empty(trim($d->lottable03))) {


                                    echo 'insert detail ' . $d->lottable07 . PHP_EOL;
                                    $this->connection->createCommand()->insert('el_schenker_inbound_detail', [
                                        'header_id' => $header_id,
                                        'susr1' => $d->susr1 ?? '',
                                        'susr2' => $d->susr2 ?? '',
                                        'receiptLineNumber' => $d->receiptLineNumber ?? '',
                                        'externLineNo' => $d->externLineNo ?? '',
                                        'storerKey' => $d->storerKey ?? '',
                                        'sku' => $d->sku ?? '',
                                        'toId' => $d->toId ?? '',
                                        'qtyExpected' => $d->qtyExpected ?? '',
                                        'qtyAdjusted' => $d->qtyAdjusted ?? '',
                                        'qtyReceived' => $d->qtyReceived ?? '',
                                        'toLoc' => $d->toLoc ?? '',
                                        'toLot' => $d->toLot ?? '',
                                        'receiptKey' => $d->receiptKey ?? '',
                                        'uom' => $d->uom ?? '',
                                        'externReceiptKey' => $d->externReceiptKey ?? '',
                                        'status' => $d->status ?? '',
                                        'dateReceived' => $d->dateReceived ?? '',
                                        'conditionCode' => $d->conditionCode ?? '',
                                        'lottable02' => $d->lottable02 ?? '',
                                        'lottable03' => $d->lottable03 ?? '',
                                        'lottable07' => $d->lottable07 ?? '',
                                        'lottable08' => $d->lottable08 ?? '',
                                        'created_at' => date('Y-m-d H:i:s'),
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $notes =  'Sync done' . PHP_EOL;
            echo $notes . PHP_EOL;

            //update log done all_inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
                'offset' => $offset,
            ], 'sync_name=:sync_name', array(':sync_name' => 'all_inbound'));
        } else {
            $notes =  'Masih ada process sync all_inbound yang sedang berjalan' . PHP_EOL;
            echo $notes . PHP_EOL;
        }
    }

    //sync ulang outbound pick yang belum selesai
    public function actionPickReSync()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'pick'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'notes' => 'Sedang proses'
            ], 'sync_name=:sync_name', array(':sync_name' => 'pick'));


            $sql = "SELECT DISTINCT externOrderKey FROM el_schenker_outbound_detail WHERE lottable07 IN (
                    SELECT lottable07 FROM view_schenker_inbound WHERE itemInDetail <> totalPick
                    );";
            $to_sync = $this->connection->createCommand($sql)->queryAll();

            foreach ($to_sync as $r) {

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/shipment/listpicks',
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
                                "externOrderKey": "' . $r['externOrderKey'] . '"
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
                    die(var_dump($error_msg));
                    return $error_msg;
                } else {
                    $result = json_decode($response);

                    if (!empty($result->result->shipmentOrder[0]->shipmentOrderHeader)) {

                        //die(var_dump($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->ccompany));

                        $headers = $result->result->shipmentOrder[0]->shipmentOrderHeader;
                        foreach ($headers as $h) {

                            if (!empty($h->actualShipDate)) {
                                //cek sdh ada di db
                                // $cek = $this->connection->createCommand()
                                //     ->select('*')
                                //     ->from('el_schenker_outbound_pick')
                                //     ->where('orderKey=:orderKey', array(':orderKey' => $h->orderKey))
                                //     ->queryAll();

                                //delete existing
                                $this->connection->createCommand()->delete('el_schenker_outbound_pick', 'orderKey=:id', array(
                                    ':id' => $h->orderKey
                                ));

                                $shipmentOrderDetail = $h->shipmentOrderDetail;
                                foreach ($shipmentOrderDetail as $s) {
                                    foreach ($s->PickDetail as $p) {
                                        $this->connection->createCommand()->insert('el_schenker_outbound_pick', [
                                            'orderKey' => empty($p->orderKey) ? NULL : $p->orderKey,
                                            'lot' => empty($p->lot) ? NULL : $p->lot,
                                            'created_at' => date('Y-m-d H:i:s'),
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            }


            $notes =  'Sync done' . PHP_EOL;
            echo $notes . PHP_EOL;

            //update log done pick
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
            ], 'sync_name=:sync_name', array(':sync_name' => 'pick'));
        } else {
            $notes =  'Masih ada process sync pick yang sedang berjalan' . PHP_EOL;
            echo $notes . PHP_EOL;
        }
    }

    //sync ulang inbound yang belum selesai status <> 9
    public function actionInboundReSync()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'inbound_resync'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'notes' => 'Sedang proses'
            ], 'sync_name=:sync_name', array(':sync_name' => 'inbound_resync'));


            //$sql = "SELECT * FROM el_schenker_inbound_header WHERE status != '9';";
            $sql = "SELECT * FROM el_schenker_inbound_header WHERE status != '9' AND status != '11'";
            //$sql = "SELECT * FROM el_schenker_inbound_header WHERE externReceiptKey = '973239539';";
            // $sql = "SELECT * FROM el_schenker_inbound_header WHERE status = '0' AND externReceiptKey NOT IN ('9.69494497E8')";
            $to_sync = $this->connection->createCommand($sql)->queryAll();

            foreach ($to_sync as $r) {

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/receipt/list',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => '{
                        "advancedShipNotice": [
                        {
                            "advancedShipNoticeHeader": [
                            {
                                "storerKey": "IDGEHC",
                                "externReceiptKey": "' . $r['externReceiptKey'] . '"
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
                    die(var_dump($error_msg));
                    return $error_msg;
                } else {
                    $result = json_decode($response);

                    if (!empty($result->result->advancedShipNotice[0]->advancedShipNoticeHeader)) {

                        if (!empty($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->receiptKey)) {

                            $headers = $result->result->advancedShipNotice[0]->advancedShipNoticeHeader;
                            foreach ($headers as $h) {

                                //if (!empty($h->actualShipDate)) {

                                echo 'processing header receiptKey: ' . $h->receiptKey . PHP_EOL;

                                //get header_id
                                $header = $this->connection->createCommand()
                                    ->select('*')
                                    ->from('el_schenker_inbound_header')
                                    ->where('receiptKey=:receiptKey', array(':receiptKey' => $h->receiptKey))
                                    ->queryRow();

                                if (!empty($header)) {

                                    // echo 'insert header '. $h->receiptKey. PHP_EOL;
                                    #1. UPDATE data Schenker ke el_schenker_inbound_header
                                    $this->connection->createCommand()->update('el_schenker_inbound_header', [
                                        'susr1' => $h->susr1 ?? '',
                                        'susr2' => $h->susr2 ?? '',
                                        'status' => $h->status ?? '',
                                        'serialKey' => $h->serialKey ?? '',
                                        'receiptKey' => $h->receiptKey ?? '',
                                        'externReceiptKey' => $h->externReceiptKey ?? '',
                                        'receiptDate' => $h->receiptDate ?? '',
                                        'carrierName' => $h->carrierName ?? '',
                                        'type' => $h->type ?? '',
                                        'actualShipDate' => $h->actualShipDate ?? '',
                                        'adviceDate' => $h->adviceDate ?? '',
                                        'externReceiptKey2' => $h->externReceiptKey2 ?? '',
                                        'storerKey' => $h->storerKey ?? '',
                                        'created_at' => date('Y-m-d H:i:s'),
                                    ], 'id=:id', array(':id' => $header['id']));


                                    #2. INSERT data Schenker ke el_schenker_inbound_detail

                                    //delete existing
                                    $this->connection->createCommand()->delete('el_schenker_inbound_detail', 'header_id=:id', array(
                                        ':id' => $header['id']
                                    ));

                                    $details = $h->advancedShipNoticeDetail;
                                    foreach ($details as $d) {

                                        $dateReceived = $d->dateReceived;
                                        if (!empty($dateReceived)) {
                                            $date = DateTime::createFromFormat('d/m/Y H:i:s', $dateReceived);
                                            $dateReceived = $date->format('Y-m-d H:i:s');
                                        }

                                        if (($d->status == 11 || $d->status == 9) && $d->qtyReceived != "0.0" && !empty(trim($d->lottable03))) {

                                            echo 'insert detail ' . $d->lottable07 . PHP_EOL;
                                            $this->connection->createCommand()->insert('el_schenker_inbound_detail', [
                                                'header_id' => $header['id'],
                                                'susr1' => $d->susr1 ?? '',
                                                'susr2' => $d->susr2 ?? '',
                                                'receiptLineNumber' => $d->receiptLineNumber ?? '',
                                                'externLineNo' => $d->externLineNo ?? '',
                                                'storerKey' => $d->storerKey ?? '',
                                                'sku' => $d->sku ?? '',
                                                'toId' => $d->toId ?? '',
                                                'qtyExpected' => $d->qtyExpected ?? '',
                                                'qtyAdjusted' => $d->qtyAdjusted ?? '',
                                                'qtyReceived' => $d->qtyReceived ?? '',
                                                'toLoc' => $d->toLoc ?? '',
                                                'toLot' => $d->toLot ?? '',
                                                'receiptKey' => $d->receiptKey ?? '',
                                                'uom' => $d->uom ?? '',
                                                'externReceiptKey' => $d->externReceiptKey ?? '',
                                                'status' => $d->status ?? '',
                                                'dateReceived' => $d->dateReceived ?? '',
                                                'conditionCode' => $d->conditionCode ?? '',
                                                'lottable02' => $d->lottable02 ?? '',
                                                'lottable03' => $d->lottable03 ?? '',
                                                'lottable07' => $d->lottable07 ?? '',
                                                'lottable08' => $d->lottable08 ?? '',
                                                'created_at' => date('Y-m-d H:i:s'),
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            $notes =  'Sync done' . PHP_EOL;
            echo $notes . PHP_EOL;

            //update log done inbound_resync
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
            ], 'sync_name=:sync_name', array(':sync_name' => 'inbound_resync'));
        } else {
            $notes =  'Masih ada process sync inbound_resync yang sedang berjalan' . PHP_EOL;
            echo $notes . PHP_EOL;
        }
    }

    //sync ulang outbound yang belum selesai actualshipdate nya kosong
    public function actionOutboundReSync()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'outbound_resync'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing outbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
                'notes' => 'Sedang proses'
            ], 'sync_name=:sync_name', array(':sync_name' => 'outbound_resync'));


            $sql = "SELECT * FROM el_schenker_outbound_header WHERE actualShipDate = ''";
            //$sql = "SELECT * FROM el_schenker_outbound_header WHERE externOrderKey = '5292002.1'";
            $to_sync = $this->connection->createCommand($sql)->queryAll();

            foreach ($to_sync as $r) {

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
                                "externOrderKey": "' . $r['externOrderKey'] . '"
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
                    die(var_dump($error_msg));
                    return $error_msg;
                } else {
                    $result = json_decode($response);

                    if (!empty($result->result->shipmentOrder[0]->shipmentOrderHeader)) {

                        //die(var_dump($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->ccompany));

                        // if ($offset == 2) {
                        //     break;
                        // }

                        // if (empty($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->orderKey)) {
                        //     break;
                        // }

                        $headers = $result->result->shipmentOrder[0]->shipmentOrderHeader;
                        foreach ($headers as $h) {

                            //if (!empty($h->actualShipDate)) {

                            echo 'processing header orderKey: ' . $h->orderKey . '-' . $h->actualShipDate . PHP_EOL;

                            $cek = $this->connection->createCommand()
                                ->select('*')
                                ->from('el_schenker_outbound_header')
                                ->where('orderKey=:orderKey', array(':orderKey' => $h->orderKey))
                                ->queryRow();

                            if (!empty($cek)) {
                                // echo 'insert header '. $h->orderKey. PHP_EOL;
                                #1. INSERT data Schenker ke el_schenker_outbound_header
                                $this->connection->createCommand()->update('el_schenker_outbound_header', [
                                    'ccompany' => $h->ccompany ?? '',
                                    'status' => $h->status ?? '',
                                    'serialKey' => $h->serialKey ?? '',
                                    'externOrderKey' => $h->externOrderKey ?? '',
                                    'orderDate' => $h->orderDate ?? '',
                                    'priority' => $h->priority ?? '',
                                    'carrierName' => $h->carrierName ?? '',
                                    'buyerPO' => $h->buyerPO ?? '',
                                    'type' => $h->type ?? '',
                                    'actualShipDate' => $h->actualShipDate,
                                    'externalOrderKey2' => $h->externalOrderKey2 ?? '',
                                    'storerKey' => $h->storerKey ?? '',
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ], 'orderKey=:orderKey', array(':orderKey' => $h->orderKey));

                                echo 'updated header orderKey: ' . $h->orderKey . '-' . $h->actualShipDate . PHP_EOL;

                                $this->connection->createCommand()->delete('el_schenker_outbound_detail', 'orderKey=:id', array(
                                    ':id' => $h->orderKey
                                ));

                                #2. INSERT data Schenker ke el_schenker_outbound_detail
                                $details = $h->shipmentOrderDetail;
                                foreach ($details as $d) {
                                    if (!empty(trim($d->sku))) {
                                        if (!empty(trim($d->lottable07))) {

                                            // echo 'insert detail ' . $d->lottable07 . PHP_EOL;
                                            $this->connection->createCommand()->insert('el_schenker_outbound_detail', [
                                                'header_id' => $cek['id'],
                                                'susr1' => $d->susr1 ?? '',
                                                'orderLineNumber' => $d->orderLineNumber ?? '',
                                                'externLineNo' => $d->externLineNo ?? '',
                                                'storerKey' => $d->storerKey ?? '',
                                                'sku' => $d->sku ?? '',
                                                'packKey' => $d->packKey ?? '',
                                                'uom' => $d->uom ?? '',
                                                'openQty' => $d->openQty ?? '',
                                                'orderKey' => $d->orderKey ?? '',
                                                'orderDetailSysId' => $d->orderDetailSysId ?? '',
                                                'externOrderKey' => $d->externOrderKey ?? '',
                                                'status' => $d->status ?? '',
                                                'lottable07' => $d->lottable07 ?? '',
                                                'lottable08' => $d->lottable08 ?? '',
                                                'shippedQty' => $d->shippedQty ?? '',
                                                'created_at' => date('Y-m-d H:i:s'),
                                            ]);
                                        }
                                    }
                                }
                            }
                            //}
                        }
                    }
                }
            }


            $notes =  'Sync done' . PHP_EOL;
            echo $notes . PHP_EOL;

            //update log done outbound_resync
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'updated_at' => date('Y-m-d H:i:s'),
                'notes' => $notes,
            ], 'sync_name=:sync_name', array(':sync_name' => 'outbound_resync'));
        } else {
            $notes =  'Masih ada process sync outbound_resync yang sedang berjalan' . PHP_EOL;
            echo $notes . PHP_EOL;
        }
    }

    public function actionChores()
    {
        echo 'Checking Sync at ' . date('Y-m-d H:i:s') . PHP_EOL;

        $sql = "SELECT * FROM el_sync_status WHERE sync_status <> 'done' AND created_at <= CURDATE() - INTERVAL 3 DAY;";
        $q = $this->connection->createCommand($sql)->queryAll();

        foreach ($q as $r) {
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'done',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
            ], 'sync_name=:sync_name', array(':sync_name' => $r['sync_name']));
        }

        echo 'Done Checking Sync at ' . date('Y-m-d H:i:s') . PHP_EOL;
    }
}
