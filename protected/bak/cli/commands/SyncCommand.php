<?php

class SyncCommand extends CConsoleCommand
{

    //php yiic.php sync inbound
    public $connection;

    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
        //$this->connection = new CDbConnection('mysql:host=localhost;dbname=wmslite', 'root', '');
        $this->connection = new CDbConnection('mysql:host=localhost;dbname=wmslite', 'root', 'Buk4nM@!n@n$B*55');
        $this->connection->active = true;
    }

    public function actionInbound($delivery = '')
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'inbound'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing inbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
            ], 'sync_name=:sync_name', array(':sync_name' => 'inbound'));

            if (!empty($delivery)) {
                $sql = "SELECT hawb, delivery_id FROM el_inbound_header WHERE delivery_id='" . $delivery . "' AND warehouse='marunda'";
            } else {
                $sql = "SELECT h.hawb, h.delivery_id FROM el_inbound_header h 
                    WHERE h.warehouse='marunda' AND (h.status='Warehouse in Transit') 
                    AND h.delivery_id IS NOT NULL AND h.delivery_id <> '' AND h.delivery_id <> '-' AND h.delivery_id <> '0';";
            }

            //WHERE h.warehouse='marunda' AND h.delivery_id IN ('W00010590', 'W00010560') 

            //dev only
            //$sql = "SELECT h.hawb, h.delivery_id FROM el_inbound_header h WHERE h.delivery_id IN ('W00010560')";

            $to_sync = $this->connection->createCommand($sql)->queryAll();

            foreach ($to_sync as $r) {
                //jangan pernah di delete lagi!!! matching id_outbound_details nya susahhh
                // $sql = "DELETE FROM el_inbound_details WHERE hawb = '" . $r['hawb'] . "' AND sso_delivery_id='" . $r['delivery_id'] . "'";
                // $delete = $this->connection->createCommand($sql)->execute();

                $log = $this->sync($r['delivery_id'], $r['hawb']);

                $this->connection->createCommand()->insert('el_sync_schenker_log', [
                    'hawb' => $r['hawb'],
                    'sso_delivery_id' => $r['delivery_id'],
                    'description' => $log,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } else {
            echo 'Masih ada process sync inbound yang sedang berjalan, ulangi beberapa saat lagi' . PHP_EOL;
        }

        //update log done inbound
        $this->connection->createCommand()->update('el_sync_status', [
            'sync_status' => 'done',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'sync_name=:sync_name', array(':sync_name' => 'inbound'));
    }

    public function sync($externReceiptKey, $hawb_asli)
    {
        $call = '"externReceiptKey": "' . $externReceiptKey . '"';
        // if (isset($_GET['externReceiptKey'])) {
        //     $call = '"externReceiptKey": "' . $_GET['externReceiptKey'] . '"';
        // } else if (isset($_GET['receiptKey'])) {
        //     $call = '"receiptKey": "' . $_GET['receiptKey'] . '"';
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
                    ' . $call . '
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
            //die(var_dump($error_msg));
            return $error_msg;
        } else {
            $result = json_decode($response);


            $type = [
                '21' => 'Import',
                '22' => 'Local',
                '23' => 'Return',
                '24' => 'Express',
            ];

            //echo "<strong>INBOUND PO</strong><br>";

            //echo '[hawb] hawb: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->hawb;
            //$hawb = $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->hawb;

            //die(var_dump($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->externReceiptKey));

            if (!empty($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->externReceiptKey)) {
                $status = $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->status;
                if ($status == 11 || $status == 9) {

                    // echo '<br>[modality] susr1: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->susr1;
                    // echo '<br>[ship_method] type: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->type . ' >>> ' . $type[$result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->type];
                    // echo '<br>[sso_delivery_id] externReceiptKey: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->externReceiptKey;
                    // echo '<br>[ata] arrivalDateTime: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->arrivalDateTime;

                    // echo '<br>';

                    $sso_delivery_id = $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->externReceiptKey;
                    $data = [];

                    foreach ($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->advancedShipNoticeDetail as $r) {

                        $dateReceived = $r->dateReceived;
                        if (!empty($dateReceived)) {
                            $date = DateTime::createFromFormat('d/m/Y H:i:s', $dateReceived);
                            $dateReceived = $date->format('Y-m-d H:i:s');
                        }

                        if (($r->status == 11 || $r->status == 9) && $r->qtyReceived != "0.0" && !empty(trim($r->lottable03))) {
                            //die(var_dump($r->sku));

                            //begin ambil detail aja, to be confirmed
                            //$sku_descr = $this->getSkuDesc($r->sku);
                            $data[] = [
                                'sku' => $r->sku,
                                'lpn' => $r->toId,
                                'sso_delivery_id' => $sso_delivery_id,
                                //'invs_sku_descr' => $sku_descr,
                                'invs_lot' => '',
                                'invs_qty' => (int)$r->qtyReceived,
                                'invs_loc' => $r->toLoc,
                                'invs_id' => '',
                                'invs_status' => '',
                                'invl_locator' => '',
                                'scan_time' => $dateReceived,
                                'lottable03' => $r->lottable03,
                            ];
                            //end to be confirmed
                        }
                    }

                    //delete record existing
                    //20240411 karena ada kolom qty_out maka tidak bisa di delete
                    //$this->connection->createCommand()->delete('el_inbound_details', 'sso_delivery_id=:id', array(':id' => $sso_delivery_id));
                    foreach ($data as $r) {

                        // $this->connection->createCommand()->insert('el_inbound_details', [
                        //     'hawb' => $hawb_asli,
                        //     'descr' => $r['sku'],
                        //     'sso_delivery_id' => $r['sso_delivery_id'],
                        //     'loc' => $r['invs_loc'],
                        //     'weight' => 0,
                        //     'long' => 0,
                        //     'wide' => 0,
                        //     'high' => 0,
                        //     'flag' => 0,
                        //     'scan_time' => $r['scan_time'], //harusnya ada
                        //     'qty' => $r['invs_qty'],
                        //     'date_created' => date('Y-m-d H:i:s'),
                        // ]);

                        // //cari ada record existing atau tidak
                        $detail_existing = $this->connection->createCommand()
                            ->select('*')
                            ->from('el_inbound_details')
                            ->where('sso_delivery_id=:id', array(':id' => $sso_delivery_id))
                            ->andWhere('descr=:sku', array(':sku' => $r['sku']))
                            ->andWhere('hawb=:hawb', array(':hawb' => $hawb_asli))
                            ->andWhere('lottable03=:lottable03', array(':lottable03' => $r['lottable03']))
                            ->queryRow();

                        // //die(var_dump($r));

                        if (empty($detail_existing)) {
                            $this->connection->createCommand()->insert('el_inbound_details', [
                                'hawb' => $hawb_asli,
                                'descr' => $r['sku'],
                                'sso_delivery_id' => $r['sso_delivery_id'],
                                'loc' => $r['invs_loc'],
                                'weight' => 0,
                                'long' => 0,
                                'wide' => 0,
                                'high' => 0,
                                'flag' => 0,
                                'scan_time' => $r['scan_time'], //harusnya ada
                                'qty' => $r['invs_qty'],
                                'date_created' => date('Y-m-d H:i:s'),
                                'lottable03' => $r['lottable03'],
                            ]);
                        } else {
                            $this->connection->createCommand()->update('el_inbound_details', [
                                'hawb' => $hawb_asli,
                                'descr' => $r['sku'],
                                'sso_delivery_id' => $r['sso_delivery_id'],
                                'loc' => $r['invs_loc'],
                                // 'weight' => 0,
                                // 'long' => 0,
                                // 'wide' => 0,
                                // 'high' => 0,
                                // 'flag' => 0,
                                'scan_time' => $r['scan_time'], //harusnya ada
                                'qty' => $r['invs_qty'],
                                'date_updated' => date('Y-m-d H:i:s'),
                                'lottable03' => $r['lottable03'],
                            ], 'sso_delivery_id=:id AND descr=:sku AND hawb=:hawb AND lottable03=:lottable03', array(':id' => $sso_delivery_id, ':sku' => $r['sku'], ':hawb' => $hawb_asli, ':lottable03' => $r['lottable03'],));
                        }

                        //update headernya
                        $this->connection->createCommand()->update('el_inbound_header', [
                            'status' => 'successful',
                            'date_updated' => date('Y-m-d H:i:s'),
                        ], 'hawb=:hawb', array(':hawb' => $hawb_asli));

                        //update loc
                        $loc = $this->connection->createCommand()
                            ->select('*')
                            ->from('el_loc')
                            ->where('loc_name=:id', array(':id' => $r['invs_loc']))
                            ->queryRow();

                        if (empty($loc)) {
                            $this->connection->createCommand()->insert('el_loc', [
                                'loc_name' => $r['invs_loc'],
                                'loc_descr' => $r['invs_loc'] . ' (at Schenker)',
                            ]);
                        }
                    }
                }

                return 'Done sync';
            } else {

                return 'Data tidak tersedia di Schenker';
            }
        }

        curl_close($curl);
    }

    public function getSkuDesc($sku)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/sku/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
  "itemMaster": [
    {
      "item": [
        {
          "storerKey": "IDGEHC",
          "sku": "' . $sku . '"
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
            //die(var_dump($error_msg));
            return $error_msg;
        } else {
            $result = json_decode($response);

            // echo "SKU CHECK<br>";

            // echo '[sku] sku: ' . $result->result->itemMaster[0]->item[0]->sku;
            // echo '<br>[descr] descr: ' . $result->result->itemMaster[0]->item[0]->descr;

            return $result->result->itemMaster[0]->item[0]->descr;
        }

        curl_close($curl);
    }


    public function getInventoryLot($lottable03)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/lot/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
      "lotObject": [
        {
          "lotHeader": [
            {
              "storerKey": "IDGEHC",
              "lottable03": "' . $lottable03 . '"
            }
          ]
        }
      ]
    }
    ',
            CURLOPT_HTTPHEADER => array(
                'ngwSystemId: PRDO2_wmwhse153',
                'Content-Type: application/json',
                'Authorization: Basic MFFQSnRwcUIxUlhnakpVejpZIUxhY3xFdVxILlpqRXx4U3Vud3I9VEdQLGJyQip4eQ=='
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            //die(var_dump($error_msg));
            return $error_msg;
        } else {
            $result = json_decode($response);

            return $result->result->lotObject[0]->lotHeader;
        }

        curl_close($curl);
    }


    public function getInventorySnapshot($lot)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/schenkerSerialInventory/list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
      "schenkerSerialInventory": [
        {
          "schenkerSerialInventoryHeader": [
            {
              "storerKey": "IDGEHC",
              "lot": "' . $lot . '"
            }
          ]
        }
      ]
    }
    ',
            CURLOPT_HTTPHEADER => array(
                'ngwSystemId: PRDO2_wmwhse153',
                'Content-Type: application/json',
                'Authorization: Basic MFFQSnRwcUIxUlhnakpVejpZIUxhY3xFdVxILlpqRXx4U3Vud3I9VEdQLGJyQip4eQ=='
            ),
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            //die(var_dump($error_msg));
            return $error_msg;
        } else {
            $result = json_decode($response);

            return $result->result->schenkerSerialInventory[0]->schenkerSerialInventoryHeader;
        }

        curl_close($curl);
    }

    public function actionOutbound($id = '')
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'outbound'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (!empty($sync_status)) {

            //update log processing outbound
            $this->connection->createCommand()->update('el_sync_status', [
                'sync_status' => 'processing',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => NULL,
            ], 'sync_name=:sync_name', array(':sync_name' => 'outbound'));


            if (!empty($id)) {
                //
                //$sql = "SELECT id, po FROM el_outbound_header WHERE id='" . $id . "'";

                $sql = "SELECT id, po FROM (
                SELECT h.id, h.po, d.hawb, d.descr, 
                (SELECT warehouse FROM el_inbound_header ih LEFT JOIN el_inbound_details id ON id.hawb=ih.hawb WHERE id.hawb=d.hawb AND id.descr=d.descr LIMIT 1) as warehouse 
                FROM el_outbound_header h LEFT JOIN el_outbound_details d ON h.id=d.id 
                WHERE h.status <> 'successful'
                ) AS x WHERE warehouse='marunda' AND id='" . $id . "' GROUP BY id, po";
            } else {
                //marunda dan include campuran
                $sql = "SELECT id, po FROM (
                    SELECT h.id, h.po, d.hawb, d.descr, 
                    (SELECT warehouse FROM el_inbound_header ih LEFT JOIN el_inbound_details id ON id.hawb=ih.hawb WHERE id.hawb=d.hawb AND id.descr=d.descr LIMIT 1) as warehouse 
                    FROM el_outbound_header h LEFT JOIN el_outbound_details d ON h.id=d.id 
                    WHERE h.status <> 'successful'
                    ) AS x WHERE warehouse='marunda' GROUP BY id, po;";
            }

            $to_sync = $this->connection->createCommand($sql)->queryAll();

            if (empty($to_sync)) {
                echo 'Tidak di sync karena bukan marunda / campuran';
            }


            foreach ($to_sync as $sync) {
                //die(var_dump($sync['po'], $sync['id']));

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
                    "externOrderKey": "' . $sync['po'] . '"
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

                    if (!empty($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey)) {

                        $externOrderKey = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey;
                        $actualShipDate = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->actualShipDate;
                        if (!empty($actualShipDate)) {
                            $date = DateTime::createFromFormat('d/m/Y H:i:s', $actualShipDate);
                            $actualShipDate = $date->format('Y-m-d H:i:s');
                        }


                        $status = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->status;

                        //if (($status == 4 || $status == 9) && !empty($actualShipDate)) {
                        if (!empty($actualShipDate)) {

                            //BEGIN NEW SYNC MULTI QTY

                            #1. DELETE data Schenker di el_outbound_details_schenker
                            $this->connection->createCommand()->delete('el_outbound_details_schenker', 'id_outbound_header=:id', array(
                                ':id' => $sync['id']
                            ));

                            #2. INSERT data Schenker ke el_outbound_details_schenker
                            $detail = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->shipmentOrderDetail;
                            foreach ($detail as $d) {
                                if (!empty(trim($d->sku))) {
                                    if (!empty(trim($d->lottable07))) {

                                        $this->connection->createCommand()->insert('el_outbound_details_schenker', [
                                            'id_outbound_header' => $sync['id'],
                                            'po' => $sync['po'],
                                            'actualshipdate' => $actualShipDate,
                                            'sku' => $d->sku,
                                            'lottable07' => $d->lottable07,
                                            'lottable03' => $d->lottable03,
                                            'shippedQty' => $d->shippedQty,
                                            'created_at' => date('Y-m-d H:i:s'),
                                            //'notes' => ''
                                        ]);
                                    }
                                }
                            }

                            #3. COMPARE data Schenker dg WMSLite
                            // ambil data di outbound by id
                            // cari di detail yang dia warehousenya marunda -> compare ke table Schenker
                            $sql = "SELECT * FROM el_outbound_details WHERE id=" . $sync['id'];
                            $outbound_details = $this->connection->createCommand($sql)->queryAll();

                            //set ntoes ke null di el_outbound_details
                            $this->connection->createCommand()->update('el_outbound_details', [
                                'date_updated' => date('Y-m-d H:i:s'),
                                'notes' => NULL,
                            ], 'id=:id', array(
                                ':id' => $sync['id'],
                            ));

                            foreach ($outbound_details as $r) {
                                $notes = null;
                                //cek warehousenya, jika marunda -> el_inbound_details flag -> 0

                                $sql = "SELECT d.hawb, d.descr, d.sso_delivery_id, h.warehouse
                            FROM el_inbound_details d 
                            LEFT JOIN el_inbound_header h ON h.hawb=d.hawb AND h.delivery_id=d.sso_delivery_id
                            WHERE d.hawb='" . $r['hawb'] . "' AND d.descr='" . $r['descr'] . "';";
                                $warehouse = $this->connection->createCommand($sql)->queryRow();

                                if ($warehouse['warehouse'] == 'marunda') {

                                    //di inprogreskan dulu tiap sync
                                    $this->connection->createCommand()->update('el_outbound_header', [
                                        'status' => 'inprogress',
                                        'date_updated' => date('Y-m-d H:i:s'),
                                    ], 'id=:id', array(':id' => $sync['id']));

                                    //cek ulang apakah ada arcadia nya
                                    //cek apakah hawb tsb merupakan inbound lama (arcadia), lama tidak di sync
                                    //$sql = "SELECT * FROM el_inbound_header WHERE hawb='" . $d->lottable07 . "' AND warehouse='arcadia'";
                                    //$arcadia = $this->connection->createCommand($sql)->queryRow();

                                    //if (empty($arcadia)) {

                                    //cek ke el_outbound_details_schenker
                                    //jika tersedia & qty nya sama
                                    $sql = "SELECT *
                                    FROM el_outbound_details_schenker s WHERE s.id_outbound_header='" . $r['id'] . "' AND s.sku='" . $r['descr'] . "'" . ";";
                                    $schenker = $this->connection->createCommand($sql)->queryRow();

                                    if (!empty($schenker)) {

                                        //successful karena qty wmslite sama dg schenker
                                        // if($r['descr'] == '0231-1018-810'){
                                        //     var_dump((int)$r['qty'], (int)$schenker['shippedqty'], $r['id']);
                                        // }


                                        //successful adalah ketika qty yg di out wmslite = qty yg di out schenker & qty_out <> 0
                                        $flag = 0;
                                        $sql = "SELECT SUM(qty) AS total FROM el_outbound_details WHERE hawb='" . $r['hawb'] . "' AND descr='" . $r['descr'] . "'";
                                        $qty_out = $this->connection->createCommand($sql)->queryRow();

                                        $sql = "SELECT SUM(shippedqty) AS total FROM el_outbound_details_schenker WHERE lottable07='" . $r['hawb'] . "' AND sku='" . $r['descr'] . "'";
                                        $qty_schenker = $this->connection->createCommand($sql)->queryRow();

                                        if ((int)$qty_out['total'] === (int)$qty_schenker['total']) {
                                            $flag = 1;
                                        }


                                        if ((int)$r['qty'] === (int)$schenker['shippedqty']) {
                                            $this->connection->createCommand()->update('el_outbound_details', [
                                                'flag' => $flag,
                                                'scan_time' => $actualShipDate,
                                                'notes' => $notes,
                                                'date_updated' => date('Y-m-d H:i:s'),
                                            ], 'id=:id AND descr=:descr', array(
                                                ':id' => $r['id'],
                                                ':descr' => $r['descr'],
                                            ));
                                        } else {
                                            //notes karena berbeda
                                            $notes = "qty yg di out di WMSLite tidak sama dengan Schenker (" . (int)$r['qty'] . " vs " . (int)$schenker['shippedqty'] . ")";
                                            $this->connection->createCommand()->update('el_outbound_details', [
                                                'date_updated' => date('Y-m-d H:i:s'),
                                                'notes' => $notes,
                                            ], 'id=:id AND descr=:descr', array(
                                                ':id' => $r['id'],
                                                ':descr' => $r['descr'],
                                            ));
                                        }
                                    } else {
                                        $notes = 'di outbound Schenker tidak ada SKU ini';
                                        $this->connection->createCommand()->update('el_outbound_details', [
                                            'scan_time' => $actualShipDate,
                                            'notes' => $notes,
                                            'date_updated' => date('Y-m-d H:i:s'),
                                        ], 'id=:id AND descr=:descr', array(
                                            ':id' => $r['id'],
                                            ':descr' => $r['descr'],
                                        ));
                                    }
                                    //}

                                    //ubah status outbound jadi successfull jika semuanya sdh flag 1
                                    $sql = "SELECT SUM(qty) AS total
                                    FROM el_outbound_details d WHERE d.id='" . $r['id'] . "';";
                                    $jml_row = $this->connection->createCommand($sql)->queryRow();

                                    $sql = "SELECT SUM(qty) AS total
                                    FROM el_outbound_details d WHERE d.id='" . $r['id'] . "' AND flag=1;";
                                    $jml_row_flag_1 = $this->connection->createCommand($sql)->queryRow();

                                    if (($jml_row['total'] == $jml_row_flag_1['total']) && $jml_row['total'] != 0) {
                                        $this->connection->createCommand()->update('el_outbound_header', [
                                            'qty' => $jml_row['total'],
                                            'status' => 'successful',
                                            'scan_time' => $actualShipDate,
                                            'date_updated' => date('Y-m-d H:i:s'),
                                        ], 'id=:id', array(':id' => $sync['id']));
                                    } else {
                                        $this->connection->createCommand()->update('el_outbound_header', [
                                            'qty' => $jml_row['total'],
                                            'status' => 'inprogress',
                                            'scan_time' => $actualShipDate,
                                            'date_updated' => date('Y-m-d H:i:s'),
                                        ], 'id=:id', array(':id' => $sync['id']));
                                    }
                                } else {
                                    $notes = 'Bukan Marunda';
                                    $this->connection->createCommand()->update('el_outbound_details', [
                                        'scan_time' => $actualShipDate,
                                        'notes' => $notes,
                                        'date_updated' => date('Y-m-d H:i:s'),
                                    ], 'id=:id AND descr=:descr', array(
                                        ':id' => $r['id'],
                                        ':descr' => $r['descr'],
                                    ));
                                }
                            }
                            echo 'Sync done: ' . $sync['id'] . ' ' . date('Y-m-d H:i:s') . PHP_EOL;
                        } else {
                            echo 'Nothing to sync' . PHP_EOL;
                        }
                    }
                }
            }
        } else {
            echo 'Masih ada proses sync outbound yang berjalan, silakan coba beberapa saat lagi' . PHP_EOL;
        }

        //update log done outbound
        $this->connection->createCommand()->update('el_sync_status', [
            'sync_status' => 'done',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'sync_name=:sync_name', array(':sync_name' => 'outbound'));

        echo 'Done sync outbound';
    }

    public function actionLoc()
    {

        $sync_status = $this->connection->createCommand()
            ->select('*')
            ->from('el_sync_status')
            ->where('sync_name=:sync_name', array(':sync_name' => 'loc'))
            ->andWhere('sync_status=:sync_status', array(':sync_status' => 'done'))
            ->queryRow();

        if (empty($sync_status)) {
            die('Masih ada proses sync loc yang berjalan' . PHP_EOL);
        }

        //update log processing loc
        $this->connection->createCommand()->update('el_sync_status', [
            'sync_status' => 'processing',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => NULL,
        ], 'sync_name=:sync_name', array(':sync_name' => 'loc'));

        //cari loc yg blm 1
        $sql = "SELECT h.warehouse, d.descr FROM el_inbound_header h 
        LEFT JOIN el_inbound_details d ON d.hawb=h.hawb
        WHERE h.warehouse='marunda' AND loc_sync=0";

        $to_sync = $this->connection->createCommand($sql)->queryAll();

        foreach ($to_sync as $sync) {
            //die(var_dump($sync['po']));

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/inventoryBalance/list',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "inventoryBalance": [
                    {
                        "inventoryBalanceHeader": [
                        {
                            "storerKeyMin": "IDGEHC",
                            "storerKeyMax": "IDGEHC",
                            "skuMin": "' . $sync['descr'] . '",
                            "skuMax": "' . $sync['descr'] . '"
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
                //die(var_dump($error_msg));
                return $error_msg;
            } else {
                $result = json_decode($response);

                $data = $result->result->inventoryBalance[0]->inventoryBalanceHeader;
                if (!empty($data)) {

                    $last_loc = $data[count($data) - 1];

                    if (!empty($last_loc->sku)) {

                        //update loc
                        $loc = $this->connection->createCommand()
                            ->select('*')
                            ->from('el_loc')
                            ->where('loc_name=:id', array(':id' => $last_loc->loc))
                            ->queryRow();

                        if (empty($loc)) {
                            $this->connection->createCommand()->insert('el_loc', [
                                'loc_name' => $last_loc->loc,
                                'loc_descr' => $last_loc->loc . ' (at Schenker)',
                            ]);
                        }

                        $this->connection->createCommand()->update('el_inbound_details', [
                            'loc_sync' => ($last_loc->qty = '0.0') ? 1 : 0,
                            'loc' => $last_loc->loc,
                            'loc_sync_updated' => date('Y-m-d H:i:s'),
                        ], 'descr=:descr', array(':descr' => $last_loc->sku));
                    }
                }
            }
        }

        //update log done loc
        $this->connection->createCommand()->update('el_sync_status', [
            'sync_status' => 'done',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'sync_name=:sync_name', array(':sync_name' => 'loc'));
    }







    //tarik data outbound dari schenker, yg ada inbound nya, dan itu
    public function actionOutboundFillData($id, $po)
    {
        $sync['id'] = $id;
        $sync['po'] = $po;
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
                    "externOrderKey": "' . $sync['po'] . '"
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

            if (!empty($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey)) {

                $externOrderKey = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey;
                $actualShipDate = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->actualShipDate;
                if (!empty($actualShipDate)) {
                    $date = DateTime::createFromFormat('d/m/Y H:i:s', $actualShipDate);
                    $actualShipDate = $date->format('Y-m-d H:i:s');
                }


                $status = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->status;

                //if (($status == 4 || $status == 9) && !empty($actualShipDate)) {
                if (!empty($actualShipDate)) {

                    $detail = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->shipmentOrderDetail;
                    foreach ($detail as $d) {
                        $sql = "SELECT d.hawb, d.descr, d.sso_delivery_id, h.warehouse, d.loc, d.id
                            FROM el_inbound_details d 
                            LEFT JOIN el_inbound_header h ON h.hawb=d.hawb AND h.delivery_id=d.sso_delivery_id
                            WHERE d.hawb='" . $d->lottable07 . "' AND d.descr='" . $d->sku . "' AND h.warehouse='marunda';";
                        $id = $this->connection->createCommand($sql)->queryRow();

                        if (!empty($id)) {
                            $this->connection->createCommand()->insert('el_outbound_details', [
                                'hawb' => $d->lottable07,
                                'descr' => $d->sku,
                                'loc' => $id['loc'],
                                'id' => $sync['id'],
                                'flag' => 1,
                                'qty' => $d->shippedQty,
                                'scan_time' => $actualShipDate,
                                'lottable03' => $d->lottable03,
                                'date_created' => date('Y-m-d H:i:s'),
                                'id_inbound_details' => $id['id'],
                                //'notes' => ''
                            ]);
                        }
                    }
                }
            }
        }
    }
}
