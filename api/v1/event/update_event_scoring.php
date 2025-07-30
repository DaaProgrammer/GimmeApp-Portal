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

// Validate the required fields
if(!isset($_POST['event_id']) || empty($_POST['event_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Event ID is required and cannot be empty."));
    exit;
}
if(!isset($_POST['user_id']) || empty($_POST['user_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "User ID is required and cannot be empty."));
    exit;
}
if(!isset($_POST['has_custom_settings']) || empty($_POST['has_custom_settings'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Custom settings flag is required and cannot be empty."));
    exit;
}
if(!isset($_POST['scoring']) || empty($_POST['scoring'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Scoring method is required and cannot be empty."));
    exit;
}
if(!isset($_POST['handicap']) || empty($_POST['handicap'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Handicap information is required and cannot be empty."));
    exit;
}

$event_id = $_POST['event_id'];
$user_id = $_POST['user_id'];
$hole_data = $_POST['hole_data'];
$has_custom_settings = $_POST['has_custom_settings'];
$scoring = $_POST['scoring'];
$handicap = $_POST['handicap'];

if($has_custom_settings == "true"){
    if(!isset($_POST['hole_data']) || empty($_POST['hole_data'])){
        http_response_code(400);
        echo json_encode(array("msg" => "Hole data is required and cannot be empty."));
        exit;
    }
}

// Initialize the users database
$event_db = $supabase->initializeDatabase('gimme_events','id');
$query = [
    'select' => '*',
    'from' => 'gimme_events',
    'where' => [
        'id' => 'eq.'.$event_id
    ]
];
try{
    // Get event
    $eventObject = $event_db->createCustomQuery($query)->getResult();

    // Handle if no event is found
    if(empty($eventObject)){
        http_response_code(400);
        echo json_encode(array("msg" => "Event not found"));
        exit; 
    }

    // Update settings
    $eventObject[0]->course_data->settings->course_data = $hole_data;

    // Build object to update
    $updateData = $has_custom_settings ? [
        'course_data' => $eventObject[0]->course_data,
        'event_scoring' => $scoring,
        'event_handicap' => $handicap
    ] :
    [
        'event_scoring' => $scoring,
        'hevent_andicap' => $handicap
    ];

    $updateResult = $event_db->update($eventObject[0]->id,$updateData);

    http_response_code(200);
    echo json_encode(array("msg" => "Success",'data' => $updateResult));
    exit;
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

?>