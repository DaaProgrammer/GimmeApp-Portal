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

$email = $_POST['email'] ?? null;
$new_password = $_POST['new_password'] ?? null;
$code = $_POST['code'] ?? null;

if($code == null || $email == null || $new_password == null) {  
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid reset credentials"));
    exit;
}    

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

if (empty($new_password)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid password"));
    exit;
}

// check if password is at least 8 characters long
if (strlen($new_password) < 8) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Password must be at least 8 characters long"));
    exit;
}

$check_code = $supabase->initializeDatabase('gimme_users');
$response = $check_code->findBy("email", $email)->getResult();

// If the email doesn't exist
if (empty($response)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Email not found."));
    exit;
}

$response = json_decode(json_encode($response), true);
$forgot_password_code = $response[0]['forgot_password_code'];

// check if the code matches the one in the database
if ($forgot_password_code != $code) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid or expired code"));
    exit;
} else {

    $userId = $response[0]['id'];
    // Generate a 4-digit code
    $code = null;
    // hash the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    // Update the user's record with the generated code and the new password using the ID
    $updateResponse = $check_code->update($userId, array("forgot_password_code" => $code, "password" => $hashed_password));

    if (!empty($updateResponse)) {
        // return 200 success
        http_response_code(200);
        echo json_encode(array("msg" => "success"));
        exit;

        // Show the create password field in the user's app
    }    

}
