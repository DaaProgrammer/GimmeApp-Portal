<?php 
namespace Mail;

class GroomerRegisteredEmail {

    private function createTemplate($emailToName) {
        $template = '
            <html>
                <head>
                    <title>Equine Tendon - Registered Groomer</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            padding: 20px;
                            max-width: 600px;
                            margin: 0 auto;
                        }
                        h1 {
                            color: #2C3E50;
                        }
                        p {
                            font-size: 16px;
                        }
                        a {
                            color: #3498DB;
                            text-decoration: none;
                        }
                        .footer {
                            margin-top: 20px;
                            font-size: 14px;
                            color: #7F8C8D;
                        }
                    </style>
                </head>
                <body>
                    <h1>You\'ve been registered as a Groomer</h1>
                    
                    <p>Dear '.$emailToName.',</p>
                    
                    <p>You have been registered as a groomer for an Equine Tendon user. If you believe that this was done in error, please contact our support team.</p>
                    
                    <p>If you need further assistance or have any other questions, please feel free to reply to this email or contact our support team directly.</p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Equine Tendon Team</strong></p>
                    
                    <div class="footer">
                        Equine Tendon, Address Line 1, City, Postal Code<br>
                        Contact: +1-123-456-7890 | support@equinetendon.co
                    </div>
                </body>
            </html>
    
        ';
        return $template;
    }

    public function generateTemplate($emailTo, $emailToName) {
        return [
            'replyToEmail' => "no-reply@equinetendon.online",
            'emailTitle' => 'Equine Tendon',
            'emailTo' => $emailTo,
            'emailToName' => $emailToName,
            'emailSubject' => 'Equine Tendon - Registered Groomer',
            'emailMessage' => $this->createTemplate($emailToName)
        ];
    }
}

?>