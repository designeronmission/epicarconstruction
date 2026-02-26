<?php
// process-contact.php - COMPLETE VERSION WITH EMAILS

// ========== CONFIGURATION ==========
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========== EMAIL CONFIGURATION ==========
define('ADMIN_EMAIL', 'designeronmission@gmail.com');  // Admin receives enquiries
define('ADMIN_NAME', 'Epicarc Construction Team');
define('FROM_EMAIL', 'noreply@epicarconstruction.com'); // Sender email
define('FROM_NAME', 'Epicarc Construction');
define('COMPANY_NAME', 'Epicarc Construction');
define('COMPANY_PHONE', '+91 636 991 1989');
define('COMPANY_ADDRESS', '2/26A, Vellar Street, Kalikappan, Madurai, Tamil Nadu - 625107');

// ========== SMTP CONFIGURATION ==========
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'gururl@aparajayah.com');     // Your SMTP username
define('SMTP_PASSWORD', 'sfysxbydyxohmnoc');           // Your SMTP password
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// ========== LOAD PHPMailer ==========
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ========== MAIN PROCESS ==========
try {
    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }
    
    // ========== 1. GET FORM DATA ==========
    $name = cleanInput($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $location = cleanInput($_POST['location'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    $contactMethod = cleanInput($_POST['contactMethod'] ?? 'phone');
    $contactTime = cleanInput($_POST['contactTime'] ?? 'anytime');
    
    // ========== 2. VALIDATION ==========
    $errors = validateForm($name, $email, $phone, $location, $message);
    
    if (!empty($errors)) {
        throw new Exception(implode(' ', $errors));
    }
    
    // ========== 3. GENERATE REFERENCE ==========
    $ref_id = 'ENQ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    $submission_date = date('d M Y, h:i A');
    
    // ========== 4. SEND EMAILS ==========
    $email_results = sendAllEmails($name, $email, $phone, $location, $message, 
                                 $contactMethod, $contactTime, $ref_id, $submission_date);
    
    // ========== 5. SAVE TO FILE ==========
    saveEnquiry($ref_id, $name, $email, $phone, $location, $message, 
                $contactMethod, $contactTime, $email_results);
    
    // ========== 6. RETURN SUCCESS ==========
    $response = [
        'success' => true,
        'message' => "Thank you, $name! Your enquiry has been submitted successfully. We've sent a confirmation email to $email",
        'ref_id' => $ref_id,
        'emails_sent' => $email_results,
        'data' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'ref_id' => $ref_id,
            'timestamp' => $submission_date
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    http_response_code(400);
    echo json_encode($response);
}

// ========== FUNCTIONS ==========

/**
 * Clean input data
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $data;
}

/**
 * Validate form data
 */
function validateForm($name, $email, $phone, $location, $message) {
    $errors = [];
    
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
    
    return $errors;
}

/**
 * Send all emails (admin + customer)
 */
function sendAllEmails($name, $email, $phone, $location, $message, 
                      $contactMethod, $contactTime, $ref_id, $submission_date) {
    
    $results = [
        'admin' => false,
        'customer' => false
    ];
    
    // 1. Send to ADMIN
    $admin_subject = " New Website Enquiry: $name - $ref_id";
    $admin_body = createAdminEmail($name, $email, $phone, $location, $message, 
                                   $contactMethod, $contactTime, $ref_id, $submission_date);
    
    $results['admin'] = sendEmail(
        ADMIN_EMAIL, 
        ADMIN_NAME,
        $admin_subject, 
        $admin_body,
        $email,  // Reply-to
        $name    // Reply-to name
    );
    
    // 2. Send to CUSTOMER
    $customer_subject = "✅ Enquiry Received - Epicarc Construction";
    $customer_body = createCustomerEmail($name, $email, $phone, $location, $message, 
                                        $contactMethod, $contactTime, $ref_id, $submission_date);
    
    $results['customer'] = sendEmail(
        $email,
        $name,
        $customer_subject,
        $customer_body,
        FROM_EMAIL,  // Reply-to
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
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
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
            .btn-whatsapp { background: #25D366; color: white !important; font-weight: bold; }
            .btn-call { background: #28a745; color: white !important; font-weight: bold; }
            .btn-email { background: #17a2b8; color: white !important; font-weight: bold;}
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 14px; }
            .ref-id { font-size: 24px; font-weight: bold; color: #ffffff; letter-spacing: 2px; }
            .urgent { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 6px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1> New Website Enquiry</h1>
                <p class="ref-id" >Reference: <span class="ref-id">' . $ref_id . '</span></p>
            </div>
            
            <div class="content">
                <div class="urgent">
                    <h3>🚨 Action Required</h3>
                    <p>Please contact the customer within <strong>24 hours</strong></p>
                </div>
                
                <h2>Customer Details</h2>
                <div class="card">
                    <p><strong> Name:</strong> ' . $name . '</p>
                    <p><strong> Email:</strong> ' . $email . '</p>
                    <p><strong> Phone:</strong> +91 ' . $phone . '</p>
                    <p><strong> Location:</strong> ' . $location . '</p>
                    <p><strong> Contact Method:</strong> ' . ucfirst($contactMethod) . '</p>
                    <p><strong> Preferred Time:</strong> ' . ucfirst($contactTime) . '</p>
                    <p><strong> Submitted:</strong> ' . $submission_date . '</p>
                </div>
                
                <h2>Message</h2>
                <div class="card">
                    <p>' . nl2br($message) . '</p>
                </div>
                
                <h2>Quick Actions</h2>
                <p>
                    <a href="' . $whatsapp_link . '" class="btn btn-whatsapp"> WhatsApp</a>
                    <a href="tel:+91' . $phone . '" class="btn btn-call"> Call Now</a>
                    <a href="mailto:' . $email . '" class="btn btn-email"> Reply Email</a>
                </p>
                
                <div style="margin-top: 30px; padding: 15px; background: #e8f5e9; border-radius: 6px;">
                    <p><strong>⚠️ Important:</strong></p>
                    <p>• Contact customer within 24 hours</p>
                    <p>• Update enquiry status in your system</p>
                    <p>• Save this reference: ' . $ref_id . '</p>
                </div>
            </div>
            
            <div class="footer">
                <p>This is an automated email from Epicarc Construction website.</p>
                <p>📍 ' . COMPANY_ADDRESS . '</p>
                <p>📞 ' . COMPANY_PHONE . ' | 📧 info@epicarconstruction.com</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Create customer confirmation email
 */
function createCustomerEmail($name, $email, $phone, $location, $message, 
                           $contactMethod, $contactTime, $ref_id, $submission_date) {
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enquiry Confirmation - Epicarc Construction</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
            .header { background: #12223B; color: white; padding: 40px 30px; text-align: center; }
            .content { padding: 30px; }
            .thank-you { text-align: center; padding: 30px; background: #e8f5e9; border-radius: 8px; margin: 20px 0; }
            .details { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 25px; margin: 20px 0; }
            .ref-box { background: #12223B; color: white; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0; }
            .contact-info { background: #e3f2fd; padding: 20px; border-radius: 6px; margin: 20px 0; }
            .footer { background: #f8f9fa; padding: 25px; text-align: center; color: #666; font-size: 14px; }
            .steps { display: flex; flex-wrap: wrap; gap: 15px; margin: 25px 0; }
            .step { flex: 1; min-width: 150px; background: white; border: 1px solid #dee2e6; border-radius: 6px; padding: 20px; text-align: center; }
             .logo {
                max-width: 180px;
                margin: 0 auto 20px;
                display: block;
            }
            .step-number { font-size: 24px; font-weight: bold; color: #12223B; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
            <img src="https://epicarconstruction.com/assets/images/logo/logo-final.png" alt="Epicarc Construction" class="logo">
                <h1>Thank You, ' . $name . '!</h1>
                <p>Your enquiry has been received successfully</p>
            </div>
            
            <div class="content">
                <div class="thank-you">
                    <h2 style="color: #28a745;">Enquiry Confirmed</h2>
                    <p>We have received your enquiry and our team will contact you shortly.</p>
                    
                    <div class="ref-box">
                        <div style="font-size: 12px; opacity: 0.8;">REFERENCE NUMBER</div>
                        <div style="font-size: 24px; font-weight: bold; letter-spacing: 2px;">' . $ref_id . '</div>
                    </div>
                </div>
                
                <div class="details">
                    <h3>Your Enquiry Details</h3>
                    <p><strong>Name:</strong> ' . $name . '</p>
                    <p><strong>Email:</strong> ' . $email . '</p>
                    <p><strong>Phone:</strong> +91 ' . $phone . '</p>
                    <p><strong>Location:</strong> ' . $location . '</p>
                    <p><strong>Preferred Contact:</strong> ' . ucfirst($contactMethod) . '</p>
                    <p><strong>Preferred Time:</strong> ' . ucfirst($contactTime) . '</p>
                    <p><strong>Submitted:</strong> ' . $submission_date . '</p>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                        <p><strong>Your Message:</strong></p>
                        <p>' . nl2br($message) . '</p>
                    </div>
                </div>
                
                
                <div class="contact-info">
                    <h3>📞 Need Immediate Assistance?</h3>
                    <p><strong>Call:</strong> ' . COMPANY_PHONE . '</p>
                    <p><strong>Email:</strong> info@epicarconstruction.com</p>
                    <p><strong>Address:</strong> ' . COMPANY_ADDRESS . '</p>
                    <p style="margin-top: 15px;">
                        <em>Office Hours: Monday to Saturday, 9:00 AM - 7:00 PM</em>
                    </p>
                </div>
                
                <div style="background: #fff8e6; padding: 15px; border-radius: 6px; margin: 20px 0;">
                    <p><strong>💡 Tips for our discussion:</strong></p>
                    <p>• Have your project requirements ready</p>
                    <p>• Share any existing plans or sketches</p>
                    <p>• Mention your budget range if possible</p>
                    <p>• Note down any specific questions</p>
                </div>
            </div>
            
            <div class="footer">
                <p><strong>Epicarc Construction</strong></p>
                <p>Where Vision Meets Structure</p>
                <p style="margin-top: 20px; font-size: 12px; color: #999;">
                    This is an automated email. Please do not reply to this message.<br>
                    For enquiries, please contact: info@epicarconstruction.com
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
        'message' => substr($message, 0, 1000), // Limit length
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
    
    // Also save to CSV for easy viewing
    $csv_file = $dir . '/enquiries.csv';
    $csv_header = ['Reference', 'Date', 'Name', 'Email', 'Phone', 'Location', 'Contact Method'];
    
    if (!file_exists($csv_file)) {
        $fp = fopen($csv_file, 'w');
        fputcsv($fp, $csv_header);
        fclose($fp);
    }
    
    $fp = fopen($csv_file, 'a');
    fputcsv($fp, [
        $ref_id,
        date('Y-m-d H:i:s'),
        $name,
        $email,
        $phone,
        $location,
        $contactMethod
    ]);
    fclose($fp);
}
?>