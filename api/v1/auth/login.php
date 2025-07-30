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
$email = $_POST['email'];
$internal_login = $_POST['internal_login'];
$password = $_POST['password'];
$_POST = json_decode(file_get_contents('php://input'), true);


if(empty($email)){
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials1"));
    exit;
}
// Sanitize email
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials2"));
    exit;
}

if(empty($password)){
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials3"));
    exit;
}
// Validate password
if (empty($password)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials4"));
    exit;
}

$remember_me = false;
$access_time = 7;
if (isset($_POST['remember_me'])) {
    if ($_POST['remember_me'] === true) {
        $remember_me = true;
        $access_time = 30;
    }
}

// check if email already exists
$check_email = $supabase->initializeDatabase('gimme_users');
try{
    $response = $check_email->findBy("email", $email)->getResult(); 
    // check if response is not empty
    if(!empty($response)){
        // get the password from the response
        $password_db = $response[0]->password;
        if(password_verify($password, $password_db) || $internal_login==true){

            // set the token expire time (Currently 1 Week)
            $token_expire_time = time() + (60 * 60 * 24 * $access_time);
            // create token
            $token = JWT::encode(array(
                "uname" => $response[0]->name,
                "surname" => $response[0]->surname,
                "email" => $response[0]->email,
                "user_role" => $response[0]->user_role,
                "user_id" => $response[0]->id,
                "exp" => $token_expire_time
            ), SECRET_KEY, 'HS256');
            // set header x-access-token with token
            header('x-access-token: '.$token);
            // return 200 success with token
            http_response_code(200);
            echo json_encode(array("token" => $token, "response" => $response, "remember_me" => $remember_me));
            exit;
        } else {
            // return 400 error with message
            http_response_code(400);
            echo json_encode(array("msg" => "Authentication failed"));
            exit;
        }
    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}


