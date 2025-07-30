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

$search = $_POST['search'];

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$articles_db = $supabase->initializeDatabase('gimme_articles','id');
try {
    $query = [
        'select' => "*",
        'from' => "gimme_articles",
        'order' => "id.desc"
    ];

    $articles = $articles_db->createCustomQuery($query)->getResult();
    if (empty($articles)) {
        http_response_code(400);
        echo json_encode(array("msg" => "No data found"));
        exit;
    }


    if (!empty($search)) {
        $filtered_articles = array_filter($articles, function ($article) use ($search) {
            foreach ($article as $key => $value) {
                if (strpos(strtolower($value), strtolower($search)) !== false) {
                    return true;
                }
            }
            return false;
        });
        http_response_code(200);
        echo json_encode(array("articles" => array_values($filtered_articles)));
    }else{
        http_response_code(200);
        echo json_encode(array("articles" => $articles));

    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array("msg" => "Server error: " . $e->getMessage()));
}
?>