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
    echo json_encode(array("msg" => "Invalid event_id"));
    exit;
}
if(!isset($_POST['team_index'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid team_index"));
    exit;
}
if(!isset($_POST['team']) || empty($_POST['team'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid team"));
    exit;
}

$event_id = $_POST['event_id'];
$team_index = $_POST['team_index'];
$team = $_POST['team'];

// Initialize the users database
$scores_db = $supabase->initializeDatabase('gimme_scores','id');
$events_db = $supabase->initializeDatabase('gimme_events','id');

foreach ($team as $member) {
    $query = [
        'select' => '*',
        'from' => 'gimme_scores',
        'where' => [
            'user_id' => 'eq.'.$member['uid'],
            'match_type_id' => 'eq.'.$event_id
        ]
    ];
    try{
        // Get event
        $scoreObject = $scores_db->createCustomQuery($query)->getResult();

        // Handle if no event is found
        if(empty($scoreObject)){
            http_response_code(400);
            echo json_encode(array("msg" => "Event not found"));
            exit; 
        }

        $updateResult = $scores_db->update($scoreObject[0]->id,['status' => 'active']);

        if(!$updateResult){
            http_response_code(400);
            echo json_encode(array("msg" => "Update not successful ".$memebr['uid']));
            exit;
        }

        // Should we create a status on the teams JSON to disqualify an entire team as well?
    }
    catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => $e->getMessage()));
        exit;
    }
}

// Update team JSON
$query = [
    'select' => '*',
    'from' => 'gimme_events',
    'where' => [
        'id' => 'eq.'.$event_id
    ]
];
try{
    // Get event
    $eventObject = $events_db->createCustomQuery($query)->getResult();

    // Handle if no event is found
    if(empty($eventObject)){
        http_response_code(400);
        echo json_encode(array("msg" => "Could not update team data"));
        exit; 
    }

    $eventObject[0]->match_data->teams[intval($team_index)]->status = 'active'; // Status = active || disqualified

    $updateResult = $events_db->update($event_id,['match_data' => $eventObject[0]->match_data]);

    if(!$updateResult){
        http_response_code(400);
        echo json_encode(array("msg" => "Update not successful ".$team_index));
        exit;
    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

http_response_code(200);
echo json_encode(array("msg" => "Success"));
exit;

?>