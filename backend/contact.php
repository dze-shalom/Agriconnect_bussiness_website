<?php
/**
 * AgriConnect Contact Form Handler
 * Handles contact form submissions and sends emails
 */

// Set content type
header('Content-Type: application/json');

// Enable CORS for development (remove in production)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Configuration
$config = [
    'admin_email' => 'agri.connek@gmail.com',
    'from_email' => 'noreply@agriconnect.cm',
    'subject_prefix' => '[AgriConnect] ',
    'max_message_length' => 5000,
    'required_fields' => ['name', 'email', 'message']
];

// Rate limiting (simple file-based)
$rate_limit_file = '/tmp/agriconnect_rate_limit.json';
$max_requests_per_hour = 10;
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

function checkRateLimit($ip, $limit_file, $max_requests) {
    if (!file_exists($limit_file)) {
        file_put_contents($limit_file, json_encode([]));
    }
    
    $rate_data = json_decode(file_get_contents($limit_file), true) ?: [];
    $current_time = time();
    $hour_ago = $current_time - 3600;
    
    // Clean old entries
    foreach ($rate_data as $recorded_ip => $timestamps) {
        $rate_data[$recorded_ip] = array_filter($timestamps, function($timestamp) use ($hour_ago) {
            return $timestamp > $hour_ago;
        });
        if (empty($rate_data[$recorded_ip])) {
            unset($rate_data[$recorded_ip]);
        }
    }
    
    // Check current IP
    if (!isset($rate_data[$ip])) {
        $rate_data[$ip] = [];
    }
    
    if (count($rate_data[$ip]) >= $max_requests) {
        return false;
    }
    
    // Add current request
    $rate_data[$ip][] = $current_time;
    file_put_contents($limit_file, json_encode($rate_data));
    
    return true;
}

// Check rate limit
if (!checkRateLimit($client_ip, $rate_limit_file, $max_requests_per_hour)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.']);
    exit;
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validation function
function validateInput($data, $config) {
    $errors = [];
    
    // Check required fields
    foreach ($config['required_fields'] as $field) {
        if (empty($data[$field])) {
            $errors[] = "Field '{$field}' is required";
        }
    }
    
    // Validate email
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address';
    }
    
    // Validate message length
    if (!empty($data['message']) && strlen($data['message']) > $config['max_message_length']) {
        $errors[] = 'Message is too long';
    }
    
    // Basic spam detection
    $spam_keywords = ['viagra', 'casino', 'loan', 'bitcoin', 'cryptocurrency'];
    $message_lower = strtolower($data['message'] ?? '');
    foreach ($spam_keywords as $keyword) {
        if (strpos($message_lower, $keyword) !== false) {
            $errors[] = 'Message appears to be spam';
            break;
        }
    }
    
    return $errors;
}

// Validate input
$validation_errors = validateInput($input, $config);
if (!empty($validation_errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Validation failed', 'details' => $validation_errors]);
    exit;
}

// Sanitize input
$name = htmlspecialchars(trim($input['name']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
$phone = htmlspecialchars(trim($input['phone'] ?? ''), ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars(trim($input['subject'] ?? 'General Inquiry'), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($input['message']), ENT_QUOTES, 'UTF-8');
$form_type = htmlspecialchars(trim($input['form_type'] ?? 'contact'), ENT_QUOTES, 'UTF-8');

// Determine email subject based on form type
$email_subjects = [
    'contact' => 'New Contact Form Submission',
    'pilot' => 'New Pilot Program Interest',
    'partnership' => 'New Partnership Inquiry',
    'investment' => 'New Investment Inquiry',
    'newsletter' => 'New Newsletter Subscription'
];

$email_subject = $config['subject_prefix'] . ($email_subjects[$form_type] ?? 'New Form Submission');

// Create email content
$email_content = "
New submission from AgriConnect website

Form Type: " . ucfirst($form_type) . "
Name: {$name}
Email: {$email}
Phone: {$phone}
Subject: {$subject}

Message:
{$message}

---
Submitted: " . date('Y-m-d H:i:s') . "
IP Address: {$client_ip}
User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "
";

// Email headers
$headers = [
    'From: ' . $config['from_email'],
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Send email
$mail_sent = mail(
    $config['admin_email'],
    $email_subject,
    $email_content,
    implode("\r\n", $headers)
);

if ($mail_sent) {
    // Log successful submission
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'form_type' => $form_type,
        'email' => $email,
        'name' => $name,
        'ip' => $client_ip
    ];
    
    $log_file = '/tmp/agriconnect_submissions.log';
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    
    // Send auto-response to user (optional)
    if ($form_type === 'pilot') {
        $auto_response_subject = 'Thank you for your interest in AgriConnect Pilot Program';
        $auto_response_content = "
Dear {$name},

Thank you for expressing interest in the AgriConnect Pilot Program!

We've received your application and our team will review it carefully. We'll contact you when pilot applications officially open in Q2 2025.

In the meantime, feel free to:
- Follow our progress at https://agriconnect.cm/progress.html
- Learn more about our technology at https://agriconnect.cm/technology.html
- Contact us if you have any questions

Best regards,
The AgriConnect Team

---
This is an automated response. Please do not reply to this email.
For questions, contact us at agri.connek@gmail.com or +237 650 218 174
";
        
        $auto_headers = [
            'From: ' . $config['from_email'],
            'X-Mailer: PHP/' . phpversion(),
            'Content-Type: text/plain; charset=UTF-8'
        ];
        
        mail($email, $auto_response_subject, $auto_response_content, implode("\r\n", $auto_headers));
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully!'
    ]);
} else {
    error_log("AgriConnect contact form: Failed to send email to {$config['admin_email']}");
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send message. Please try again or contact us directly.'
    ]);
}
?>