<?php
// show errors
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

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
require_once '../../Tools/Supabase/vendor/autoload.php';
$config = require_once '../../Tools/Supabase/config.php';

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

$code = $_POST['code'];
$email = $_POST['email'];
$type = $_POST['type'];

if (empty($code)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid code"));
    exit;
}

if (empty($email)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid email"));
    exit;
}

$check_code = $supabase->initializeDatabase('gimme_users');
$response1 = $check_code->findBy("email", $email)->getResult();
$uid = $response1[0]->id;
// If the email doesn't exist
if (empty($response1)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Email not found."));
    exit;
}

$response = json_decode(json_encode($response1), true);


if($type=='signup'){
    $register_otp = $response[0]['register_otp'];

    // check if the code matches the one in the database
    if ($register_otp != $code) {
        // return 400 error with message
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid/Expired code"));
        exit;
    } else{

        $update_status = $supabase->initializeDatabase('gimme_users');

        try{
            $update_status->update($response[0]['id'], array("registration_status" => "active"));

            $remember_me = false;
            $access_time = 7;
            if (isset($_POST['remember_me'])) {
                if ($_POST['remember_me'] === true) {
                    $remember_me = true;
                    $access_time = 30;
                }
            }
            $token_expire_time = time() + (60 * 60 * 24 * $access_time);
            // create token
            $token = JWT::encode(array(
                "email" => $response1[0]->email,
                "user_role" => $response1[0]->user_role,
                "user_id" => $response1[0]->id,
                "exp" => $token_expire_time
            ), SECRET_KEY, 'HS256');
            // set header x-access-token with token
            header('x-access-token: '.$token);
            // return 200 success with token
            http_response_code(200);
            echo json_encode(array("msg"=>"valid", "uid"=>$uid, "token" => $token, "response" => $response,  "remember_me" => $remember_me, "user_role" => $response1[0]->user_role));

            // return 200 success
            // http_response_code(200);
            // echo json_encode(array("msg" => "valid", "uid"=>$uid));
            exit;

        }catch(Exception $e){
            http_response_code(400);
        }

        exit;
    }



}else{
    $forgot_password_code = $response[0]['forgot_password_code'];

    // check if the code matches the one in the database
    if ($forgot_password_code != $code) {
        // return 400 error with message
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid/Expired code"));
        exit;
    } else{
        // return 200 success
        http_response_code(200);
        echo json_encode(array("msg" => "valid"));
        exit;
    }



}










