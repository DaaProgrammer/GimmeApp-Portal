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

// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid Request"));
    exit;
}

if(!isset($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Course ID are required"));
    exit;
}

if(!isset($_POST['holes'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Course ID are required"));
    exit;
}

$holeData = $_POST['holes'];
$course_id = $_POST['course_id'];
$gamemode = $_POST['gamemode'];

if(empty($holeData)){
    http_response_code(400);
    echo json_encode(array("msg" => "Hole data cannot be empty"));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);



$plans_db = $supabase->initializeDatabase('gimme_plan_game','id');

$query = [
    'select' => "*",
    'from' => "gimme_plan_game",
    'where' => [
        'user_id' => 'eq.'.$uid,
        'course_id' => 'eq.'.$course_id
    ]
];

// Validation on object array
if(count($holeData) > 18){
    http_response_code(400);
    echo json_encode(array("msg" => "Number of holes is too large. Max holes is 18"));
    exit;
}

$current_gps = '';

try{
    $plans = $plans_db->createCustomQuery($query)->getResult();

    if($gamemode=='plan'){
	    $planning_gps = $plans[0]->planning_gps;
	    $current_gps = $planning_gps->current_gps;   

		// Grab the last hole object
		$lastHole = end($holeData['holes']);
		// Reset array pointer
		reset($holeData['holes']);

		// Grab the last shot object within the last hole
		$lastShot = end($lastHole['shots']);

		// Append the $current_gps value to the 'gps' key within the last shot object
		$lastShot['gps'] = $current_gps;

		// Reassign the last shot object to the last shot within the last hole
		$lastHole['shots'][count($lastHole['shots']) - 1] = $lastShot;

		// Reassign the last hole object to the last hole within the holeData array
		$holeData['holes'][count($holeData['holes']) - 1] = $lastHole;

    }


    if(empty($plans)){
        http_response_code(400);
        echo json_encode(array("msg" => "Plan not found"));
        exit;
    }

	
    $result = $plans_db->update($plans[0]->id,['game_data' => $holeData]);

    http_response_code(200);
    echo json_encode(array("msg" => "Hole data saved!", "holeData" => $holeData));
    exit;
} catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}


/*
Required:
- Token
- User ID
- Course ID
- Current Hole
*/

?>