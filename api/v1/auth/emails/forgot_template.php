<?php 
namespace Mail;

class ForgotEmail {

    private function createTemplate($emailToName, $code) {
        $template = '
            <html>
                <head>
                    <title>Gimme App - Forgot Password OTP</title>
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
                    <h1>Password Reset Requested</h1>
                    
                    <p>Dear '.$emailToName.',</p>
                    
                    <p>We received a request to reset your password for your Gimme account. If you made this request, then kindly use the provided OTP below. If you did not make this request, please ignore this email or contact our support team.</p>
                    
                    <h2><strong>'.$code.'</strong></h2>
                    
                    <p>If you need further assistance or have any other questions, please feel free to reply to this email or contact our support team directly.</p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Gimme Team</strong></p>

                </body>
            </html>
    
        ';
        return $template;
    }

    public function generateTemplate($emailTo, $emailToName, $code) {
        return [
            'replyToEmail' => "no-reply@equinetendon.online",
            'emailTitle' => 'Gimme App',
            'emailTo' => $emailTo,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme App - Forgot Password',
            'emailMessage' => $this->createTemplate($emailToName, $code)
        ];
    }
}

?>