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

// Get the parameters from the request
$event_id = htmlspecialchars($_POST['event_id']);

// Validate the required fields
if(empty($event_id)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

// Initialize the users database
$events_db = $supabase->initializeDatabase('gimme_events','id');
$course_db = $supabase->initializeDatabase('gimme_courses','id');

try{
    // Get event
    // $eventObject = $events_db->findBy("id", $event_id)->getResult();
    $eventObject = $events_db->findBy("id", $event_id)->join('gimme_courses','id')->getResult();

    // Handle if no event is found
    if(empty($eventObject)){
        http_response_code(400);
        echo json_encode(array("msg" => "Event not found"));
        exit; 
    }

	   $responseData = array();
	    foreach ($eventObject as $event) {
	    	    $event->event_date_time = date("d F Y", strtotime($event->event_date_time));
	        $responseData[] = $event;
	    }


    http_response_code(200);
    echo json_encode(array("msg" => "Success",'data' => $responseData));
    exit;
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

?>