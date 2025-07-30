<?php
// display errors
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
header('Content-Type: application/json');

$_POST = json_decode(file_get_contents('php://input'), true);

require '../auth/token.php';
require_once '../util/util.php';

error_reporting(0); // Turn off all error reporting
// Validate the required fields
if(empty($_POST['token']) || empty($_POST['course_id']) || empty($_POST['hole']) || empty($_POST['gamemode'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}


// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);


$course_id = $_POST['course_id'];
$hole = $_POST['hole'];
$gamemode = $_POST['gamemode'];
$roundID = $_POST['roundID'];
$shotnumber = $_POST['shotnumber'];

if($gamemode=='plan'){
    $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
    try {
        $query = [
            'select' => "game_data",
            'from' => "gimme_plan_game",
            'where' => [
                'user_id' => 'eq.'.$uid,
                'course_id' => 'eq.'.$course_id,
                'status' => 'eq.pending'
            ]
        ];
    
        $plans = $plans_db->createCustomQuery($query)->getResult();
        // print_r($plans[0]->game_data->holes[$hole-1]->shots);
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }
    

        $filter = $plans[0]->game_data->holes[$hole-1]->shots;
        $updatedShots = [];
        $counter = 0;
        foreach ($filter as $key => $shot) {
            if ($shot->shot_number != $shotnumber) {
                $counter+=1;
                $shot->shot_number = $counter;
                $updatedShots[] = $shot;
            }
        }

        $plans[0]->game_data->holes[$hole-1]->shots = $updatedShots;

        $newHoles = $plans[0]->game_data;
 
        try{
            $data = $plans_db->update($plans[0]->id, ['game_data'=>$newHoles]);
            if($data){
                http_response_code(200);
                echo json_encode(array("msg" => "Shot deleted successfully"));
            }else{
                http_response_code(400);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
}else if($gamemode=='match'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'pending';
    
    try {
        $query = '';
        if($_POST['roundID']!=0 || $_POST['roundID']!='0'){ 
            $query = [
                'select' => '*',
                'from'   => 'gimme_scores',
                'where' => 
                [
                    'match_type_id' => 'eq.'.$roundID,
                    'user_id' => 'eq.'.$uid
                ]
            ];
        }else{
            $query = [
                'select' => '*',
                'from'   => 'gimme_scores',
                'where' => 
                [
                    'user_id' => 'eq.'.$uid,
                    'status' => 'eq.'.$status,
                    'match_type' => 'eq.'.$gamemode,
                ]
            ];
        }
  
    
        $plans = $plans_db->createCustomQuery($query)->getResult();
        
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }
        $filter = $plans[0]->score_details->holes[$hole-1]->shots;
        $updatedShots = [];
        $counter = 0;
        foreach ($filter as $key => $shot) {
            if ($shot->shot_number != $shotnumber) {
                $counter+=1;
                $shot->shot_number = $counter;
                $updatedShots[] = $shot;
            }
        }

        $plans[0]->score_details->holes[$hole-1]->shots = $updatedShots;

        $newHoles = $plans[0]->score_details;
 
        try{
            $data = $plans_db->update($plans[0]->id, ['score_details'=>$newHoles]);
            if($data){
                http_response_code(200);
                echo json_encode(array("msg" => "Shot deleted successfully"));
            }else{
                http_response_code(400);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }


    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}else if($gamemode=='event'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'active';
    
    try {
        $query = '';
        if($_POST['roundID']!=0 || $_POST['roundID']!='0'){ 
            $query = [
                'select' => '*',
                'from'   => 'gimme_scores',
                'where' => 
                [
                    'match_type_id' => 'eq.'.$roundID,
                    'user_id' => 'eq.'.$uid,
                    'match_type' => 'eq.'.$gamemode,
                ]
            ];
        }else{
            $query = [
                'select' => '*',
                'from'   => 'gimme_scores',
                'where' => 
                [
                    'user_id' => 'eq.'.$uid,
                    'status' => 'eq.'.$status,
                    'match_type' => 'eq.'.$gamemode,
                ]
            ];
        }


    
        $plans = $plans_db->createCustomQuery($query)->getResult();
        // print_r($plans[0]->game_data->holes[$hole-1]->shots);
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found1"));
            exit;
        }
    
        $filter = $plans[0]->score_details->holes[$hole-1]->shots;
        $updatedShots = [];
        $counter = 0;
        foreach ($filter as $key => $shot) {
            if ($shot->shot_number != $shotnumber) {
                $counter+=1;
                $shot->shot_number = $counter;
                $updatedShots[] = $shot;
            }
        }

        $plans[0]->score_details->holes[$hole-1]->shots = $updatedShots;

        $newHoles = $plans[0]->score_details;
 
        try{
            $data = $plans_db->update($plans[0]->id, ['score_details'=>$newHoles]);
            if($data){
                http_response_code(200);
                echo json_encode(array("msg" => "Shot deleted successfully"));
            }else{
                http_response_code(400);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}



?>