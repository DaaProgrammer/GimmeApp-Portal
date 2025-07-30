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
    echo json_encode(['msg' => 'Token not found']);
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
$scorecard_db = $supabase->initializeDatabase('gimme_scores','id');

// Validate event ID
if (empty($_POST['event_id'])) {
  http_response_code(400);
  echo json_encode(['msg' => 'Invalid event ID']);
  exit;
}

// ----------------------------------------------------------------------------------------------------------------------------------------------------
// DEV TESTS ONLY
if (empty($_POST['inviter_id'])) {
  http_response_code(400);
  echo json_encode(['msg' => 'Invalid inviter ID']);
  exit;
}
$inviter_id = $_POST['inviter_id'];
// ----------------------------------------------------------------------------------------------------------------------------------------------------
// END

if (empty($_POST['email'])) {
  http_response_code(400);
  echo json_encode(['msg' => 'Invalid email']);
  exit;
}

$event_id = $_POST['event_id'];
$invitation_email = $_POST['email'];

// Get invitation from database
$query = [
  'select' => '*',
  'from'   => 'gimme_event_invitations',
  'where' => 
  [
    // 'inviter_id' => 'eq.'.$uid,

    // ----------------------------------------------------------------------------------------------------------------------------------------------------
    // DEV TESTS ONLY
    'inviter_id' => 'eq.'.$uid,
    // ----------------------------------------------------------------------------------------------------------------------------------------------------
    // END
    'event_id' => 'eq.'.$event_id,
  ]
];

$invitationRow = $invitations_db->createCustomQuery($query)->getResult();

if (empty($invitationRow)) {
  http_response_code(400);
  echo json_encode(['msg' => 'Invitation not found','data'=>$query]);
  exit;
}

// Delete the invitation
// ----------------------------------------------------------------------------------------------------------------------------------------------------
// Loop through the invitation details JSON array to find the index of the object with email matching $_POST['email']
$invitationDetails = $invitationRow[0]->invitation_details;
$indexToDelete = null;
foreach ($invitationDetails as $index => $detail) {
  if ($detail->email === $invitation_email) {
    // Ensure that the invite has not already been accepted before deleting
    if($detail->status == 'accepted'){
      http_response_code(400);
      echo json_encode(['msg' => 'Invite has already been accepted']);
      exit;
    }
    // If email has not already been accepted, then it can be deleted
    $indexToDelete = $index;
    break;
  }
}

// If index to delete is found, remove the object from the JSON array
if ($indexToDelete !== null) {
  if(is_numeric($invitationDetails[$indexToDelete]->user_id)){
    // Delete the row from the scorecard_db table where userID = $invitationDetails[$indexToDelete]->user_id and match_type_id = $event_id
    $deleteQuery = [
      'select' => 'id',
      'from' => 'gimme_scores',
      'where' => [
        'user_id' => 'eq.' . $invitationDetails[$indexToDelete]->user_id,
        'match_type_id' => 'eq.' . $event_id
      ]
    ];
    $rowToDelete = $invitations_db->createCustomQuery($deleteQuery)->getResult();

    // Ensure that score row exists
    if(empty($rowToDelete)){
      http_response_code(400);
      echo json_encode(['msg' => 'Could not find score to delete']);
      exit;
    }

    // Delete score row
    $invitations_db->delete($rowToDelete[0]->id);
  }
  // Remove invite from array
  array_splice($invitationDetails,$indexToDelete,1);
}else{
  http_response_code(400);
  echo json_encode(['msg' => 'Invite could not be found - '.$invitation_email]);
  exit;
}

// Update the invitation details in the database
$updateResult = $invitations_db->update($invitationRow[0]->id, ['invitation_details' => $invitationDetails]);


// Return success message
http_response_code(200);
echo json_encode(array("msg" => 'Success'));
exit;
?>