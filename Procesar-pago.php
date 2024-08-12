<?php
$input = file_get_contents("php://input");
$data = json_decode($input);

if (!isset($data->type) && $data->type != 'payment') {
  http_response_code(200);
  exit();
}

require_once './vendor/autoload.php';
require_once 'MercadoPago.php';

$mp = new MercadoPago();

$file = fopen("notificacion-mp.log", "a");

fputs($file,"-------------------------- \r\n $input \r\n");
fputs($file, json_encode($_SERVER, JSON_PRETTY_PRINT) . "\r\n");
fputs($file, json_encode($_GET, JSON_PRETTY_PRINT) . "\r\n");

if ($mp->validarOrigenOperacion($_SERVER['HTTP_X_SIGNATURE'], $_SERVER['HTTP_X_REQUEST_ID'], $_GET['data_id'])) {
  // HMAC verification passed
  fputs($file,"HMAC verification passed \r\n");
} else {
  // HMAC verification failed
  fputs($file,"HMAC verification failed \r\n");
}


$operacion = $mp->getDetalleOperacion($data->data->id);

fputs($file, json_encode($operacion, JSON_PRETTY_PRINT)."\r\n");

fclose($file);

http_response_code(201);