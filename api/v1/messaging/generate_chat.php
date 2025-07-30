<?php 
// display errors
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
header('Content-Type: application/json');

$_POST = json_decode(file_get_contents('php://input'), true);

require '../auth/token.php';
// require_once '../util/util.php';
// require '../auth/email_config.php';
// require '../emails/email.php';

// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}


if(!isset($_POST['name'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Please enter the name"));
    exit;
}


if(!isset($_POST['surname'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Please enter the surname"));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
// $emailSender = new Email();
$iframe_link = 'https://go.crisp.chat/chat/embed/?website_id=4865d913-d1f3-4323-b820-a1e6d0a11f36';

$email_jwt = $jwt->email;
$email_jwt = urlencode($email_jwt);
$token_id = hash('sha256', $email_jwt);

$nickname = $_POST['name'].' '.$_POST['surname'];
$nickname = str_replace(' ', '%20', $nickname);

$iframe_link .= '&user_email='.$u_email.'&user_nickname='.$nickname.'&crisp_sid=user-'.$uid.'&token_id='.$token_id.'&session_merge=true';

http_response_code(200);
echo json_encode(array("iframe_link" => $iframe_link));
?>