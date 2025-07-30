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

    // Validate the required fields
    if(!isset($_POST['token'])){
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid request - ".$_POST['token']));
        exit;
    }

    require '../auth/token.php';

    $supabase = new PHPSupabase\Service(
        $config['supabaseKey'],
        $config['supabaseUrl']
    );

    $db = $supabase->initializeDatabase('gimme_events', 'id');

    if(!isset($_POST['id'])){
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid request, id parameter missing"));
    }
    
    $updateData = array();
    
    // Validate and set event_code
    if(isset($_POST['event_code'])){
        $updateData['event_code'] = $_POST['event_code'];
    }
    
    // Validate and set name
    if(isset($_POST['event_name'])){
        $updateData['event_name'] = $_POST['event_name'];
    }

    // Validate and set event_description
    if(isset($_POST['event_description'])){
        $updateData['event_description'] = $_POST['event_description'];
    }
    
    // Validate and set event_date_time
    if(isset($_POST['event_date_time'])){
        $updateData['event_date_time'] = $_POST['event_date_time'];
    }
    
    // Validate and set event_participants
    if(isset($_POST['event_participants'])){
        $updateData['event_participants'] = $_POST['event_participants'];
    }
    
    // Validate and set event_status
    if(isset($_POST['event_status'])){
        $updateData['event_status'] = $_POST['event_status'];
    }
    
    // Validate and set event_course
    if(isset($_POST['event_course'])){
        $updateData['event_course'] = $_POST['event_course'];
    }
    
    // Validate and set event_holes
    if(isset($_POST['event_holes'])){
        $updateData['event_holes'] = $_POST['event_holes'];
    }
    
    // Validate and set event_default_tees
    if(isset($_POST['event_default_tees'])){
        $updateData['event_default_tees'] = $_POST['event_default_tees'];
    }
    
    // Validate and set event_registration_date
    if(isset($_POST['event_registration_date'])){
        $updateData['event_registration_date'] = $_POST['event_registration_date'];
    }
    
    // Validate and set event_notification
    if(isset($_POST['event_notification'])){
        $updateData['event_notification'] = $_POST['event_notification'];
    }
    
    // Validate and set event_type
    if(isset($_POST['event_type'])){
        $updateData['event_type'] = $_POST['event_type'];
    }
    
    // Validate and set event_team_format
    if(isset($_POST['event_team_format'])){
        $updateData['event_team_format'] = $_POST['event_team_format'];
    }
    
    // Validate and set event_max_players
    if(isset($_POST['event_max_players'])){
        $updateData['event_max_players'] = $_POST['event_max_players'];
    }
    
    // Validate and set event_auto_assign
    if(isset($_POST['event_auto_assign'])){
        $updateData['event_auto_assign'] = $_POST['event_auto_assign'];
    }
    
    // Validate and set event_scoring
    if(isset($_POST['event_scoring'])){
        $updateData['event_scoring'] = $_POST['event_scoring'];
    }
    
    // Validate and set event_handicap
    if(isset($_POST['event_handicap'])){
        $updateData['event_handicap'] = $_POST['event_handicap'];
    }
    
    // Validate and set event_badge
    if(isset($_POST['event_badge'])){
        $updateData['event_badge'] = $_POST['event_badge'];
    }
    
    // Validate and set event_banner
    if(isset($_POST['event_banner'])){
        $updateData['event_banner'] = $_POST['event_banner'];
    }
    
    // Validate and set event_colour
    if(isset($_POST['event_colour'])){
        $updateData['event_colour'] = $_POST['event_colour'];
    }
    
    // Validate and set course_data
    if(isset($_POST['course_data'])){
        $updateData['course_data'] = $_POST['course_data'];
    }
    
    // Validate and set match_data
    if(isset($_POST['match_data'])){
        $updateData['match_data'] = $_POST['match_data'];
    }

    try{
        $res = $db->update($_POST['id'], $updateData);
    }catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("status"=>400,"msg" => "failed", "error" => $e));
        exit;
    }

    http_response_code(200);
    echo json_encode(array("status"=>200,"msg" => "success", "event_data" => $res));

?>
