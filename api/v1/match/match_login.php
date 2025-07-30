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

// Get the parameters from the request
// $event_id = htmlspecialchars($_POST['event_id']);
$match_code = htmlspecialchars($_POST['match_code']);
$popups = htmlspecialchars($_POST['popups']);
$distance = htmlspecialchars($_POST['distance']);

// Validate the required fields
if(empty($match_code)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}
if(strlen($match_code) != 8){
    http_response_code(400);
    echo json_encode(array("msg" => "Code length is not valid. The event code must be 8 digits."));
    exit;
}



// Initialize the users database
$match_db = $supabase->initializeDatabase('gimme_match','id');
$events_scores_db = $supabase->initializeDatabase('gimme_scores','id');
$courses_db = $supabase->initializeDatabase('gimme_courses','id');

try{
    // Check if event event_code is correct
    $matchObject = $match_db->findBy("match_code", $match_code)->getResult();

    // echo json_encode($matchObject[0]->match_settings);
        
    // Handle if no event is found
    if(empty($matchObject)){
        http_response_code(400);
        echo json_encode(array("msg" => "Match not found"));
        exit; 
    }
    $match_id = $matchObject[0]->id;
    $course_id = $matchObject[0]->course_id;
    $match_settings = $matchObject[0]->match_settings;
    $type = $matchObject[0]->type;


    // Throw an unauthorised error if code is incorrect
    if($matchObject[0]->match_code == $match_code){

        // Check if user status is invited
        $events_invitations_db = $supabase->initializeDatabase('gimme_event_invitations','id');

        $eventInvitationObject = $events_invitations_db->findBy('event_id',$match_id)->getResult();

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
            if($invite->email == $u_email){
                $userInvite = $invite;

                // Change status of user invitation to 'accepted'
                $invitationDetails[$index]->status = "accepted";
                $invitationDetails[$index]->user_id = $uid;

                // $match_settings][]

                // Update invitations JSON
                $events_invitations_db->update($eventInvitationObject[0]->id,["invitation_details"=>$invitationDetails]);

                
                if($type=='team'){
                    $match_settings->teams[$invitationDetails[$index]->team_index]->team_members[]['uid'] = $invitationDetails[$index]->user_id = $uid;
                    $match_db->update($match_id,["match_settings"=>$match_settings]);
                }
                // print_r($match_settings);

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
                    "match_type" => "match",
                    "match_type_id" => $match_id, // This is the match or event ID
                    "score_details" => ['holes'=>$holes],
                    "status" => 'pending',
                    'hole_gps' => [
                        "mode"=> "match",
                        "popups"=> $popups,
                        "user_id"=> $uid,
                        "distance"=> $distance,
                        "course_id"=> $course_id,
                        "hole_number"=> 1
                    ]
                ];
                // Check if row with this user id and event ID already exists
                $userScoreExistsQuery = [
                    'select' => '*',
                    'from' => 'gimme_scores',
                    'where' => [
                        'match_type' => "eq.match",
                        'match_type_id' => 'eq.'.$match_id,
                        'user_id' => 'eq.'.$uid,
                        'status' => 'eq.pending'
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

                // Fetch course name from the gimme_courses table based on course_id
                $courseQuery = [
                    'select' => '*',
                    'from' => 'gimme_courses',
                    'where' => [
                        'id' => 'eq.1'
                    ]
                ];

                $courseResult = $courses_db->createCustomQuery($courseQuery)->getResult();
                $course_name = $courseResult[0]->course_name;
                $course_address = $courseResult[0]->course_address;

                http_response_code(200);
                echo json_encode(array("msg" => "Round successfully started", "match_id" => $match_id, "match_type" => $match_type, "course_id" => $course_id, "course_name" => $course_name, "course_address" => $course_address));
                exit;

               
            }
        }

        http_response_code(400); // Not welcome
        echo json_encode(array("msg" => "User has not been invited to this match"));
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