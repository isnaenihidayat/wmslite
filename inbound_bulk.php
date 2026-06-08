<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://eservice-apac-fat.dbschenker.com/api-catalog/v1/wms/receipt/list',
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
          "storerKey": "IDGEHC"
        }
      ]
    }
  ]
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Basic RTE5dXpqYmZET2lQcmxmdDpYbmBtNW9qeERqeDNjNGxPOyNpVFJTVD1yO1BcIydYcg=='
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


  echo "INBOUND CHECK<br>";

  foreach ($result->result->advancedShipNotice[0]->advancedShipNoticeHeader as $row) {
    echo '[hawb] hawb: ' . $row->hawb;
    echo '<br>[modality] susr1: ' . $row->susr1;
    echo '<br>[ship_method] type: ' . $row->type . ' >>> ' . $type[$row->type];
    echo '<br>[sso_delivery_id] externReceiptKey: ' . $row->externReceiptKey;
    echo '<br>[ata] arrivalDateTime: ' . $row->arrivalDateTime;

    echo '<br>';

    foreach ($row->advancedShipNoticeDetail as $r) {
      echo '&emsp;[descr] SKU (harus trigger lagi ke endpoint master sku): ' . $r->sku . '<br>';
      echo '&emsp;[loc] toLoc: ' . $r->toLoc . '<br>';
      echo '&emsp;[delivery_id] lottable02: ' . $r->lottable02 . '<br>';
      echo '&emsp;[locator] lottable08: ' . $r->lottable08 . '<br>';
      echo '&emsp;[qty] qtyReceived: ' . $r->qtyReceived . '<br>';
    }

    echo '<hr><br>';
  }




  die();
}

curl_close($curl);
