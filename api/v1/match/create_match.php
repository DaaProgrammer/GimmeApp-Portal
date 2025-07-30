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
require_once '../util/util.php';
require '../auth/email_config.php';
require '../emails/email.php';

// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$emailSender = new Email();

// Get the parameters from the request
$player1_id = htmlspecialchars($_POST['player1_id']);
$player2_id = htmlspecialchars($_POST['player2_id']);
$match_date = htmlspecialchars($_POST['match_date']);
$match_time = htmlspecialchars($_POST['match_time']);
$location = htmlspecialchars($_POST['location']);
$match_type = htmlspecialchars($_POST['match_type']);

// Validate the required fields
if(empty($player1_id) || empty($player2_id) || empty($match_date) || empty($match_time) || empty($location) || empty($match_type)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request. Please ensure all fields are filled."));
    exit;
}

// Additional validation for match_date to ensure it's a valid date
if(!DateTime::createFromFormat('Y-m-d', $match_date)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid date format. Please use YYYY-MM-DD."));
    exit;
}

// Additional validation for match_time to ensure it's a valid time
if(!preg_match("/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/", $match_time)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid time format. Please use HH:MM in 24-hour format."));
    exit;
}

// Initialize the users database
$match_db = $supabase->initializeDatabase('gimme_match','id');

try{
    // Create a match here
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

?>