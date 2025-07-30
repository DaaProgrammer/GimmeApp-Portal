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

error_reporting(E_ALL & ~E_WARNING);
error_reporting(0); // Turn off all error reporting
// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid Request"));
    exit;
}

if(!isset($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Course ID is required"));
    exit;
}

if(!isset($_POST['hole'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Hole Number is required"));
    exit;
}

if(!isset($_POST['club'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Club is required"));
    exit;
}

if(!isset($_POST['condition'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Condition is required"));
    exit;
}

$hole = $_POST['hole'];
$course_id = $_POST['course_id'];

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
        'course_id' => 'eq.'.$course_id,
        'status' => 'eq.pending'
    ]
];

$planObj = $plans_db->createCustomQuery($query)->getResult();
$id = $planObj[0]->id;
$planning_gps = $planObj[0]->planning_gps;
// print_r(($planning_gps));
$shots = null;

$clubs = array("Driver", "3 Wood", "5 Wood", "3 Hybrid", "4 Hybrid", "4 Iron", "5 Iron", "6 Iron", "7 Iron", "8 Iron", "9 Iron", "Pitching Wedge", "Sand Wedge", "Lob Wedge", "Putter");
$clubs_bg_colors = array("#e57f35", "#e6b713", "#e6b614", "#043d5a", "#003f5b", "#025887", "#00598b", "#025887", "#015989", "#025887", "#025887", "#0f7ec6", "#0f7ec6", "#0f7ec6", "#349961");
$clubs_abbr = array();
foreach($clubs as $club) {
    $abbr = "";
    $words = explode(" ", $club);
    foreach($words as $word) {
        $abbr .= strtoupper($word[0]);
    }
    $clubs_abbr[] = $abbr;
}


$unique_clubs = [];
if(empty($planning_gps) || $planning_gps == null){
    http_response_code(400);
    echo json_encode(array("msg" => "shot_not_tracked"));
    exit;
} else {
    $holes = $planObj[0]->game_data->holes;
    $hole_c = $hole - 1;
    
    // print_r($holes);
    foreach($holes as $hole_data){
        if($hole_data->hole_number == $hole){
            $shots = $hole_data->shots;
            break;
        }
    }

    if($shots == null || empty($shots)){

        $count = count($shots);

        if($count == 0){
            $count = 1;
            if($planning_gps->current_shot == $count){
                $shots[] = array(
                    'gps' => $planning_gps->current_gps,
                    'club' => $_POST['club'],
                    'abbr' => $clubs_abbr[array_search($_POST['club'], $clubs)],
                    'abbr_color'=> $clubs_bg_colors[array_search($_POST['club'], $clubs)],
                    'distance' => $planning_gps->distance,
                    'shot_type' => 'Plan',
                    'condition' => $_POST['condition'],
                    'shot_number' => $count
                );
                // echo json_encode(array("msg" => "success", "shots" => $shots));
                $holes[$hole - 1]->shots = $shots;
                foreach ($holes[$hole - 1]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                }
                
                $clubs_used = count($unique_clubs);
                update_the_hole($id, $holes, $plans_db, $clubs_used);

            } else {
                http_response_code(400);
                echo json_encode(array("msg" => "shot_mismatch"));
                exit;
            }
        } elseif($planning_gps->current_shot == $count){

                $shots[] = array(
                    'gps' => $planning_gps->current_gps,
                    'club' => $_POST['club'],
                    'abbr' =>  $clubs_abbr[array_search($_POST['club'], $clubs)],
                    'abbr_color'=> $clubs_bg_colors[array_search($_POST['club'], $clubs)],
                    'distance' => $planning_gps->distance,
                    'shot_type' => 'Plan',
                    'condition' => $_POST['condition'],
                    'shot_number' => $count
                );
                // echo json_encode(array("msg" => "success", "shots" => $shots));
                $holes[$hole - 1]->shots = $shots;
                foreach ($holes[$hole - 1]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                }
                
                $clubs_used = count($unique_clubs);
                update_the_hole($id, $holes, $plans_db, $clubs_used);
        } else {
            http_response_code(400);
            echo json_encode(array("msg" => "shot_mismatch"));
            exit;
        }

    }else{
        // Append the new shot
        $count = count($shots) + 1;
        // echo $count;
        $planning_gps_shot = $planning_gps->current_shot;
        
        if($count == $planning_gps_shot){
            $new_shot = array(
                'gps' => $planning_gps->current_gps,
                'abbr' =>  $clubs_abbr[array_search($_POST['club'], $clubs)],
                'club' => $_POST['club'],
                'distance' => $planning_gps->distance,
                'shot_type' => 'Plan',
                'condition' => $_POST['condition'],
                'abbr_color'=> $clubs_bg_colors[array_search($_POST['club'], $clubs)],
                'shot_number' => $planning_gps_shot
            );
       
            $holes[$hole - 1]->shots[] = $new_shot;

            $unique_clubs[] = $new_shot['club'];

            $clubs_used = count(array_unique(array_column($holes[$hole - 1]->shots, 'club')));
            update_the_hole($id, $holes, $plans_db, $clubs_used);

        } else {
            http_response_code(400);
            echo json_encode(array("msg" => "Shot mismatch"));
            exit;
        }    	
    }

}


function update_the_hole($id, $holes, $plans_db, $clubs_used){

	$update_the_hole_data = [
	    'game_data' => ['holes' => $holes]
	];

	try{
	    $data = $plans_db->update($id, $update_the_hole_data);
	    if($data){



            http_response_code(200);
            echo json_encode(array("msg" => "Map updated successfully", "holes" => ['holes' => $holes], "clubs_used" => $clubs_used));
	    }else{
            http_response_code(400);
            echo json_encode(array("msg" => "Something went wrong"));

	    }
	}
	catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => "Something went wrong"));
	}
}

?>