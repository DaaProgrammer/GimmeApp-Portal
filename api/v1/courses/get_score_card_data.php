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


$courses_db = $supabase->initializeDatabase('gimme_courses','id');


$query = [
    'select' => "*",
    'from' => "gimme_courses",
    'where' => [
        'id' => 'eq.'.$course_id
    ]
];


try{

    $course = $courses_db->createCustomQuery($query)->getResult();
    $course_data = $course[0]->course_data->course_data->hole_data;

    // Initialize arrays for first 8 holes and the rest
    $first_8_holes = [];
    $remaining_holes = [];

    // Loop through course data and separate first 8 holes
    $count = 1; // Start counting from 1
    $first_pars_total = 0;
    $second_pars_total = 0;
    foreach ($course_data as $hole_name => $hole_data) {
        $hole = array(
            "par" => $hole_data->par,
            "tee_box" => $hole_data->tee_box,
            "hole_name" => $hole_data->hole_name,
            "from_the_tee" => $hole_data->from_the_tee,
            "on_the_green" => $hole_data->on_the_green,
            "from_the_fairway" => $hole_data->from_the_fairway,
            "pin_location_gps" => $hole_data->pin_location_gps
        );

        if ($count <= 9) {
            $first_pars_total+=$hole_data->par;
            $first_8_holes["hole_$count"] = $hole;
        } else {
            $second_pars_total+=$hole_data->par;
            $remaining_holes["hole_$count"] = $hole;
        }
        $count++;
    }

    $new_first_pars = [];
    foreach ($first_8_holes as $hole) {
        $new_first_pars[] = $hole;
    }

    $new_second_pars = [];
    foreach ($remaining_holes as $hole) {
        $new_second_pars[] = $hole;
    }

    $new_first_pars[] = array('par'=>strval($first_pars_total));
    $new_second_pars[] = array('par'=>strval($second_pars_total));
    $new_second_pars[] = array('par'=>strval($first_pars_total+$second_pars_total));

    $output = array(
        "first_pars" => $new_first_pars,
        "second_pars" => $new_second_pars
    );

    http_response_code(200);
    echo json_encode(array("course_data" => $output));

} catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}    
?>