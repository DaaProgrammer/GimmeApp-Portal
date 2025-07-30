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

$username = $_POST['username'];
$useremail = $_POST['useremail'];

$name = $_POST['name'];
$email = $_POST['email'];
$handicap = $_POST['handicap'];
$gender = $_POST['gender'];
$course_id = $_POST['course_id'];
$distance_prefer = $_POST['distanceprefer'];
$tees = $_POST['tees'];
// $teamIndex = $_POST['team_number'];


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
                $db_invite = $supabase->initializeDatabase('gimme_event_invitations', 'id');
                $match_invite_query = [
                    'select' => '*',
                    'from'   => 'gimme_event_invitations',
                    'where' => 
                    [
                        'event_id' => 'eq.'.$match_id,
                        'inviter_id' => 'eq.'.$uid,
                        'game_type' => 'eq.'.$match_type
                    ]
                ];
                try{
                    $match_invite = $db_invite->createCustomQuery($match_invite_query)->getResult();

                    if(count($match_invite)>0){
                        $invitation_details = $match_invite[0]->invitation_details;
                        $invitation_details[] = [
                            'tee' => $tees,
                            'date' => date('Y-m-d H:i:s'),
                            'name' => $name,
                            'phone' => '',                                 
                            'email' => $email,
                            'gender' => $gender,
                            'status' => 'pending',
                            'user_id' => '',
                            'handicap' => $handicap,
                            'match_type' => 'individual',
                            'team_index' => 0
                        ];


                        $db_invitations_update = $supabase->initializeDatabase('gimme_event_invitations', 'id');

                        $invitation_details_new_data = [
                            'invitation_details' => $invitation_details
                        ];

                        
                        try{
                            $data = $db_invitations_update->update($match_invite[0]->id, $invitation_details_new_data); //the first parameter ('1') is the product id
                            http_response_code(200);
                            echo json_encode(["msg" => "Golfer added successfully"]);
                            exit;
                        }
                        catch(Exception $e){
                            http_response_code(400);
                            echo json_encode(array("msg" => "Failed to add golfer2"));
                        }

                    }else{
                        http_response_code(400);
                        echo json_encode(array("msg" => "Failed to add golfer1"));
                        exit;
                    }
                }
                catch(Exception $e){
                    http_response_code(400);
                    echo json_encode(array("msg" => "Failed to add golfer3"));
                }
            } else {                        
                http_response_code(400);
                echo json_encode(array("msg" => "No match found for the given score"));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to add golfer"));
        }
    }else{
        
        $plans_db = $supabase->initializeDatabase('gimme_user_preferences','id');
    
        try {
            $query = [
                'select' => '*',
                'from'   => 'gimme_user_preferences',
                'where' => 
                [
                    'uid' => 'eq.'.$uid,
                ]
            ];
    
    
            $plans = $plans_db->createCustomQuery($query)->getResult();

            $db_match = $supabase->initializeDatabase('gimme_match', 'id');
            $match_settings_json = [
                "teams" => [
                    []
                ],
                "settings" => [
                    "max_teams" => 0,
                    "max_team_players" => 0
                ],
                "creator_id" => $uid
            ];

            // print_r($match_settings_json);
            try {
                // Insert into gimme_match
                $inserted_match_id = $db_match->insert(['course_id' => $course_id, 'type'=>'individual', 'match_settings' => $match_settings_json]);

                if ($inserted_match_id) {
                    
                    $db_scores = $supabase->initializeDatabase('gimme_scores', 'id');


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

                    try{
                        $insert_result = $db_scores->insert([
                            'user_id' => $uid,
                            'match_type_id' => $inserted_match_id[0]->id,
                            'score_details' => ["holes"=> $holes],
                            'match_type' => 'match',
                            'status' => 'awaiting_match',
                            'hole_gps' => [
                                "mode"=> "match",
                                "popups"=> false,
                                "user_id"=> $uid,
                                "distance"=> $distance_prefer,
                                "course_id"=> $course_id,
                                "hole_number"=> 1
                            ]
                        ]);

                        if ($insert_result) {

                            $db_invitations = $supabase->initializeDatabase('gimme_event_invitations', 'id');

                            $invitations_details = [
                                [
                                    'tee' => $plans[0]->tees,
                                    'date' => date('Y-m-d H:i:s'),
                                    'name' => $username,
                                    'phone' => '',                                    
                                    'email' => $useremail, 
                                    'gender' => $plans[0]->gender,
                                    'status' => 'accepted',
                                    'user_id' => $uid,
                                    'handicap' => $plans[0]->handicap,
                                    'match_type' => 'individual',
                                    'team_index' => 0

                                ],
                                [
                                    'tee' => $tees,
                                    'date' => date('Y-m-d H:i:s'),
                                    'name' => $name,
                                    'phone' => '',                                    
                                    'email' => $email,
                                    'gender' => $gender,
                                    'status' => 'pending',
                                    'user_id' => '',
                                    'handicap' => $handicap,
                                    'match_type' => 'individual',
                                    'team_index' => 0

                                ]
                            ];


                            $query_invitations = [
                                'event_id' => $inserted_match_id[0]->id,
                                'inviter_id' => $uid,
                                'invitation_details' => $invitations_details,
                                'game_type' => $match_type
                            ];
                            
                            try{
                                $invitations_data = $db_invitations->insert($query_invitations);
                                if($invitations_data){                                    
                                    http_response_code(200);
                                    echo json_encode(["msg" => "Golfer added successfully"]);
                                    exit;
                                }else{
                                    http_response_code(400);
                                    echo json_encode(array("msg" => "Failed to add golfer"));
                                    exit;
                                }
                             
                            }
                            catch(Exception $e){
                                http_response_code(400);
                                echo json_encode(array("msg" => "Failed to add golfer"));
                            }
                        } else {
                            http_response_code(400);
                            echo json_encode(array("msg" => "Failed to add golfer"));
                        }
                    } catch (Exception $e) {
                        http_response_code(400);
                        echo json_encode(array("msg" => "Failed to add golfer"));
                    }
                } else {
                    throw new Exception("Failed to insert new match into gimme_match");
                }

            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(array("msg" => "Failed to add golfer"));
            }

        }catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => $e->getMessage()));
            exit;
        }

    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Failed to add golfer"));
}



?>