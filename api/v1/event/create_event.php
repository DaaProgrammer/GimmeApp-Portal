<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    exit;
}

// Set headers for CORS and content type
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Decode JSON from request body into $_POST
$_POST = json_decode(file_get_contents('php://input'), true);

// Include required files for token authentication and utility functions
require '../auth/token.php';
require '../util/util.php';

// Check if token is provided in the request
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Token is missing"));
    exit;
}

// Initialize database connections
$supabase = new PHPSupabase\Service($config['supabaseKey'], $config['supabaseUrl']);
$db = $supabase->initializeDatabase('gimme_events', 'id');
$invitationsTable = $supabase->initializeDatabase('gimme_event_invitations', 'id');
$courseTable = $supabase->initializeDatabase('gimme_courses', 'id');
$matchTable = $supabase->initializeDatabase('gimme_match', 'id');

// Extract and validate event details from request
$requiredFields = [
    'event_description','event_name', 'event_date_time', 'event_participants', 'event_course',
    'event_holes', 'event_default_tees', 'event_registration_date', 'event_type',
    'event_scoring'
];

foreach ($requiredFields as $field) {
    if($field != "event_course"){
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(array("msg" => "Missing or invalid field: $field"));
            exit;
        }
    }else{
        if (!isset($_POST[$field])){
            http_response_code(400);
            echo json_encode(array("msg" => "Missing or invalid field: $field"));
            exit;
        }
    }
}

// Validate specific fields with predefined values
$validParticipants = ['men', 'woman'];
$validHoles = ['18', 'front-nine', 'back-nine'];
if (!in_array($_POST['event_participants'], $validParticipants) || !in_array($_POST['event_holes'], $validHoles)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid participants or holes value"));
    exit;
}

// Generate a unique event code
$start_time = time();
do {
    $eventCode = generateEventCode();
    $eventExistsQueryResult = $db->findBy('event_code', $eventCode)->getResult();
} while (!empty($eventExistsQueryResult) && (time() - $start_time) < 60);

// Fetch course data and validate existence
$courseData = $courseTable->findBy('id', $_POST['event_course'])->getResult();
if (empty($courseData)) {
    http_response_code(400);
    echo json_encode(array("msg" => "Specified course does not exist"));
    exit;
}

// Format the event_date_time and event_registration_date post variables to timestampZ format
// $eventDateTime = new DateTime($_POST['event_date_time']);
// $event_data['event_date_time'] = $eventDateTime->format('Y-m-d\TH:i:s.v\Z');

// $regDateTime = new DateTime($_POST['event_registration_date']);  
// $event_data['event_registration_date'] = $regDateTime->format('Y-m-d\TH:i:s.v\Z');


// Prepare event data for insertion
$event_data = [
    "event_name" => $_POST['event_name'],
    "event_code" => $eventCode,
    "event_description" => $_POST['event_description'],
    "event_date_time" => $_POST['event_date_time'],
    "event_participants" => $_POST['event_participants'],
    "event_status" => 'pending',
    "event_course" => $_POST['event_course'],
    "event_holes" => $_POST['event_holes'],
    "event_default_tees" => $_POST['event_default_tees'],
    "event_registration_date" => $_POST['event_registration_date'],
    "event_notification" => 'Email Reminder',
    "event_type" => $_POST['event_type'],
    // "event_team_format" =>  $_POST['event_team_format'] ? '' : 'none',
    "event_max_players" => $_POST['event_max_players'],
    "event_auto_assign" => false,
    "event_scoring" => $_POST['event_scoring'],
    "event_handicap" => $_POST['event_handicap'],
    "course_data" => $courseData[0]->course_data,
    'uid' => "".$uid.""
];

// Insert event data into database
$insertEventResult = $db->insert($event_data);

// Insert invitation data
$insertInvitationsResult = $invitationsTable->insert([
    'inviter_id' => $uid,
    'event_id' => $insertEventResult[0]->id,
    'invitation_details' => [],

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // YOUR ERROR IS HERE ///////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    'game_type' => 'event'
]);

// Respond based on insertion result
if (!$insertEventResult) {
    http_response_code(400);
    echo json_encode(array("msg" => "Failed to create event"));
    exit;
}

// Update match data
// Format teams if event type is teams
if ($_POST['event_type'] == 'team'){
// 'event_player_per_team','event_max_teams'
    if(!isset($_POST['event_player_per_team']) || !isset($_POST['event_max_teams'])){
        http_response_code(400);
        echo json_encode(array("msg" => "Missing or invalid field: event_player_per_team or event_max_teams"));
        exit;
    }
    $event_id = $insertEventResult[0]->id;
    $match_data = [];

    $match_data['teams'] = [];
    $match_data['event_id'] = $event_id;
    $match_data['settings'] = [
        "max_teams" => $_POST['event_player_per_team'],
        "max_team_players" => $_POST['event_max_teams']
    ];

    $updateEventResult = $db->update($event_id,['match_data' => $match_data]);
    if (!$updateEventResult) {
        http_response_code(400);
        echo json_encode(array("msg" => "Failed to format event team data"));
        exit;
    }
}

http_response_code(200);
echo json_encode(array("msg" => "Event created successfully"));
?>
