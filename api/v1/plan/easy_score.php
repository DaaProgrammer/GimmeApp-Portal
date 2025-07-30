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
error_reporting(0);

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
$plans_db_scores = $supabase->initializeDatabase('gimme_scores','id');

$course_id = $_POST['course_id'];
$hole = $_POST['hole'];
$gamemode = $_POST['gamemode'];
$roundID = $_POST['roundID'];
$quick_score_bool = $_POST['quick_score_bool'];
// $quick_sc = strval($_POST['quick_sc']);
if($gamemode=="plan"){  
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
        $id = $plans[0]->id;
        // print_r($plans[0]->game_data->holes[$hole-1]);
        

        // if(strval($plans[0]->game_data->holes[$hole-1]->quick_sc) == "true"){
        //     $plans[0]->game_data->holes[$hole-1]->quick_sc = "false";
        // }else{
        //     $plans[0]->game_data->holes[$hole-1]->quick_sc = "true";
        // }

        
        $plans[0]->game_data->holes[$hole-1]->quick_sc = $quick_score_bool;
        $game_data = $plans[0]->game_data->holes;


        $quick_putts = $plans[0]->game_data->holes[$hole-1]->quick_putss;
        $quick_shots = $plans[0]->game_data->holes[$hole-1]->quick_shots;

        // print_r($plans[0]->game_data->holes);
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }



        $update_plan = [
            'game_data' => array("holes"=>$game_data)
        ];
        
        try{
            $update_plan_response = $plans_db->update($id, $update_plan);

            if (empty($update_plan_response)) {
                http_response_code(400);
                echo json_encode(array("msg" => "No data found"));
                exit;
            }
        
            http_response_code(200);
            echo json_encode(array("quick_shots" => $quick_shots, "quick_putts" => $quick_putts));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }
        // http_response_code(200);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
    
}else if($gamemode=="match" || $gamemode=="event"){
    $query = $supabase->initializeQueryBuilder();
    try {
        if($_POST['roundID']!=0 || $_POST['roundID']!='0'){ 
            if($gamemode=="match"){
                $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('match_type', 'eq.'.$gamemode)
                ->where('match_type_id', 'eq.'.$roundID)
                ->order('id.desc')
                ->execute()
                ->getResult();
            }else{
                $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('match_type', 'eq.'.$gamemode)
                ->where('match_type_id', 'eq.'.$roundID)
                ->order('id.desc')
                ->execute()
                ->getResult();
            }
        }else{

            if($gamemode=="match"){
                $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('status', 'eq.pending')
                ->where('match_type', 'eq.'.$gamemode)
                ->order('id.desc')
                ->execute()
                ->getResult();
            }else{
                $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('status', 'eq.active')
                ->where('match_type', 'eq.'.$gamemode)
                ->order('id.desc')
                ->execute()
                ->getResult();
            }            
        }


        $id = $plans[0]->id;
        // print_r($plans[0]->game_data->holes[$hole-1]);
        

        // if(strval($plans[0]->score_details->holes[$hole-1]->quick_sc) == "true"){
        //     $plans[0]->score_details->holes[$hole-1]->quick_sc = "false";
        // }else{
        //     $plans[0]->score_details->holes[$hole-1]->quick_sc = "true";
        // }

        $plans[0]->score_details->holes[$hole-1]->quick_sc = $quick_score_bool;
        $game_data = $plans[0]->score_details->holes;

        $quick_putts = $plans[0]->score_details->holes[$hole-1]->quick_putss;
        $quick_shots = $plans[0]->score_details->holes[$hole-1]->quick_shots;

        // print_r($plans[0]->game_data->holes);
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }



        $update_plan = [
            'score_details' => array("holes"=>$game_data)
        ];
        
        try{
            $update_plan_response = $plans_db_scores->update($id, $update_plan);

            if (empty($update_plan_response)) {
                http_response_code(400);
                echo json_encode(array("msg" => "No data found"));
                exit;
            }
        
            http_response_code(200);
            echo json_encode(array("quick_shots" => $quick_shots, "quick_putts" => $quick_putts));
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }
        // http_response_code(200);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
    
}

?>