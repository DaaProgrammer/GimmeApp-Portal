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

error_reporting(0); // Turn off all error reporting
// Validate the required fields
if(empty($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$gamemode = $_POST['gamemode'];
// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

if($gamemode=='plan'){
    $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
    $course_ids = [];
    try {
        $query = [
            'select' => "course_id",
            'from' => "gimme_plan_game",
            'where' => [
                'user_id' => 'eq.'.$uid,
                'order' => 'id.asc'
            ]
        ];
    
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }
        http_response_code(200);
        foreach ($plans as $plan) {
            if (!in_array($plan->course_id, $course_ids)) {
                $course_ids[] = $plan->course_id;
            }
        }

        if(!empty($course_ids)){
            $courses_db = $supabase->initializeDatabase('gimme_courses','id');
            $courses_info = [];
            foreach ($course_ids as $course_id) {
                try {
                    $query = [
                        'select' => "course_name, course_address",
                        'from' => "gimme_courses",
                        'where' => [
                            'id' => 'eq.'.$course_id
                        ]
                    ];
                
                    $course = $courses_db->createCustomQuery($query)->getResult();
                    if (!empty($course)) {
                        $courses_info[] = [
                            'course_name' => $course[0]->course_name,
                            'course_address' => $course[0]->course_address
                        ];
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                    exit;
                }
            }
            echo json_encode(array("courses_info" => $courses_info));
        }else{
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;

        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }
}else{
    $match_type_ids = [];
    $course_ids  = [];
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');

    try {
        $query = [
            'select' => "*",
            'from' => 'gimme_scores',
            'where' => [
                'user_id' => 'eq.'.$uid,
                'match_type' => 'eq.'.$gamemode,
                'order' => 'id.asc'
            ]
        ];
    
        $plans = $plans_db->createCustomQuery($query)->getResult();
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }
        http_response_code(200);
    foreach ($plans as $plan) {
        $match_type_ids[] = $plan->match_type_id;
    }
    // echo json_encode(array("match_type_ids" => $match_type_ids));

    if(!empty($match_type_ids)){
        $table = '';
        $course_id_column_name = '';
        $course_ids = [];
        if($gamemode=='match'){
            $table = 'gimme_match';
            $course_id_column_name = 'course_id';
        }else{
            $table = 'gimme_events';
            $course_id_column_name = 'event_course';

        }
        $gimme_match_db = $supabase->initializeDatabase($table, 'id');
        foreach ($match_type_ids as $match_type_id) {
            try {
                $match_query = [
                    'select' => "*",
                    'from' => $table,
                    'where' => [
                        'id' => 'eq.'.$match_type_id
                    ]
                ];
            
                $matches = $gimme_match_db->createCustomQuery($match_query)->getResult();
              
                foreach ($matches as $match) {
                    if (!in_array($match->$course_id_column_name, $course_ids)) {
                        $course_ids[] = $match->$course_id_column_name;
                    }
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("msg" => "Server error while fetching course IDs: " . $e->getMessage()));
                exit;
            }
        }
        // echo json_encode(array("course_ids" => $course_ids));
        if(!empty($course_ids) || $course_ids!=null){
        $gimme_courses_db = $supabase->initializeDatabase('gimme_courses', 'id');
        $courses_info = [];
        foreach ($course_ids as $course_id) {
            try {
                $course_query = [
                    'select' => "id, course_name, course_address",
                    'from' => 'gimme_courses',
                    'where' => [
                        'id' => 'eq.'.$course_id
                    ]
                ];
            
                $courses = $gimme_courses_db->createCustomQuery($course_query)->getResult();
                foreach ($courses as $course) {
                    $courses_info[] = [
                        "id" => $course->id,
                        'course_name' => $course->course_name,
                        'course_address' => $course->course_address
                    ];
                }
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(array("msg" => "Server error while fetching courses info: " . $e->getMessage()));
                exit;
            }
        }
        echo json_encode(array("courses_info" => $courses_info));
        }
    }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }    
}



?>