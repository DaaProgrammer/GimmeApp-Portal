<?php 
error_reporting(E_ALL);
ini_set('display_errors', '1');
require '../../Tools/JWT/jwt.php';
require '../../Tools/JWT/key_signed.php';
require_once '../../Tools/Supabase/vendor/autoload.php';
$config = require_once '../../Tools/Supabase/config.php';

$token = $_POST['token'];


// $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InNpcGhhbWFuZGxhZHVlbmRlQGdtYWlsLmNvbSIsInVzZXJfcm9sZSI6InVzZXIiLCJ1c2VyX2lkIjo0NywiZXhwIjoxNzEyMDQ5NDkzfQ.OxZ4u-4CAOWeRocj61szj79N8JGawVDjj6_uj0eZjyY";
// $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InNpcGhhbWFuZGxhZHVlbmRlQGdtYWlsLmNvbSIsInVzZXJfcm9sZSI6InVzZXIiLCJ1c2VyX2lkIjo0NywiZXhwIjoxNzEyNjYxNzE0fQ.2dkSg64rp3iP5tgUu0S5zrbERcuOa_NPo2vfmY1MvnM";

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
    // print_r($jwt);
    // return 200 success with message
    $token_valid = true;
    $uid = $jwt->user_id;
    $u_email = $jwt->email;
    // $u_name = $jwt->uname;
    http_response_code(200);
}
} catch (Exception $e) {
// return 400 error with message
http_response_code(400);
echo json_encode(array("status" => "invalid", "msg" => "Invalid token", "error" => $e->getMessage()));
exit;
}

?>