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

        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        $first_8_holes = [];
        $remaining_holes = [];
        $first_green_total = 0;
        $second_green_total = 0;
        $count = 0; 
        foreach ($plans[0]->game_data->holes as $hole) {
            $green_score = count($hole->shots);
            $hole_data = array("green_score" => strval($green_score));

            if ($count <= 8) {
                $first_green_total += $green_score;
                $first_8_holes[] = $hole_data;
            } else {
                $second_green_total += $green_score;
                $remaining_holes[] = $hole_data;
            }
            $count++;
        }

        $output = array(
            "first_pars" => $first_8_holes,
            "second_pars" => $remaining_holes
        );

        // Add total green scores to each array
        $output["first_pars"][] = array("green_score" => strval($first_green_total));
        $output["second_pars"][] = array("green_score" => strval($second_green_total));
        $output["second_pars"][] = array("green_score" => strval($first_green_total + $second_green_total));
        // $output["all_total_pars"][] = $first_green_total + $second_green_total;

        http_response_code(200);
        echo json_encode(array("plans" => $output));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }

}else if($gamemode==='match'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'pending';
    if($internal_login==true){
        $status = 'complete';

    }
    
    try {
        $query = $supabase->initializeQueryBuilder();


        if(isset($_POST['roundID']) && !empty($_POST['roundID'])){
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

        $first_8_holes = [];
        $remaining_holes = [];
        $first_green_total = 0;
        $second_green_total = 0;
        $count = 0; 
        foreach ($plans[0]->score_details->holes as $hole) {
            $green_score = count($hole->shots);
            $hole_data = array("green_score" => strval($green_score));

            if ($count <= 8) {
                $first_green_total += $green_score;
                $first_8_holes[] = $hole_data;
            } else {
                $second_green_total += $green_score;
                $remaining_holes[] = $hole_data;
            }
            $count++;
        }

        $output = array(
            "first_pars" => $first_8_holes,
            "second_pars" => $remaining_holes
        );

        // Add total green scores to each array
        $output["first_pars"][] = array("green_score" => strval($first_green_total));
        $output["second_pars"][] = array("green_score" => strval($second_green_total));
        $output["second_pars"][] = array("green_score" => strval($first_green_total + $second_green_total));
        // $output["all_total_pars"][] = $first_green_total + $second_green_total;

        http_response_code(200);
        echo json_encode(array("plans" => $output));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }

}else if($gamemode==='event'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'pending';
    
    try {
        $query = $supabase->initializeQueryBuilder();


        if(isset($_POST['roundID']) && !empty($_POST['roundID'])){

            $plans = $query->select('*')
                        ->from('gimme_scores')
                        ->where('user_id', 'eq.'.$uid)
                        ->where('match_type_id', 'eq.'.$roundID)
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

        
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        $first_8_holes = [];
        $remaining_holes = [];
        $first_green_total = 0;
        $second_green_total = 0;
        $count = 0; 
        foreach ($plans[0]->score_details->holes as $hole) {
            $green_score = count($hole->shots);
            $hole_data = array("green_score" => strval($green_score));

            if ($count <= 8) {
                $first_green_total += $green_score;
                $first_8_holes[] = $hole_data;
            } else {
                $second_green_total += $green_score;
                $remaining_holes[] = $hole_data;
            }
            $count++;
        }
     
        $output = array(
            "first_pars" => $first_8_holes,
            "second_pars" => $remaining_holes
        );

        // Add total green scores to each array
        $output["first_pars"][] = array("green_score" => strval($first_green_total));
        $output["second_pars"][] = array("green_score" => strval($second_green_total));
        $output["second_pars"][] = array("green_score" => strval($first_green_total + $second_green_total));
        // $output["all_total_pars"][] = $first_green_total + $second_green_total;

        http_response_code(200);
        echo json_encode(array("plans" => $output));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }

}


?>