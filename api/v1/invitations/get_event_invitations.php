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

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

// Initialize the users database
$invitations_db = $supabase->initializeDatabase('gimme_event_invitations','id');

// Validate event ID
if (!filter_var($_POST['event_id'], FILTER_VALIDATE_INT)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid event ID']);
  exit;
}

$event_id = $_POST['event_id'];

// Get event from database
// $invitation = $invitations_db->findBy('event_id',$event_id)->getResult();
$invitation = $invitations_db->createCustomQuery([
  "select" => "*",
  "from" => "gimme_event_invitations",
  "where" => [
    "event_id" => 'eq.'.$event_id,
    "game_type" => "eq.event"
  ]
])->getResult();

if (!$invitation) {
  http_response_code(404);
  echo json_encode(['error' => 'Event not found']);
  exit;
}

// Return event
http_response_code(200);
echo json_encode(array("msg" => 'Success','data' => $invitation[0]));
exit;

?>