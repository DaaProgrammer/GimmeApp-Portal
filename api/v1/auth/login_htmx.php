<?php
// show errors
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // It's a preflight request, respond accordingly
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Credentials: true");
    exit;
}
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");

require '../../Tools/JWT/jwt.php';
require '../../Tools/JWT/key_signed.php';
require_once '../../Tools/Supabase/vendor/autoload.php';
$config = require_once '../../Tools/Supabase/config.php';

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

header('Content-Type: text/html');

$email = $_POST['email'];
if(empty($email)){
    // return 400 error with message
    echo "<div class='error-message'>Authentication failed</div>";
    echo '<script>gimmeToast("Authentication failed", "error")</script>';
    exit;
}
// Sanitize email
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // return 400 error with message
    echo "<div class='error-message'>Email failed</div>";
    echo '<script>gimmeToast("Email failed", "error")</script>';
    exit;
}
$password = $_POST['password'];
if(empty($password)){
    // return 400 error with message
    echo "<div class='error-message'>Password failed</div>";
    echo '<script>gimmeToast("Password failed", "error")</script>';
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
        // Access check. Exit if user and not admin or event organiser
        if ($response[0]->user_role == 'user') {
            echo "<div class='error-message'>Invalid Access</div>";
            echo '<script>gimmeToast("Invalid Access", "error")</script>';
            exit;
        }
        // get the password from the response
        $password_db = $response[0]->password;
        if(password_verify($password, $password_db)){

            // set the token expire time (Currently 1 Week)
            $token_expire_time = time() + (60 * 60 * 24 * $access_time);
            // create token
            $token = JWT::encode(array(
                "name" => $response[0]->name,
                "surname" => $response[0]->surname,
                "email" => $response[0]->email,
                "contact_number" => $response[0]->contact_number,
                "user_role" => $response[0]->user_role,
                "user_id" => $response[0]->id,
                "exp" => $token_expire_time
            ), SECRET_KEY, 'HS256');
            // set header x-access-token with token
            header('x-access-token: '.$token);
            // return 200 success with token
            // setcookie('jwt', $token, $token_expire_time, '/', '', true, true);
            // echo json_encode(array("token" => $token, "response" => $response, "remember_me" => $remember_me));
            echo "<script>var token = '".$token."';</script>";
            echo '<script>document.cookie = "jwt="+token+"; path=/";</script>';
            echo '<script>gimmeToast("Success! Redirecting...", "success")</script>';
            echo '<script>setTimeout(function(){ window.location.href = "/"; }, 2000);</script>';
            exit;
        } else {
            echo "<div class='error-message'>Invalid Credentials</div>";
            echo '<script>gimmeToast("Invalid Credentials", "error")</script>';
            exit;
        }
    } else {
        echo "<div class='error-message'>Invalid Credentials</div>";
        echo '<script>gimmeToast("Invalid Credentials", "error")</script>';
        exit;
    }
}
catch(Exception $e){
    echo "<div class='error-message'>Invalid Credentials</div>";
    echo '<script>gimmeToast("Invalid Credentials", "error")</script>';
    exit;
}


