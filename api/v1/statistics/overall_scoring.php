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
if(empty($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$gamemode = $_POST['gamemode'];
// $par = $_POST['par'];
// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);



$courses_info = [];
// $unique_pars = [];
// $gimme_courses_db = $supabase->initializeDatabase('gimme_courses', 'id');
// try {
//     $course_query = [
//         'select' => "course_data",
//         'from' => 'gimme_courses',
//     ];

//     $courses = $gimme_courses_db->createCustomQuery($course_query)->getResult();

//     http_response_code(200);
//     foreach ($courses as $course) {
//         foreach ($course->course_data->course_data->hole_data as $hole => $data) {
//             $par_value = $data->par;
//             if (!array_key_exists($par_value, $unique_pars)) {
//                 $unique_pars[$par_value] = [
//                     'par' => $par_value,
//                     'par_label' => 'Par ' . $par_value . ' Scoring'
//                 ];
//             }
//         }
//     }
//     ksort($unique_pars); // Sort the pars by key for better readability
//     echo json_encode(array("unique_pars" => array_values($unique_pars))); // Use array_values to reset the keys for JSON output
// } catch (Exception $e) {
//     http_response_code(500);
//     echo json_encode(array("msg" => "Server error while fetching unique pars: " . $e->getMessage()));
//     exit;
// }
$plans_db = $supabase->initializeDatabase('gimme_scores','id');
try {
    $query = [
        'select' => "*",
        'from' => "gimme_scores",
        'where' => [
            'user_id' => 'eq.'.$uid,
            'match_type' => 'eq.'.$gamemode,
            'order' => 'id.asc'
        ]
    ];

    $plans = $plans_db->createCustomQuery($query)->getResult();
    // print_r($plans[0]->game_data->holes[$hole-1]->shots);
    if (empty($plans)) {
        http_response_code(400);
        echo json_encode(array("msg" => "No data found"));
        exit;
    }

    http_response_code(200);
    $parname_counts = [];
    foreach ($plans as $plan) {
        foreach ($plan->score_details->holes as $hole) {
      
        
            if (isset($hole->parname)) {
                if (!isset($parname_counts[$hole->parname])) {
                    $parname_counts[$hole->parname] = ['parname' => $hole->parname, 'counter' => 1];
                } else {
                    $parname_counts[$hole->parname]['counter']++;
                }
            }
        }
    }
    // Convert associative array to indexed array of objects
    $parname_counts_array = array_values($parname_counts);
    echo json_encode(array("parname_counts" => $parname_counts_array));

    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
}    
// if($gamemode=='plan'){
//     $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
//     try {
//         $query = [
//             'select' => "game_data",
//             'from' => "gimme_plan_game",
//             'where' => [
//                 'user_id' => 'eq.'.$uid,
//                 'order' => 'id.asc'
//             ]
//         ];
    
//         $plans = $plans_db->createCustomQuery($query)->getResult();
//         if (empty($plans)) {
//             http_response_code(400);
//             echo json_encode(array("msg" => "No data found"));
//             exit;
//         }
//         http_response_code(200);
    
//         $parname_counts = [];
//         foreach ($plans as $plan) {
//             foreach ($plan->game_data->holes as $hole) {
//                 if (isset($hole->parname)) {
//                     if (!isset($parname_counts[$hole->parname])) {
//                         $parname_counts[$hole->parname] = ['parname' => $hole->parname, 'counter' => 1];
//                     } else {
//                         $parname_counts[$hole->parname]['counter']++;
//                     }
//                 }
//             }
//         }
//         // Convert associative array to indexed array of objects
//         $parname_counts_array = array_values($parname_counts);
//         echo json_encode(array("parname_counts" => $parname_counts_array));
//     } catch (Exception $e) {
//         http_response_code(500);
//         echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
//     }
// }else{

// }



?>                                                                                                                                                                                                                          