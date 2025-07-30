<?php 
// display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handle preflight request for CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    exit;
}

// Set headers for CORS and content type
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

$supabase = new PHPSupabase\Service(
    $config['supabaseKey'],
    $config['supabaseUrl']
);
$emailSender = new Email();

// Get the parameters from the request
$name = htmlspecialchars($_POST['name']);
$surname = htmlspecialchars($_POST['surname']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$contact = htmlspecialchars($_POST['contact']);
$confirm = htmlspecialchars($_POST['confirm']);

// Validate the required fields
if(empty($name) || empty($surname) || empty($email) || empty($contact)){
    http_response_code(400);
    echo json_encode(array("msg" => "Invalid request"));
    exit;
}

// Initialize the users database
$users_db = $supabase->initializeDatabase('gimme_users','id');

try{
    // Check if user exists
    $user = $users_db->findBy("email", $email)->getResult();

    // If user does not exist, create a new user with default password
    if(empty($user)){

        $default_password = generatePassword(); // Send this on emails
        $default_password_hashed = password_hash($default_password, PASSWORD_DEFAULT);
        $new_user = [
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'contact_number' => $contact,
            'password' => "$default_password_hashed",
            'user_role' => 'event_organiser',
            'status' => 'active'
        ];
        $users_db->insert($new_user);

        // if($confirm){
            // Send welcome email with default password
            $template = $emailSender->generateOrganiserRegisteredWithPasswordTemplate($email, $name.' '.$surname,$default_password);

            $response = $mailjet->sendEmail(
                $template['replyToEmail'], 
                $template['emailTitle'],
                $template['emailTo'], 
                $template['emailToName'],
                $template['emailSubject'],
                $template['emailMessage']
            );
        // }else{
            // User does not want to send confirm email
            // http_response_code(400);
            // echo json_encode(array("msg" => "Not confirmed"));
            // exit;
        // }
    } else {
        if($confirm){
            // If user exists and role is 'user', update role to 'event_organiser'
            if($user[0]->user_role == 'user'){
                $users_db->update($user[0]->id, ['user_role' => 'event_organiser']);

                // Send email notifying user of role change
                $template = $emailSender->generateOrganiserRoleChangedTemplate($email, $name.' '.$surname,'event_organiser');

                $response = $mailjet->sendEmail(
                    $template['replyToEmail'], 
                    $template['emailTitle'],
                    $template['emailTo'], 
                    $template['emailToName'],
                    $template['emailSubject'],
                    $template['emailMessage']
                );
            } else if($user[0]->user_role == 'event_organiser'){
                // If user is already an event organiser, throw an error
                http_response_code(400);
                echo json_encode(array("msg" => "User is already an event organiser"));
                exit;
            }
        }else{
            // If user has not confirmed
            http_response_code(400);
            echo json_encode(array("msg" => "Not confirmed"));
            exit;
        }
    }
    echo json_encode(array("msg" => "success"));
}
catch(Exception $e){
    http_response_code(400);
    echo json_encode(array("msg" => $e->getMessage()));
    exit;
}

?>