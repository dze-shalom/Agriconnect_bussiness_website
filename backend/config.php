<?php
/**
 * AgriConnect Configuration
 */

// Email Configuration
define('ADMIN_EMAIL', 'agri.connek@gmail.com');
define('FROM_EMAIL', 'noreply@agriconnect.cm');
define('NEWSLETTER_FROM_EMAIL', 'updates@agriconnect.cm');

// Site Configuration
define('SITE_NAME', 'AgriConnect');
define('SITE_URL', 'https://agriconnect.cm');
define('SITE_DESCRIPTION', 'Building Africa\'s first satellite-powered smart farming platform');

// Form Configuration
define('MAX_MESSAGE_LENGTH', 5000);
define('CONTACT_RATE_LIMIT', 10); // per hour
define('NEWSLETTER_RATE_LIMIT', 5); // per hour

// File Upload Configuration (if needed later)
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('MAX_LOGIN_ATTEMPTS', 5);

// Database Configuration (for future use)
define('DB_HOST', 'localhost');
define('DB_NAME', 'agriconnect');
define('DB_USER', 'agriconnect_user');
define('DB_PASS', ''); // Set in production

// Development/Production Settings
define('ENVIRONMENT', 'development'); // Change to 'production' when live
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Africa/Douala');

// CORS Settings (for API if needed)
$allowed_origins = [
    'https://agriconnect.cm',
    'https://www.agriconnect.cm'
];

if (DEBUG_MODE) {
    $allowed_origins[] = 'http://localhost:8000';
    $allowed_origins[] = 'http://127.0.0.1:8000';
}

// Helper Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function log_activity($type, $data) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    $log_file = '/tmp/agriconnect_activity.log';
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Auto-create necessary directories
$directories = [
    '/tmp/agriconnect_logs',
    '/tmp/agriconnect_uploads',
    '/tmp/agriconnect_cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>