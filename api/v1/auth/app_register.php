<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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


// use Mail\RegisterEmail;
// $RegisterEmail = new RegisterEmail();

// require '../emails/email.php';
require_once '../util/util.php';
require '../auth/email_config.php';
require '../emails/email.php';

error_reporting(0);
$emailSender = new Email();

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$terms_and_conditions = $_POST['terms_and_conditions'];
$surname = $_POST['surname'];

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid email address"));
    exit;
}

// Validate password
if (empty($password)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Password cannot be empty"));
    exit;
}

if (empty($surname)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Surname cannot be empty"));
    exit;
}

// Validate terms_and_conditions
if (!isset($terms_and_conditions) || $terms_and_conditions !== true) {
    http_response_code(400);
    echo json_encode(array("msg" => "You must agree to the terms and conditions to register"));
    exit;
}


// Check if email already exists
$check_email = $supabase->initializeDatabase('gimme_users');
$emailExists = $check_email->findBy("email", $email)->getResult();

if (!empty($emailExists)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Email already registered"));
    exit;
}

$code = rand(1000, 9999);
// Prepare the data for insertion
$newUser = [
    'user_role' => 'user',
    'name' => $username,
    'surname' => $surname,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'surname' => 'none',
    'contact_number' => '000000000',
    'status' => 'active',
    'register_otp' => $code,
    'registration_status' => 'inactive',
];


// Insert the user into the database
$db = $supabase->initializeDatabase('gimme_users');

try {
    $data = $db->insert($newUser);
    if (!empty($data)) {
        http_response_code(200);
        echo json_encode(array("msg" => "success"));

        $template = $emailSender->generateUserRegisteredTemplate($email, $username, $code);

        $response = $mailjet->sendEmail(
            $template['replyToEmail'], 
            $template['emailTitle'],
            $template['emailTo'], 
            $template['emailToName'],
            $template['emailSubject'],
            $template['emailMessage']
        );

        // $response = json_decode($response, true);
        // $log_file_name = $email . "_" . "Registration" . "_" . date("Y-m-d") . ".txt";
        // $log_file = fopen("../../Tools/Mail/Logs/" . $log_file_name, "w") or die("Unable to open file!");
        // fwrite($log_file, json_encode($response));

        exit;
    } else {
        http_response_code(500);
        echo json_encode(array("msg" => "Failed to register user. Please try again."));
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => $e->getMessage()));
}
