<?php

class MercadoPago {
  private $preference;

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


}