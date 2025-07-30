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
// error_reporting(0); // Turn off all error reporting

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
// $event_id = htmlspecialchars($_POST['event_id']);
$event_code = htmlspecialchars($_POST['event_code']);

// Validate the required fields
if(empty($event_code)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}
if(strlen($event_code) != 8){
    http_response_code(400);
    echo json_encode(array("msg" => "Code length is not valid. The event code must be 8 digits."));
    exit;
}
// Initialize the users database
$events_db = $supabase->initializeDatabase('gimme_events','id');
$users_db = $supabase->initializeDatabase('gimme_users','id');
$events_scores_db = $supabase->initializeDatabase('gimme_scores','id');

try{

    // Get users email
    $userObject = $users_db->findBy('id',$uid)->getResult();

    // echo json_encode($userObject);
    $userEmail = $userObject[0]->email;

    // Check if event event_code is correct
    $eventObject = $events_db->findBy("event_code", $event_code)->getResult();
    $event_id = $eventObject[0]->id;
    $match_settings = $eventObject[0]->match_data;
    $type = $eventObject[0]->event_type;
        
    // Handle if no event is found
    if(empty($eventObject)){
        http_response_code(400);
        echo json_encode(array("msg" => "Event not found"));
        exit; 
    }

    // Throw an unauthorised error if code is incorrect
    if($eventObject[0]->event_code == $event_code){

        // Check if user status is invited
        $events_invitations_db = $supabase->initializeDatabase('gimme_event_invitations','id');

        $eventInvitationObject = $events_invitations_db->findBy('event_id',$event_id)->getResult();

        if(empty($eventInvitationObject)){
            http_response_code(401); // Unauthorized
            echo json_encode(array("msg" => "Invalid code"));
            exit;
        }

        // Extract invitaion details JSON
        $invitationDetails = $eventInvitationObject[0]->invitation_details;

        // Loop through each invitation to find user
        $userInvite = null;
        foreach ($invitationDetails as $index => $invite) {
            // Assign invite to var and exit loop when the users invite is found
            // echo '| '.$userEmail.'-';
            // echo $invite->email;
            if($invite->email == $userEmail){
                $userInvite = $invite;

                if($invite->status != "declined"){

                    // Change status of user invitation to 'accepted'
                    $invitationDetails[$index]->status = "accepted";
                    $invitationDetails[$index]->user_id = $uid;
                    // Update invitations JSON
                    $events_invitations_db->update($eventInvitationObject[0]->id,["invitation_details"=>$invitationDetails]);


                    // if($type=='team'){
                    //     $match_settings->teams[$invitationDetails[$index]->team_index]->team_members[]['uid'] = $invitationDetails[$index]->user_id = $uid;
                    //     $events_db->update($event_id,["match_data"=>$match_settings]);
                    // }

                    $holes = [];
                    for($i = 1; $i <= 18; $i++){
                        array_push(
                            $holes,
                            [
                                "shots"=> [],
                                "status"=> "incomplete",      
                                "quick_sc"=> false,
                                "quick_shots"=> 0,
                                "quick_putss"=> 0,
                                "condition"=> [
                                    "green"=> "none",
                                    "teebox"=> "none"
                                ],
                                "hole_number"=> $i,
                                "hole_status"=> "none",
                                "total_putts"=> 0,
                                "total_shots"=> 0
                            ],
                        );
                    }

                    // Create new score row object
                    $eventsObject = [
                        "user_id" => $uid,
                        "match_type" => "event",
                        "match_type_id" => $event_id, // This is the match or event ID
                        "score_details" => ['holes'=>$holes],
                        "status" => 'active',
                    ];
                    // Check if row with this user id and event ID already exists
                    $userScoreExistsQuery = [
                        'select' => '*',
                        'from' => 'gimme_scores',
                        'where' => [
                            'match_type' => "eq.event",
                            'match_type_id' => 'eq.'.$event_id,
                            'user_id' => 'eq.'.$uid,
                            'status' => 'eq.active',
                        ]
                    ];

                    $userScoreExistsObject = $events_scores_db->createCustomQuery($userScoreExistsQuery)->getResult();
                    // print_r($userScoreExistsObject);
                    $get_match_type = [];
                    if(empty($userScoreExistsObject)){
                        // Insert new 'blank' row to scores
                        $get_match_type = $events_scores_db->insert($eventsObject);
                        $match_type = $get_match_type[0]->match_type;
                    }else{
                        $match_type = $userScoreExistsObject[0]->match_type;
                    }

                    http_response_code(200);
                    echo json_encode(array("msg" => "Success", "event_id" => $event_id, "match_type" => $match_type));
                    exit;
                }else{
                    http_response_code(400);
                    echo json_encode(array("msg" => "User declined invitation"));
                    exit;
                }
                echo "Still executing";
            }
        }

        http_response_code(400); // Not welcome
        echo json_encode(array("msg" => "User has not been invited to this event"));
        exit;
    }else{
        http_response_code(401); // Unauthorized
        echo json_encode(array("msg" => "Invalid code"));
        exit;
    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

?>