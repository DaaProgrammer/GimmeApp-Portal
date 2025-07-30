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

$email = $_POST['email'];
if(empty($email)){
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials"));
    exit;
}
// Sanitize email
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials"));
    exit;
}
$password = $_POST['password'];
if(empty($password)){
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials"));
    exit;
}
// Validate password
if (empty($password)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid credentials"));
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

        // if user_role is not user then return 400 error with message
        if($response[0]->user_role !== "user"){
            http_response_code(400);
            echo json_encode(array("msg" => "Invalid permissions"));
            exit;
        }
        // get the password from the response
        $password_db = $response[0]->password;
        if(password_verify($password, $password_db)){

            // set the token expire time (Currently 1 Week)
            $token_expire_time = time() + (60 * 60 * 24 * $access_time);
            // create token
            $token = JWT::encode(array(
                "email" => $response[0]->email,
                "user_role" => $response[0]->user_role,
                "user_id" => $response[0]->id,
                "exp" => $token_expire_time
            ), SECRET_KEY, 'HS256');
            // set header x-access-token with token
            header('x-access-token: '.$token);
            // return 200 success with token

            $plans_db = $supabase->initializeDatabase('gimme_user_preferences','id');
    
            try {
                $query = [
                    'select' => '*',
                    'from'   => 'gimme_user_preferences',
                    'where' => 
                    [
                        'uid' => 'eq.'.$response[0]->id,
                    ]
                ];
        
        
                $plans = $plans_db->createCustomQuery($query)->getResult();

                if (!empty($plans)) {
                    $response[0]->distance = $plans[0]->distance; 
                    $response[0]->gender = $plans[0]->gender; 
                    $response[0]->handicap = $plans[0]->handicap; 
                    $response[0]->tees = $plans[0]->tees; 
                }
            

                http_response_code(200);
                echo json_encode(array("msg"=>"success", "token" => $token, "response" => $response,  "remember_me" => $remember_me, "user_role" => $response[0]->user_role));

            }catch(Exception $e){
                http_response_code(400);
                echo json_encode(array("msg" => $e->getMessage()));
                exit;
            }

            exit;

        } else {
            // return 400 error with message
            http_response_code(400);
            echo json_encode(array("msg" => "Incorrect login credentials"));
            exit;
        }
    }
}
catch(Exception $e){
    http_response_code(400);
}


