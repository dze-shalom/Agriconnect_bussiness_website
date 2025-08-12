<?php
/**
 * AgriConnect Pilot Program Signup Handler
 * Handles pilot program applications with comprehensive validation
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

// Rate limiting for pilot applications
$rate_limit_file = '/tmp/pilot_rate_limit.json';
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!checkPilotRateLimit($client_ip, $rate_limit_file, 3)) { // Max 3 applications per hour
    http_response_code(429);
    echo json_encode(['error' => 'Too many applications. Please try again later.']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Required fields for pilot program
$required_fields = [
    'firstName', 'lastName', 'email', 'phone', 
    'farmLocation', 'farmSize', 'primaryCrop', 
    'farmingExperience', 'currentIrrigation', 
    'motivation', 'techComfort'
];

// Validate required fields
$missing_fields = [];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Missing required fields',
        'missing_fields' => $missing_fields
    ]);
    exit;
}

// Sanitize input data
$application_data = [
    'firstName' => sanitize_input($input['firstName']),
    'lastName' => sanitize_input($input['lastName']),
    'email' => filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL),
    'phone' => sanitize_input($input['phone']),
    'farmLocation' => sanitize_input($input['farmLocation']),
    'farmSize' => sanitize_input($input['farmSize']),
    'primaryCrop' => sanitize_input($input['primaryCrop']),
    'farmingExperience' => sanitize_input($input['farmingExperience']),
    'currentIrrigation' => sanitize_input($input['currentIrrigation']),
    'motivation' => sanitize_input($input['motivation']),
    'biggestChallenge' => sanitize_input($input['biggestChallenge'] ?? ''),
    'techComfort' => sanitize_input($input['techComfort']),
    'commitment' => isset($input['commitment']) ? 'Yes' : 'No',
    'dataConsent' => isset($input['dataConsent']) ? 'Yes' : 'No'
];

// Validate email
if (!filter_var($application_data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Validate phone number (basic Cameroon format)
$phone_clean = preg_replace('/[^\d+]/', '', $application_data['phone']);
if (!preg_match('/^(\+237)?[26]\d{8}$/', $phone_clean)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid phone number format for Cameroon']);
    exit;
}

// Validate farm location (must be in SouthWest Region)
$valid_locations = [
    'Buea', 'Limbe', 'Manfe', 'Kumba', 'Tiko', 
    'eséka', 'mfou', 'obala', 'ntui', 'monatele'
];
$location_valid = false;
foreach ($valid_locations as $location) {
    if (stripos($application_data['farmLocation'], $location) !== false) {
        $location_valid = true;
        break;
    }
}

// Check if farm size meets minimum requirement
$valid_farm_sizes = ['0.5-5', '6-10', '11-20', '21-50', '50+'];
if (!in_array($application_data['farmSize'], $valid_farm_sizes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid farm size selection']);
    exit;
}

// Check commitments
if ($application_data['commitment'] !== 'Yes' || $application_data['dataConsent'] !== 'Yes') {
    http_response_code(400);
    echo json_encode(['error' => 'Pilot program commitments must be accepted']);
    exit;
}

// Save application to file (you can replace this with database later)
$applications_file = '/tmp/pilot_applications.json';
$applications = [];

if (file_exists($applications_file)) {
    $applications = json_decode(file_get_contents($applications_file), true) ?: [];
}

// Check for duplicate application
foreach ($applications as $app) {
    if ($app['email'] === $application_data['email']) {
        echo json_encode([
            'success' => true,
            'message' => 'Application already received! We\'ll contact you when pilot applications open.'
        ]);
        exit;
    }
}

// Add timestamp and application ID
$application_data['application_id'] = 'PILOT_' . date('Ymd') . '_' . substr(md5($application_data['email']), 0, 6);
$application_data['submitted_at'] = date('Y-m-d H:i:s');
$application_data['ip_address'] = $client_ip;
$application_data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$application_data['status'] = 'pending_review';

// Calculate application score (for prioritization)
$score = calculateApplicationScore($application_data);
$application_data['score'] = $score;

// Add to applications array
$applications[] = $application_data;

// Save applications
file_put_contents($applications_file, json_encode($applications, JSON_PRETTY_PRINT));

// Send notification email to admin
$admin_subject = '[AgriConnect] New Pilot Program Application - ' . $application_data['application_id'];
$admin_message = createAdminNotificationEmail($application_data);

$admin_headers = [
    'From: ' . FROM_EMAIL,
    'Reply-To: ' . $application_data['email'],
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/html; charset=UTF-8'
];

mail(ADMIN_EMAIL, $admin_subject, $admin_message, implode("\r\n", $admin_headers));

// Send confirmation email to applicant
$applicant_subject = 'Thank you for your AgriConnect Pilot Program Interest!';
$applicant_message = createApplicantConfirmationEmail($application_data);

$applicant_headers = [
    'From: ' . FROM_EMAIL,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/html; charset=UTF-8'
];

mail($application_data['email'], $applicant_subject, $applicant_message, implode("\r\n", $applicant_headers));

// Log the application
log_activity('pilot_application', [
    'application_id' => $application_data['application_id'],
    'email' => $application_data['email'],
    'farm_location' => $application_data['farmLocation'],
    'farm_size' => $application_data['farmSize'],
    'score' => $score
]);

// Send success response
echo json_encode([
    'success' => true,
    'message' => 'Application submitted successfully! We\'ll contact you when pilot applications open in Q2 2025.',
    'application_id' => $application_data['application_id']
]);

// Helper Functions

function checkPilotRateLimit($ip, $limit_file, $max_requests) {
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

function calculateApplicationScore($data) {
    $score = 0;
    
    // Farm size scoring (larger farms get higher priority for testing)
    $size_scores = [
        '2-5' => 5,
        '6-10' => 8,
        '11-20' => 10,
        '21-50' => 7,
        '50+' => 5
    ];
    $score += $size_scores[$data['farmSize']] ?? 0;
    
    // Experience scoring
    $exp_scores = [
        '1-2' => 3,
        '3-5' => 7,
        '6-10' => 10,
        '10+' => 8
    ];
    $score += $exp_scores[$data['farmingExperience']] ?? 0;
    
    // Technology comfort scoring
    $tech_scores = [
        'beginner' => 5,
        'intermediate' => 8,
        'advanced' => 10
    ];
    $score += $tech_scores[$data['techComfort']] ?? 0;
    
    // Crop diversity bonus
    if (in_array($data['primaryCrop'], ['vegetables', 'other'])) {
        $score += 3;
    }
    
    // Motivation length bonus (shows commitment)
    if (strlen($data['motivation']) > 200) {
        $score += 2;
    }
    
    return $score;
}

function createAdminNotificationEmail($data) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: #2e7d32; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .field { margin-bottom: 15px; }
            .field strong { color: #2e7d32; }
            .score { background: #e8f5e9; padding: 10px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>New Pilot Program Application</h2>
            <p>Application ID: {$data['application_id']}</p>
        </div>
        
        <div class='content'>
            <div class='score'>
                <strong>Application Score: {$data['score']}/25</strong>
                (Higher scores indicate better fit for pilot program)
            </div>
            
            <h3>Personal Information</h3>
            <div class='field'><strong>Name:</strong> {$data['firstName']} {$data['lastName']}</div>
            <div class='field'><strong>Email:</strong> {$data['email']}</div>
            <div class='field'><strong>Phone:</strong> {$data['phone']}</div>
            
            <h3>Farm Information</h3>
            <div class='field'><strong>Location:</strong> {$data['farmLocation']}</div>
            <div class='field'><strong>Size:</strong> {$data['farmSize']} hectares</div>
            <div class='field'><strong>Primary Crop:</strong> {$data['primaryCrop']}</div>
            <div class='field'><strong>Experience:</strong> {$data['farmingExperience']} years</div>
            <div class='field'><strong>Current Irrigation:</strong> {$data['currentIrrigation']}</div>
            <div class='field'><strong>Tech Comfort:</strong> {$data['techComfort']}</div>
            
            <h3>Motivation</h3>
            <div class='field'>{$data['motivation']}</div>
            
            " . (!empty($data['biggestChallenge']) ? "<h3>Biggest Challenge</h3><div class='field'>{$data['biggestChallenge']}</div>" : "") . "
            
            <h3>Application Details</h3>
            <div class='field'><strong>Submitted:</strong> {$data['submitted_at']}</div>
            <div class='field'><strong>Commitments Accepted:</strong> {$data['commitment']}</div>
            <div class='field'><strong>Data Consent:</strong> {$data['dataConsent']}</div>
        </div>
    </body>
    </html>
    ";
}

function createApplicantConfirmationEmail($data) {
    return "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .header { background: #2e7d32; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .highlight { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Thank You for Your Interest!</h2>
            <p>AgriConnect Pilot Program</p>
        </div>
        
        <div class='content'>
            <p>Dear {$data['firstName']},</p>
            
            <p>Thank you for expressing interest in the AgriConnect Pilot Program! We've received your application and are excited about your enthusiasm for agricultural innovation.</p>
            
            <div class='highlight'>
                <strong>Your Application ID:</strong> {$data['application_id']}<br>
                <strong>Submitted:</strong> {$data['submitted_at']}<br>
                <strong>Status:</strong> Under Review
            </div>
            
            <h3>What Happens Next?</h3>
            <ul>
                <li><strong>Q2 2025:</strong> Pilot applications will officially open</li>
                <li><strong>Selection Process:</strong> We'll review all applications and select 50 pilot farms</li>
                <li><strong>Notification:</strong> Selected participants will be contacted directly</li>
                <li><strong>Installation:</strong> Complete system setup at no cost to selected farms</li>
            </ul>
            
            <h3>Selection Criteria</h3>
            <p>We'll prioritize applications based on:</p>
            <ul>
                <li>Farm size and location suitability</li>
                <li>Farming experience and technology readiness</li>
                <li>Commitment to the 6-month program</li>
                <li>Potential for providing valuable feedback</li>
            </ul>
            
            <h3>Stay Connected</h3>
            <p>In the meantime, we encourage you to:</p>
            <ul>
                <li>Follow our progress at <a href='https://agriconnect.cm/progress.html'>agriconnect.cm/progress</a></li>
                <li>Learn about our technology at <a href='https://agriconnect.cm/technology.html'>agriconnect.cm/technology</a></li>
                <li>Contact us with any questions</li>
            </ul>
            
            <p>We appreciate your interest in transforming African agriculture through technology!</p>
            
            <p>Best regards,<br>
            The AgriConnect Team</p>
        </div>
        
        <div class='footer'>
            <p><strong>Contact Us:</strong> agri.connek@gmail.com | +237 695 465 755</p>
            <p>This is an automated confirmation. Please save your Application ID for reference.</p>
        </div>
    </body>
    </html>
    ";
}
?>