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
header('Content-Type: application/json');
$_POST = json_decode(file_get_contents('php://input'), true);

require '../auth/token.php';

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

$current_gps = $_POST['current_gps'];
$distancePopupValue = $_POST['distancePopupValue'];
$course_id = $_POST['course_id'];
$user_id = $_POST['user_id'];
$hole_id = $_POST['hole_id'];
$distance_pref = $_POST['distance_pref'];
$mode = $_POST['mode'];
$current_shot = $_POST['current_shot'];

// create json object to store in db
$courseData = [];
$courseData['current_gps'] = $current_gps;
$courseData['distance'] = $distancePopupValue;
$courseData['course_id'] = $course_id;
$courseData['user_id'] = $user_id;
$courseData['hole_number'] = $hole_id;
$courseData['current_shot'] = $current_shot;

$updatedPlanningData = [
    'planning_gps' => $courseData,
];


if($mode==='plan'){
    $db = $supabase->initializeDatabase('gimme_plan_game', 'id');

    $query = [
        'select' => '*',
        'from'   => 'gimme_plan_game',
        'where'  => [
            'user_id' => 'eq.'.$user_id,
            'course_id' => 'eq.'.$course_id,
        ],
        'limit'  => 1
    ];

    try{
        $plan_game = $db->createCustomQuery($query)->getResult();
        if(empty($plan_game)){
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        // get id of the record
        $id = $plan_game[0]->id;

        // update the record
        try{
            $db->update($id,$updatedPlanningData);
        
            http_response_code(200);
            echo json_encode(array("msg" => "success"));
            exit;
        }
        catch(Exception $e){
            http_response_code(400);
            echo $e->getMessage();
            exit;
        } 

        
    } catch(Exception $e){
        http_response_code(400);
        echo $e->getMessage();
        exit;
    } 
}else if($mode==='match'){
    $db = $supabase->initializeDatabase('gimme_scores', 'id');
    $status = 'pending';
    $match_type = 'match';
    $query = [
        'select' => '*',
        'from'   => 'gimme_scores',
        'where'  => [
            'user_id' => 'eq.'.$uid,
            'match_type' => 'eq.'.$match_type,
            'status' => 'eq.'.$status,
        ]
    ];

    try{
        $plan_game = $db->createCustomQuery($query)->getResult();
        if(empty($plan_game)){
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        // get id of the record
        $id = $plan_game[0]->id;

        // update the record
        try{
            $db->update($id,$updatedPlanningData);
        
            http_response_code(200);
            echo json_encode(array("msg" => "success"));
            exit;
        }
        catch(Exception $e){
            http_response_code(400);
            echo $e->getMessage();
            exit;
        } 

        
    } catch(Exception $e){
        http_response_code(400);
        echo $e->getMessage();
        exit;
    } 
}else if($mode==='event'){
    $db = $supabase->initializeDatabase('gimme_scores', 'id');
    $status = 'active';
    $match_type = 'event';
    $query = [
        'select' => '*',
        'from'   => 'gimme_scores',
        'where'  => [
            'user_id' => 'eq.'.$uid,
            'match_type' => 'eq.'.$match_type,
            'status' => 'eq.'.$status,
        ]
    ];

    try{
        $plan_game = $db->createCustomQuery($query)->getResult();
        if(empty($plan_game)){
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        // get id of the record
        $id = $plan_game[0]->id;

        // update the record
        try{
            $db->update($id,$updatedPlanningData);
        
            http_response_code(200);
            echo json_encode(array("msg" => "success"));
            exit;
        }
        catch(Exception $e){
            http_response_code(400);
            echo $e->getMessage();
            exit;
        } 

        
    } catch(Exception $e){
        http_response_code(400);
        echo $e->getMessage();
        exit;
    } 
}

?>