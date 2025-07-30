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

// Validate the required fields
if(empty($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}


// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$gimme_scores_db = $supabase->initializeDatabase('gimme_scores','id');


$match_type = $_POST['gamemode'];
if($match_type=='match'){
    $status = 'pending';
}else{
    $status = 'active';
    
}

try{

    $query = [
        'select' => "*",
        'from' => "gimme_scores",
        'where' => [
            'user_id' => 'eq.'.$uid,
            'match_type' => 'eq.'.$match_type,
            'status' => 'eq.'.$status
        ]
    ];

    $plans = $gimme_scores_db->createCustomQuery($query)->getResult();

    if(empty($plans)){
        http_response_code(400);
        echo json_encode(array("msg" => "No data found"));
        exit;
    }

    $id = $plans[0]->id;

    $db = $supabase->initializeDatabase('gimme_scores', 'id');

    $complete_round = [
        'status' => 'complete',
    ];

    try{
        $data = $db->update($id, $complete_round);

        if(count($data)>0){
            http_response_code(200);
            echo json_encode(array("plans" => $plans));          
        }
    }
    catch(Exception $e){
        http_response_code(500);
        echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
    }

} catch(Exception $e){
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}






?>