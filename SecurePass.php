
<?php
// password_strength_api.php

// Enforce HTTPS
if ($_SERVER["HTTPS"] != "on") {
    http_response_code(403);
    exit(json_encode(['error' => 'HTTPS is required.']));
}

header('Content-Type: application/json');

// Database Connection for Rate Limiting (Pseudo-code)
$db = new PDO('mysql:host=your_host;dbname=your_db', 'username', 'password');

function rateLimitCheck($db, $ip) {
    // Define the rate limit parameters
    $rateLimit = 5; // Max requests allowed per minute
    $timeFrame = 60; // Time frame in seconds

    // Query to check the number of requests in the last minute
    $stmt = $db->prepare("SELECT COUNT(*) as request_count FROM rate_limit WHERE ip_address = :ip AND timestamp > NOW() - INTERVAL 1 MINUTE");
    $stmt->execute(['ip' => $ip]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data && $data['request_count'] >= $rateLimit) {
        // Rate limit exceeded
        return false;
    }

    // Update the rate limit table with the new request
    $stmt = $db->prepare("INSERT INTO rate_limit (ip_address, timestamp) VALUES (:ip, NOW())");
    $stmt->execute(['ip' => $ip]);

    // Rate limit not exceeded
    return true;
}

function evaluatePasswordStrength($password) {
    $strength = 0;
    $suggestions = [];

    // Length Check
    if (strlen($password) >= 10) {
        $strength += 2;
    } elseif (strlen($password) >= 8) {
        $strength += 1;
        array_push($suggestions, "Increase password length to at least 10 characters for better security.");
    } else {
        array_push($suggestions, "Increase password length to at least 8 characters.");
    }

    // Numeric, Special, Upper & Lowercase Characters Check
    $patterns = [
        'Numeric' => '/[0-9]/',
        'Special' => '/[!@#$%^&*()\-_=+{};:,<.>]/',
        'Uppercase' => '/[A-Z]/',
        'Lowercase' => '/[a-z]/'
    ];

    foreach ($patterns as $type => $pattern) {
        if (preg_match($pattern, $password)) {
            $strength += 1;
        } else {
            array_push($suggestions, "Add $type characters.");
        }
    }

    // Additional Criteria (e.g., no consecutive identical characters)
    if (!preg_match('/(.)\1/', $password)) {
        $strength += 1;
    } else {
        array_push($suggestions, "Avoid using consecutive identical characters.");
    }

    return [
        'strength' => $strength,
        'suggestions' => $suggestions,
    ];
}

$input = json_decode(file_get_contents('php://input'), true);
$password = $input['password'] ?? '';

// Input Validation and Sanitization
if (empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Password is required.']);
    exit;
}

$password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');

// Rate Limiting
$ip = $_SERVER['REMOTE_ADDR'];
if (!rateLimitCheck($db, $ip)) {
    http_response_code(429);
    exit(json_encode(['error' => 'Rate limit exceeded.']));
}

// Evaluating the password strength
$result = evaluatePasswordStrength($password);

// Logging (Pseudo-code)
// logActivity($ip, $result);

echo json_encode($result);
?>
