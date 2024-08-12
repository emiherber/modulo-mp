<?php

class MercadoPago {
  private $preference;
  private $claveSecreta = '380e7a952a2d8a1dbdb3a6498709bdc7840827e2be5e22b2a8bff890b4d5a045';

  function __construct()
  {
    MercadoPago\SDK::setAccessToken("APP_USR-7793416169177571-032622-345717d068044b7c1e118fad16f6a229-1303671046");
  }

  function setPreference() {
    $this->preference = new MercadoPago\Preference();
    $this->preference->payment_methods = [
      "excluded_payment_types" => [
        ["id" => "ticket"],
        ["id" => "atm"],
      ]
    ];
  }

  function setItems($items) {
    $itemsPreference = [];
    foreach ($items as $value) {
      $item = new MercadoPago\Item();
      $item->id = $value['id'];
      $item->title = $value['title']; 
      $item->quantity = $value['quantity'];
      $item->unit_price = $value['unit_price'];
      $item->currency_id = "ARS";
      $itemsPreference[] = $item;
    }

    $this->preference->items= $itemsPreference;
    
    $this->preference->save();
  }

  function getBotonPagar() {
    return "<a href='". $this->preference->init_point ."' target='_blank'> <button>Paga con MercadoPago</button>  </a>";
  }

  function getDetalleOperacion($idOperacion) {
    $detalle = ['id' => $idOperacion, 'total_paid_amount' => 0, 'net_received_amount' => 0, 'items' => []];
    $pago = MercadoPago\Payment::find_by_id($idOperacion);
    echo '<pre>';
    print_r($pago);
    echo '</pre>';
    echo "<br> ------------------------------------------: <br>";

    $detalle['total_paid_amount'] = $pago->transaction_details->total_paid_amount;
    $detalle['net_received_amount'] = $pago->transaction_details->net_received_amount;

    foreach ($pago->additional_info->items as $key => $value) {
      $detalle['items'][$key] = [
        'id' => $value->id,
        'title' => $value->title,
        'quantity' => $value->quantity,
        'unit_price' => $value->unit_price,
      ];
    }

    return $detalle;
  }

  function validarOrigenOperacion($xSignature, $xRequestId, $dataID) {
    // Separating the x-signature into parts
    $parts = explode(',', $xSignature);
    $ts = null;
    $hash = null;
    // Iterate over the values to obtain ts and v1
    foreach ($parts as $part) {
      // Split each part into key and value
      $keyValue = explode('=', $part, 2);
      if (count($keyValue) == 2) {
        $key = trim($keyValue[0]);
        $value = trim($keyValue[1]);
        if ($key === "ts") {
          $ts = $value;
        } elseif ($key === "v1") {
          $hash = $value;
        }
      }
    }

    // Generate the manifest string
    $manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";
    $sha = hash_hmac('sha256', $manifest, $this->claveSecreta);
    return $hash === $sha;
  }
}

/*

// Obtain the x-signature value from the header
$xSignature = $_SERVER['HTTP_X_SIGNATURE'];
$xRequestId = $_SERVER['HTTP_X_REQUEST_ID'];
$dataID = $_GET['data_id'];
// Separating the x-signature into parts
$parts = explode(',', $xSignature);
// Initializing variables to store ts and hash
$ts = null;
$hash = null;


fputs($file,"$manifest \r\n");
// Create an HMAC signature defining the hash type and the key as a byte array

*/