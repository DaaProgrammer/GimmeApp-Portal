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
if(empty($_POST['token']) || empty($_POST['article_id'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

// Validation
$required_fields = array('title','contents');
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(array("msg" => "Invalid request"));
        exit;
    }
}

// Initialize the courses database
$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);

$articles_db = $supabase->initializeDatabase('gimme_articles','id');

$data = [
    'title' => $_POST['title'],
    'contents' => $_POST['contents'],
];
if($_POST['image_path'] != null){
    $data['image_path'] = $_POST['image_path'];
}

$articles = $articles_db->update($_POST['article_id'],$data);

http_response_code(200);
echo json_encode(["msg" => "success"]);
?>