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

$team_name = $_POST['team_name'];
$email = $_POST['email'];
$match_id = $_POST['match_id'];
$userid = $_POST['userid'];
// $useremail = $_POST['useremail'];

// $name = $_POST['name'];
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
        $db_match = $supabase->initializeDatabase('gimme_match', 'id');
        $match_query = [
            'select' => '*',
            'from'   => 'gimme_match',
            'where' => 
            [
                'id' => 'eq.'.$match_id,
            ]
        ];
        try{
            $match = $db_match->createCustomQuery($match_query)->getResult();
            if(count($match)>0){
                $decoded_match_settings = $match[0]->match_settings;
                //print_r($decoded_match_settings);
                foreach($decoded_match_settings->teams as &$team){
                    if($team->team_name == $team_name){
                        foreach($team->team_members as $key => $member){
                            if($member->uid == $userid){
                                unset($team->team_members[$key]);
                                $team->team_members = array_values($team->team_members); // Reindex array
                                break 2; // Exit both loops
                            }
                        }
                    }
                }

                // print_r(json_encode($decoded_match_settings));
                $gimme_match_db = $supabase->initializeDatabase('gimme_match', 'id');

                $gimme_match_settings_data = [
                    'match_settings' => $decoded_match_settings
                ];
                
                try{
                    $data = $gimme_match_db->update($match_id, $gimme_match_settings_data);
                    // if(count($data)>0){
                    //     http_response_code(200);
                    //     echo json_encode(array("msg" => "Golfer deleted successfully"));
                    // }


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
                            $invitation_id = $match[0]->id;
            
                            $decoded_match_settings_arr = [];
                            foreach($decoded_match_settings as $team_member){
                                if($team_member->email == $email){
                                    unset($team_member);
                                    continue;
                                }
                                $decoded_match_settings_arr[] = $team_member;
                            }
                            
                            // echo json_encode($decoded_match_settings_arr, JSON_PRETTY_PRINT);
                            $db_delete_invite = $supabase->initializeDatabase('gimme_event_invitations', 'id');

                            $invitations_delete = [
                                'invitation_details' => $decoded_match_settings_arr,
                            ];
                            
                            try{
                                $data = $db_delete_invite->update($invitation_id, $invitations_delete);
                                http_response_code(200);
                                echo json_encode(array("msg" => "Golfer deleted successfully"));
                            }
                            catch(Exception $e){
                                http_response_code(400);
                                echo json_encode(array("msg" => "Failed to delete golfer"));
                            }
                        } else {                        
                            http_response_code(400);
                            echo json_encode(array("msg" => "Failed to delete golfer"));
                        }
                    }
                    catch(Exception $e){
                        http_response_code(400);
                        echo json_encode(array("msg" => "Failed to retrieve golfer"));
                    }
                }
                catch(Exception $e){
                    http_response_code(400);
                    echo json_encode(array("msg" => "Failed to delete golfer"));
                }

            } else {                        
                http_response_code(400);
                echo json_encode(array("msg" => "Failed to delete golfer"));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to delete golfer"));
        }
    }else{
        http_response_code(400);
        echo json_encode(array("msg" => "No Golfers"));

    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Failed to delete golfer"));
}


?>