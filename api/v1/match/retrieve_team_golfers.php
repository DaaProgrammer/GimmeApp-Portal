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
require '../auth/email_config.php';
require '../emails/email.php';

error_reporting(0); // Turn off all error reporting
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
$emailSender = new Email();

$team_index = $_POST['team_index'];
// $useremail = $_POST['useremail'];

// $name = $_POST['name'];
// $email = $_POST['email'];
// $handicap = $_POST['handicap'];
// $gender = $_POST['gender'];
// $course_id = $_POST['course_id'];


$db = $supabase->initializeDatabase('gimme_scores', 'id');
$status = 'awaiting_match';
$match_type = 'match';

$query = [
    'select' => '*',
    'from'   => 'gimme_scores',
    'where' => 
    [
        'user_id' => 'eq.'.$uid,
        'status' => 'eq.'.$status,
        'match_type' => 'eq.'.$match_type,
    ]
];

try{
    $score = $db->createCustomQuery($query)->getResult();
    if(count($score)>0){
        $match_id = $score[0]->match_type_id;
        $db_match = $supabase->initializeDatabase('gimme_event_invitations', 'id');
        $match_query = [
            'select' => '*',
            'from'   => 'gimme_event_invitations',
            'where' => 
            [
                'event_id' => 'eq.'.$match_id,
                'inviter_id' => 'eq.'.$uid,
            ]
        ];
        try{
            $match = $db_match->createCustomQuery($match_query)->getResult();

          
            if(count($match)>0){
                $decoded_match_settings = $match[0]->invitation_details;
                // print_r($decoded_match_settings);

                $team_members = [];
                foreach($decoded_match_settings as $team){
                    if($team->team_index == $team_index && $team->match_type == 'team'){
                        $team_members[] = $team;
                    }
                }
                echo json_encode(array("match_id" => $match_id, "team_members" => $team_members));
            } else {                        
                http_response_code(400);
                echo json_encode(array("msg" => "No match found for the given score"));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to retrieve golfer"));
        }
    }else{
        http_response_code(400);
        echo json_encode(array("msg" => "No Golfers"));

    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Failed to retrieve golfer"));
}


?>