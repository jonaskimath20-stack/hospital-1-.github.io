<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'hospital_management_system');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/hms/');  // Badilisha 'hms' iwe jina la folder yako

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Function to redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to sanitize input
function sanitize($data) {
    global $conn;
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return mysqli_real_escape_string($conn, htmlspecialchars(trim(strip_tags($data))));
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

// Function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Function to generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Function to verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to display CSRF token field
function csrf_field() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}
?>