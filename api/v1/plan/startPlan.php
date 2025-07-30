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

// Validate the required fields
if(!isset($_POST['course_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Course ID are required"));
    exit;
}

// Insert data into the gimme_plan_game table
try{
    $supabase = new PHPSupabase\Service(
        $config['supabaseKey'],
        $config['supabaseUrl']
    );
    $plans_db = $supabase->initializeDatabase('gimme_plan_game','id');
 
    $course_id = $_POST['course_id'];

    $query = [
        'select' => "*",
        'from' => "gimme_plan_game",
        'where' => [
            'user_id' => 'eq.'.$uid,
            'course_id' => 'eq.'.$course_id,
            'status' => 'eq.pending',
        ]
    ];


    $plans = $plans_db->createCustomQuery($query)->getResult();

    if(empty($plans)){

        $holes = [];
        for($i = 1; $i <= 18; $i++){
            array_push(
                $holes,
                [
                    "shots"=> [],
                    "status"=> "incomplete",      
                    "quick_sc"=> false,
                    "quick_shots"=> 0,
                    "quick_putss"=> 0,
                    "condition"=> [
                        "green"=> "none",
                        "teebox"=> "none"
                    ],
                    "hole_number"=> $i,
                    "hole_status"=> "none",
                    "total_putts"=> 0,
                    "total_shots"=> 0
                ],
            );
        }

        $data = [
            'user_id' => $uid,
            'course_id' => $course_id,
            'game_data'=>[
                "holes"=> $holes
            ],
            'status' => 'pending'
        ];

        $inserted = $plans_db->insert($data);
        if(!$inserted){
            http_response_code(400);
            echo json_encode(array("msg" => "Failed to insert data into gimme_plan_game"));
            exit;
        }
        
        http_response_code(200);
        echo json_encode(array("msg" => "Data inserted successfully","data"=>$inserted));
    }else{
        http_response_code(400);
        echo json_encode(array("msg" => "Plan already exists"));
    }
} catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => "Server error: ".$e->getMessage()));
}

?>