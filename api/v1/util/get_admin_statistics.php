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
if(!isset($_POST['is_event_organiser'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

// Initialize the users database
$events_db = $supabase->initializeDatabase('gimme_events','id');
$users_db = $supabase->initializeDatabase('gimme_users','id');
$match_db = $supabase->initializeDatabase('gimme_match','id');
$event_invitations_db = $supabase->initializeDatabase('gimme_event_invitations','id');

if($_POST['is_event_organiser'] == "true"){
    try{

        // Get events
        $eventCount = count($events_db->fetchAll()->getResult());

        $query = [
            "select" => "*",
            "from" => "gimme_events",
            "where" => [
                "event_status" => "eq.complete"
            ]
        ];
        $eventsCompleted = count($events_db->createCustomQuery($query)->getResult());

        // Get invitations
        $invitationCount = count($event_invitations_db->fetchAll()->getResult());

        $statistics = [
            "eventCount"=>$eventCount,
            "eventsCompleted"=>$eventsCompleted,
            "eventInvitations"=>$invitationCount
        ];

        http_response_code(200);
        echo json_encode(array("msg" => "Success",'statistics' => $statistics));
        exit;
    }
    catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => $e->getMessage()));
        exit;
    }
}else{
    try{
        // Get event
        $eventObject = $events_db->fetchAll()->getResult();
        $userObject = $users_db->fetchAll()->getResult();
        $matchObject = $match_db->fetchAll()->getResult();

        // Handle if no event is found
        if(empty($eventObject)){
            http_response_code(400);
            echo json_encode(array("msg" => "Event not found"));
            exit; 
        }
        if(empty($userObject)){
            http_response_code(400);
            echo json_encode(array("msg" => "Users not found"));
            exit; 
        }
        if(empty($matchObject)){
            http_response_code(400);
            echo json_encode(array("msg" => "Matches not found"));
            exit; 
        }

        $statistics = [
            "match"=>count($matchObject),
            "user"=>count($userObject),
            "event"=>count($eventObject)
        ];

        http_response_code(200);
        echo json_encode(array("msg" => "Success",'statistics' => $statistics));
        exit;
    }
    catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => $e->getMessage()));
        exit;
    }
}

?>