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

// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$course_id = $_POST['course_id'];
$course_name = $_POST['course_name'];
$course_gps = $_POST['course_gps'];
$course_description = $_POST['course_description'];
$teeData = $_POST['teeData'];
$mensTees = $_POST['mensTees'];
$womensTees = $_POST['womensTees'];
$holeData = $_POST['holeData'];
$stimpReading = $_POST['stimpReading'];


if(!isset($course_name) || !isset($course_gps) || !isset($course_description) || !isset($teeData) || !isset($mensTees) || !isset($womensTees) || !isset($holeData) || !isset($stimpReading)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$db = $supabase->initializeDatabase('gimme_courses', 'id');

// Form updated course_data object
$courseData = [];
// Set values
$courseData['settings'] ['custom_data'] = false;
$courseData['course_data']['hole_data'] = $holeData;
$courseData['course_data']['stimp_reading'] = $stimpReading;

// Set tee data
$tee_data = [
    "tees" => $teeData
];

// Form updated Course object
$updatedCourseData = [
    'course_name'           =>$course_name,
    'course_description'    =>$course_description,
    'location_gps'          =>$course_gps,
    'course_data'           =>$courseData,
    'default_tees_men'      =>$mensTees,
    'tee_data'              =>$tee_data,
    'default_tees_woman'    =>$womensTees
];

try{
    $db->update($course_id,$updatedCourseData);

    http_response_code(200);
    echo json_encode(array("msg" => "success"));
    exit;
}
catch(Exception $e){
    http_response_code(400);
    echo $e->getMessage();
    exit;
}   

?>