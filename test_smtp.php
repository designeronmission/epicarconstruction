<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'gururl@aparajayah.com';
    $mail->Password = 'sfysxbydyxohmnoc';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    
    $mail->setFrom('info@epicarconstruction.com', 'Epicarc Construction');
    $mail->addAddress('gururl@aparajayah.com');
    
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = '<h1>Test Email</h1><p>If you receive this, SMTP is working!</p>';
    
    if ($mail->send()) {
        echo 'Email sent successfully!';
    } else {
        echo 'Failed to send email.';
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>