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
if(empty($_POST['token']) || empty($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$hole = $_POST['hole'];
// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);


$course_id = $_POST['course_id'];
$gamemode = $_POST['gamemode'];
$internal_login = $_POST['internal_login'];
$roundID = $_POST['roundID'];

if($gamemode === 'plan'){
    $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
    $putts_shot_totals_first = [];
    $putts_shot_totals_second = [];

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

        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        for($x = 0; $x < 18; $x++){
            $quick_putts = $plans[0]->game_data->holes[$x]->quick_putss;
            $quick_shots = $plans[0]->game_data->holes[$x]->quick_shots;

            $sum = $quick_putts + $quick_shots;
            
            if($x<=8){
                $putts_shot_totals_first[] = $sum; 
            }else{       
                $putts_shot_totals_second[] = $sum;        
            }
        }


        $quick_putts_hole = $plans[0]->game_data->holes[$hole-1]->quick_putss;
        $quick_shots_hole = $plans[0]->game_data->holes[$hole-1]->quick_shots;
        $sum_hole = $quick_putts_hole + $quick_shots_hole;

        $putts_shot_totals_first[] = array_sum($putts_shot_totals_first);
        $putts_shot_totals_second[] = array_sum($putts_shot_totals_second);
        $putts_shot_totals_second[] = end($putts_shot_totals_first) + end($putts_shot_totals_second);



        // print_r($plans[0]->game_data->holes[$hole-1]->shots);
        $unique_clubs = [];
        foreach ($plans[0]->game_data->holes[$hole-1]->shots as $shot) {
            if (!in_array($shot->club, $unique_clubs)) {
                $unique_clubs[] = $shot->club;
            }
        }
        $clubs_used = count($unique_clubs);

        http_response_code(200);
        echo json_encode(array("first_putts_shots" => $putts_shot_totals_first, "second_putts_shots" => $putts_shot_totals_second, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole));

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
}else if($gamemode==='match'){
    $plans_db = $supabase->initializeDatabase('score_details','id');
    $putts_shot_totals_first = [];
    $putts_shot_totals_second = [];

    $status = 'pending';
    // if($internal_login==true){
    //     $status = 'complete';

    // }

    $query = $supabase->initializeQueryBuilder();

    try {


        if($_POST['roundID']!=0 || $_POST['roundID']!='0'){ 
            $plans = $query->select('*')
                        ->from('gimme_scores')
                        ->where('user_id', 'eq.'.$uid)
                        ->where('match_type_id', 'eq.'.$roundID)
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
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        for($x = 0; $x < 18; $x++){
            $quick_putts = $plans[0]->score_details->holes[$x]->quick_putss;
            $quick_shots = $plans[0]->score_details->holes[$x]->quick_shots;

            $sum = $quick_putts + $quick_shots;
            
            if($x<=8){
                $putts_shot_totals_first[] = $sum; 
            }else{       
                $putts_shot_totals_second[] = $sum;        
            }
        }


        $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
        $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
        $sum_hole = $quick_putts_hole + $quick_shots_hole;

        $putts_shot_totals_first[] = array_sum($putts_shot_totals_first);
        $putts_shot_totals_second[] = array_sum($putts_shot_totals_second);
        $putts_shot_totals_second[] = end($putts_shot_totals_first) + end($putts_shot_totals_second);



        // print_r($plans[0]->game_data->holes[$hole-1]->shots);
        $unique_clubs = [];
        foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
            if (!in_array($shot->club, $unique_clubs)) {
                $unique_clubs[] = $shot->club;
            }
        }
        $clubs_used = count($unique_clubs);

        http_response_code(200);
        echo json_encode(array("first_putts_shots" => $putts_shot_totals_first, "second_putts_shots" => $putts_shot_totals_second, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole));

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}else if($gamemode==='event'){
    
    $plans_db = $supabase->initializeDatabase('score_details','id');
    $putts_shot_totals_first = [];
    $putts_shot_totals_second = [];

    $status = 'pending';
    
    $query = $supabase->initializeQueryBuilder();
    try {

        if($_POST['roundID']!=0 || $_POST['roundID']!='0'){     
            $plans = $query->select('*')
                        ->from('gimme_scores')
                        ->where('user_id', 'eq.'.$uid)
                        ->where('match_type_id', 'eq.'.$roundID)
                        ->where('match_type', 'eq.'.$gamemode)
                        ->order('id.desc')
                        ->execute()
                        ->getResult();
        }else{
            $plans = $query->select('*')
                        ->from('gimme_scores')
                        ->where('user_id', 'eq.'.$uid)
                        ->where('status', 'neq.'.$status)
                        ->where('match_type', 'eq.'.$gamemode)
                        ->order('id.desc')
                        ->execute()
                        ->getResult();            
        }
    


        // $plans = $plans_db->createCustomQuery($query)->getResult();

        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        for($x = 0; $x < 18; $x++){
            $quick_putts = $plans[0]->score_details->holes[$x]->quick_putss;
            $quick_shots = $plans[0]->score_details->holes[$x]->quick_shots;

            $sum = $quick_putts + $quick_shots;
            
            if($x<=8){
                $putts_shot_totals_first[] = $sum; 
            }else{       
                $putts_shot_totals_second[] = $sum;        
            }
        }


        $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
        $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
        $sum_hole = $quick_putts_hole + $quick_shots_hole;

        $putts_shot_totals_first[] = array_sum($putts_shot_totals_first);
        $putts_shot_totals_second[] = array_sum($putts_shot_totals_second);
        $putts_shot_totals_second[] = end($putts_shot_totals_first) + end($putts_shot_totals_second);



        // print_r($plans[0]->game_data->holes[$hole-1]->shots);
        $unique_clubs = [];
        foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
            if (!in_array($shot->club, $unique_clubs)) {
                $unique_clubs[] = $shot->club;
            }
        }
        $clubs_used = count($unique_clubs);

        http_response_code(200);
        echo json_encode(array("first_putts_shots" => $putts_shot_totals_first, "second_putts_shots" => $putts_shot_totals_second, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole));

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}

?>