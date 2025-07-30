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

$db = $supabase->initializeDatabase('gimme_events', 'id');

$query = [
    'select' => '*',
    'from'   => 'gimme_events',
    'join'  => [
       [
        'table' => 'gimme_courses',
        'tablekey' => 'id'
       ]
    ]
];

try{
    $users = $db->createCustomQuery($query)->getResult();
    echo json_encode(array("msg" => "success", "event_data" => $users));
}
catch(Exception $e){
    echo $e->getMessage();
}   

?>