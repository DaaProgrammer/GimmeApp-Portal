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
$distance_preference = $_POST['distance_preference'];
$gender = $_POST['gender'];
$handicap = $_POST['handicap'];

// Validate password
if (empty($token) || empty($distance_preference) || empty($gender) || empty($handicap)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Something went wrong, please make sure to fill in all the fields"));
    exit;
}


// Prepare the data for insertion
$preferences = [
    'uid' => $uid,
    'distance' => $distance_preference,
    'gender' => $gender,
    'handicap' => $handicap,
    'tees'=>''
];


// Insert the user into the database
$db = $supabase->initializeDatabase('gimme_user_preferences', "uid");

$existingPreferences = $db->findBy("uid", $uid)->getResult();

if (!empty($existingPreferences)) {
    try {
        $updateResult = $db->update($uid, $preferences);
        if (!empty($updateResult)) {
            http_response_code(200);
            echo json_encode(array("msg" => "Preferences updated successfully"));
            exit;
        } else {
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to update preferences"));
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
    exit;
}else{
    try {
        $data = $db->insert($preferences);
        if (!empty($data)) {
            http_response_code(200);
            echo json_encode(array("msg" => "Preferences saved successfully"));
            exit;
    
        } else {
            http_response_code(400);
            echo json_encode(array("msg" => "Something went wrong"));
            exit;
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("msg" => $e->getMessage()));
        exit;
    }
    exit;
}

?>