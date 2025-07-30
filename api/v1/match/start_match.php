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

$handicaps = $_POST['handicaps'];
$scoring_formats = $_POST['formats'];
$teamscheck = $_POST['teamscheck'];
$course_id = $_POST['course_id'];

$team_format = '';
$type = '';

if(isset($_POST['teamscheck']) && $_POST['teamscheck']==1){
    $team_format = $_POST['team_scoring'];
    $type = 'team';
}else{
    $team_format = 'none';
    $type = 'individual';
}

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
            $match_code = bin2hex(random_bytes(4));
            if(count($match)>0){

                $update_data = [
                    'course_id' => $course_id,
                    'scoring_format' => $scoring_formats,
                    'match_format' => $handicaps, 
                    'team_format' => $team_format,
                    'type' => $type,
                    'match_code' => $match_code,
                ];

                $data = $db_match->update($match_id, $update_data);
                if(count($data)>0){
                
                    $db_invitations = $supabase->initializeDatabase('gimme_event_invitations', 'id');
                    $query_invitations = [
                        'select' => 'invitation_details',
                        'from'   => 'gimme_event_invitations',
                        'where' => 
                        [
                            'event_id' => 'eq.'.$match_id,
                            'inviter_id' => 'eq.'.$uid,
                            'game_type' => 'eq.'.$match_type
                        ]
                    ];
                    try{
                        $invitations_data = $db_invitations->createCustomQuery($query_invitations)->getResult();
                        if(count($invitations_data)>0){
                            $invitations = $invitations_data[0]->invitation_details;
                            $usernames = [];
                            $emails = [];
                            foreach ($invitations as $invitation) {
                                if($invitation->match_type === $type && $invitation->user_id != $uid){
                                    $emails[] = $invitation->email;
                                    $usernames[] = $invitation->name;
                                }
                            }
                            $counter=0;
                            foreach ($emails as $email) {
                                $template = $emailSender->GolferMatchCode($email, $usernames[$counter], $match_code);
                                $counter+=1;
                                $response = $mailjet->sendEmail(
                                    $template['replyToEmail'], 
                                    $template['emailTitle'],
                                    $template['emailTo'], 
                                    $template['emailToName'],
                                    $template['emailSubject'],
                                    $template['emailMessage']
                                );
                            }

                            if(count($data)>0){

                                $db_scores = $supabase->initializeDatabase('gimme_scores', 'id');
                                $update_score_data = [
                                    'status' => 'pending'
                                ];
                                $score_update = $db_scores->update($score[0]->id, $update_score_data);

                                http_response_code(200);
                                echo json_encode(array("msg" => "Round started successfully", "match_code" => $match_code));
                            }
                        }
                    }
                    catch(Exception $e){
                        http_response_code(400);
                        echo json_encode(array("msg" => "Failed to retrieve event invitations"));
                    }
                }
                // echo json_encode(array("msg" => "Continuing the match"));

            } else {                        
                http_response_code(400);
                echo json_encode(array("msg" => "No Round found for the given score"));
                exit;
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to start a Round"));
            exit;
        }
    }else{
        $btnStartPlan = isset($_POST['btnStartPlan']) ? $_POST['btnStartPlan'] : false;
        $db_match = $supabase->initializeDatabase('gimme_match', 'id');
        $match_code = bin2hex(random_bytes(4));
        $gimme_match_settings_data = [
            'course_id' => $course_id,
            'scoring_format' => $scoring_formats,
            'match_format' => $handicaps, 
            'team_format' => $team_format,
            'type' => $type,
            'match_code' => $match_code,
        ];

        try {
            // Insert into gimme_match
            $inserted_match_id = $db_match->insert($gimme_match_settings_data);
            if(count($inserted_match_id)>0){

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
                    $insert_result = $db->insert([
                        'user_id' => $uid,
                        'match_type_id' => $inserted_match_id[0]->id,
                        'score_details' => $btnStartPlan ? $holes : ['holes'=>$holes],
                        'match_type' => 'match',
                        'status' => 'pending'
                    ]);

                    if ($insert_result) {
                        http_response_code(200);
                        echo json_encode(array("msg" => "Round started successfully", "match_code" => $match_code));
                        exit;
                    } else {
                        http_response_code(400);
                        echo json_encode(array("msg" => "Failed to start a Round"));
                        exit;
                    }
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(array("msg" => "Failed to start a Round"));
                    exit;
                }


            }
        }catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to start a Round"));
            exit;
        }


    }
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Failed to start a Round"));
    exit;
}


?>