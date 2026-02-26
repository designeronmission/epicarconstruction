<?php
// ========== LOCAL TEST VERSION ==========
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

// Prevent CORS issues
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
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
    
    // ========== SAVE FOR LOCAL TESTING ==========
    $data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ref_id' => $ref_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'location' => $location,
        'message' => $message,
        'contact_method' => $contactMethod,
        'contact_time' => $contactTime,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'localhost',
        'test_environment' => true
    ];
    
    // Save to file
    if (!file_exists('test_enquiries')) {
        mkdir('test_enquiries', 0777, true);
    }
    
    $filename = 'test_enquiries/' . $ref_id . '.json';
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    
    // ========== CREATE EMAIL PREVIEWS ==========
    createEmailPreviews($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date);
    
    // ========== RESPONSE ==========
    $response = [
        'success' => true,
        'message' => '✅ LOCAL TEST SUCCESSFUL!<br><br>' .
                    '👤 <strong>Name:</strong> ' . $name . '<br>' .
                    '📧 <strong>Email:</strong> ' . $email . '<br>' .
                    '📱 <strong>Phone:</strong> +91 ' . $phone . '<br>' .
                    '📍 <strong>Location:</strong> ' . $location . '<br>' .
                    '📞 <strong>Contact Method:</strong> ' . ucfirst($contactMethod) . '<br>' .
                    '⏰ <strong>Preferred Time:</strong> ' . ucfirst($contactTime) . '<br>' .
                    '💬 <strong>Message:</strong> ' . nl2br($message) . '<br><br>' .
                    '<div style="background:#e3f2fd;padding:15px;border-radius:8px;margin:10px 0;">' .
                    '📋 <strong>Reference ID:</strong> ' . $ref_id . '<br>' .
                    '📅 <strong>Submitted:</strong> ' . $submission_date . '<br>' .
                    '🌐 <strong>Environment:</strong> Local Test (XAMPP)' .
                    '</div>' .
                    '<div style="background:#fff3cd;padding:15px;border-radius:8px;margin:10px 0;">' .
                    '📧 <strong>Email Simulation:</strong><br>' .
                    '• On live server, emails would be sent to:<br>' .
                    '&nbsp;&nbsp;↳ Admin: info@epicarconstruction.com<br>' .
                    '&nbsp;&nbsp;↳ Customer: ' . $email . '<br>' .
                    '• <a href="email_preview_admin.html" target="_blank" style="color:#3498db;">View Admin Email Preview</a><br>' .
                    '• <a href="email_preview_customer.html" target="_blank" style="color:#3498db;">View Customer Email Preview</a>' .
                    '</div>',
        'ref_id' => $ref_id,
        'emails_sent' => [
            'admin' => false,
            'customer' => false,
            'note' => 'Local test - emails would be sent on live server'
        ],
        'data' => $data
    ];
    
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => '❌ Error: ' . $e->getMessage()
    ];
    http_response_code(400);
    echo json_encode($response);
}

// ========== HELPER FUNCTIONS ==========
function createEmailPreviews($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date) {
    // Admin Email Preview
    $admin_html = createAdminEmailHTML($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date);
    file_put_contents('email_preview_admin.html', $admin_html);
    
    // Customer Email Preview
    $customer_html = createCustomerEmailHTML($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date);
    file_put_contents('email_preview_customer.html', $customer_html);
}

function createAdminEmailHTML($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date) {
    // ... (copy the HTML template from previous send_email.php admin section)
    // Return the full HTML
    return '<!DOCTYPE html>...'; // Your admin email HTML here
}

function createCustomerEmailHTML($name, $email, $phone, $location, $message, $contactMethod, $contactTime, $ref_id, $submission_date) {
    // ... (copy the HTML template from previous send_email.php customer section)
    // Return the full HTML
    return '<!DOCTYPE html>...'; // Your customer email HTML here
}

exit;
?>