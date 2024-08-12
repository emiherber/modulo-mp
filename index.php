<?php
require_once './vendor/autoload.php';

require_once 'MercadoPago.php';

$mp = new MercadoPago();
$mp->setPreference();

$mp->setItems([
  [
    'id' => '00001',
    'title' => 'item1', 
    'quantity' => 1,
    'unit_price' => 100
  ],
  [
    'id' => '00002',
    'title' => 'item2', 
    'quantity' => 1,
    'unit_price' => 100
  ],
  [
    'id' => '00003',
    'title' => 'item3', 
    'quantity' => 1,
    'unit_price' => 100
  ],
]);

echo $mp->getBotonPagar();