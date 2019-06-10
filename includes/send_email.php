<?php 
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

//Load Composer's autoloader
require 'vendor/autoload.php';

function send_email($email, $body, $fn = ''): bool{
    $mail = new PHPMailer(true);                              
    try {
    $mail->Host = gethostbyname("smtp.gmail.com"); 
    $mail->CharSet = "text/html; charset=UTF-8;";
    $mail->isSMTP();
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

        $mail->SMTPDebug = 0;                                
        $mail->SMTPAuth = true;                               
        $mail->Username = EMAIL;                 
        $mail->Password = PASSWORD;                           
        $mail->SMTPSecure = 'tls';                            
        $mail->Port = 587;                                  

    //     //Recipients
        $mail->setFrom(EMAIL, 'Mailer');
        $mail->addAddress($email, 'Joe User');    

        $mail->Subject = 'Hello, '. $fn .'!';
        $mail->Body    = $body;
        $mail->isHTML(true); 

        $mail->send();
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        return 0;
    }
    return 1;
}