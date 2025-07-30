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

$type = $_POST['type'];
$counter = $_POST['counter'];
$course_id = $_POST['course_id'];
$hole = $_POST['hole'];
$gamemode = $_POST['gamemode'];
$internal_login = $_POST['internal_login'];

if($gamemode==='plan'){
    $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
    try {
        $query = [
            'select' => "*",
            'from' => "gimme_plan_game",
            'where' => [
                'user_id' => 'eq.'.$uid,
                'course_id' => 'eq.'.$course_id,
                'status' => 'eq.pending'
            ]
        ];
    
        $plans = $plans_db->createCustomQuery($query)->getResult();
    
        $quick_putts = $plans[0]->game_data->holes[$hole-1]->quick_putss;
        $quick_shots = $plans[0]->game_data->holes[$hole-1]->quick_shots;
     
        $updated_game_data = $plans[0]->game_data;
        if($type == 'putts') {
            $updated_game_data->holes[$hole-1]->quick_putss = $counter;
        } else if($type == 'shot'){
            $updated_game_data->holes[$hole-1]->quick_shots = $counter;
        }
    
    
        if($type=='putts' || $type=='shot'){
            $plans_db2 = $supabase->initializeDatabase('gimme_plan_game','id');
            $id = $plans[0]->id;
            
            try{
                $data = $plans_db2->update($id, ['game_data' => ['holes' => $updated_game_data->holes]]); 
                http_response_code(200);
                echo json_encode(array("quick_shots" => $updated_game_data->holes[$hole-1]->quick_shots, "quick_putts" => $updated_game_data->holes[$hole-1]->quick_putss, 'total_shots_putts' => $updated_game_data->holes[$hole-1]->quick_shots + $updated_game_data->holes[$hole-1]->quick_putss));
                exit;
            }
            catch(Exception $e){
                echo $e->getMessage();
            }
        }
    
    
        http_response_code(200);
        echo json_encode(array("quick_shots" => $quick_shots, "quick_putts" => $quick_putts,  'total_shots_putts' => $quick_shots + $quick_putts));
    
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
}else if($gamemode==='match'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'pending';
    
    $query = $supabase->initializeQueryBuilder();
    try {

        if($internal_login==true){
        
            $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('match_type', 'eq.'.$gamemode)
                ->order('id.desc')
                ->execute()
                ->getResult();
        
        }else{
            $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('status', 'eq.'.$status)
                ->where('match_type', 'eq.'.$gamemode)
                ->order('id.desc')
                ->execute()
                ->getResult();            
        }
     
        $quick_putts = $plans[0]->score_details->holes[$hole-1]->quick_putss;
        $quick_shots = $plans[0]->score_details->holes[$hole-1]->quick_shots;
     
        $updated_game_data = $plans[0]->score_details;
        if($type == 'putts') {
            $updated_game_data->holes[$hole-1]->quick_putss = $counter;
        } else if($type == 'shot'){
            $updated_game_data->holes[$hole-1]->quick_shots = $counter;
        }
    
        // print_r($updated_game_data);
        if($type=='putts' || $type=='shot'){
            $plans_db2 = $supabase->initializeDatabase('gimme_scores','id');
            
            $id = $plans[0]->id;
            // echo $updated_game_data->holes[$hole-1]->quick_shots;
            // echo $updated_game_data->holes[$hole-1]->quick_putts;
            try{
                $data = $plans_db2->update($id, ['score_details' => ['holes' => $updated_game_data->holes]]); 
                http_response_code(200);
                echo json_encode(array("quick_shots" => $updated_game_data->holes[$hole-1]->quick_shots, "quick_putts" => $updated_game_data->holes[$hole-1]->quick_putss, 'total_shots_putts' => $updated_game_data->holes[$hole-1]->quick_shots + $updated_game_data->holes[$hole-1]->quick_putss));
                exit;
            }
            catch(Exception $e){
                http_response_code(500);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        }
    
    
        http_response_code(200);
        echo json_encode(array("quick_shots" => $quick_shots, "quick_putts" => $quick_putts,  'total_shots_putts' => $quick_shots + $quick_putts));
    
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}else if($gamemode==='event'){
    $status = 'active';
    $query = $supabase->initializeQueryBuilder();
    try {


        $plans = $query->select('*')
                    ->from('gimme_scores')
                    ->where('user_id', 'eq.'.$uid)
                    ->where('status', 'eq.'.$status)
                    ->where('match_type', 'eq.'.$gamemode)
                    ->order('id.desc')
                    ->execute()
                    ->getResult();
    

     
        $quick_putts = $plans[0]->score_details->holes[$hole-1]->quick_putss;
        $quick_shots = $plans[0]->score_details->holes[$hole-1]->quick_shots;
     
        $updated_game_data = $plans[0]->score_details;
        if($type == 'putts') {
            $updated_game_data->holes[$hole-1]->quick_putss = $counter;
        } else if($type == 'shot'){
            $updated_game_data->holes[$hole-1]->quick_shots = $counter;
        }
    
    
        if($type=='putts' || $type=='shot'){
            $plans_db2 = $supabase->initializeDatabase('gimme_scores','id');
            $id = $plans[0]->id;
            
            try{
                $data = $plans_db2->update($id, ['score_details' => ['holes' => $updated_game_data->holes]]); 
                http_response_code(200);
                echo json_encode(array("quick_shots" => $updated_game_data->holes[$hole-1]->quick_shots, "quick_putts" => $updated_game_data->holes[$hole-1]->quick_putss, 'total_shots_putts' => $updated_game_data->holes[$hole-1]->quick_shots + $updated_game_data->holes[$hole-1]->quick_putss));
                exit;
            }
            catch(Exception $e){
                http_response_code(500);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        }
    
    
        http_response_code(200);
        echo json_encode(array("quick_shots" => $quick_shots, "quick_putts" => $quick_putts,  'total_shots_putts' => $quick_shots + $quick_putts));
    
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}




?>