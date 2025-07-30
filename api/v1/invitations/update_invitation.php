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

if (empty($_POST['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Token not found']);
    exit;
}

require '../auth/token.php';
require '../auth/email_config.php';
require '../emails/email.php';

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$emailSender = new Email();

// Initialize the users database
$invitations_db = $supabase->initializeDatabase('gimme_event_invitations','id');
$event_db = $supabase->initializeDatabase('gimme_events','id');

// Validate event ID
if (empty($_POST['event_id'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid event ID']);
  exit;
}
if (empty($_POST['invitation'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

$event_id = $_POST['event_id'];
$invitation = $_POST['invitation'];

// Get event from database
$query = [
  'select' => '*',
  'from'   => 'gimme_event_invitations',
  'where' => 
  [
    'event_id' => 'eq.'.$event_id,
    // 'inviter_id' => 'eq.'.$uid,
  ]
];

$invitationRow = $invitations_db->createCustomQuery($query)->getResult();

if (empty($invitationRow)) {
  http_response_code(400);
  echo json_encode(['error' => 'Event not found','event_id' => 'eq.'.$event_id,'row'=>$invitationRow]);
  exit;
}

// Validate the invitation has the required fields
if (!isset($invitation['email']) || !isset($invitation['handicap']) || !isset($invitation['gender']) || !isset($invitation['tee'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Invitation is missing required fields']);
  exit;
}

// Add additional details to invitation
$invitation['status'] = "pending";
$invitation['user_id'] = $uid;
$invitation['date'] = date('Y-m-d H:i:s');

// array_push($invitationRow[0]->invitation_details,$invitation);

foreach ($invitationRow[0]->invitation_details as $index => $invite) {
    if ($invite->email == $invitation['email']) {
        $invitationRow[0]->invitation_details[$index] = $invitation;
        $invitationRow[0]->invitation_details[$index]['handicap'] = $invitation['handicap'];
        $invitationRow[0]->invitation_details[$index]['gender'] = $invitation['gender'];
        $invitationRow[0]->invitation_details[$index]['tee'] = $invitation['tee'];
    }
}

$updateResult = $invitations_db->update($invitationRow[0]->id,['invitation_details'=>$invitationRow[0]->invitation_details]);

// Return event
http_response_code(200);
echo json_encode(array("msg" => 'Success'
,'data' => $updateResult[0]
));

exit;

?>