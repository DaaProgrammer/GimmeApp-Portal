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
if(empty($_POST['token']) || empty($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}


// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$plans_db = $supabase->initializeDatabase('gimme_plan_game','id');

$course_id = $_POST['course_id'];
// echo $uid;
$status = "pending";
try{

    $query = [
        'select' => "*",
        'from' => "gimme_plan_game",
        'where' => [
            'user_id' => 'eq.'.$uid,
            'course_id' => 'eq.'.$course_id,
            'status' => 'eq.'.$status
        ]
    ];

    $plans = $plans_db->createCustomQuery($query)->getResult();

    if(empty($plans)){
        http_response_code(400);
        echo json_encode(array("msg" => "No data found"));
        exit;
    }

    http_response_code(200);

    //Return the Total Putts and Shots 
    $quick_putts_total = 0;
    $quick_shots_total = 0;
    
    foreach ($plans as $plan) {
        for ($i = 0; $i < 18; $i++) {
            $quick_putts_total += $plan->game_data->holes[$i]->quick_putss;
            $quick_shots_total += $plan->game_data->holes[$i]->quick_shots;
        }
    }
    
    $lastUpdate = date('d F Y', strtotime($plans[0]->updated_at));
    echo json_encode(array("plans" => $plans, 'total_putts' => $quick_putts_total, 'total_shots' => $quick_shots_total, "lastupdate" => $lastUpdate));
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}

?>