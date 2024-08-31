<?php

class MercadoPago {
  private $preference;
  private $claveSecreta = 'clave_notificacion_es_de_donde_pertenece';

  function __construct()
  {
    MercadoPago\SDK::setAccessToken("");
  }

  function setPreference() {
    $this->preference = new MercadoPago\Preference();
    $this->preference->auto_return = "approved";
    $this->preference->back_urls = array(
      "success" => "https://pet-complete-mule.ngrok-free.app", //dominio ngrok para local
      "failure" => "https://pet-complete-mule.ngrok-free.app",
      "pending" => "https://pet-complete-mule.ngrok-free.app"
    );
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

  function getDetalleOperacion($idOperacion, $soloAcreditados = true) {
    $detalle = ['id' => $idOperacion, 'total_paid_amount' => 0, 'net_received_amount' => 0, 'items' => []];
    $pago = MercadoPago\Payment::find_by_id($idOperacion);

    if(isset($pago) && $soloAcreditados && $pago->status_detail != "accredited"){
      return [];
    } 

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
