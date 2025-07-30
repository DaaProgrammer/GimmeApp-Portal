<?php

require '../../Tools/Mail/MailjetEmail.php';
require '../../Tools/Mail/Config.php';

use Mailjet\MailjetEmail;

$mailjet = new MailjetEmail($apiKey, $apiSecret);

$replyToEmail = "no-reply@equinetendon.online";
$emailTitle = "Equine Tendon";
$emailTo = "darshan@duendedigital.io";
$emailToName = "Darshan Naicker";
$emailSubject = "Test Subject";
$emailMessage = "This is a test message";

$response = $mailjet->sendEmail(
    $replyToEmail, 
    $emailTitle,
    $emailTo, 
    $emailToName,
    $emailSubject,
    $emailMessage
);

// get the message status and check if it was sent successfully
if ($response['Messages'][0]['Status'] === 'success') {
    echo json_encode(array("msg" => "Email sent successfully", "data" => $response));
} else {
    echo json_encode(array("msg" => "Email failed to send", "data" => $response));
}

?>
