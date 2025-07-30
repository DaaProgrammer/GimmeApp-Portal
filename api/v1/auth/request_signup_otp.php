<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
require '../auth/email_config.php';
require 'emails/register_template.php';


use Mail\RegisterEmail;
$RegisterEmail = new RegisterEmail();

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

$email = $_POST['email'];


$check_code = $supabase->initializeDatabase('gimme_users');
$response = $check_code->findBy("email", $email)->getResult();

// If the email doesn't exist
if (empty($response)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Email not found."));
    exit;
}

$response = json_decode(json_encode($response), true);


$code = rand(1000, 9999);
// Prepare the data for insertion
$new_register_otp = [
    'register_otp' => $code,
];

$update_status = $supabase->initializeDatabase('gimme_users');

try{
    $update_status->update($response[0]['id'], $new_register_otp);

    // return 200 success
    http_response_code(200);
    echo json_encode(array("msg" => "valid"));
    exit;

}catch(Exception $e){
    echo json_encode(array("msg" => "Something went wrong"));
    http_response_code(400);
}
?>