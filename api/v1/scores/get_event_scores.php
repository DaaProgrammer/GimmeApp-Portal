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

// Validate the required fields
if(!isset($_POST['event_id']) || empty($_POST['event_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

$event_id = $_POST['event_id'];

// Initialize the users database
$scores_db = $supabase->initializeDatabase('gimme_scores','id');
$query = [
    'select' => '*',
    'from' => 'gimme_scores',
    // 'join' =>[
    //     [
    //         'table' => 'gimme_users',
    //         'table_key' => 'id'
    //     ]
    // ],
    'where' => [
        'match_type' => 'eq.event',
        'match_type_id' => 'eq.'.$event_id
    ],
];

try{
    // Get event
    $scoreObject = $scores_db->createCustomQuery($query)->getResult();

    // Handle if no event is found
    if(empty($scoreObject)){
        http_response_code(400);
        echo json_encode(array("msg" => "Event not found"));
        exit; 
    }

    $users_db = $supabase->initializeDatabase('gimme_users', 'id');
    foreach($scoreObject as $index => $score) {
        $userQuery = [
            'select' => '*',
            'from' => 'gimme_users',
            'where' => [
                'id' => 'eq.'.$score->user_id
            ],
        ];
        try {
            $userObject = $users_db->createCustomQuery($userQuery)->getResult();
            if(!empty($userObject)) {
                $scoreObject[$index]->user = $userObject[0];
            } else {
                $scoreObject[$index]->user = null;
            }
        } catch(Exception $e) {
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to fetch user data: ".$e->getMessage()));
            exit;
        }
    }

    http_response_code(200);
    echo json_encode(array("msg" => "Success",'data' => $scoreObject));
    exit;
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

?>