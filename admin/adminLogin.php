<?php

include '../dbconnect.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
    exit;
}

// Check database connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Validate input
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : null;
$admin_password = $_POST['password'] ?? null;

if (!$email || !$admin_password) {
    http_response_code(201); // Bad Request
    echo json_encode(["status_code" => '400', "status" => "error", "message" => "Email and password are required."]);
    exit;
}

if ($admin_password=== $adminPass && $email=== $adminEmail) {
        http_response_code(200);
        echo json_encode([
            "status_code" => '200',
            "status" => "success",
            "message" => "Logged In",
            "email" => $email,
        ]);
} else {
        http_response_code(201); // Unauthorized
        echo json_encode(["status_code" => '401', "status" => "fail", "message" => "Password is incorrect"]);
    }

$conn->close();

?>
