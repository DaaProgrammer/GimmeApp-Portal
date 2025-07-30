<?php
// display errors
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
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
require_once '../util/util.php';

error_reporting(0); // Turn off all error reporting
// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$id = $_POST['id'];
$game_type = $_POST['game_type'];


$invitations_db = $supabase->initializeDatabase('gimme_event_invitations','id');
$query = [
    'select' => "*",
    'from' => "gimme_event_invitations",
    'where' => [
        'event_id' => 'eq.'.$id,
        'game_type' => 'eq.'.$game_type
    ]
];

try{
    $match_organiser = $invitations_db->createCustomQuery($query)->getResult();
    if($match_organiser){
        $invitation_details = $match_organiser[0]->invitation_details;

        $firstIteration = true;
        foreach ($invitation_details as $invitation) {
            if ($firstIteration && $invitation->user_id == $uid) {
                http_response_code(200);
                echo json_encode(array("msg" => "You are a match organiser"));
                exit;
            }
            $firstIteration = false;
        }

        http_response_code(400);
        echo json_encode(array("msg" => "You are not a match organiser"));
    }else{
        http_response_code(400);
        echo json_encode(array("msg" => "You are not a match organiser"));
    }


} catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}        

?>