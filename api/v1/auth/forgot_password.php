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
require_once '../util/util.php';
require '../auth/email_config.php';
require '../emails/email.php';
// require '../emails/email.php';
$emailSender = new Email();

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
    echo json_encode(array("msg" => "Invalid email address"));
    exit;
}
// Sanitize email
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // return 400 error with message
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid email address"));
    exit;
}

$check_email = $supabase->initializeDatabase('gimme_users');

try {
    $response = $check_email->findBy("email", $email)->getResult();

    // If the email doesn't exist
    if (empty($response)) {
        http_response_code(400);
        echo json_encode(array("msg" => "Email not found."));
        exit;
    }

    
    
    $userId = $response[0]->id;
    $emailTo = $response[0]->email;
    $emailToName = $response[0]->name;

    // Generate a 4-digit code
    $code = rand(1000, 9999);

    // Update the user's record with the generated code using the ID
    $updateResponse = $check_email->update($userId, array("forgot_password_code" => $code));

    if (!empty($updateResponse)) {

        http_response_code(200);
        // create reset link with base url
        // $resetEmailLink = SITE_URL."reset-password?code=" . $code . "&email=" . $email;
        echo json_encode(array("msg" => "success", "email" => $email));
        
        $template = $emailSender->generateForgotPasswordTemplate($emailTo, $emailToName, $code);

            $response = $mailjet->sendEmail(
                $template['replyToEmail'], 
                $template['emailTitle'],
                $template['emailTo'], 
                $template['emailToName'],
                $template['emailSubject'],
                $template['emailMessage']
            );

            // $response = json_decode($response, true);
            // $log_file_name = $emailTo . "_" . "Forgot Password" . "_" . date("Y-m-d") . ".txt";
            // $log_file = fopen("../../Tools/Mail/Logs/" . $log_file_name, "w") or die("Unable to open file!");
            // fwrite($log_file, json_encode($response));

        exit;
    } else {
        http_response_code(500);
        echo json_encode(array("msg" => "Failed to generate reset code. Please try again."));
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

