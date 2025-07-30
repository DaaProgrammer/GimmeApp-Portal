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

$search = $_POST['search'];
// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$match_type_ids = [];
$course_ids  = [];
$match_types = [];
// $plans_db = $supabase->initializeDatabase('gimme_scores','id');
$plans_db = $supabase->initializeQueryBuilder();
try {
    $plans = $plans_db->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->order('match_type_id.desc')
                ->execute()
                ->getResult();


    // $plans = $plans_db->createCustomQuery($query)->getResult();
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
        $roundIDs = [];
        $course_ids = [];
        $match_codes = [];
        $match_date_updated = [];
        $scoring_formats = [];
        $team_types = [];
        $tables = ['gimme_match', 'gimme_events'];

        foreach ($tables as $table) {
            $gimme_db = $supabase->initializeDatabase($table, 'id');
            foreach ($match_type_ids as $match_type_id) {
                try {
                    $match_query = [
                        'select' => "*",
                        'from' => $table,
                        'where' => [
                            'id' => 'eq.'.$match_type_id,
                            'order' => 'id.desc'
                        ]
                    ];
                
                    $matches = $gimme_db->createCustomQuery($match_query)->getResult();
                
                    foreach ($matches as $match) {
                        // Determine the correct column name based on the table
                        $course_id_column_name = $table == 'gimme_match' ? 'course_id' : 'event_course';
                        $match_code_column_name = $table == 'gimme_match' ? 'match_code' : 'event_code';
                        $scoring_format_column_name = $table == 'gimme_match' ? 'scoring_format' : 'event_scoring';
                        $updated_at_column_name = 'updated_at'; // Assuming both tables have an 'updated_at' column
                    
                        $course_ids[] = $match->$course_id_column_name;
                        $match_codes[] = $match->$match_code_column_name;
                        $roundIDs[] = $match->id;
                        $scoring_formats[] = $match->$scoring_format_column_name;
                        $date = new DateTime($match->$updated_at_column_name);
                        
                        $match_types[] =  $table == 'gimme_match' ? 'match' : 'event';
                        $team_types[] =  $table == 'gimme_match' ? $match->type : $match->event_type;
                        $match_date_updated[] = $date->format('d F Y');
                
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(array("msg" => "Server error while fetching course IDs: " . $e->getMessage()));
                    exit;
                }
            }
        }



        // echo json_encode(array("course_ids" => $course_ids));
        if(!empty($course_ids) || $course_ids!=null){
            $gimme_courses_db = $supabase->initializeDatabase('gimme_courses', 'id');
            $courses_info = [];
            $counter = 0;
            foreach ($course_ids as $course_id) {
                $match_code_now = $match_codes[$counter];
                $match_date_updated_now = $match_date_updated[$counter];
                $match_type = $match_types[$counter];
                $roundID = $roundIDs[$counter];
                $scoring_format = $scoring_formats[$counter];
                $team_type = $team_types[$counter];
                $counter++;
                try {
                    $course_query = [
                        'select' => "*",
                        'from' => 'gimme_courses',
                        'where' => [
                            'id' => 'eq.'.$course_id,
                            'order' => 'id.desc'
                        ]
                    ];
                
                    $courses = $gimme_courses_db->createCustomQuery($course_query)->getResult();
                    $hole_number = 'hole_1';
                    // print_r($courses);
                    foreach ($courses as $course) {
                        $courses_info[] = [
                            "id" => $course->id,
                            'course_name' => $course->course_name,
                            'course_address' => $course->course_address,
                            'match_code' => $match_code_now,
                            'match_type' => $match_type,
                            'par' => $course->course_data->course_data->hole_data->$hole_number->par,
                            'match_date_created' => $match_date_updated_now,
                            'roundID' => $roundID,
                            'scoring_format' => $scoring_format,
                            'team_type' => $team_type
                        ];
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(array("msg" => "Server error while fetching courses info: " . $e->getMessage()));
                    exit;
                }
            }
            if (!empty($search) || $search!='' || $search!=null) {
                $filtered_courses_info = array_filter($courses_info, function ($course) use ($search) {
                    foreach ($course as $key => $value) {
                        if (strpos(strtolower($value), strtolower($search)) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
                echo json_encode(array("playing_history" => $filtered_courses_info));
            } else {
                echo json_encode(array("playing_history" => $courses_info));
            }
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
}    


?>