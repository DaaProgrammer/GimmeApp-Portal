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

$course_id = $_POST['course_id'];
$hole = $_POST['hole'];
$gamemode = $_POST['gamemode'];
$popups = $_POST['popups'];
$distance = $_POST['distance'];


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
    } elseif ($userScore - $parScore == 2 || $userScore=='pu') {
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


// $quick_sc = strval($_POST['quick_sc']);
if($gamemode==="plan"){
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
                echo json_encode(array("msg" => "Could not complete the hole, something went wrong"));
                exit;
            }
        
        
        


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $unique_clubs = [];
            $holeGreenValue = [];
            foreach ($plans[0]->game_data->holes[$hole-1]->shots as $shot) {
                if (!in_array($shot->club, $unique_clubs)) {
                    $unique_clubs[] = $shot->club;
                }
                $holeGreenValue[] = $shot->club;
            }
            $clubs_used = count($unique_clubs);
            $holeGreenValue_counter = count($holeGreenValue);



            $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
            $parname = $resultData['result'];
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


            $id = $plans[0]->id;
            // print_r($plans[0]->game_data->holes[$hole-1]);sd
            
        
            $completed_holes_count = 0;
            $game_data = $plans[0]->game_data->holes;
            foreach ($game_data as $hole_data) {
                if ($hole_data->status == "complete") {
                    $completed_holes_count++;
                }
            }
        
            if($completed_holes_count==18){
                http_response_code(250);
                echo json_encode(array("msg" => "The scorecard is complete"));
                exit;
            }
        
            // if(strval($plans[0]->game_data->holes[$hole-1]->status) == "incomplete"){
                $plans[0]->game_data->holes[$hole-1]->complete_type = "pu";
                $plans[0]->game_data->holes[$hole-1]->status = "complete";
                // $plans[0]->game_data->holes[$hole-1]->parname = $parname;
                // $plans[0]->game_data->holes[$hole-1]->parscore = $course_data->par;
            // }
        
            $game_data = $plans[0]->game_data->holes;
        
            $update_plan = [
                'game_data' => array("holes"=>$game_data)
            ];
        
        
            try{
                $update_plan_response = $plans_db->update($id, $update_plan);
                if (empty($update_plan_response)) {
                    http_response_code(400);
                    echo json_encode(array("msg" => "Could not complete the hole, something went wrong"));
                    exit;
                }
        
                // http_response_code(200);
        
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
                
                    $quick_sc =  $plans[0]->game_data->holes[$hole]->quick_sc;
                    $quick_putts_hole = $plans[0]->game_data->holes[$hole]->quick_putss;
                    $quick_shots_hole = $plans[0]->game_data->holes[$hole]->quick_shots;
                    $sum_hole = $quick_putts_hole + $quick_shots_hole;
            
                    // print_r($plans[0]->game_data->holes[$hole-1]->shots);
                    $unique_clubs = [];
                    foreach ($plans[0]->game_data->holes[$hole]->shots as $shot) {
                        if (!in_array($shot->club, $unique_clubs)) {
                            $unique_clubs[] = $shot->club;
                        }
                    }
                    $clubs_used = count($unique_clubs);
                
                    $db = $supabase->initializeDatabase('gimme_plan_game', 'id');
                    $hole_number = $hole+1;

                    if($hole==18 || $hole=="18"){
                        $hole_number = 18;
                    }
                    $hole_gps_data = [
                        "hole_gps" => [
                            "user_id" => $uid,
                            "mode" => "plan",
                            "popups" => $popups,
                            "distance" =>$distance,
                            "course_id" => $course_id,
                            "hole_number" => $hole_number
                        ]
                    ];
                    
                    try{
                        $data = $db->update($plans[0]->id, $hole_gps_data); 
                        if(count($data)>0){
                            $completed_holes_count = 0;
                            $game_data = $plans[0]->game_data->holes;
                            foreach ($game_data as $hole_data) {
                                if ($hole_data->status == "complete") {
                                    $completed_holes_count++;
                                }
                            }


                            if($completed_holes_count==18){
                                
                                $db_status = $supabase->initializeDatabase('gimme_plan_game', 'id');
                                try{
                                    $data2 = $db_status->update($plans[0]->id, ["status"=>"complete"]); 
                                    if(count($data2)>0){
                                        http_response_code(250);
                                        echo json_encode(array("msg" => "The scorecard is complete"));
                                        exit;
                                    }
                                }catch(Exception $e){
                                    http_response_code(500);
                                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                                }  
                            }
                        

                            http_response_code(200);
                            echo json_encode(array("selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "quick_sc" => $quick_sc));
                        }
                    }
                    catch(Exception $e){
                        echo $e->getMessage();
                    }                
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                }
        
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
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

        
            $plans = $plans_db->createCustomQuery($query)->getResult();
            if (empty($plans)) {
                http_response_code(400);
                echo json_encode(array("msg" => "Could not complete the hole, something went wrong"));
                exit;
            }
        
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $unique_clubs = [];
            $holeGreenValue = [];
            foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                if (!in_array($shot->club, $unique_clubs)) {
                    $unique_clubs[] = $shot->club;
                }
                $holeGreenValue[] = $shot->club;
            }
            $clubs_used = count($unique_clubs);
            $holeGreenValue_counter = count($holeGreenValue);



            $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
            $parname = $resultData['result'];
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        
            $id = $plans[0]->id;
        
            $completed_holes_count = 0;
            $game_data = $plans[0]->score_details->holes;
            foreach ($game_data as $hole_data) {
                if ($hole_data->status == "complete") {
                    $completed_holes_count++;
                }
            }
        
            if($completed_holes_count==18){
                http_response_code(250);
                echo json_encode(array("msg" => "The scorecard is complete"));
                exit;
            }
        
        
            // if(strval($plans[0]->score_details->holes[$hole-1]->status) == "incomplete"){
                $plans[0]->score_details->holes[$hole-1]->complete_type = "pu";
                $plans[0]->score_details->holes[$hole-1]->status = "complete";
                // $plans[0]->score_details->holes[$hole-1]->parname = $parname;
                // $plans[0]->score_details->holes[$hole-1]->parscore = $course_data->par;
                
            // }
        
            $game_data = $plans[0]->score_details->holes;
        
            $update_plan = [
                'score_details' => array("holes"=>$game_data)
            ];
        
        
            try{
                $update_plan_response = $plans_db->update($id, $update_plan);
                if (empty($update_plan_response)) {
                    http_response_code(400);
                    echo json_encode(array("msg" => "Could not complete the hole, something went wrong"));
                    exit;
                }
        
                // http_response_code(200);
        
                $plans_db = $supabase->initializeDatabase('gimme_scores','id');
                $status = 'pending';
        
                try {
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
                
                
                    $plans = $plans_db->createCustomQuery($query)->getResult();
            
                
                    if (empty($plans)) {
                        http_response_code(400);
                        echo json_encode(array("msg" => "No data found"));
                        exit;
                    }
                
                    $quick_sc = $plans[0]->score_details->holes[$hole-1]->quick_sc;
                    $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
                    $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
                    $sum_hole = $quick_putts_hole + $quick_shots_hole;
            
                    // print_r($plans[0]->game_data->holes[$hole-1]->shots);
                    $unique_clubs = [];
                    foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                        if (!in_array($shot->club, $unique_clubs)) {
                            $unique_clubs[] = $shot->club;
                        }
                    }
                    $clubs_used = count($unique_clubs);
                    $db = $supabase->initializeDatabase('gimme_scores', 'id');

                    $hole_gps_data = [
                        "hole_gps" => [
                            "user_id" => $uid,
                            "mode" => "match",
                            "popups" => $popups,
                            "distance" =>$distance,
                            "course_id" => $course_id,
                            "hole_number" => $hole+1
                        ]
                    ];
                    
                    try{
                        $data = $db->update($plans[0]->id, $hole_gps_data); 
                        if(count($data)>0){
                            $completed_holes_count = 0;
                            $game_data = $plans[0]->score_details->holes;
                            foreach ($game_data as $hole_data) {
                                if ($hole_data->status == "complete") {
                                    $completed_holes_count++;
                                }
                            }


                            if($completed_holes_count==18){
                                
                                $db_status = $supabase->initializeDatabase('gimme_scores', 'id');
                                try{
                                    $data2 = $db_status->update($plans[0]->id, ["status"=>"complete"]); 
                                    if(count($data2)>0){
                                        http_response_code(250);
                                        echo json_encode(array("msg" => "The scorecard is complete"));
                                        exit;
                                    }
                                }catch(Exception $e){
                                    http_response_code(500);
                                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                                }  
                            }

                            http_response_code(200);
                            echo json_encode(array("selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "quick_sc" => $quick_sc));
                        }
                    }
                    catch(Exception $e){
                        echo $e->getMessage();
                    }    
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                }
        
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
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
        $status = 'active';
        
        try {
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

        
            $plans = $plans_db->createCustomQuery($query)->getResult();
            if (empty($plans)) {
                http_response_code(400);
                echo json_encode(array("msg" => "Could not complete the hole, something went wrong"));
                exit;
            }
        
            ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $unique_clubs = [];
            $holeGreenValue = [];
            foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                if (!in_array($shot->club, $unique_clubs)) {
                    $unique_clubs[] = $shot->club;
                }
                $holeGreenValue[] = $shot->club;
            }
            $clubs_used = count($unique_clubs);
            $holeGreenValue_counter = count($holeGreenValue);



            $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
            $parname = $resultData['result'];
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        
            $id = $plans[0]->id;
        
            $completed_holes_count = 0;
            $game_data = $plans[0]->score_details->holes;
            foreach ($game_data as $hole_data) {
                if ($hole_data->status == "complete") {
                    $completed_holes_count++;
                }
            }
        
            if($completed_holes_count==18){
                http_response_code(250);
                echo json_encode(array("msg" => "The scorecard is complete"));
                exit;
            }
        
        
            // if(strval($plans[0]->score_details->holes[$hole-1]->status) == "incomplete"){
                $plans[0]->score_details->holes[$hole-1]->complete_type = "pu";
                $plans[0]->score_details->holes[$hole-1]->status = "complete";
                // $plans[0]->score_details->holes[$hole-1]->parname = $parname;
                // $plans[0]->score_details->holes[$hole-1]->parscore = $course_data->par;
                
            // }
        
            $game_data = $plans[0]->score_details->holes;
        
            $update_plan = [
                'score_details' => array("holes"=>$game_data)
            ];
        
        
            try{
                $update_plan_response = $plans_db->update($id, $update_plan);
                if (empty($update_plan_response)) {
                    http_response_code(400);
                    echo json_encode(array("msg" => "Could not complete the hole, something went wrong"));
                    exit;
                }
        
                // http_response_code(200);
        
                $plans_db = $supabase->initializeDatabase('gimme_scores','id');
                $status = 'active';
        
                try {
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
                
                
                    $plans = $plans_db->createCustomQuery($query)->getResult();
            
                
                    if (empty($plans)) {
                        http_response_code(400);
                        echo json_encode(array("msg" => "No data found"));
                        exit;
                    }
                
                    $quick_sc = $plans[0]->score_details->holes[$hole-1]->quick_sc;
                    $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
                    $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
                    $sum_hole = $quick_putts_hole + $quick_shots_hole;
            
                    // print_r($plans[0]->game_data->holes[$hole-1]->shots);
                    $unique_clubs = [];
                    foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                        if (!in_array($shot->club, $unique_clubs)) {
                            $unique_clubs[] = $shot->club;
                        }
                    }
                    $clubs_used = count($unique_clubs);
                    $db = $supabase->initializeDatabase('gimme_scores', 'id');

                    $hole_gps_data = [
                        "hole_gps" => [
                            "user_id" => $uid,
                            "mode" => "events",
                            "popups" => $popups,
                            "distance" =>$distance,
                            "course_id" => $course_id,
                            "hole_number" => $hole+1
                        ]
                    ];
                    
                    try{
                        $data = $db->update($plans[0]->id, $hole_gps_data); 
                        if(count($data)>0){
                            $completed_holes_count = 0;
                            $game_data = $plans[0]->score_details->holes;
                            foreach ($game_data as $hole_data) {
                                if ($hole_data->status == "complete") {
                                    $completed_holes_count++;
                                }
                            }


                            if($completed_holes_count==18){
                                
                                $db_status = $supabase->initializeDatabase('gimme_scores', 'id');
                                try{
                                    $data2 = $db_status->update($plans[0]->id, ["status"=>"complete"]); 
                                    if(count($data2)>0){
                                        http_response_code(250);
                                        echo json_encode(array("msg" => "The scorecard is complete"));
                                        exit;
                                    }
                                }catch(Exception $e){
                                    http_response_code(500);
                                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                                }  
                            }

                            http_response_code(200);
                            echo json_encode(array("selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "quick_sc" => $quick_sc));
                        }
                    }
                    catch(Exception $e){
                        echo $e->getMessage();
                    }    
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                }
        
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
            }
        
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
        
}



?>