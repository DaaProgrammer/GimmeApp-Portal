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
if(!isset($_POST['token']) || !isset($_POST['event_id']) || !isset($_POST['team_index']) || !isset($_POST['team_name']) || empty($_POST['team_name']) || !isset($_POST['team_members']) || empty($_POST['team_members'])){
    http_response_code(400);
    $missingFields = [];
    if(!isset($_POST['token'])){
        $missingFields[] = 'token';
    }
    if(!isset($_POST['event_id'])){
        $missingFields[] = 'event_id';
    }
    if(!isset($_POST['team_index'])){
        $missingFields[] = 'team_index';
    }
    if(!isset($_POST['team_name']) || empty($_POST['team_name'])){
        $missingFields[] = 'team_name';
    }
    if(!isset($_POST['team_members']) || empty($_POST['team_members'])){
        $missingFields[] = 'team_members';
    }
    echo json_encode(array("msg" => "Missing required fields: " . implode(', ', $missingFields)));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$db = $supabase->initializeDatabase('gimme_events', 'id');

$query = [
    'select' => '*',
    'from'   => 'gimme_events',
    'where' => [
        'id' => 'eq.'.$_POST['event_id']
    ]
];

try{
    $event = $db->createCustomQuery($query)->getResult();

    $team_members = [];

    // Format team members
    foreach ($_POST['team_members'] as $member) {
        array_push($team_members, ['uid' => $member]);
    }

    // Create a new team object with team_name and team_members
    $newTeam = [
        'team_name' => $_POST['team_name'],
        'team_members' => $team_members
    ];

    // Update the team at specific index
    $event[0]->match_data->teams[$_POST['team_index']] = $newTeam;

    // Update the event data in the database with the new team added
    $updateResult = $db->update($event[0]->id, ['match_data' => $event[0]->match_data]);

    // Return a success message along with event data
    echo json_encode(array("msg" => "success", "event_data" => $updateResult));
}
catch(Exception $e){
    echo $e->getMessage();
}   
?>