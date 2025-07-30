<?php 
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
require '../../Tools/JWT/jwt.php';
require '../../Tools/JWT/key_signed.php';
require_once '../../Tools/Supabase/vendor/autoload.php';
$config = require_once '../../Tools/Supabase/config.php';

header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

$token = $_POST['token'];

if(empty($token)){
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("status" => "invalid", "msg" => "Invalid token"));
    exit;
}
try {
// validate the jwt token
$jwt = JWT::decode($token, SECRET_KEY, array('HS256'));
// check if the token is valid
if($jwt->exp < time()){
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("status" => "invalid", "msg" => "Invalid token"));
    exit;
} else {
    // return 200 success with message
    $token_valid = true;
    $uid = $jwt->user_id;
    http_response_code(200);
}
} catch (Exception $e) {
// return 400 error with message
http_response_code(400);
echo json_encode(array("status" => "invalid", "msg" => "Invalid token", "error" => $e->getMessage()));
exit;
}

?>