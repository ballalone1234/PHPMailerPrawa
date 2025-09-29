<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer();

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'live.smtp.mailtrap.io';
$mail->SMTPAuth = true;
$mail->Username = 'api';
$mail->Password = '059cd341850afe004195c5dab5d65693';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;                             // TCP port to connect to

$mail->setFrom('hello@demomailtrap.co', 'Mailer');
$mail->addAddress('ballpol.127@gmail.com', 'Joe User');     // Add a recipient

$mail->isHTML(true);                                  // Set email format to HTML
$mail->Subject = 'Here is the subject';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

try {
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
}