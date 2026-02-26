<?php
// Test if emails can be sent
$to = "your-test-email@gmail.com";
$subject = "Test Email from Server";
$message = "If you receive this, email is working!";
$headers = "From: test@epicarconstruction.com";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
?>