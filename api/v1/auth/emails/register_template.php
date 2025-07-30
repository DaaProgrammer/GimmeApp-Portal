<?php 
namespace Mail;

class RegisterEmail {

    private function createTemplate($emailToName, $code) {
        $template = "
            <html>
                <head>
                    <title>Gimme App - Registration OTP</title>
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
                    <h1>Gimme App Registration</h1>
                    
                    <p>Dear ".$emailToName.",</p>
                    
                    <p>Thank you for joining the Gimme App! We\'re thrilled to have you on board. To activate your account, please use the One-Time Password (OTP) provided below.</p>
                    
                    <h2><strong>".$code."</strong></h2>
                    
                  
                    
                    <p>If you need further assistance or have any other questions, please feel free to reply to this email or contact our support team directly.</p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Gimme Team</strong></p>
                    

                </body>
            </html>
    
        ";
        return $template;
    }

    public function generateTemplate($emailTo, $emailToName, $code) {
        return [
            'replyToEmail' => "no-reply@equinetendon.online",
            'emailTitle' => 'Gimme App',
            'emailTo' => $emailTo,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme App - Registration OTP',
            'emailMessage' => $this->createTemplate($emailToName, $code)
        ];
    }
}

?>