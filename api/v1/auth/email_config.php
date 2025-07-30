<?php

require '../../Tools/Mail/MailjetEmail.php';
require '../../Tools/Mail/Config.php';

use Mailjet\MailjetEmail;

$mailjet = new MailjetEmail($apiKey, $apiSecret);

?>
