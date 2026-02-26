<?php
// ========== CONFIGURATION ==========
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========== LOAD PHPMailer ==========
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ========== EMAIL CONFIGURATION ==========
define('ADMIN_EMAIL', 'designeronmission@gmail.com');
define('FROM_EMAIL', 'designeronmission@gmail.com');
define('FROM_NAME', 'Epicarc Construction');
define('WEBSITE_URL', 'https://epicarconstruction.com');
define('COMPANY_NAME', 'Epicarc Construction');
define('LOGO_URL', 'https://epicarconstruction.com/assets/images/logo/logo-final.png');

// ========== SMTP CONFIGURATION ==========
define('SMTP_HOST', 'smtp.gmail.com');  
define('SMTP_USERNAME', 'gururl@aparajayah.com');
define('SMTP_PASSWORD', 'sfysxbydyxohmnoc');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// ========== MAIN PROCESSING ==========
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method. Use POST.');
    }
    
    // Get form data
    $name = isset($_POST['name']) ? htmlspecialchars(trim($_POST['name'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone = isset($_POST['phone']) ? preg_replace('/\D/', '', $_POST['phone']) : '';
    $location = isset($_POST['location']) ? htmlspecialchars(trim($_POST['location'])) : '';
    $message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';
    $contactMethod = isset($_POST['contactMethod']) ? htmlspecialchars(trim($_POST['contactMethod'])) : 'phone';
    $contactTime = isset($_POST['contactTime']) ? htmlspecialchars(trim($_POST['contactTime'])) : 'anytime';
    
    // Validation
    $errors = [];
    $required = ['name', 'email', 'phone', 'location', 'message'];
    
    foreach ($required as $field) {
        if (empty($$field)) {
            $errors[] = ucfirst($field) . " is required";
        }
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = "Invalid Indian mobile number";
    }
    
    if (!empty($errors)) {
        throw new Exception(implode('. ', $errors));
    }
    
    // Generate reference ID
    $ref_id = 'ENQ' . date('YmdHis') . rand(100, 999);
    $submission_date = date('d M Y, h:i A');
    
    // ========== EMAIL TEMPLATES ==========
    $admin_subject = "New Website Enquiry: $name - $ref_id";
    $admin_message = createAdminEmail($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date);
    
    $customer_subject = "Enquiry Confirmation - " . COMPANY_NAME;
    $customer_message = createCustomerEmail($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date);
    
    // ========== SEND EMAILS ==========
    $email_results = [
        'admin' => sendEmailWithPHPMailer(ADMIN_EMAIL, $admin_subject, $admin_message, FROM_EMAIL, FROM_NAME, $email),
        'customer' => sendEmailWithPHPMailer($email, $customer_subject, $customer_message, FROM_EMAIL, FROM_NAME, FROM_EMAIL)
    ];
    
    // Save enquiry
    saveEnquiryToFile($ref_id, $name, $email, $phone, $location, $message, $contactMethod, $contactTime, $email_results);
    
    // ========== RESPONSE ==========
    $response = [
        'success' => true,
        'message' => getSuccessMessage($name, $email_results),
        'ref_id' => $ref_id,
        'emails_sent' => $email_results,
        'data' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'location' => $location,
            'contact_method' => $contactMethod,
            'contact_time' => $contactTime,
            'submission_date' => $submission_date
        ]
    ];
    
    http_response_code(200);
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

exit;

// ========== FUNCTIONS ==========

/**
 * Send email using PHPMailer
 */
function sendEmailWithPHPMailer($to, $subject, $message, $from_email, $from_name, $reply_to = '') {
    try {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        
        if ($reply_to) {
            $mail->addReplyTo($reply_to);
        }
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);
        
        return $mail->send();
        
    } catch (Exception $e) {
        return false;
    }
}



/**

 * Create clean admin email template with Font Awesome icons
 */

/**
 * Create clean admin email template without icons
 */
function createAdminEmail($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date) {
    $whatsapp_link = "https://wa.me/91$phone?text=" . urlencode("Hi $name, regarding your enquiry $ref_id on Epicare Construction");
    
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Epicare Construction - New Website Enquiry</title>
    </head>
    <body style="font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f8f9fa; padding: 20px; margin: 0;">
        <div style="max-width: 680px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            
            <!-- Header -->
            <div style="background: #ffffff; padding: 30px 40px 20px 40px; text-align: center; border-bottom: 3px solid #12223B;">
                <img src="https://epicarconstruction.com/assets/images/logo/logo-final.png" alt="Epicarc Construction" style="max-width: 180px; margin: 0 auto 15px; display: block;">
                <p style="color: #666; margin: 0; font-size: 16px;">New Website Enquiry</p>
            </div>
            
            <!-- Main Content -->
            <div style="padding: 0 40px 30px 40px;">
                
                <!-- Reference Number -->
                <div style="text-align: center; margin: 30px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; border: 1px solid #e9ecef;">
                    <div style="color: #666; font-size: 14px; margin-bottom: 5px;">
                        Reference Number
                    </div>
                    <div style="font-size: 20px; font-weight: 700; color: #12223B; letter-spacing: 1px; font-family: monospace;">' . $ref_id . '</div>
                </div>
                
                <!-- Action Required -->
                <div style="margin-bottom: 35px; padding: 20px; background: #fff8e6; border: 1px solid #ffd54f; border-radius: 8px;">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <h2 style="margin: 0; font-size: 20px; color: #e65100; font-weight: 600;">Action Required</h2>
                    </div>
                    
                    <p style="margin: 0 0 20px 55px; color: #666; font-size: 16px; padding-left: 5px;">
                        Please contact the customer within <strong style="color: #d84315;">24 hours</strong>
                    </p>
                    
                    <!-- Action Buttons -->
                    <div style="margin-left: 55px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: separate; border-spacing: 10px;">
                            <tr>
                                <td width="33%" align="center">
                                    <a href="' . $whatsapp_link . '" target="_blank" style="display: block; text-decoration: none; background: #25D366; color: white; padding: 14px 10px; border-radius: 6px; font-size: 15px; font-weight: 600;">
                                        WhatsApp
                                    </a>
                                </td>
                                <td width="33%" align="center">
                                    <a href="tel:+91' . $phone . '" style="display: block; text-decoration: none; background: #2196F3; color: white; padding: 14px 10px; border-radius: 6px; font-size: 15px; font-weight: 600;">
                                        Call
                                    </a>
                                </td>
                                <td width="33%" align="center">
                                    <a href="mailto:' . $email . '?subject=Re: Enquiry ' . $ref_id . '&body=Dear ' . urlencode($name) . ',%0D%0A%0D%0AThank you for contacting Epicare Construction regarding your enquiry ' . $ref_id . '" style="display: block; text-decoration: none; background: #9C27B0; color: white; padding: 14px 10px; border-radius: 6px; font-size: 15px; font-weight: 600;">
                                        Email
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Customer Details -->
                <div style="margin-bottom: 35px;">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; font-size: 20px; color: #12223B; font-weight: 600;">Customer Details</h2>
                    </div>
                    
                    <!-- Details Table Container -->
                    <div style="background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;">
                        <!-- Row 1 -->
                        <div style="display: flex; border-bottom: 1px solid #f0f0f0;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Full Name</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <span style="color: #12223B; font-size: 16px; font-weight: 500;">' . htmlspecialchars($name) . '</span>
                            </div>
                        </div>
                        
                        <!-- Row 2 -->
                        <div style="display: flex; border-bottom: 1px solid #f0f0f0;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Email Address</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <a href="mailto:' . $email . '" style="color: #2196F3; text-decoration: none; font-size: 16px; font-weight: 500;">' . htmlspecialchars($email) . '</a>
                            </div>
                        </div>
                        
                        <!-- Row 3 -->
                        <div style="display: flex; border-bottom: 1px solid #f0f0f0;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Phone Number</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <a href="tel:+91' . $phone . '" style="color: #2196F3; text-decoration: none; font-size: 16px; font-weight: 500;">+91 ' . $phone . '</a>
                            </div>
                        </div>
                        
                        <!-- Row 4 -->
                        <div style="display: flex; border-bottom: 1px solid #f0f0f0;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Location</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <span style="color: #12223B; font-size: 16px; font-weight: 500;">' . htmlspecialchars($location) . '</span>
                            </div>
                        </div>
                        
                        <!-- Row 5 -->
                        <div style="display: flex; border-bottom: 1px solid #f0f0f0;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Contact Method</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <span style="color: #12223B; font-size: 16px; font-weight: 500;">' . ucfirst($contactMethod) . '</span>
                            </div>
                        </div>
                        
                        <!-- Row 6 -->
                        <div style="display: flex; border-bottom: 1px solid #f0f0f0;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Preferred Time</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <span style="color: #12223B; font-size: 16px; font-weight: 500;">' . ucfirst($contactTime) . '</span>
                            </div>
                        </div>
                        
                        <!-- Row 7 -->
                        <div style="display: flex;">
                            <div style="width: 40%; padding: 16px 20px; background: #f9f9f9; border-right: 1px solid #f0f0f0;">
                                <span style="color: #666; font-size: 14px; font-weight: 500;">Submitted On</span>
                            </div>
                            <div style="width: 60%; padding: 16px 20px;">
                                <span style="color: #12223B; font-size: 16px; font-weight: 500;">' . $submission_date . '</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Message -->
                <div style="margin-bottom: 35px;">
                    <div style="display: flex; align-items: center; margin-bottom: 20px;">
                        <h2 style="margin: 0; font-size: 20px; color: #12223B; font-weight: 600;">Customer Message</h2>
                    </div>
                    
                    <div style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 0; overflow: hidden;">
                        <!-- Message Header -->
                        <div style="padding: 18px 20px; background: #f0f2f5; border-bottom: 1px solid #e0e0e0;">
                            <span style="color: #666; font-size: 14px; font-weight: 500;">Message</span>
                        </div>
                        <!-- Message Content -->
                        <div style="padding: 25px 20px;">
                            <div style="color: #333; font-size: 16px; line-height: 1.7; white-space: pre-wrap;">
                                ' . nl2br(htmlspecialchars($message)) . '
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Info -->
                <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                    <div style="margin-bottom: 15px;">
                        <div style="color: #2e7d32; font-size: 14px; font-weight: 600; margin-bottom: 5px;">Submitted On</div>
                        <div style="color: #1b5e20; font-size: 15px; font-weight: 500;">' . $submission_date . '</div>
                    </div>
                    
                    <div>
                        <div style="color: #2e7d32; font-size: 14px; font-weight: 600; margin-bottom: 5px;">Notification Source</div>
                        <div style="color: #1b5e20; font-size: 15px; font-weight: 500;">Epicare Construction Website Enquiry</div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="background: #12223B; color: white; padding: 30px 40px;">
                <!-- Support & Assistance -->
                <div style="margin-bottom: 25px;">
                    <h3 style="color: #8EE6B8; font-size: 16px; font-weight: 600; margin-bottom: 20px; text-align: left;">
                        For Support & Assistance
                    </h3>
                    
                    <div style="display: flex; margin-bottom: 20px;">
                        <div style="width: 50%; padding-right: 20px;">
                            <div style="margin-bottom: 15px;">
                                <div style="color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 3px;">Reach Us</div>
                                <div style="color: #e2e8f0; font-size: 14px; line-height: 1.5;">
                                    +91 638 991 1989<br>
                                    +91 904 750 8430
                                </div>
                            </div>
                        </div>
                        
                        <div style="width: 50%; padding-left: 20px;">
                            <div style="margin-bottom: 15px;">
                                <div style="color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 3px;">Office Address</div>
                                <div style="color: #e2e8f0; font-size: 14px; line-height: 1.5;">
                                    225A, Walter Street<br>
                                    Kallikapumpur, Madurai
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Automated Notice -->
                <div style="text-align: center; margin-bottom: 25px; padding-top: 25px; border-top: 1px solid #2d3748;">
                    <div style="font-size: 13px; color: #94a3b8; line-height: 1.6; max-width: 500px; margin: 0 auto;">
                        This is an automated email from Epicare Construction website enquiry system.<br>
                        For any issues, please contact: 
                        <a href="mailto:info@epicarconstruction.com" style="color: #8EE6B8; text-decoration: none; font-weight: 500;">info@epicarconstruction.com</a>
                    </div>
                </div>
                
                <!-- Company Signature -->
                <div style="text-align: center; padding-top: 25px; border-top: 1px solid #2d3748;">
                    <div style="font-size: 18px; font-weight: 700; color: white; margin-bottom: 5px;">Epicare Construction</div>
                    <div style="font-size: 14px; color: #8EE6B8;">Where Vision Meets Structure</div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Responsive CSS -->
        <style type="text/css">
            @media only screen and (max-width: 600px) {
                body {
                    padding: 10px !important;
                }
                div[style*="padding: 0 40px 30px 40px"],
                div[style*="padding: 30px 40px"] {
                    padding: 20px !important;
                }
                div[style*="display: flex"] {
                    flex-direction: column !important;
                }
                div[style*="width: 40%"],
                div[style*="width: 60%"],
                div[style*="width: 50%"] {
                    width: 100% !important;
                    padding-right: 0 !important;
                    padding-left: 0 !important;
                }
                td[style*="width: 33%"] {
                    width: 100% !important;
                    display: block !important;
                    margin-bottom: 10px !important;
                }
                table[style*="border-spacing: 10px"] {
                    border-spacing: 0 !important;
                }
                div[style*="margin-left: 55px"] {
                    margin-left: 0 !important;
                }
                div[style*="padding-right: 20px"],
                div[style*="padding-left: 20px"] {
                    padding-right: 0 !important;
                    padding-left: 0 !important;
                }
            }
        </style>
    </body>
    </html>';
}

function createCustomerEmail($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enquiry Confirmation - Epicarc Construction</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
                line-height: 1.6;
                color: #333333;
                background-color: #f9fafb;
                padding: 0;
                margin: 0;
            }
            
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
            }
            
            .header {
                background: linear-gradient(135deg, #12223B 0%, #1a2d4f 100%);
                color: #ffffff;
                padding: 40px 30px;
                text-align: center;
            }
            
            .logo {
                max-width: 180px;
                margin: 0 auto 20px;
                display: block;
            }
            
            .header h1 {
                font-size: 32px;
                font-weight: 700;
                margin-bottom: 10px;
                color: #ffffff;
            }
            
            .header p {
                font-size: 18px;
                opacity: 0.9;
                margin: 0;
                color: #e5e7eb;
            }
            
            .content {
                padding: 40px 30px;
            }
            
            .divider {
                height: 1px;
                background: linear-gradient(to right, transparent, #d1d5db, transparent);
                margin: 30px 0;
            }
            
            .section {
                margin-bottom: 30px;
            }
            
            .section-title {
                font-size: 20px;
                font-weight: 600;
                color: #12223B;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .section-title::before {
                content: "";
                width: 4px;
                height: 24px;
                background: #8EE6B8;
                border-radius: 2px;
                display: inline-block;
            }
            
            .confirmation-box {
                background: #f0f9ff;
                border: 1px solid #bae6fd;
                border-radius: 12px;
                padding: 25px;
                text-align: center;
                margin-bottom: 30px;
            }
            
            .confirmation-icon {
                font-size: 48px;
                margin-bottom: 15px;
                display: block;
            }
            
            .confirmation-title {
                font-size: 24px;
                font-weight: 700;
                color: #12223B;
                margin-bottom: 10px;
            }
            
            .confirmation-subtitle {
                font-size: 16px;
                color: #6b7280;
                margin-bottom: 20px;
            }
            
            .reference-box {
                background: #ffffff;
                border: 2px solid #8EE6B8;
                border-radius: 8px;
                padding: 15px;
                margin: 20px auto;
                max-width: 400px;
            }
            
            .reference-label {
                font-size: 14px;
                color: #6b7280;
                margin-bottom: 5px;
                font-weight: 500;
            }
            
            .reference-value {
                font-size: 20px;
                font-weight: 700;
                color: #12223B;
                letter-spacing: 1px;
            }
            
            .description {
                font-size: 16px;
                color: #4b5563;
                line-height: 1.7;
                max-width: 500px;
                margin: 0 auto;
            }
            
            .details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }
            
            .detail-item {
                padding: 20px;
                background: #f8fafc;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
                transition: transform 0.2s ease;
            }
            
            .detail-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            
            .detail-label {
                font-size: 14px;
                color: #6b7280;
                margin-bottom: 8px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .detail-value {
                font-size: 18px;
                color: #12223B;
                font-weight: 500;
            }
            
            .steps-container {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 15px;
                margin-top: 20px;
            }
            
            .step {
                text-align: center;
                padding: 15px;
                background: #f8fafc;
                border-radius: 10px;
                border: 1px solid #e5e7eb;
            }
            
            .step-number {
                font-size: 24px;
                margin-bottom: 10px;
                display: block;
            }
            
            .step-title {
                font-size: 14px;
                color: #12223B;
                font-weight: 600;
            }
            
            .footer {
                background: #1f2937;
                color: #ffffff;
                padding: 40px 30px;
                text-align: center;
            }
            
            .footer-links {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 20px;
                margin-bottom: 30px;
                padding-bottom: 30px;
                border-bottom: 1px solid #374151;
            }
            
            .footer-link {
                color: #ffffff;
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: color 0.2s;
            }
            
            .footer-link:hover {
                color: #8EE6B8;
            }
            
            .footer-note {
                font-size: 14px;
                color: #9ca3af;
                line-height: 1.6;
                max-width: 500px;
                margin: 0 auto 30px;
            }
            
            .contact-info {
                font-size: 14px;
                color: #d1d5db;
                line-height: 1.6;
                margin-bottom: 20px;
            }
            
            .company-signature {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #374151;
            }
            
            .company-name {
                font-size: 20px;
                font-weight: 700;
                color: #ffffff;
                margin-bottom: 5px;
            }
            
            .company-tagline {
                font-size: 14px;
                color: #9ca3af;
            }
            
            .highlight {
                color: #8EE6B8;
                font-weight: 600;
            }
            
            @media (max-width: 768px) {
                .content {
                    padding: 30px 20px;
                }
                
                .header {
                    padding: 30px 20px;
                }
                
                .header h1 {
                    font-size: 28px;
                }
                
                .header p {
                    font-size: 16px;
                }
                
                .details-grid {
                    grid-template-columns: 1fr;
                }
                
                .steps-container {
                    grid-template-columns: repeat(2, 1fr);
                }
                
                .footer-links {
                    flex-direction: column;
                    gap: 10px;
                }
                
                .footer {
                    padding: 30px 20px;
                }
            }
            
            @media (max-width: 480px) {
                .steps-container {
                    grid-template-columns: 1fr;
                }
                
                .confirmation-title {
                    font-size: 20px;
                }
                
                .section-title {
                    font-size: 18px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <!-- Header -->
            <div class="header">
                <img src="https://epicarconstruction.com/assets/images/logo/logo-final.png" alt="Epicarc Construction" class="logo">
                <h1>Thank You, ' . $name . '</h1>
                <p>We have received your enquiry and will contact you shortly.</p>
            </div>
            
            <div class="content">
                <!-- Divider -->
                <div class="divider"></div>
                
                <!-- Confirmation Section -->
                <div class="confirmation-box">
                    <span class="confirmation-icon">✅</span>
                    <h2 class="confirmation-title">Enquiry Confirmed</h2>
                    <p class="confirmation-subtitle">Your enquiry has been successfully submitted</p>
                    
                    <div class="reference-box">
                        <div class="reference-label">Reference Number</div>
                        <div class="reference-value">' . $ref_id . '</div>
                    </div>
                    
                    <p class="description">
                        We appreciate your interest in Epicarc Construction. Our team is reviewing your enquiry and will contact you soon. 
                        If you need to update any details or have additional information, feel free to contact us directly.
                    </p>
                </div>
                
                <!-- Divider -->
                <div class="divider"></div>
                
                <!-- Enquiry Details Section -->
                <div class="section">
                    <h3 class="section-title">Your Enquiry Details</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <div class="detail-label">Full Name</div>
                            <div class="detail-value">' . $name . '</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Email Address</div>
                            <div class="detail-value">' . $email . '</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Phone Number</div>
                            <div class="detail-value">+91 ' . $phone . '</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Project Location</div>
                            <div class="detail-value">' . $location . '</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Contact Method</div>
                            <div class="detail-value">' . ucfirst($contactMethod) . '</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Preferred Time</div>
                            <div class="detail-value">' . ucfirst($contactTime) . '</div>
                        </div>
                    </div>
                </div>
            
            </div>
            
            <!-- Footer -->
            <div class="footer">
                
                <div class="footer-note">
                    This is an automated email. Please do not reply to this message.<br>
                    For immediate assistance, please call <span class="highlight">+91 636 991 1989</span> or visit our site.
                </div>
                
                <div class="contact-info">
                    <strong>Email:</strong> info@epicarconstruction.com<br>
                    <strong>Phone:</strong> +91 636 991 1989 | +91 904 750 8430<br>
                    <strong>Address:</strong> 2/26A, Vellar Street, Kalikappan, Madurai
                </div>
                
                <div class="company-signature">
                    <div class="company-name">Epicarc Construction</div>
                    <div class="company-tagline">Where Vision Meets Structure</div>
                </div>
            </div>
        </div>
    </body>
    </html>';
}


/**
 * Save enquiry to file
 */
function saveEnquiryToFile($ref_id, $name, $email, $phone, $location, $message, $contactMethod, $contactTime, $email_results) {
    $data = [
        'ref_id' => $ref_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'location' => $location,
        'message' => $message,
        'contact_method' => $contactMethod,
        'contact_time' => $contactTime,
        'email_results' => $email_results,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A'
    ];
    
    $file = 'enquiries/' . date('Y-m') . '_enquiries.json';
    if (!file_exists('enquiries')) {
        mkdir('enquiries', 0777, true);
    }
    
    $enquiries = [];
    if (file_exists($file)) {
        $enquiries = json_decode(file_get_contents($file), true) ?? [];
    }
    
    $enquiries[] = $data;
    file_put_contents($file, json_encode($enquiries, JSON_PRETTY_PRINT));
}

/**
 * Get success message
 */
function getSuccessMessage($name, $email_results) {
    $msg = "Thank you, $name! Your enquiry has been submitted successfully.";
    
    if ($email_results['customer']) {
        $msg .= " A confirmation email has been sent to you.";
    }
    
    return $msg;
}