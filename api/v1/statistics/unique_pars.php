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
    ];

    $courses = $gimme_courses_db->createCustomQuery($course_query)->getResult();

    http_response_code(200);
    $unique_pars = [];
    foreach ($courses as $course) {
        foreach ($course->course_data->course_data->hole_data as $hole => $data) {
            $par_value = $data->par;
            if (!array_key_exists($par_value, $unique_pars)) {
                $unique_pars[$par_value] = [
                    'par' => $par_value,
                    'par_label' => 'Par ' . $par_value . ' Scoring'
                ];
            }
        }
    }
    ksort($unique_pars); // Sort the pars by key for better readability
    echo json_encode(array("unique_pars" => array_values($unique_pars))); // Use array_values to reset the keys for JSON output
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error while fetching unique pars: " . $e->getMessage()));
    exit;
}

?>