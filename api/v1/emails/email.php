<?php
// show errors
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

class Email{

    // Socials Settings
    private $visit_us = "";
    private $privacy_policy = "";
    private $terms_of_use = "";

    // Admin Settings
    private $admin_email = "shekhar@duendedigital.io";
    private $admin_name = "Admin";

    // Template
    private $header = <<<EOD
    <html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="en">
    <head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>New Template</title><!--[if (mso 16)]>
    <style type="text/css">
    a {text-decoration: none;}
    </style>
    <![endif]--><!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!--[if gte mso 9]>
    <xml>
    <o:OfficeDocumentSettings>
    <o:AllowPNG></o:AllowPNG>
    <o:PixelsPerInch>96</o:PixelsPerInch>
    </o:OfficeDocumentSettings>
    </xml>
        <![endif]-->
            <style type="text/css">
        #outlook a {
            padding:0;
        }
        .es-button {
            mso-style-priority:100!important;
            text-decoration:none!important;
        }
        a[x-apple-data-detectors] {
            color:inherit!important;
            text-decoration:none!important;
            font-size:inherit!important;
            font-family:inherit!important;
            font-weight:inherit!important;
            line-height:inherit!important;
        }
        .es-desk-hidden {
            display:none;
            float:left;
            overflow:hidden;
            width:0;
            max-height:0;
            line-height:0;
            mso-hide:all;
        }
        @media only screen and (max-width:600px) {p, ul li, ol li, a { line-height:150%!important } h1, h2, h3, h1 a, h2 a, h3 a { line-height:120% } h1 { font-size:36px!important; text-align:left } h2 { font-size:26px!important; text-align:left } h3 { font-size:20px!important; text-align:left } .es-header-body h1 a, .es-content-body h1 a, .es-footer-body h1 a { font-size:36px!important; text-align:left } .es-header-body h2 a, .es-content-body h2 a, .es-footer-body h2 a { font-size:26px!important; text-align:left } .es-header-body h3 a, .es-content-body h3 a, .es-footer-body h3 a { font-size:20px!important; text-align:left } .es-menu td a { font-size:12px!important } .es-header-body p, .es-header-body ul li, .es-header-body ol li, .es-header-body a { font-size:14px!important } .es-content-body p, .es-content-body ul li, .es-content-body ol li, .es-content-body a { font-size:16px!important } .es-footer-body p, .es-footer-body ul li, .es-footer-body ol li, .es-footer-body a { font-size:14px!important } .es-infoblock p, .es-infoblock ul li, .es-infoblock ol li, .es-infoblock a { font-size:12px!important } *[class="gmail-fix"] { display:none!important } .es-m-txt-c, .es-m-txt-c h1, .es-m-txt-c h2, .es-m-txt-c h3 { text-align:center!important } .es-m-txt-r, .es-m-txt-r h1, .es-m-txt-r h2, .es-m-txt-r h3 { text-align:right!important } .es-m-txt-l, .es-m-txt-l h1, .es-m-txt-l h2, .es-m-txt-l h3 { text-align:left!important } .es-m-txt-r img, .es-m-txt-c img, .es-m-txt-l img { display:inline!important } .es-button-border { display:inline-block!important } a.es-button, button.es-button { font-size:20px!important; display:inline-block!important } .es-adaptive table, .es-left, .es-right { width:100%!important } .es-content table, .es-header table, .es-footer table, .es-content, .es-footer, .es-header { width:100%!important; max-width:600px!important } .es-adapt-td { display:block!important; width:100%!important } .adapt-img { width:100%!important; height:auto!important } .es-m-p0 { padding:0!important } .es-m-p0r { padding-right:0!important } .es-m-p0l { padding-left:0!important } .es-m-p0t { padding-top:0!important } .es-m-p0b { padding-bottom:0!important } .es-m-p20b { padding-bottom:20px!important } .es-mobile-hidden, .es-hidden { display:none!important } tr.es-desk-hidden, td.es-desk-hidden, table.es-desk-hidden { width:auto!important; overflow:visible!important; float:none!important; max-height:inherit!important; line-height:inherit!important } tr.es-desk-hidden { display:table-row!important } table.es-desk-hidden { display:table!important } td.es-desk-menu-hidden { display:table-cell!important } .es-menu td { width:1%!important } table.es-table-not-adapt, .esd-block-html table { width:auto!important } table.es-social { display:inline-block!important } table.es-social td { display:inline-block!important } .es-m-p5 { padding:5px!important } .es-m-p5t { padding-top:5px!important } .es-m-p5b { padding-bottom:5px!important } .es-m-p5r { padding-right:5px!important } .es-m-p5l { padding-left:5px!important } .es-m-p10 { padding:10px!important } .es-m-p10t { padding-top:10px!important } .es-m-p10b { padding-bottom:10px!important } .es-m-p10r { padding-right:10px!important } .es-m-p10l { padding-left:10px!important } .es-m-p15 { padding:15px!important } .es-m-p15t { padding-top:15px!important } .es-m-p15b { padding-bottom:15px!important } .es-m-p15r { padding-right:15px!important } .es-m-p15l { padding-left:15px!important } .es-m-p20 { padding:20px!important } .es-m-p20t { padding-top:20px!important } .es-m-p20r { padding-right:20px!important } .es-m-p20l { padding-left:20px!important } .es-m-p25 { padding:25px!important } .es-m-p25t { padding-top:25px!important } .es-m-p25b { padding-bottom:25px!important } .es-m-p25r { padding-right:25px!important } .es-m-p25l { padding-left:25px!important } .es-m-p30 { padding:30px!important } .es-m-p30t { padding-top:30px!important } .es-m-p30b { padding-bottom:30px!important } .es-m-p30r { padding-right:30px!important } .es-m-p30l { padding-left:30px!important } .es-m-p35 { padding:35px!important } .es-m-p35t { padding-top:35px!important } .es-m-p35b { padding-bottom:35px!important } .es-m-p35r { padding-right:35px!important } .es-m-p35l { padding-left:35px!important } .es-m-p40 { padding:40px!important } .es-m-p40t { padding-top:40px!important } .es-m-p40b { padding-bottom:40px!important } .es-m-p40r { padding-right:40px!important } .es-m-p40l { padding-left:40px!important } .es-desk-hidden { display:table-row!important; width:auto!important; overflow:visible!important; max-height:inherit!important } }
        @media screen and (max-width:384px) {.mail-message-content { width:414px!important } }
        </style>
            </head>
            <body data-new-gr-c-s-loaded="14.1147.0" style="width:100%;font-family:arial, "helvetica neue", helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
            <div dir="ltr" class="es-wrapper-color" lang="en" style="background-color:#FAFAFA"><!--[if gte mso 9]>
                    <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                        <v:fill type="tile" color="#fafafa"></v:fill>
                    </v:background>
                <![endif]-->

                <table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
                    <tr>
                    <td align="center" style="padding:0;Margin:0">
                    <table bgcolor="#ffffff" class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
                        <tr>
                        <td align="left" style="Margin:0;padding-left:20px;padding-right:20px;padding-top:30px;padding-bottom:30px">
                        <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                            <tr>
                            <td align="center" valign="top" style="padding:0;Margin:0;width:560px">
                            <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                                <tr>
                                <td align="center" style="padding:0;Margin:0;padding-bottom:20px;font-size:0px"><img src="https://duendedisplay.co.za/gimme/images/gimme.png" alt="Logo" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;font-size:12px" width="150" title="Logo"></td>
                                </tr>
    EOD;
    public function getFooter(){
        return <<<EOD

        </table></td>
        </tr>
            </table></td>
            </tr>
            </table></td>
            </tr>
            </table>
            <table cellpadding="0" cellspacing="0" class="es-footer" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
            <tr>
            <td align="center" style="padding:0;Margin:0">
            <table class="es-footer-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:640px">
            <tr>
            <td align="left" style="Margin:0;padding-top:20px;padding-bottom:20px;padding-left:20px;padding-right:20px">
            <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                <tr>
                <td align="left" style="padding:0;Margin:0;width:600px">
                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                    <tr>
                    <td align="center" style="padding:0;Margin:0;padding-bottom:35px"><p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:18px;color:#333333;font-size:12px">Gimme &nbsp;© 2024 All Rights Reserved.</p></td>
                    </tr>
                    <tr>
                    <td style="padding:0;Margin:0">
                    <table cellpadding="0" cellspacing="0" width="100%" class="es-menu" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                        <tr class="links">
                        <td align="center" valign="top" width="33.33%" style="Margin:0;padding-left:5px;padding-right:5px;padding-top:5px;padding-bottom:5px;border:0"><a target="_blank" href="#" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;display:block;font-family:arial, 'helvetica neue', helvetica, sans-serif;color:#999999;font-size:12px">Visit Us </a></td>
                        <td align="center" valign="top" width="33.33%" style="Margin:0;padding-left:5px;padding-right:5px;padding-top:5px;padding-bottom:5px;border:0;border-left:1px solid #cccccc"><a target="_blank" href="#" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;display:block;font-family:arial, 'helvetica neue', helvetica, sans-serif;color:#999999;font-size:12px">Privacy Policy</a></td>
                        <td align="center" valign="top" width="33.33%" style="Margin:0;padding-left:5px;padding-right:5px;padding-top:5px;padding-bottom:5px;border:0;border-left:1px solid #cccccc"><a target="_blank" href="#" style="-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;text-decoration:none;display:block;font-family:arial, 'helvetica neue', helvetica, sans-serif;color:#999999;font-size:12px">Terms of Use</a></td>
                        </tr>
                    </table></td>
                    </tr>
                </table></td>
                </tr>
            </table></td>
            </tr>
            </table></td>
            </tr>
            </table>
            <table cellpadding="0" cellspacing="0" class="es-content" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%">
            <tr>
            <td class="es-info-area" align="center" style="padding:0;Margin:0">
            <table class="es-content-body" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:transparent;width:600px" bgcolor="#FFFFFF">
            <tr>
            <td align="left" style="padding:20px;Margin:0">
            <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                <tr>
                <td align="center" valign="top" style="padding:0;Margin:0;width:560px">
                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                    <tr>
                    <td align="center" style="padding:0;Margin:0;display:none"></td>
                    </tr>
                </table></td>
                </tr>
            </table></td>
            </tr>
            </table></td>
            </tr>
            </table></td>
            </tr>
            </table>
            </div>
            </body>
            </html>
        EOD;
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////
    // TEMPLATES
    /////////////////////////////////////////////////////////////////////////////////////////////////

    // Check-In
    // -------------------------------------------------------------------------------------
    public function generateCheckInTemplate_Admin($userName,$userEmail,$horseName,$subscription){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-txt-c" style="padding:0;Margin:0;padding-bottom:10px">
                <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:46px;font-style:normal;font-weight:bold;color:#333333">
                    New Check-in
                </h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    Dear Admin,<br/><br/>
        
                    A new check-in has been made by a user. Please find their details below:<br/><br/>
                    
                    <strong>User Name:</strong><br/>
                    <span style="font-size:21px;font-weight:bold">$userName</span><br/><br/>
        
                    <strong>User Email:</strong><br/>
                    <span style="font-size:21px;font-weight:bold">$userEmail</span><br/><br/>
        
                    <strong>Horse Name:</strong><br/>
                    <span style="font-size:21px;font-weight:bold">$horseName</span><br/><br/>
        
                    <strong>Payment Status:</strong><br/>
                    <span style="font-size:21px;font-weight:bold">$subscription</span><br/><br/>
        
                    Please check the Gimme dashboard for further details.<br/><br/>
        
                    Kind regards,<br/>
                    Gimme Webmaster
                </p>
            </td>
        </tr>    
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $this->admin_email,
            'emailToName' => $this->admin_name,
            'emailSubject' => 'Gimme - User Completed Check-In',
            'emailMessage' => $message
        ];
    }
    public function generateCheckInTemplate($email,$emailToName,$horseName,$subscriptionStatus){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-txt-c" style="padding:0;Margin:0;padding-bottom:10px">
                <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:46px;font-style:normal;font-weight:bold;color:#333333">
                    Check-In Complete!
                </h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <p>Dear <strong>$emailToName</strong>,</p>
        
                    <p>Your check-in has been completed successfully. Below are the details:</p>
        
                    <p>
                        <strong>Horse Name:</strong><br/>
                        $horseName<br/><br/>
        
                        <strong>Payment Status:</strong><br/>
                        <strong>$subscriptionStatus</strong><br/><br/>
                    </p>
        
                    <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
                </p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme - Check-In Complete',
            'emailMessage' => $message
        ];
    }
    // Forgot Password
    // -------------------------------------------------------------------------------------
    public function generateForgotPasswordTemplate($email,$emailToName,$code){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p>Dear <strong>$emailToName</strong>,</p>
                <p>We received a request to reset your password for your Gimme account. If you made this request, kindly use the provided OTP below. If you didn't make this request, please ignore this email or contact our support team.</p>
                
                <p style="font-size:30px"><strong>$code</strong></p>
                
                <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme - Forgot Password',
            'emailMessage' => $message
        ];
    }
    // Organiser Registered (Groomer)
    // -------------------------------------------------------------------------------------
    public function generateOrganiserRegisteredTemplate($email,$emailToName){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-txt-c" style="padding:0;Margin:0;padding-bottom:10px">
                <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:46px;font-style:normal;font-weight:bold;color:#333333">
                    You've been registered as an Organizer
                </h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <p>Dear <strong>$emailToName</strong>,</p>
                    
                    <p>You have been registered as an organizer for a Gimme user. If you believe that this was done in error, please contact our support team.</p>
                    
                    <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Gimme Team</strong></p>
                </p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme - organiser Registration',
            'emailMessage' => $message
        ];
    }

    public function generateOrganiserRegisteredWithPasswordTemplate($email,$emailToName,$password){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-txt-c" style="padding:0;Margin:0;padding-bottom:10px">
                <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:46px;font-style:normal;font-weight:bold;color:#333333">
                    You've been registered as an Organizer
                </h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <p>Dear <strong>$emailToName</strong>,</p>
                    
                    <p>You have been registered as an organizer for a Gimme user. If you believe that this was done in error, please contact our support team. Please see your account password below:</p>

                    <p>$password</p>
                    
                    <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Gimme Team</strong></p>
                </p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme - organiser Registration',
            'emailMessage' => $message
        ];
    }
    // Organiser Role Changed
    // -------------------------------------------------------------------------------------
    public function generateOrganiserRoleChangedTemplate($email,$emailToName, $newRole){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-txt-c" style="padding:0;Margin:0;padding-bottom:10px">
                <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:46px;font-style:normal;font-weight:bold;color:#333333">
                    You're now an Event Organizer!
                </h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <p>Dear <strong>$emailToName</strong>,</p>
                    
                    <p>Please note that your role has been updated to $newRole.</p>
                    
                    <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Gimme Team</strong></p>
                </p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme - organiser Registration',
            'emailMessage' => $message
        ];
    }
    // User Registered
    // -------------------------------------------------------------------------------------
    public function generateUserRegisteredTemplate($email,$emailToName,$code){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <h1 style="Margin:0;line-height:32px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:24px;font-style:normal;font-weight:bold;color:#333333">Gimme App Registration</h1>
                    
                    <p>Dear <strong>$emailToName</strong>,</p>
                    
                    <p>Thank you for joining the Gimme App! We're thrilled to have you on board. To activate your account, please use the One-Time Password (OTP) provided below.</p>
                    
                    <h2 style="Margin:0;line-height:28px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:20px;font-style:normal;font-weight:bold;color:#333333">$code</h2>
                    
                    
                    <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
                </p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme Registration OTP',
            'emailMessage' => $message
        ];
    }


    // User Registered
    // -------------------------------------------------------------------------------------
    public function GolferMatchCode($email,$emailToName,$code){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <h1 style="Margin:0;line-height:32px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:24px;font-style:normal;font-weight:bold;color:#333333">Gimme App Match Code</h1>
                    <p>Dear <strong>$emailToName</strong>,</p>
                    <p>Welcome to the Gimme App community! We're excited to have you with us. To kick off your gaming experience, please use the following Code to join the round:</p>
                    <h2 style="Margin:0;line-height:28px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:20px;font-style:normal;font-weight:bold;color:#333333">$code</h2>
                    <p>Should you have any questions or need assistance, please don't hesitate to email info@gimmestat.com or reach out to our support team directly.</p>
                    <p>Happy golfing!</p>
                    <p>Best regards,<br/>The Gimme App Team</p>
                </p>
            </td>
        </tr>
    
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();
        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme App',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme Match Code',
            'emailMessage' => $message
        ];
    }

    // Payment Confirmation
    // -------------------------------------------------------------------------------------
    public function generatePaymentConfirmationTemplate($email,$emailToName,$invoiceLink,$paymentType){

        // Set Email Content
        $body = <<<EOD
        <tr>
            <td align="center" class="es-m-txt-c" style="padding:0;Margin:0;padding-bottom:10px">
                <h1 style="Margin:0;line-height:46px;mso-line-height-rule:exactly;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';font-size:46px;font-style:normal;font-weight:bold;color:#333333">
                    Welcome to Gimme!
                </h1>
            </td>
        </tr>
        <tr>
            <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                    <p>Dear <strong>$emailToName</strong>,</p>
                    
                    <p>Thank you for your payment. Your payment has been successfully processed.</p>
        
                    <p>Please click the link below to access your invoice and receipt:</p>
                    <p><a href="$invoiceLink">View Invoice</a></p>
                    
                    <p>Kind Regards,</p>
                    <p><strong>The Gimme Team</strong></p>
                </p>
            </td>
        </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Gimme - '.$paymentType.' Confirmation',
            'emailMessage' => $message
        ];
    }

    // User Invited
    // -------------------------------------------------------------------------------------
    public function generateUserInvitedTemplate($email,$emailToName,$eventName,$code){

        // Set Email Content
        $body = <<<EOD
            <tr>
                <td align="center" class="es-m-p0r es-m-p0l" style="Margin:0;padding-top:5px;padding-bottom:5px;padding-left:40px;padding-right:40px">
                    <p>Dear <strong>$emailToName</strong>,</p>
                    <p>We are thrilled to announce that you are officially part of <strong>$eventName</strong>! To ensure you're fully in the loop and can make the most out of this event, we invite you to take the next steps through our GIMME app.</p>
                    
                    <p>Here's how to get started:</p>
                    
                    <ol>
                        <li><strong>Download the GIMME App:</strong> If you haven't yet, please download the GIMME app from the Google Play Store or the Apple App Store. It's completely free and your gateway to everything related to <strong>$eventName</strong>.</li>
                        
                        <li><strong>Register for the Event:</strong> Once the app is installed on your phone, register for <strong>$eventName</strong> within the app using the following Event Code:</li>
                    </ol>
                    
                    <p style="font-size:30px"><strong>$code</strong></p>
                    
                    <p>This will unlock access to detailed event information, live updates, and the ability to post live scores - keeping you engaged every step of the way.</p>
                    
                    <p>Should you have any questions or wish to share feedback, don't hesitate to reach out by emailing your tournament organizer directly. We're here to ensure your experience is seamless and enjoyable.</p>
                    
                    <p>We can't wait to see you in action and be a part of your journey through <strong>$eventName</strong>. Let the excitement begin!</p>
                    
                    <p>Warm regards,<br>Team GIMME</p>
                </td>
            </tr>
        EOD;
        
        // Join email sections
        $message = $this->header."".$body."".$this->getFooter();

        // Return data to send email
        return [
            'replyToEmail' => "no-reply@gimme.online",
            'emailTitle' => 'Gimme',
            'emailTo' => $email,
            'emailToName' => $emailToName,
            'emailSubject' => 'Welcome to '.$eventName.' - Get Ready to Engage!',
            'emailMessage' => $message
        ];
    }
}

?>