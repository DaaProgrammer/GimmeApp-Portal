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
if(empty($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$course_id = $_POST['course_id'];
// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$courses_info = [];
$gimme_courses_db = $supabase->initializeDatabase('gimme_courses', 'id');
try {
    $course_query = [
        'select' => "course_data",
        'from' => 'gimme_courses',
        'where' => [
            'id' => 'eq.'.$course_id
        ]
    ];

    $courses = $gimme_courses_db->createCustomQuery($course_query)->getResult();

    http_response_code(200);
    $hole_data = [];
    foreach ($courses[0]->course_data->course_data->hole_data as $hole => $data) {
        $hole_data[] = array_merge((array)$data, ['hole_name' => str_replace("_", " ", $hole)]);
    }
    echo json_encode(array("course_holes_and_pars" => $hole_data));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error while fetching courses info: " . $e->getMessage()));
    exit;
}


?>