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
require '../auth/email_config.php';
require '../emails/email.php';

// Validate the required fields
if(!isset($_POST['token'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}


if(!isset($_POST['name'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Please enter the name"));
    exit;
}


if(!isset($_POST['surname'])){
    http_response_code(400);
    echo json_encode(array("msg" => "Please enter the surname"));
    exit;
}

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$emailSender = new Email();


$name = $_POST['name'];
$surname = $_POST['surname'];
$contact_number = $_POST['contact_number'];

$query = $supabase->initializeQueryBuilder();
$gimme_users = $query->select('*')
    ->from('gimme_users')
    ->where('id', 'eq.'.$uid)
    ->order('id.desc')
    ->execute()
    ->getResult();

if(count($gimme_users)>0){
    $db = $supabase->initializeDatabase('gimme_users', 'id');

    $updateUser = [
        'name' => $name,
        'surname' => $surname,
        'contact_number' => $contact_number
    ];
    
    try{
        $data = $db->update($uid, $updateUser);
       if(count($data)>0){
            http_response_code(200);
            echo json_encode(array("msg" => "Profile updated successfully"));
       }else{
        http_response_code(400);
        echo json_encode(array("msg" => "Something went wrong"));

       }
    }
    catch(Exception $e){
        http_response_code(400);
        echo json_encode(array("msg" => "Something went wrong"));
    }

}

?>