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
if(!isset($_POST['token'])){
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

$popups = $_POST['popups'];
$distance = $_POST['distance'];
$gps = $_POST['gps'];
$roundID = $_POST['roundID'];
$mainUser = isset($_POST['mainUser']) ? $_POST['mainUser'] : false;

$edit_hole_gps = isset($_POST['edit_hole_gps']) ? $_POST['edit_hole_gps'] : false;

function calculateResult($userScore, $parScore) {
    $result = '';
    $icon = '';

    if ($userScore == 1) {
        $result = 'Hole-in-One';
        $icon = 'golf_ball_in_cup';
    } elseif ($userScore - $parScore == -4) {
        $result = 'Condor';
        $icon = 'four_birds';
    } elseif ($userScore - $parScore == -3) {
        $result = 'Albatross';
        $icon = 'triple_bird';
    } elseif ($userScore - $parScore == -2) {
        $result = 'Eagle';
        $icon = 'double_bird';
    } elseif ($userScore - $parScore == -1) {
        $result = 'Birdie';
        $icon = 'single_bird';
    } elseif ($userScore - $parScore == 0) {
        $result = 'Par';
        $icon = 'score_border';
    } elseif ($userScore - $parScore == 1) {
        $result = 'Bogey';
        $icon = 'single_arrow';
    } elseif ($userScore - $parScore == 2) {
        $result = 'Double Bogey';
        $icon = 'double_arrow';
    } elseif ($userScore - $parScore == 3) {
        $result = 'Triple Bogey';
        $icon = 'triple_arrow';
    } else {
        $result = 'Worse than Triple Bogey';
        $icon = 'no_icon';
    }

    return array('result' => $result, 'icon' => $icon);
}


if($gamemode==='plan'){
    $courses_db = $supabase->initializeDatabase('gimme_courses','id');
    $query = [
        'select' => "*",
        'from' => "gimme_courses",
        'where' => [
            'id' => 'eq.'.$course_id
        ]
    ];

    try{
        $hole_number = 'hole_'.$hole;
        $course = $courses_db->createCustomQuery($query)->getResult();
        $course_data = $course[0]->course_data->course_data->hole_data->$hole_number;

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
        

            $unique_clubs = [];
            $holeGreenValue = [];


            $db = $supabase->initializeDatabase('gimme_plan_game', 'id');
          
            if($edit_hole_gps && $hole!=18){

                foreach ($plans[0]->game_data->holes[$hole]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                    $holeGreenValue[] = $shot->club;
                }
                $clubs_used = count($unique_clubs);

                $holeGreenValue_counter = count($holeGreenValue);
                if($plans[0]->game_data->holes[$hole]->quick_sc==true){
                    $holeGreenValue_counter =$plans[0]->game_data->holes[$hole]->quick_putss + $plans[0]->game_data->holes[$hole]->quick_shots;
                }

                $hole_gps_data = [
                    "hole_gps" => [
                        "user_id" => $uid,
                        "mode" => "plan",
                        "popups" => $popups,
                        "distance" =>$distance,
                        "course_id" => $course_id,
                        "hole_number" => $hole+1
                    ]
                ];

                $quick_sc = $plans[0]->game_data->holes[$hole]->quick_sc;
                $quick_putts_hole = $plans[0]->game_data->holes[$hole]->quick_putss;
                $quick_shots_hole = $plans[0]->game_data->holes[$hole]->quick_shots;
                $sum_hole = $quick_putts_hole + $quick_shots_hole;
              
            }else{

                foreach ($plans[0]->game_data->holes[$hole-1]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                    $holeGreenValue[] = $shot->club;
                }
                $clubs_used = count($unique_clubs);
                $holeGreenValue_counter = count($holeGreenValue);

                if($plans[0]->game_data->holes[$hole-1]->quick_sc==true){
                    $holeGreenValue_counter =$plans[0]->game_data->holes[$hole-1]->quick_putss + $plans[0]->game_data->holes[$hole-1]->quick_shots;
                }

                $hole_gps_data = [
                    "hole_gps" => [
                        "user_id" => $uid,
                        "mode" => "plan",
                        "popups" => $popups,
                        "distance" =>$distance,
                        "course_id" => $course_id,
                        "hole_number" => $hole
                    ]
                ];

                $quick_sc = $plans[0]->game_data->holes[$hole-1]->quick_sc;
                $quick_putts_hole = $plans[0]->game_data->holes[$hole-1]->quick_putss;
                $quick_shots_hole = $plans[0]->game_data->holes[$hole-1]->quick_shots;
                $sum_hole = $quick_putts_hole + $quick_shots_hole;

            }
            
            try{
                $data = $db->update($plans[0]->id, $hole_gps_data); 
                if(count($data)>0){
                    http_response_code(200);
                    if($plans[0]->game_data->holes[$hole-1]->status == "complete"){
                        $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
                        echo json_encode(array("course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => $resultData['result'],
                        'icon' => $resultData['icon'], "quick_score" => boolval ($quick_sc)));
                    }else{

                        echo json_encode(array("course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => '',
                        'icon' => '', "quick_score" => boolval ($quick_sc)));

                    }

                }

       

            }
            catch(Exception $e){
                echo $e->getMessage();
            }
        
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }

    } catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
    }    
}else if($gamemode==='match'){
    $courses_db = $supabase->initializeDatabase('gimme_courses','id');
    $query = [
        'select' => "*",
        'from' => "gimme_courses",
        'where' => [
            'id' => 'eq.'.$course_id
        ]
    ];

    try{
        $hole_number = 'hole_'.$hole;
        $course = $courses_db->createCustomQuery($query)->getResult();

        $course_data = $course[0]->course_data->course_data->hole_data->$hole_number;

        $plans_db = $supabase->initializeDatabase('gimme_scores','id');
        $status = 'pending';
        
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
                if($edit_hole_gps==true){
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

            }

            if (empty($plans)) {
                http_response_code(400);
                echo json_encode(array("msg" => "No data found"));
                exit;
            }
            $unique_clubs = [];
            $holeGreenValue = [];


            $db = $supabase->initializeDatabase('gimme_scores', 'id');
            $hole_gps_data = [];

            if($edit_hole_gps==true && $hole!=18){
                if($mainUser==true){
                    $hole = $hole;
                }else{
                    $hole = $hole - 1;
                }

                foreach ($plans[0]->score_details->holes[$hole]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                    $holeGreenValue[] = $shot->club;
                }
                $clubs_used = count($unique_clubs);
                $holeGreenValue_counter = count($holeGreenValue);


                if($plans[0]->score_details->holes[$hole]->quick_sc==true){
                    $holeGreenValue_counter = $plans[0]->score_details->holes[$hole]->quick_putss + $plans[0]->score_details->holes[$hole]->quick_shots;
                }


                $hole_gps_data = [
                    "hole_gps" => [
                        "user_id" => $uid,
                        "mode" => "match",
                        "popups" => $popups,
                        "distance" =>$distance,
                        "course_id" => $course_id,
                        "hole_number" => $hole+1
                    ],
                    "planning_gps" => [
                        "user_id" => $uid,
                        "distance" => "",
                        "course_id" => $course_id,
                        "current_gps" => $gps,
                        "hole_number" => $hole+1,
                        "current_shot" => count($plans[0]->score_details->holes[($hole+1)-1]->shots) + 1
                    ]
                ];
                $quick_sc = $plans[0]->score_details->holes[$hole]->quick_sc;
                $quick_putts_hole = $plans[0]->score_details->holes[$hole]->quick_putss;
                $quick_shots_hole = $plans[0]->score_details->holes[$hole]->quick_shots;
                $sum_hole = $quick_putts_hole + $quick_shots_hole;
            }else{


                foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                    $holeGreenValue[] = $shot->club;
                }
                $clubs_used = count($unique_clubs);
                $holeGreenValue_counter = count($holeGreenValue);

                if($plans[0]->score_details->holes[$hole-1]->quick_sc==true){
                    $holeGreenValue_counter =$plans[0]->score_details->holes[$hole-1]->quick_putss + $plans[0]->score_details->holes[$hole-1]->quick_shots;
                }
                $quick_sc = $plans[0]->score_details->holes[$hole-1]->quick_sc;
                $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
                $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
                $sum_hole = $quick_putts_hole + $quick_shots_hole;

                if(isset($_POST['roundID']) && !empty($_POST['roundID'])){
                    if($plans[0]->score_details->holes[$hole-1]->status=='incomplete'){
                        $holeGreenValue_counter = 0;
                        $quick_putts_hole = 0; 
                        $quick_shots_hole = 0; 
                        $sum_hole = 0;        
                    }
                }

                $hole_gps_data = [
                    "hole_gps" => [
                        "user_id" => $uid,
                        "mode" => "match",
                        "popups" => $popups,
                        "distance" =>$distance,
                        "course_id" => $course_id,
                        "hole_number" => $hole
                    ],
                    "planning_gps" => [
                        "user_id" => $uid,
                        "distance" => "",
                        "course_id" => $course_id,
                        "current_gps" => $gps,
                        "hole_number" => $hole,
                        "current_shot" => count($plans[0]->score_details->holes[$hole-1]->shots) + 1
                    ]
                ];
 
                // print_r($hole_gps_data);
            }

            try{
                $data = $db->update($plans[0]->id, $hole_gps_data); 
                if(count($data)>0){
                    http_response_code(200);
                    
                    if($plans[0]->score_details->holes[$hole-1]->status == "complete"){
                        $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
                        echo json_encode(array("course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => $resultData['result'],
                        'icon' => $resultData['icon'], 'quick_score' => boolval($quick_sc)));
                    }else{

                        echo json_encode(array("course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => '',
                        'icon' => '', 'quick_score' => boolval($quick_sc)));

                    }
                }
            }
            catch(Exception $e){
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }

    } catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
    }        
}else if($gamemode==='event'){
    $courses_db = $supabase->initializeDatabase('gimme_courses','id');
    $query = [
        'select' => "*",
        'from' => "gimme_courses",
        'where' => [
            'id' => 'eq.'.$course_id
        ]
    ];

    try{
        $hole_number = 'hole_'.$hole;
        $course = $courses_db->createCustomQuery($query)->getResult();

        $course_data = $course[0]->course_data->course_data->hole_data->$hole_number;

        $plans_db = $supabase->initializeDatabase('gimme_scores','id');
        $status = 'pending';
        
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


            // print_r($plans[0]->game_data->holes[$hole-1]->shots);
            $unique_clubs = [];
            $holeGreenValue = [];


            $db = $supabase->initializeDatabase('gimme_scores', 'id');


            if($edit_hole_gps==true && $hole!=18){
                if($mainUser==true){
                    $hole = $hole;
                }else{
                    $hole = $hole - 1;
                }

                foreach ($plans[0]->score_details->holes[$hole]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                    $holeGreenValue[] = $shot->club;
                }
                $clubs_used = count($unique_clubs);
                $holeGreenValue_counter = count($holeGreenValue);

                if($plans[0]->score_details->holes[$hole]->quick_sc==true){
                    $holeGreenValue_counter =$plans[0]->score_details->holes[$hole]->quick_putss + $plans[0]->score_details->holes[$hole]->quick_shots;
                }

                $hole_gps_data = [
                    "hole_gps" => [
                        "user_id" => $uid,
                        "mode" => "event",
                        "popups" => $popups,
                        "distance" =>$distance,
                        "course_id" => $course_id,
                        "hole_number" => $hole+1
                    ],
                    "planning_gps" => [
                        "user_id" => $uid,
                        "distance" => "",
                        "course_id" => $course_id,
                        "current_gps" => $gps,
                        "hole_number" => $hole+1,
                        "current_shot" => count($plans[0]->score_details->holes[($hole+1)-1]->shots) + 1
                    ]
                ];
              
        
                $quick_sc = $plans[0]->score_details->holes[$hole]->quick_sc;
                $quick_putts_hole = $plans[0]->score_details->holes[$hole]->quick_putss;
                $quick_shots_hole = $plans[0]->score_details->holes[$hole]->quick_shots;
                $sum_hole = $quick_putts_hole + $quick_shots_hole;
            }else{

                foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                    if (!in_array($shot->club, $unique_clubs)) {
                        $unique_clubs[] = $shot->club;
                    }
                    $holeGreenValue[] = $shot->club;
                }
                $clubs_used = count($unique_clubs);
                $holeGreenValue_counter = count($holeGreenValue);

                if($plans[0]->score_details->holes[$hole-1]->quick_sc==true){
                    $holeGreenValue_counter =$plans[0]->score_details->holes[$hole-1]->quick_putss + $plans[0]->score_details->holes[$hole-1]->quick_shots;
                }

                $hole_gps_data = [
                    "hole_gps" => [
                        "user_id" => $uid,
                        "mode" => "event",
                        "popups" => $popups,
                        "distance" =>$distance,
                        "course_id" => $course_id,
                        "hole_number" => $hole
                    ],

                    "planning_gps" => [
                        "user_id" => $uid,
                        "distance" => "",
                        "course_id" => $course_id,
                        "current_gps" => $gps,
                        "hole_number" => $hole,
                        "current_shot" => count($plans[0]->score_details->holes[$hole-1]->shots) + 1
                    ]
                ];
                $quick_sc = $plans[0]->score_details->holes[$hole-1]->quick_sc;
                $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
                $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
                $sum_hole = $quick_putts_hole + $quick_shots_hole;
            }

            
            try{
                $data = $db->update($plans[0]->id, $hole_gps_data); 
                if(count($data)>0){
                    http_response_code(200);
                    
                    if($plans[0]->score_details->holes[$hole-1]->status == "complete"){
                        $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
                        echo json_encode(array("course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => $resultData['result'],
                        'icon' => $resultData['icon'], "quick_score" => boolval($quick_sc)));
                    }else{

                        echo json_encode(array("course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => '',
                        'icon' => '', "quick_score" => boolval($quick_sc)));

                    }
                }
            }
            catch(Exception $e){
                echo $e->getMessage();
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }

    } catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
    }        
}
?>