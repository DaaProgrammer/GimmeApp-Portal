<?php 
// display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // It's a preflight request, respond accordingly
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, GET, PUT, DELETE, OPTIONS");
    exit;
}
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require '../auth/token_maps_reload.php';
require_once '../util/util.php';

// Validate the required fields
$token = null;
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
} else {
    $token = $_POST['token'];
}

$course_id = null;
if(!isset($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid course id"));
    exit;
} else {
    $course_id = $_POST['course_id'];
}

$user_id = null;
if(!isset($_POST['user_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid user id"));
    exit;
} else {
    $user_id = $_POST['user_id'];
}

$mode = null;
if(!isset($_POST['mode'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid game mode"));
    exit;
} else {
    $mode = $_POST['mode'];
}

$hole = null;
if(!isset($_POST['hole'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid hole data"));
    exit;
} else {
    $hole = $_POST['hole'];
}

$popups = false;
if(!isset($_POST['popups'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid popup value"));
    exit;
} else {
    $popups = $_POST['popups'];
}

$distance_pref = 'yards';
if(!isset($_POST['distance'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid distance value"));
    exit;
} else {
    $distance_pref = $_POST['distance'];
}

$plan = null;
if(isset($_POST['mode'])){
    
    if($_POST['mode'] == 'plan'){
        $plan = $_POST['mode'];
    } elseif($_POST['mode'] == 'event'){
        $plan = $_POST['mode'];
    } elseif($_POST['mode'] == 'match'){
        $plan = $_POST['mode'];
    } else {
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid game mode"));
        exit;
    }

}

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$load_map = false;
$courses_db = $supabase->initializeDatabase('gimme_courses','id');

try {
    $query = [
       'select' => "*",
        'from' => "gimme_courses",
        'where' => [
            'id' => 'eq.'.$_POST['course_id']
        ]
    ];

    $courses = $courses_db->createCustomQuery($query)->getResult();

    if(empty($courses)){
        http_response_code(400);
        echo json_encode(array("msg" => "No data found"));
        exit;
    }

    $course = $courses[0];
    // get the location_gps from the course record
    $location_gps = $course->location_gps;
    $course_data = $course->course_data;
    $location_gps = $location_gps;
    $course_data = $course_data;

    $load_map = true;

    if($plan == 'plan'){
        // create a supabase query to gimme_plan_game and retrieve the game_data json object
        $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
        $status = "pending";
        $query = [
           'select' => "*",
            'from' => "gimme_plan_game",
            'where' => [
                'user_id' => 'eq.'.$_POST['user_id'],
                'course_id' => 'eq.'.$_POST['course_id'],
                'status' => 'eq.'.$status,
            ]
        ];
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if(empty($plans)){
            http_response_code(400);
            echo json_encode(array("msg" => "No plan record found"));
            exit;
        }

        $id = $plans[0]->id;
        $game_data = $plans[0]->game_data;

        // example game data:
        // {"game_data":{"holes":[{"shots":[{"gps":"28.295634098946294,-26.183867180911847","club":"7-iron","distance":"150 yards","shot_type":"fairway shot","shot_number":1},{"gps":"28.294888444839216,-26.183674623326148","club":"putter","distance":"30 yards","shot_type":"putt","shot_number":2}],"status":"complete","quick_sc":"details for quick SC","condition":{"green":"condition details","teebox":"condition details"},"hole_number":1,"hole_status":"birdie","total_putts":2,"total_shots":5}]}}


        $hole_data = null;
        $hole_status = null;
        foreach($game_data->holes as $hole_data_item){
            if($hole_data_item->hole_number == $hole){
                $hole_data = $hole_data_item;
                break;
            }
        }
        $plot_data = [];
        if(!empty($hole_data)){
            // get the status so we know to join all the lines if complete
            $hole_status = $hole_data->status;

            foreach($hole_data->shots as $shot){
                $plot_data[] = [
                    'type' => 'Feature',
                    'type' => 'Point',
                    'coordinates' => $shot->gps,
                    'club' => $shot->club,
                    'distance' => $shot->distance,
                    'shot_type' => $shot->shot_type,
                    'shot_number' => $shot->shot_number
                ];
            }
            $plot_data = $plot_data;
        }

        http_response_code(200);
        echo json_encode(array( 
            "location_gps" => $location_gps,
            "course_data" => $course_data,
            "holeNumber" => $hole,
            "plot_data" => $plot_data,
            "hole_status" => $hole_status,
            "popups" => $popups,
            "distance_pref" => $distance_pref,
            "user_id" => $user_id,
            "course_id" => $course_id,
            "mode" => $mode,
            "token" => $token,
            "id" => $id
        ));
        
    }else if($plan === 'match'){
        // create a supabase query to gimme_plan_game and retrieve the game_data json object
        $plans_db = $supabase->initializeDatabase('gimme_scores','id');
        $match_type = 'match';
        $status = 'pending';
        $query = [
           'select' => "*",
            'from' => "gimme_scores",
            'where' => [
                'user_id' => 'eq.'.$_POST['user_id'],
                'match_type' => 'eq.'.$match_type,
                'status' => 'eq.'.$status
            ]
        ];
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if(empty($plans)){
            http_response_code(400);
            echo json_encode(array("msg" => "No plan record found"));
            exit;
        }

        $id = $plans[0]->id;
        $game_data = $plans[0]->score_details;

        // example game data:
        // {"game_data":{"holes":[{"shots":[{"gps":"28.295634098946294,-26.183867180911847","club":"7-iron","distance":"150 yards","shot_type":"fairway shot","shot_number":1},{"gps":"28.294888444839216,-26.183674623326148","club":"putter","distance":"30 yards","shot_type":"putt","shot_number":2}],"status":"complete","quick_sc":"details for quick SC","condition":{"green":"condition details","teebox":"condition details"},"hole_number":1,"hole_status":"birdie","total_putts":2,"total_shots":5}]}}


        $hole_data = null;
        $hole_status = null;
        foreach($game_data->holes as $hole_data_item){
            if($hole_data_item->hole_number == $hole){
                $hole_data = $hole_data_item;
                break;
            }
        }
        $plot_data = [];
        if(!empty($hole_data)){
            // get the status so we know to join all the lines if complete
            $hole_status = $hole_data->status;

            foreach($hole_data->shots as $shot){
                $plot_data[] = [
                    'type' => 'Feature',
                    'type' => 'Point',
                    'coordinates' => $shot->gps,
                    'club' => $shot->club,
                    'distance' => $shot->distance,
                    'shot_type' => $shot->shot_type,
                    'shot_number' => $shot->shot_number
                ];
            }
            // $plot_data = json_encode($plot_data);
            $plot_data = $plot_data;
        }

        http_response_code(200);
        echo json_encode(array( 
            "location_gps" => $location_gps,
            "course_data" => $course_data,
            "holeNumber" => $hole,
            "plot_data" => $plot_data,
            "hole_status" => $hole_status,
            "popups" => $popups,
            "distance_pref" => $distance_pref,
            "user_id" => $user_id,
            "course_id" => $course_id,
            "mode" => $mode,
            "token" => $token,
            "id" => $id
        ));
        
    }else if($plan === 'event'){
        // create a supabase query to gimme_plan_game and retrieve the game_data json object
        $plans_db = $supabase->initializeDatabase('gimme_scores','id');
        $match_type = 'event';
        $status = 'active';
        $query = [
           'select' => "*",
            'from' => "gimme_scores",
            'where' => [
                'user_id' => 'eq.'.$_POST['user_id'],
                'match_type' => 'eq.'.$match_type,
                'status' => 'eq.'.$status
            ]
        ];
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if(empty($plans)){
            http_response_code(400);
            echo json_encode(array("msg" => "No plan record found"));
            exit;
        }

        $id = $plans[0]->id;
        $game_data = $plans[0]->score_details;

  

        $hole_data = null;
        $hole_status = null;
        foreach($game_data->holes as $hole_data_item){
            if($hole_data_item->hole_number == $hole){
                $hole_data = $hole_data_item;
                break;
            }
        }
        $plot_data = [];
        if(!empty($hole_data)){
            // get the status so we know to join all the lines if complete
            $hole_status = $hole_data->status;

            foreach($hole_data->shots as $shot){
                $plot_data[] = [
                    'type' => 'Feature',
                    'type' => 'Point',
                    'coordinates' => $shot->gps,
                    'club' => $shot->club,
                    'distance' => $shot->distance,
                    'shot_type' => $shot->shot_type,
                    'shot_number' => $shot->shot_number
                ];
            }
            // $plot_data = json_encode($plot_data);
            $plot_data = $plot_data;
        }

        http_response_code(200);
        echo json_encode(array( 
            "location_gps" => $location_gps,
            "course_data" => $course_data,
            "holeNumber" => $hole,
            "plot_data" => $plot_data,
            "hole_status" => $hole_status,
            "popups" => $popups,
            "distance_pref" => $distance_pref,
            "user_id" => $user_id,
            "course_id" => $course_id,
            "mode" => $mode,
            "token" => $token,
            "id" => $id
        ));
        
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}


?>