<?php
// show errors
error_reporting(E_ALL);
ini_set('display_errors', '1');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // It's a preflight request, respond accordingly
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    exit;
}
header("Access-Control-Allow-Origin: *");

require '../../Tools/JWT/jwt.php';
require '../../Tools/JWT/key_signed.php';
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
    http_response_code(200);
    echo json_encode(array("status" => "valid", "msg" => "Valid token", "exp" => $jwt->exp, "name" => $jwt->name, "surname" => $jwt->surname, "user_role" => $jwt->user_role, "email" => $jwt->email, "contact_number" => $jwt->contact_number));
    exit;
}
} catch (Exception $e) {
// return 400 error with message
http_response_code(400);
echo json_encode(array("status" => "invalid", "msg" => "Invalid token"));
exit;
}