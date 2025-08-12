<?php
/**
 * AgriConnect Newsletter Signup Handler
 */

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Rate limiting
$rate_limit_file = '/tmp/newsletter_rate_limit.json';
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!checkNewsletterRateLimit($client_ip, $rate_limit_file, 5)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please try again later.']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Check if already subscribed (simple file-based storage)
$subscribers_file = '/tmp/newsletter_subscribers.json';
$subscribers = [];

if (file_exists($subscribers_file)) {
    $subscribers = json_decode(file_get_contents($subscribers_file), true) ?: [];
}

// Check for existing subscription
foreach ($subscribers as $subscriber) {
    if ($subscriber['email'] === $email) {
        echo json_encode(['success' => true, 'message' => 'Already subscribed!']);
        exit;
    }
}

// Add new subscriber
$subscribers[] = [
    'email' => $email,
    'subscribed_at' => date('Y-m-d H:i:s'),
    'ip' => $client_ip,
    'source' => 'website'
];

// Save subscribers
file_put_contents($subscribers_file, json_encode($subscribers, JSON_PRETTY_PRINT));

// Send notification email to admin
$subject = '[AgriConnect] New Newsletter Subscription';
$message = "
New newsletter subscription:

Email: {$email}
Date: " . date('Y-m-d H:i:s') . "
IP: {$client_ip}

Total subscribers: " . count($subscribers) . "
";

$headers = [
    'From: ' . NEWSLETTER_FROM_EMAIL,
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

mail(ADMIN_EMAIL, $subject, $message, implode("\r\n", $headers));

// Send welcome email to subscriber
$welcome_subject = 'Welcome to AgriConnect Updates!';
$welcome_message = "
Hi there!

Thank you for subscribing to AgriConnect updates. You'll now receive:

- Development progress updates
- Pilot program announcements  
- Agricultural technology insights
- Early access to new features

We're excited to have you join us on our journey to revolutionize African agriculture!

Best regards,
The AgriConnect Team

---
You can unsubscribe anytime by replying to this email.
Follow our progress: https://agriconnect.cm
";

$welcome_headers = [
    'From: ' . NEWSLETTER_FROM_EMAIL,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

mail($email, $welcome_subject, $welcome_message, implode("\r\n", $welcome_headers));

// Log subscription
$log_entry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'type' => 'newsletter_signup',
    'email' => $email,
    'ip' => $client_ip
];

$log_file = '/tmp/agriconnect_newsletter.log';
file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);

echo json_encode([
    'success' => true,
    'message' => 'Successfully subscribed! Check your email for confirmation.'
]);

function checkNewsletterRateLimit($ip, $limit_file, $max_requests) {
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
?>