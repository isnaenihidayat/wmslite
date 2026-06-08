<?php

//dev
//$url = "https://eservice-apac.dbschenker.com.dbschenker.com/api-catalog/v1/wms";
//prod
//$url = "https://shield.dbschenker.com/shield/ngw-api-catalog/v1/wms/receipt/list";

if (isset($_GET['externOrderKey'])) {
  $call = '"externOrderKey": "' . $_GET['externOrderKey'] . '"';
}

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



  echo "<strong>OUTBOUND</strong><br>";

  if (empty($result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey)) {
    die('tidak ditemukan');
  }

  $externOrderKey = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey;
  $actualShipDate = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->actualShipDate;
  if (!empty($actualShipDate)) {
    $date = DateTime::createFromFormat('d/m/Y H:i:s', $actualShipDate);
    $actualShipDate = $date->format('Y-m-d H:i:s');
  }


  $status = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->status;

  //if (($status == 4 || $status == 9) && !empty($actualShipDate)) {
  //if (!empty($actualShipDate)) {

    echo 'GON/PO: ' . $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->externOrderKey;
    echo '<br>actualShipDate: ' . $actualShipDate;

    echo '<br>';

    echo "
    <style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}
</style>
    ";

    echo '<br>';
    echo '<table id="customers">';
    echo '<tr>';
    echo '<th>No</th>';
    echo '<th>SKU</th>';
    echo '<th>Lottable07</th>';
    echo '<th>Lottable03</th>';
    echo '<th>shippedQty</th>';
    echo '<tr>';
    $detail = $result->result->shipmentOrder[0]->shipmentOrderHeader[0]->shipmentOrderDetail;
    $no = 1;
    foreach ($detail as $r) {
      echo '<tr>';
      echo '<td>' . $no . '</td>';
      echo '<td>' . $r->sku . '</td>';
      echo '<td>' . $r->lottable07 . '</td>';
      echo '<td>' . $r->lottable03 . '</td>';
      echo '<td>' . $r->shippedQty . '</td>';
      echo '</tr>';
      $no++;
      
    }
    echo '</table>';
  //}
  curl_close($curl);
}
