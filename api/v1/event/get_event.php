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


// Initialize the users database
$course_db = $supabase->initializeDatabase('gimme_courses','id');

$gimme_scores_db = $supabase->initializeQueryBuilder();

$all_events = [];
try {
    $event_rounds = $gimme_scores_db->select('*')
                ->from('gimme_scores')
                ->where('user_id', 'eq.'.$uid)
                ->where('match_type', 'eq.event')
                ->order('id.desc')
                ->execute()
                ->getResult();

    foreach ($event_rounds as $round) {
        $events_db = $supabase->initializeQueryBuilder();
        $match_type_id = $round->match_type_id;
        $events = $events_db->select('*')
            ->from('gimme_events')
            ->where('id', 'eq.'.$match_type_id) // Fixed the concatenation operator
            ->execute()
            ->getResult();

        if (!empty($events)) {
            $modified_events = [];
            foreach ($events as $event) {
                $event->event_date_time = date("d F Y", strtotime($event->event_date_time));
                
                // Retrieve course data based on event_course value
                $course_data = $course_db->findBy("id", $event->event_course)->getResult();
                $event->gimme_courses = $course_data[0]; // Append course data to the event object

                $modified_events[] = $event;

                $query = $supabase->initializeQueryBuilder();
                $event_round_status = $query->select('*')
                    ->from('gimme_scores')
                    ->where('user_id', 'eq.'.$uid)
                    ->where('match_type', 'eq.event')
                    ->where('match_type_id', 'eq.'.$match_type_id)
                    ->order('id.desc')
                    ->execute()
                    ->getResult();

                if (!empty($event_round_status)) {
                    $event->user_event_status = $event_round_status[0]->status;
                } else {
                    $event->user_event_status = 'unknown';
                }
            }

            $all_events[] = $modified_events[0]; // Store the first element of the $modified_events array
        }
    }

    
    http_response_code(200);
    echo json_encode(array("msg" => "Success",'current_uid'=>$uid,'data' => $all_events));
    exit;

}catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}



?>