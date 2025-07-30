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

// require '../../Tools/JWT/jwt.php';
// require '../../Tools/JWT/key_signed.php';
// require_once '../../Tools/Supabase/vendor/autoload.php';
// $config = require_once '../../Tools/Supabase/config.php';
header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

require '../auth/token.php';

error_reporting(0);
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$token = $_POST['token'];
// Validate password
if (empty($token)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid Token"));
    exit;
}

// Insert the user into the database
$db = $supabase->initializeDatabase('gimme_user_preferences', "uid");
$existingPreferences = $db->findBy("uid", $uid)->getResult();

if (!empty($existingPreferences)) {
    http_response_code(200);
    echo json_encode(array("msg" => "success"));
    exit;
}else{    
    http_response_code(400);
    echo json_encode(array("msg" => "No preferences found"));
    exit;
}

?>