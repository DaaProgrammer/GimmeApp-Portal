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
$user_ids = $_POST['user_ids'];

// Validate the required fields
if(empty($user_ids)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

// Initialize the users database
$users_db = $supabase->initializeDatabase('gimme_users','id');
$results = array();

    foreach ($user_ids as $index => $uid) {
        try{
            // Get event
            $query = [
                'select' => 'name,surname',
                'from' => 'gimme_users',
                'where' => ['id'=>'eq.'.$uid]
            ];

            // Get event
            $userObject = $users_db->createCustomQuery($query)->getResult();

            // Handle if no event is found
            // if(empty($userObject)){
            //     http_response_code(400);
            //     echo json_encode(array("msg" => "User not found"));
            // }

            $results['user_'.$uid] = $userObject[0];
        }
        catch(Exception $e){
            http_response_code(400);
            echo json_encode(array("msg" => $e->getMessage()));
            exit;
        }
    }

    http_response_code(200);
    echo json_encode(array("msg" => "Success",'data' => $results));
    exit;

?>