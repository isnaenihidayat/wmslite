<?php

//dev
//$url = "https://eservice-apac.dbschenker.com.dbschenker.com/api-catalog/v1/wms";
//prod
//$url = "https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/receipt/list";

if (isset($_GET['externReceiptKey'])) {
  $call = '"externReceiptKey": "' . $_GET['externReceiptKey'] . '"';
} else if (isset($_GET['receiptKey'])) {
  $call = '"receiptKey": "' . $_GET['receiptKey'] . '"';
} else if (isset($_GET['hawb'])) {
  $call = '"advancedShipNoticeDetail": [
    {
      "lottable07": "' . $_GET['hawb'] . '"
    }
]';
}

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
  die(var_dump($error_msg));
} else {
  $result = json_decode($response);


  $type = [
    '21' => 'Import',
    '22' => 'Local',
    '23' => 'Return',
    '24' => 'Express',
  ];

  echo "<strong>INBOUND PO</strong><br>";

  if(empty($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->type)){
    die('tidak ditemukan');
  }

  echo '[hawb] hawb: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->hawb;
  echo '<br>[modality] susr1: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->susr1;
  echo '<br>[ship_method] type: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->type . ' >>> ' . $type[$result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->type];
  echo '<br>[sso_delivery_id] externReceiptKey: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->externReceiptKey;


  if (!empty($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->externReceiptKey)) {
    echo '<br>[ata] arrivalDateTime: ' . $result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->arrivalDateTime;

    echo '<br>';

    foreach ($result->result->advancedShipNotice[0]->advancedShipNoticeHeader[0]->advancedShipNoticeDetail as $r) {
      echo '<hr>';
      echo '&emsp;[descr]: ' . $r->sku . ' - ' . getSkuDesc($r->sku) . '<br>';
      echo '&emsp;[loc] toLoc: ' . $r->toLoc . '<br>';
      echo '&emsp;[?] toId: ' . $r->toId . '<br>';
      echo '&emsp;[delivery_id] lottable02: ' . $r->lottable02 . '<br>';
      echo '&emsp;[?] lottable03: ' . $r->lottable03 . '<br>';
      echo '&emsp;[hawb] lottable07: ' . $r->lottable07 . '<br>';
      echo '&emsp;[locator] lottable08: ' . $r->lottable08 . '<br>';
      echo '&emsp;[qty] qtyReceived: ' . $r->qtyReceived . '<br>';
      echo '&emsp;&emsp;<strong>Inventory Lot</strong><br>';
      $inv_lot = getInventoryLot($r->lottable03);
      //var_dump($inv_lot);
      foreach ($inv_lot as $s) {
        echo '&emsp;&emsp;sku: ' . $s->sku . '<br>';
        echo '&emsp;&emsp;lot: ' . $s->lot . '<br>';
        echo '&emsp;&emsp;caseCnt: ' . $s->caseCnt . '<br>';
        echo '&emsp;&emsp;innerPack: ' . $s->innerPack . '<br>';
        echo '&emsp;&emsp;pallet: ' . $s->pallet . '<br>';
        echo '&emsp;&emsp;cube: ' . $s->cube . '<br>';
        echo '&emsp;&emsp;grossWgt: ' . $s->grossWgt . '<br>';
        echo '&emsp;&emsp;netWgt: ' . $s->netWgt . '<br>';
        echo '&emsp;&emsp;[locator] lottable08: ' . $s->lottable08 . '<br>';
        echo '&emsp;&emsp;<strong>Inventory Snapshot</strong><br>';
        $inv_snapshot = getInventorySnapshot($s->lot);
        //var_dump($inv_snapshot);
        foreach ($inv_snapshot as $t) {
          echo '&emsp;&emsp;&emsp;sku: ' . $t->sku . '<br>';
          echo '&emsp;&emsp;&emsp;lot: ' . $t->lot . '<br>';
          echo '&emsp;&emsp;&emsp;loc: ' . $t->loc . '<br>';
          echo '&emsp;&emsp;&emsp;qty: ' . $t->qty . '<br>';
          echo '&emsp;&emsp;&emsp;id: ' . $t->id . '<br>';
          echo '&emsp;&emsp;&emsp;status: ' . $t->status . '<br>';
        }
      }
    }
  }

  die();
}

curl_close($curl);


function getSkuDesc($sku)
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
    die(var_dump($error_msg));
  } else {
    $result = json_decode($response);

    // echo "SKU CHECK<br>";

    // echo '[sku] sku: ' . $result->result->itemMaster[0]->item[0]->sku;
    // echo '<br>[descr] descr: ' . $result->result->itemMaster[0]->item[0]->descr;

    return $result->result->itemMaster[0]->item[0]->descr;
  }

  curl_close($curl);
}


function getInventoryLot($lottable03)
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
    die(var_dump($error_msg));
  } else {
    $result = json_decode($response);

    return $result->result->lotObject[0]->lotHeader;
  }

  curl_close($curl);
}


function getInventorySnapshot($lot)
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
    die(var_dump($error_msg));
  } else {
    $result = json_decode($response);

    return $result->result->schenkerSerialInventory[0]->schenkerSerialInventoryHeader;
  }

  curl_close($curl);
}
