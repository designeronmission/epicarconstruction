<?php
// process-contact-server.php - COMPLETE SERVER-SIDE EMAIL PROCESSING
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========== CONFIGURATION ==========
define('ADMIN_EMAIL', 'designeronmission@gmail.com');
define('ADMIN_NAME', 'Epicarc Construction Team');
define('FROM_EMAIL', 'noreply@epicarconstruction.com');
define('FROM_NAME', 'Epicarc Construction');
define('COMPANY_NAME', 'Epicarc Construction');
define('COMPANY_PHONE', '+91 636 991 1989');
define('COMPANY_ADDRESS', '2/26A, Vellar Street, Kalikappan, Madurai, Tamil Nadu - 625107');

// ========== SMTP CONFIGURATION ==========
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'gururl@aparajayah.com');
define('SMTP_PASSWORD', 'sfysxbydyxohmnoc');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// ========== TURNSTILE CONFIGURATION ==========
define('TURNSTILE_SECRET_KEY', '0x4AAAAAACaEl-VKglWjO3FbySwySGOEdNn');
define('TURNSTILE_SITE_KEY', '0x4AAAAAACaEl6agGJbbWoW2');

// ========== LOAD PHPMailer ==========
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ========== MAIN PROCESS ==========
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Store form data in session for repopulation
    $_SESSION['form_data'] = $_POST;
    
    // ========== 1. GET FORM DATA ==========
    $name = cleanInput($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $location = cleanInput($_POST['location'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    $contactMethod = cleanInput($_POST['contactMethod'] ?? 'phone');
    $contactTime = cleanInput($_POST['contactTime'] ?? 'anytime');
    $terms = isset($_POST['terms']) ? true : false;
    $cf_turnstile_response = $_POST['cf-turnstile-response'] ?? '';
    
    // ========== 2. VALIDATION ==========
    $errors = [];
    
    // Validate required fields
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Please enter your full name.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = "Please enter a valid 10-digit Indian mobile number.";
    }
    
    if (empty($location)) {
        $errors[] = "Please enter your project location.";
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = "Please enter a detailed message (minimum 10 characters).";
    }
    
    if (!$terms) {
        $errors[] = "You must agree to the Terms and Conditions.";
    }
    
    // ========== 3. VERIFY TURNSTILE ==========
    if (!verifyTurnstile($cf_turnstile_response)) {
        $errors[] = "Please complete the security verification.";
    }
    
    // ========== 4. PROCESS IF NO ERRORS ==========
    if (empty($errors)) {
        try {
            // Generate reference ID
            $ref_id = 'ENQ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $submission_date = date('d M Y, h:i A');
            
            // ========== 5. SEND EMAILS ==========
            $email_results = sendAllEmails($name, $email, $phone, $location, $message, 
                                         $contactMethod, $contactTime, $ref_id, $submission_date);
            
            // ========== 6. SAVE TO FILE ==========
            saveEnquiry($ref_id, $name, $email, $phone, $location, $message, 
                       $contactMethod, $contactTime, $email_results);
            
            // ========== 7. CLEAR SESSION & REDIRECT ==========
            unset($_SESSION['form_data']);
            
            // Prepare success message
            $success_message = urlencode("Thank you, $name! Your enquiry has been submitted successfully. We've sent a confirmation email to $email");
            
            // Redirect with success
            header("Location: contact.php?success=1&message=$success_message&ref=$ref_id");
            exit();
            
        } catch (Exception $e) {
            $errors[] = "Email sending failed: " . $e->getMessage();
        }
    }
    
    // ========== 8. IF ERRORS, REDIRECT BACK ==========
    if (!empty($errors)) {
        // Determine which specific error to show
        $error_params = [];
        if (in_array("Please complete the security verification.", $errors)) {
            $error_params[] = 'captcha_error=1';
        }
        if (in_array("You must agree to the Terms and Conditions.", $errors)) {
            $error_params[] = 'terms_error=1';
        }
        
        $error_params[] = 'error=' . urlencode(implode(' ', $errors));
        $redirect_url = "contact.php?" . implode('&', $error_params);
        
        header("Location: $redirect_url");
        exit();
    }
    
} else {
    // If not POST, redirect to contact page
    header("Location: contact.php");
    exit();
}

// ========== FUNCTIONS ==========

/**
 * Clean input data
 */
function cleanInput($data) {
    if (empty($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

/**
 * Verify Cloudflare Turnstile
 */
function verifyTurnstile($turnstile_response) {
    if (empty($turnstile_response)) {
        return false;
    }
    
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => TURNSTILE_SECRET_KEY,
        'response' => $turnstile_response,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result === FALSE) {
        error_log("Turnstile verification failed - could not connect");
        return false;
    }
    
    $response = json_decode($result, true);
    return $response['success'] ?? false;
}

/**
 * Send all emails
 */
function sendAllEmails($name, $email, $phone, $location, $message, 
                      $contactMethod, $contactTime, $ref_id, $submission_date) {
    
    $results = ['admin' => false, 'customer' => false];
    
    // 1. Send to ADMIN
    $admin_subject = "New Website Enquiry: $name - $ref_id";
    $admin_body = createAdminEmail($name, $email, $phone, $location, $message, 
                                   $contactMethod, $contactTime, $ref_id, $submission_date);
    
    $results['admin'] = sendEmail(
        ADMIN_EMAIL, 
        ADMIN_NAME,
        $admin_subject, 
        $admin_body,
        $email,
        $name
    );
    
    // 2. Send to CUSTOMER
    $customer_subject = "✅ Enquiry Received - Epicarc Construction [$ref_id]";
    $customer_body = createCustomerEmail($name, $ref_id, $submission_date);
    
    $results['customer'] = sendEmail(
        $email,
        $name,
        $customer_subject,
        $customer_body,
        FROM_EMAIL,
        FROM_NAME
    );
    
    return $results;
}

/**
 * Send email using PHPMailer
 */
function sendEmail($to_email, $to_name, $subject, $body, $reply_to = '', $reply_name = '') {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0; // Set to 2 for debugging
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        
        if ($reply_to) {
            $mail->addReplyTo($reply_to, $reply_name ?? '');
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $result = $mail->send();
        
        if (!$result) {
            error_log("Email failed to send to: $to_email - " . $mail->ErrorInfo);
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create admin notification email
 */
function createAdminEmail($name, $email, $phone, $location, $message, 
                         $contactMethod, $contactTime, $ref_id, $submission_date) {
    
    $whatsapp_link = "https://wa.me/91{$phone}?text=" . urlencode("Hello $name, regarding your enquiry $ref_id");
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>New Enquiry - ' . $ref_id . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 700px; margin: 0 auto; background: #ffffff; }
            .header { background: #12223B; color: white; padding: 30px; text-align: center; }
            .content { padding: 30px; }
            .card { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 20px 0; }
            .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 6px; margin: 5px; }
            .btn-whatsapp { background: #25D366; }
            .btn-call { background: #28a745; }
            .btn-email { background: #17a2b8; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
            .ref-id { font-size: 24px; font-weight: bold; color: #ffffff; letter-spacing: 2px; }
            .urgent { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 6px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>New Website Enquiry</h1>
                <p>Reference: <span class="ref-id">' . $ref_id . '</span></p>
            </div>
            
            <div class="content">
                <div class="urgent">
                    <h3>🚨 Action Required</h3>
                    <p>Please contact the customer within <strong>24 hours</strong></p>
                </div>
                
                <h2>Customer Details</h2>
                <div class="card">
                    <p><strong>👤 Name:</strong> ' . $name . '</p>
                    <p><strong>📧 Email:</strong> ' . $email . '</p>
                    <p><strong>📱 Phone:</strong> +91 ' . $phone . '</p>
                    <p><strong>📍 Location:</strong> ' . $location . '</p>
                    <p><strong>📞 Contact Method:</strong> ' . ucfirst($contactMethod) . '</p>
                    <p><strong>⏰ Preferred Time:</strong> ' . ucfirst($contactTime) . '</p>
                    <p><strong>📅 Submitted:</strong> ' . $submission_date . '</p>
                </div>
                
                <h2>Message</h2>
                <div class="card">
                    <p>' . nl2br($message) . '</p>
                </div>
                
                <h2>Quick Actions</h2>
                <p>
                    <a href="' . $whatsapp_link . '" class="btn btn-whatsapp">WhatsApp</a>
                    <a href="tel:+91' . $phone . '" class="btn btn-call">Call Now</a>
                    <a href="mailto:' . $email . '" class="btn btn-email">Reply Email</a>
                </p>
            </div>
            
            <div class="footer">
                <p>This is an automated email from Epicarc Construction website.</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Create customer confirmation email
 */
function createCustomerEmail($name, $ref_id, $submission_date) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enquiry Confirmation</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background: #12223B; color: white; padding: 40px 30px; text-align: center; }
            .content { padding: 30px; }
            .thank-you { text-align: center; padding: 30px; background: #e8f5e9; border-radius: 8px; margin: 20px 0; }
            .ref-box { background: #12223B; color: white; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0; }
            .contact-info { background: #e3f2fd; padding: 20px; border-radius: 6px; margin: 20px 0; }
            .footer { background: #f8f9fa; padding: 25px; text-align: center; color: #666; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Thank You, ' . $name . '!</h1>
                <p>Your enquiry has been received successfully</p>
            </div>
            
            <div class="content">
                <div class="thank-you">
                    <h2 style="color: #28a745;">Enquiry Confirmed</h2>
                    <p>We have received your enquiry and our team will contact you within 24 hours.</p>
                    
                    <div class="ref-box">
                        <div style="font-size: 12px; opacity: 0.8;">REFERENCE NUMBER</div>
                        <div style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">' . $ref_id . '</div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <h3>📞 Need Immediate Assistance?</h3>
                    <p><strong>Call:</strong> ' . COMPANY_PHONE . '</p>
                    <p><strong>Email:</strong> info@epicarconstruction.com</p>
                    <p><strong>Address:</strong> ' . COMPANY_ADDRESS . '</p>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Epicarc Construction</strong></p>
                <p>Where Vision Meets Structure</p>
                <p style="margin-top: 20px; font-size: 12px; color: #999;">
                    This is an automated email. Please do not reply to this message.
                </p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Save enquiry to file
 */
function saveEnquiry($ref_id, $name, $email, $phone, $location, $message, 
                    $contactMethod, $contactTime, $email_results) {
    
    $data = [
        'ref_id' => $ref_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'location' => $location,
        'message' => substr($message, 0, 1000),
        'contact_method' => $contactMethod,
        'contact_time' => $contactTime,
        'email_results' => $email_results,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
    ];
    
    // Create directory if not exists
    $dir = 'enquiries';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Save to JSON file
    $file = $dir . '/' . date('Y-m') . '_enquiries.json';
    
    $enquiries = [];
    if (file_exists($file)) {
        $existing = file_get_contents($file);
        if ($existing) {
            $enquiries = json_decode($existing, true) ?? [];
        }
    }
    
    $enquiries[] = $data;
    
    file_put_contents($file, json_encode($enquiries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>