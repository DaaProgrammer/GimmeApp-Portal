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
$teamIndex = $_POST['team_number'];


$db = $supabase->initializeDatabase('gimme_scores', 'id');
$status = 'pending';
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
                // $decoded_match_settings = json_decode($match_settings, true);
                // print_r($decoded_match_settings);
                $teamName = $_POST['team_name']; // Assuming team name is TigerWood for demonstration
                $teamFound = false;
                foreach ($decoded_match_settings->teams as &$team) {
                    if ($team->team_name === $teamName) {
                        $teamFound = true;
                        $team->team_members[] = [
                            "name" => $name,
                            "email" => $email,
                            "handicap" => $handicap,
                            "gender" => $gender,
                            "invite_sent" => false,
                            "invite_status" => ""
                            
                        ];
                        break;
                    }
                }
                if (!$teamFound) {
                    $decoded_match_settings->teams[] = [
                        "team_name" => $teamName,
                        "team_members" => [
                            [
                                "name" => $name,
                                "email" => $email,
                                "handicap" => $handicap,
                                "gender" => $gender,
                                "invite_sent" => false,
                                "invite_status" => ""
                            ]
                        ]
                    ];
                }

                // Update max_teams and max_team_players
                $decoded_match_settings->settings->max_teams = count($decoded_match_settings->teams);

                $maxTeamPlayers = 0;
                // foreach ($decoded_match_settings->teams as $team) {
                //     $maxTeamPlayers += count($team->team_members);
                // }
                $decoded_match_settings->settings->max_team_players = $maxTeamPlayers;

                // Encode back to JSON to update in database
                $updated_match_settings = json_encode($decoded_match_settings);
                // print_r($updated_match_settings);
                $gimme_match_db = $supabase->initializeDatabase('gimme_match', 'id');

                $gimme_match_settings_data = [
                    'match_settings' => json_decode($updated_match_settings)
                ];
                
                try{
                    $data = $gimme_match_db->update($match_id, $gimme_match_settings_data);
                    if(count($data)>0){
                        http_response_code(200);
                        echo json_encode(array("msg" => "Golfer added successfully"));
                    }
                }
                catch(Exception $e){
                    http_response_code(400);
                    echo json_encode(array("msg" => "Failed to add golfer"));
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
            $teamName = $_POST['team_name']; // Assuming team_name is passed in POST request
            $teamMembers = [
                [
                    "name" => $username, // Assuming name is passed in POST request
                    "email" => $useremail, // Assuming email is passed in POST request
                    "handicap" => $plans[0]->handicap, // Assuming handicap is passed in POST request
                    "gender" => $plans[0]->gender, // Assuming gender is passed in POST request
                    "invite_sent" => false,
                    "invite_status" => ""
                ],
                [
                    "name" => $_POST['name'], // Assuming name is passed in POST request
                    "email" => $_POST['email'], // Assuming email is passed in POST request
                    "handicap" => $_POST['handicap'], // Assuming handicap is passed in POST request
                    "gender" => $_POST['gender'], // Assuming gender is passed in POST request
                    "invite_sent" => false,
                    "invite_status" => ""
                ]
            ];

            $match_settings_json = [
                "teams" => [
                    [
                        "team_name" => $teamName,
                        "team_members" => $teamMembers
                    ]
                ],
                "settings" => [
                    "max_teams" => 1,
                    "max_team_players" => count($teamMembers)
                ],
                "creator_id" => $uid
            ];

            // print_r($match_settings_json);
            try {
                // Insert into gimme_match
                $inserted_match_id = $db_match->insert(['type'=>'team', 'match_settings' => $match_settings_json]);

                if ($inserted_match_id) {
                    
                    $db_scores = $supabase->initializeDatabase('gimme_scores', 'id');


                    $holes = [];
                    for($i = 1; $i <= 18; $i++){
                        array_push(
                            $holes,
                            [
                                "shots"=> [],
                                "status"=> "incomplete",      
                                "quick_sc"=> "false",
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
                            'status' => 'pending'
                        ]);

                        if ($insert_result) {
                            http_response_code(200);
                            echo json_encode(["msg" => "Golfer added successfully"]);
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