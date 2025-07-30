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
error_reporting(0); // Turn off all error reporting

// Validate the required fields
if(empty($_POST['token']) || empty($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}


// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);



$course_id = $_POST['course_id'];
$gamemode = $_POST['gamemode'];
$internal_login = isset($_POST['internal_login']) ? $_POST['internal_login'] : false;
$roundID = $_POST['roundID'];
$all_pars = [];
// Send a cURL request to fetch score card data
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://duendedisplay.co.za/gimme/api/v1/courses/get_score_card_data.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(array('token' => $_POST['token'], 'course_id' => $_POST['course_id'])),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
$response_data = json_decode($response, true);

if(isset($response_data['course_data']['first_pars'])){
    foreach($response_data['course_data']['first_pars'] as $par){
        $all_pars[] = $par['par'];
    }
}

if(isset($response_data['course_data']['second_pars'])){
    foreach($response_data['course_data']['second_pars'] as $par){
        $all_pars[] = $par['par'];
    }
}
// print_r($all_pars);

curl_close($curl);



if($gamemode === 'plan'){
    $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');

    try {
        $query = [
            'select' => "*",
            'from' => "gimme_plan_game",
            'where' => [
                'user_id' => 'eq.'.$uid,
                'course_id' => 'eq.'.$course_id,
                'status' => 'eq.pending'
            ]
        ];

        $plans = $plans_db->createCustomQuery($query)->getResult();

        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        $first_8_holes = [];
        $remaining_holes = [];
        $first_green_total = 0;
        $second_green_total = 0;
        $count = 0; 
        foreach ($plans[0]->game_data->holes as $hole) {
            $green_score = count($hole->shots);

            if($hole->quick_sc==true){
                $green_score = $hole->quick_putss + $hole->quick_shots;
                if (isset($hole->complete_type)) {
                    if ($hole->complete_type == '-') {
                        $green_score = "-";
                    } else if ($hole->complete_type == 'pu') {
                        $green_score = "pu";
                    }
                }
            }

            $hole_data = array("green_score" => strval($green_score));

            if ($count <= 8) {
                if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                    $first_green_total += $green_score;
                } else {
                    $hole_data["green_score"] = $hole->complete_type;
                    if($hole->complete_type == 'pu'){
                        $first_green_total +=$all_pars[$count]+2;
                    }
                }
                $first_8_holes[] = $hole_data;
            } else {
                if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                    $second_green_total += $green_score;
                } else {
                    $hole_data["green_score"] = $hole->complete_type;
                    if($hole->complete_type == 'pu'){
                        $second_green_total +=$all_pars[$count]+2;
                    }
                }
                $remaining_holes[] = $hole_data;
            }
            $count++;
        }

        $output = array(
            "first_pars" => $first_8_holes,
            "second_pars" => $remaining_holes
        );

        // Add total green scores to each array
        $output["first_pars"][] = array("green_score" => strval($first_green_total));
        $output["second_pars"][] = array("green_score" => strval($second_green_total));
        $output["second_pars"][] = array("green_score" => strval($first_green_total + $second_green_total));
        // $output["all_total_pars"][] = $first_green_total + $second_green_total;

        http_response_code(200);
        echo json_encode(array("plans" => $output));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }

}else if($gamemode==='match'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'pending';
    if($internal_login==true){
        $status = '';

    }
    
    try {
        $query = $supabase->initializeQueryBuilder();


        if(isset($_POST['roundID']) && !empty($_POST['roundID'])){

            $plans = $query->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('match_type_id', 'eq.'.$roundID)
                ->where('match_type', 'eq.'.$gamemode)
                ->order('id.desc')
                ->execute()
                ->getResult();


    
        }else{


            if($internal_login==true){
                $plans = $query->select('*')
                    ->from('gimme_scores')
                    ->where('user_id', 'eq.'.$uid)
                    ->where('match_type', 'eq.'.$gamemode)
                    ->order('id.desc')
                    ->execute()
                    ->getResult();
            }else{
                $plans = $query->select('*')
                    ->from('gimme_scores')
                    ->where('user_id', 'eq.'.$uid)
                    ->where('status', 'eq.'.$status)
                    ->where('match_type', 'eq.'.$gamemode)
                    ->order('id.desc')
                    ->execute()
                    ->getResult();                
            }

        }
        

        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        $first_8_holes = [];
        $remaining_holes = [];
        $first_green_total = 0;
        $second_green_total = 0;
        $count = 0; 


        
        foreach ($plans[0]->score_details->holes as $hole) {
            $green_score = count($hole->shots);
            // if($hole->status=='complete' && $hole->quick_sc=='true'){
            if($hole->quick_sc==true){
                $green_score = $hole->quick_putss + $hole->quick_shots;
                if (isset($hole->complete_type)) {
                    if ($hole->complete_type == '-') {
                        $green_score = "-";
                    } else if ($hole->complete_type == 'pu') {
                        $green_score = "pu";
                    }
                }

            }

            $hole_data = array("green_score" => strval($green_score));

            if ($count <= 8) {
                if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                    $first_green_total += $green_score;
                } else {
                    $hole_data["green_score"] = $hole->complete_type;
                    if($hole->complete_type == 'pu'){
                        $first_green_total +=$all_pars[$count]+2;
                    }

                }
                $first_8_holes[] = $hole_data;
            } else {
                if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                    $second_green_total += $green_score;
                } else {
                    $hole_data["green_score"] = $hole->complete_type;
                    if($hole->complete_type == 'pu'){
                        $second_green_total +=$all_pars[$count]+2;
                    }

                }
                $remaining_holes[] = $hole_data;
            }
            $count++;
        }

        if(isset($_POST['roundID']) && !empty($_POST['roundID'])){
            $first_8_holes = [];
            $remaining_holes = [];
            $first_green_total = 0;
            $second_green_total = 0;
            $count = 0; 
            foreach ($plans[0]->score_details->holes as $hole) {
                if($hole->status=='incomplete'){
                    $green_score = 0;
                    $hole_data = array("green_score" => strval($green_score));
        
                    if ($count <= 8) {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $first_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;

                            if($hole->complete_type == 'pu'){
                                $first_green_total +=$all_pars[$count]+2;
                            }

                        }
                        $first_8_holes[] = $hole_data;
                    } else {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $second_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                            if($hole->complete_type == 'pu'){
                                $second_green_total +=$all_pars[$count]+2;
                            }

                        }
                        $remaining_holes[] = $hole_data;
                    }
                }else{
                    $green_score = count($hole->shots);
                    if($hole->quick_sc==true){
                        $green_score = $hole->quick_putss + $hole->quick_shots;
                        if (isset($hole->complete_type)) {
                            if ($hole->complete_type == '-') {
                                $green_score = "-";
                            } else if ($hole->complete_type == 'pu') {
                                $green_score = "pu";
                            }
                        }

                    }
        
                    $hole_data = array("green_score" => strval($green_score));
        
                    if ($count <= 8) {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $first_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                            if($hole->complete_type == 'pu'){
                                $first_green_total +=$all_pars[$count]+2;
                            }

                        }
                        $first_8_holes[] = $hole_data;
                    } else {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $second_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                            if($hole->complete_type == 'pu'){
                                $second_green_total +=$all_pars[$count]+2;
                            }

                        }
                        $remaining_holes[] = $hole_data;
                    }

                }
                $count++;
            }            
        }

        $output = array(
            "first_pars" => $first_8_holes,
            "second_pars" => $remaining_holes
        );



        // Add total green scores to each array
        $output["first_pars"][] = array("green_score" => strval($first_green_total));
        $output["second_pars"][] = array("green_score" => strval($second_green_total));
        $output["second_pars"][] = array("green_score" => strval($first_green_total + $second_green_total));
        // $output["all_total_pars"][] = $first_green_total + $second_green_total;

        http_response_code(200);
        echo json_encode(array("plans" => $output));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }

}else if($gamemode==='event'){
    $plans_db = $supabase->initializeDatabase('gimme_scores','id');
    $status = 'pending';
    
    try {
        $query = $supabase->initializeQueryBuilder();


        if(isset($_POST['roundID']) && !empty($_POST['roundID'])){

            $plans = $query->select('*')
                        ->from('gimme_scores')
                        ->where('user_id', 'eq.'.$uid)
                        ->where('match_type_id', 'eq.'.$roundID)
                        ->order('id.desc')
                        ->execute()
                        ->getResult();
    
        }else{
            $plans = $query->select('*')
                        ->from('gimme_scores')
                        ->where('user_id', 'eq.'.$uid)
                        ->where('status', 'neq.'.$status)
                        ->where('match_type', 'eq.'.$gamemode)
                        ->order('id.desc')
                        ->execute()
                        ->getResult();
        }

        
        if (empty($plans)) {
            http_response_code(400);
            echo json_encode(array("msg" => "No data found"));
            exit;
        }

        $first_8_holes = [];
        $remaining_holes = [];
        $first_green_total = 0;
        $second_green_total = 0;
        $count = 0; 
        foreach ($plans[0]->score_details->holes as $hole) {
            $green_score = count($hole->shots);

            if($hole->quick_sc==true){
                $green_score = $hole->quick_putss + $hole->quick_shots;
                if(isset($hole->total_shots) && $hole->total_shots!=0){
                    $green_score = $hole->total_shots;
                }else{
                    if (isset($hole->complete_type)) {
                        if ($hole->complete_type == '-') {
                            $green_score = "-";
                        } else if ($hole->complete_type == 'pu') {
                            $green_score = "pu";
                        }
                    }
                }

            }else{
                if(isset($hole->total_shots) && $hole->total_shots!=0){
                    $green_score = $hole->total_shots;
                }
            }

            $hole_data = array("green_score" => strval($green_score));

            if ($count <= 8) {
                if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                    $first_green_total += $green_score;
                } else {
                    $hole_data["green_score"] = $hole->complete_type;
                    if($hole->complete_type == 'pu'){
                        $first_green_total +=$all_pars[$count]+2;
                    }

                }
                $first_8_holes[] = $hole_data;
            } else {
                if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                    $second_green_total += $green_score;
                } else {
                    $hole_data["green_score"] = $hole->complete_type;
                    if($hole->complete_type == 'pu'){
                        $second_green_total +=$all_pars[$count]+2;
                    }

                }
                $remaining_holes[] = $hole_data;
            }
            $count++;
        }
     

        if(isset($_POST['roundID']) && !empty($_POST['roundID'])){
            $first_8_holes = [];
            $remaining_holes = [];
            $first_green_total = 0;
            $second_green_total = 0;
            $count = 0; 
            foreach ($plans[0]->score_details->holes as $hole) {
                if($hole->status=='incomplete'){
                    $green_score = 0;
                    $hole_data = array("green_score" => strval($green_score));
        
                    if ($count <= 8) {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $first_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                        }
                        $first_8_holes[] = $hole_data;
                    } else {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $second_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                        }
                        $remaining_holes[] = $hole_data;
                    }
                }else{
                    $green_score = count($hole->shots);
                    // if($hole->status=='complete' && $hole->quick_sc=='true'){
                    if($hole->quick_sc==true){
                        $green_score = $hole->quick_putss + $hole->quick_shots;
                        if(isset($hole->total_shots) && $hole->total_shots!=0){
                            $green_score = $hole->total_shots;
                        }
                    }else{
                        if(isset($hole->total_shots) && $hole->total_shots!=0){
                            $green_score = $hole->total_shots;
                        }
                    }
        
                    $hole_data = array("green_score" => strval($green_score));
        
                    if ($count <= 8) {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $first_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                            if($hole->complete_type == 'pu'){
                                $first_green_total +=$all_pars[$count]+2;
                            }

                        }
                        $first_8_holes[] = $hole_data;
                    } else {
                        if($hole->complete_type != 'pu' && $hole->complete_type != '-') {
                            $second_green_total += $green_score;
                        } else {
                            $hole_data["green_score"] = $hole->complete_type;
                            if($hole->complete_type == 'pu'){
                                $second_green_total +=$all_pars[$count]+2;
                            }

                        }
                        $remaining_holes[] = $hole_data;
                    }

                }
                $count++;
            }            
        }

        $output = array(
            "first_pars" => $first_8_holes,
            "second_pars" => $remaining_holes
        );

        // Add total green scores to each array
        $output["first_pars"][] = array("green_score" => strval($first_green_total));
        $output["second_pars"][] = array("green_score" => strval($second_green_total));
        $output["second_pars"][] = array("green_score" => strval($first_green_total + $second_green_total));
        // $output["all_total_pars"][] = $first_green_total + $second_green_total;

        http_response_code(200);
        echo json_encode(array("plans" => $output));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
    }

}


?>