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
// $event_id = $_POST['event_id'];

// // Validate the required fields
// if(empty($event_id)){
//     http_response_code(400);
//     echo json_encode(array("msg" => "Invalid request"));
//     exit;
// }

// Initialize the users database
// $events_db = $supabase->initializeDatabase('gimme_events','id');
$course_db = $supabase->initializeDatabase('gimme_courses','id');
// $gimme_scores_db = $supabase->initializeDatabase('gimme_scores','id');


$gimme_scores_db = $supabase->initializeQueryBuilder();
$events_db = $supabase->initializeQueryBuilder();


try {
    $event_rounds = $gimme_scores_db->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('match_type', 'eq.event')
                ->execute()
                ->getResult();

    // print_r($event_rounds);
    
                $responseData = [];
                $count = 0;
                foreach ($event_rounds as $round) {
                    echo $count++;
                  
                    $match_type_id = $round->match_type_id;
                    $events = $events_db->select('*')
                        ->from('gimme_events')
                        ->where('id', 'eq.'. $match_type_id) // Fixed the concatenation operator
                        ->execute()
                        ->getResult();
                        print_r($events);
                    if (!empty($events)) {

                        $eventObject = $events[0];
                        $courseObject = $course_db->findBy("id", $eventObject->event_course)->getResult();

                        $responseData[] = $eventObject;
                        $responseData['event_date_time'] = date("d F Y", strtotime($eventObject->event_date_time)); // Updated to use eventObject instead of responseData
                        $responseData['gimme_courses'] = $courseObject[0]; // Updated to use square brackets for array assignment

                        $query = $supabase->initializeQueryBuilder();

                        try {
                            $event_round_status = $query->select('*')
                                ->from('gimme_scores')
                                ->where('user_id', 'eq.'. $uid) // Fixed the concatenation operator
                                ->where('status', 'eq.'. 'complete') // Fixed the comparison operator
                                ->where('match_type', 'eq.'. 'event') // Fixed the comparison operator
                                ->order('id', 'desc') // Fixed the order method
                                ->execute()
                                ->getResult();

                            // if (count($event_round_status) > 0) {
                            //     http_response_code(200);
                            //     $responseData->user_event_status = $event_round_status[0]->status;
                            //     echo json_encode(array("msg" => "Success", 'current_uid' => $uid, 'data' => $responseData));
                            //     exit;
                            // }

                            // http_response_code(200);
                            // $responseData->user_event_status = 'active';
                            // echo json_encode(array("msg" => "Success", 'current_uid' => $uid, 'data' => $responseData));
                            // exit;

                        } catch (Exception $e) {
                            http_response_code(400);
                            echo json_encode(array("msg" => $e->getMessage()));
                            exit;
                        }
                    } else {
                        http_response_code(400);
                        echo json_encode(array("msg" => "Event not found"));
                        exit;
                    }
                }
                echo $count;
}catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}



?>