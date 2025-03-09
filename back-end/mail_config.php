<?php
require 'C:/Users/Noaman/Documents/Github/ensem_web_project/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailer(): PHPMailer
{
    $mail = new PHPMailer(true);  // Create a new PHPMailer instance

    try {
        // Server settings
        $mail->isSMTP(); // Use SMTP
        $mail->Host = 'smtp.gmail.com';  // Set the SMTP server to use (Gmail SMTP in this example)
        $mail->SMTPAuth = true;  // Enable SMTP authentication
        $mail->Username = 'makhloufnoaman58@gmail.com';  // Your SMTP username (email)
        $mail->Password = 'yuxi yuwa erfb ndsv';  // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Enable TLS encryption
        $mail->Port = 587;  // TCP port to connect to (587 for TLS)

        // Recipients
        $mail->setFrom('makhloufnoaman58@gmail.com', 'Mailer');
        return $mail;
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}
