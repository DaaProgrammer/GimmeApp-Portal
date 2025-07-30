<?php 
// display errors
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
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


// Check for active Match game
$matchData = checkActiveGame($supabase, 'awaiting_match', 'match', $uid);
if ($matchData) {
    if(empty($matchData)){
        http_response_code(400);
    }
    $match_type_id = $matchData->match_type_id;

    $query = $supabase->initializeQueryBuilder();
    $gimme_match = $query->select('*')
        ->from('gimme_match')
        ->where('id', 'eq.'.$match_type_id)
        ->order('id.desc')
        ->execute()
        ->getResult();


    $course_id = $gimme_match[0]->course_id;
    $teamcheck = $gimme_match[0]->type;

    $query2 = $supabase->initializeQueryBuilder();
    $gimme_courses = $query2->select('*')
        ->from('gimme_courses')
        ->where('id', 'eq.'.$course_id)
        ->order('id.desc')
        ->execute()
        ->getResult();

    $course_name = $gimme_courses[0]->course_name;
    $course_address = $gimme_courses[0]->course_address;

    if($teamcheck=='individual'){
        http_response_code(200);
        echo json_encode(array("title"=>"Round Settings", "msg" => "Continue setting up a Round", "gamemode" => "match", "course_id"=>$course_id, "course_name"=>$course_name, "course_address" => $course_address, "eventCode"=>'empty', "teamcheck"=>$teamcheck, "id" =>$match_type_id,'team_counter'=>0, 'team_name_one'=>'', 'team_name_two'=>'', 'team_name_three'=>'', 'team_name_four'=>''));
        exit;
    }else{
        $team_namnes = [];
        $match_settings = $gimme_match[0]->match_settings->teams;

        foreach ($match_settings as $team) {
            $team_names[] = $team->team_name;
        }

        http_response_code(200);
        echo json_encode(array("title"=>"Round Settings", "msg" => "Continue setting up a Round", "gamemode" => "match", "course_id"=>$course_id, "course_name"=>$course_name, "course_address" => $course_address, "eventCode"=>'empty', "teamcheck"=>$teamcheck, "id" =>$match_type_id,  'team_counter'=>count($team_names), 'team_name_one'=>$team_names[0], 'team_name_two'=>$team_names[1], 'team_name_three'=>$team_names[2], 'team_name_four'=>$team_names[3]));
        exit;
    }



}else{
    http_response_code(400);
}


// Check for active Match game
$matchData = checkActiveGame($supabase, 'pending', 'match', $uid);
if ($matchData) {

    if(empty($matchData)){
        http_response_code(400);
    }
    $match_type_id = $matchData->match_type_id;

    $query = $supabase->initializeQueryBuilder();
    $gimme_match = $query->select('*')
        ->from('gimme_match')
        ->where('id', 'eq.'.$match_type_id)
        ->order('id.desc')
        ->execute()
        ->getResult();


    $course_id = $gimme_match[0]->course_id;
    $match_code = $gimme_match[0]->match_code;
    $teamcheck = $gimme_match[0]->type;

    $query2 = $supabase->initializeQueryBuilder();
    $gimme_courses = $query2->select('*')
        ->from('gimme_courses')
        ->where('id', 'eq.'.$course_id)
        ->order('id.desc')
        ->execute()
        ->getResult();

    $course_name = $gimme_courses[0]->course_name;
    $course_address = $gimme_courses[0]->course_address;


    http_response_code(200);
    echo json_encode(array("title"=>"Active Round in Progress", "msg" => "You are currently playing a round. Please continue below.", "gamemode" => "match", "course_id"=>$course_id, "course_name"=>$course_name, "course_address" => $course_address, "eventCode"=>$match_code, "teamcheck"=>$teamcheck, "id" =>$match_type_id, 'team_counter'=>0, 'team_name_one'=>'', 'team_name_two'=>'', 'team_name_three'=>'', 'team_name_four'=>''));
}else{
    http_response_code(400);
}


// Check for active Event game
$eventData = checkActiveGame($supabase, 'active', 'event', $uid);
if ($eventData) {
    $match_type_id = $eventData->match_type_id;

    $query = $supabase->initializeQueryBuilder();
    $gimme_match = $query->select('*')
        ->from('gimme_events')
        ->where('id', 'eq.'.$match_type_id)
        ->order('id.desc')
        ->execute()
        ->getResult();


    $course_id = $gimme_match[0]->event_course;
    $event_code = $gimme_match[0]->event_code;

    $query2 = $supabase->initializeQueryBuilder();
    $gimme_courses = $query2->select('*')
        ->from('gimme_courses')
        ->where('id', 'eq.'.$course_id)
        ->order('id.desc')
        ->execute()
        ->getResult();

    $course_name = $gimme_courses[0]->course_name;
    $course_address = $gimme_courses[0]->course_address;


    http_response_code(200);
    echo json_encode(array("title"=>"Active Round in Progress", "msg" => "You are currently playing a round. Please continue below", "gamemode" => "event", "course_id"=>$course_id, "course_name"=>$course_name, "course_address" => $course_address, "eventCode"=>$event_code, "id" =>$match_type_id,  "teamcheck"=>'', 'team_counter'=>0, 'team_name_one'=>'', 'team_name_two'=>'', 'team_name_three'=>'', 'team_name_four'=>''));
}else{
    http_response_code(400);
}

function checkActiveGame($supabase, $status, $matchType, $uid) {
    $query = $supabase->initializeQueryBuilder();
    $score = $query->select('*')
        ->from('gimme_scores')
        ->where('user_id', 'eq.'.$uid)
        ->where('status', 'eq.'.$status)
        ->where('match_type', 'eq.'.$matchType)
        ->order('id.desc')
        ->execute()
        ->getResult();

    if (count($score) > 0) {
        return $score[0];
    }

    return null;
}

?>
