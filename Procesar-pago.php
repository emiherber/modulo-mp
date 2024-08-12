<?php
$input = file_get_contents("php://input");
$data = json_decode($input);

if (!isset($data->type) && $data->type != 'payment') {
  http_response_code(200);
  exit();
}

$file = fopen("notificacion-mp.log", "a");

fputs($file,"-------------------------- \r\n $input \r\n");
fputs($file, json_encode($_SERVER, JSON_PRETTY_PRINT) . "\r\n");
fputs($file, json_encode($_GET, JSON_PRETTY_PRINT) . "\r\n");

// Obtain the x-signature value from the header
$xSignature = $_SERVER['HTTP_X_SIGNATURE'];
$xRequestId = $_SERVER['HTTP_X_REQUEST_ID'];
$dataID = $_GET['data_id'];
// Separating the x-signature into parts
$parts = explode(',', $xSignature);
// Initializing variables to store ts and hash
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

// Obtain the secret key for the user/application from Mercadopago developers site
$secret = "380e7a952a2d8a1dbdb3a6498709bdc7840827e2be5e22b2a8bff890b4d5a045";
// Generate the manifest string
$manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";
fputs($file,"$manifest \r\n");
// Create an HMAC signature defining the hash type and the key as a byte array
$sha = hash_hmac('sha256', $manifest, $secret);
if ($sha === $hash) {
  // HMAC verification passed
  fputs($file,"HMAC verification passed \r\n");
} else {
  // HMAC verification failed
  fputs($file,"HMAC verification failed \r\n");
}


require_once './vendor/autoload.php';
require_once 'MercadoPago.php';

$mp = new MercadoPago();
$operacion = $mp->getDetalleOperacion($data->data->id);

fputs($file, json_encode($operacion, JSON_PRETTY_PRINT)."\r\n");

fclose($file);

http_response_code(201);