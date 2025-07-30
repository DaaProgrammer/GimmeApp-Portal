<?php
require '../../Tools/JWT/jwt.php';
require '../../Tools/JWT/key_signed.php';
require_once '../../Tools/Supabase/vendor/autoload.php';
$config = require_once '../../Tools/Supabase/config.php';
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

// require '../auth/token.php';
require_once '../util/util.php';

// Validate the required fields
// if(!isset($_POST['token'])){
//     http_response_code(400);
//     echo json_encode(array("msg" => "Invalid request"));
//     exit;
// }

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$uid = $_POST['user_id'];
$course_id = $_POST['course_id'];
$hole = $_POST['hole'];
$gamemode = 'match';
$match_type_id = $_POST['match_type_id'];

function calculateResult($userScore, $parScore) {
    $result = '';
    $icon = '';

    if ($userScore == 1) {
        $result = 'Hole-in-One';
        $icon = 'golf_ball_in_cup';
    } elseif ($userScore - $parScore == -4) {
        $result = 'Condor';
        $icon = 'four_birds';
    } elseif ($userScore - $parScore == -3) {
        $result = 'Albatross';
        $icon = 'triple_bird';
    } elseif ($userScore - $parScore == -2) {
        $result = 'Eagle';
        $icon = 'double_bird';
    } elseif ($userScore - $parScore == -1) {
        $result = 'Birdie';
        $icon = 'single_bird';
    } elseif ($userScore - $parScore == 0) {
        $result = 'Par';
        $icon = 'score_border';
    } elseif ($userScore - $parScore == 1) {
        $result = 'Bogey';
        $icon = 'single_arrow';
    } elseif ($userScore - $parScore == 2) {
        $result = 'Double Bogey';
        $icon = 'double_arrow';
    } elseif ($userScore - $parScore == 3) {
        $result = 'Triple Bogey';
        $icon = 'triple_arrow';
    } else {
        $result = 'Worse than Triple Bogey';
        $icon = 'no_icon';
    }

    return array('result' => $result, 'icon' => $icon);
}


$preferences_db = $supabase->initializeDatabase('gimme_user_preferences','id');
$query = [
    'select' => "*",
    'from' => "gimme_user_preferences",
    'where' => [
        'uid' => 'eq.'.$uid
    ]
];


try{
    $preferences = $preferences_db->createCustomQuery($query)->getResult();
    if(count($preferences)){
        $distance = $preferences[0]->distance;
        $popups = true;


        $user_db = $supabase->initializeDatabase('gimme_users', 'id');

        try{
            $user = $user_db->findBy('id', $uid)->getResult(); //Searches for products that have the value "PlayStation 5" in the "productname" column
            if(count($user)>0){
                $user_email = $user[0]->email;

                // create cURL request
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://duendedisplay.co.za/gimme/api/v1/auth/login.php',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array(
                        'email' => $user_email,
                        'password' => 'sd',
                        'internal_login' => true
                    ),
                ));

                $response = curl_exec($curl);
                // print_r($response);
                // extract token from response
                $token = json_decode($response, true)['token'];
                $response_data = json_decode($response, true)['response'];
        
                curl_close($curl);


                $courses_db = $supabase->initializeDatabase('gimme_courses','id');
                $query = [
                    'select' => "*",
                    'from' => "gimme_courses",
                    'where' => [
                        'id' => 'eq.'.$course_id
                    ]
                ];

            

                try{
                    $hole_number = 'hole_'.$hole;
                    $course = $courses_db->createCustomQuery($query)->getResult();

                    $course_data = $course[0]->course_data->course_data->hole_data->$hole_number;

                    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
                    // $status = 'complete';
                    
                    try {
                        $query = [
                            'select' => '*',
                            'from'   => 'gimme_scores',
                            'where' => 
                            [
                                'user_id' => 'eq.'.$uid,
                                // 'status' => 'eq.'.$status,
                                'match_type' => 'eq.'.$gamemode,
                                'match_type_id' => 'eq.'.$match_type_id
                            ]
                        ];
                        $plans = $plans_db->createCustomQuery($query)->getResult();

                        if (empty($plans)) {
                            http_response_code(400);
                            echo json_encode(array("msg" => "No data found"));
                            exit;
                        }
                    
                        $quick_putts_hole = $plans[0]->score_details->holes[$hole-1]->quick_putss;
                        $quick_shots_hole = $plans[0]->score_details->holes[$hole-1]->quick_shots;
                        $sum_hole = $quick_putts_hole + $quick_shots_hole;

                        // print_r($plans[0]->game_data->holes[$hole-1]->shots);
                        $unique_clubs = [];
                        $holeGreenValue = [];
                        foreach ($plans[0]->score_details->holes[$hole-1]->shots as $shot) {
                            if (!in_array($shot->club, $unique_clubs)) {
                                $unique_clubs[] = $shot->club;
                            }
                            $holeGreenValue[] = $shot->club;
                        }
                        $clubs_used = count($unique_clubs);
                        $holeGreenValue_counter = count($holeGreenValue);

                        $db = $supabase->initializeDatabase('gimme_scores', 'id');

                        $hole_gps_data = [
                            "hole_gps" => [
                                "user_id" => $uid,
                                "mode" => "match",
                                "popups" => $popups,
                                "distance" =>$distance,
                                "course_id" => $course_id,
                                "hole_number" => $hole
                            ]
                        ];
                        
                        try{
                            $data = $db->update($plans[0]->id, $hole_gps_data); 
                            if(count($data)>0){
                                http_response_code(200);
                                // print_r($plans);
                                // echo $plans[0]->score_details->holes[$hole-1]->status;
                                if($plans[0]->score_details->holes[$hole-1]->status == "complete"){
                                    $resultData = calculateResult($holeGreenValue_counter, $course_data->par);
                                    echo json_encode(array("temp_token"=>$token, "username"=>$response_data[0]['name'], "course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => $resultData['result'],
                                    'icon' => $resultData['icon']));
                                }else{

                                    echo json_encode(array("temp_token"=>$token, "username"=>$response_data[0]['name'], "course_data" => $course_data, "selected_hole_putts_shots" => $sum_hole, 'clubs_used' => $clubs_used, "quick_shots" => $quick_shots_hole, "quick_putts" => $quick_putts_hole, "hole_green_value" => $holeGreenValue_counter, 'result' => '',
                                    'icon' => ''));

                                }
                            }
                        }
                        catch(Exception $e){
                            echo $e->getMessage();
                        }
                    } catch (Exception $e) {
                        http_response_code(500);
                        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
                    }

                } catch(Exception $e){
                    http_response_code(400);
                    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
                }    


            }else{
                http_response_code(400);
                echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
            }
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
        }

  


    }else{
        http_response_code(400);
        echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
    }
    

} catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}  


  
?>