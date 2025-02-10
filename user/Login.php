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
$user_password = $_POST['password'] ?? null;

if (!$email || !$user_password) {
    http_response_code(400); // Bad Request
    echo json_encode(["status_code" => '400', "status" => "error", "message" => "Email and password are required."]);
    exit;
}

// Check if the email exists
$sqlCheck = "SELECT password_hash, user_id, firstname, lastname,isProfileSet FROM users WHERE email = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    $stmtCheck->bind_result($password_hash, $userId, $firstName, $lastName,$isProfileSet);
    $stmtCheck->fetch();

    // Use password_verify() if passwords are stored using password_hash()
    if (password_verify($user_password, $password_hash)) {
        http_response_code(200);
        echo json_encode([
            "status_code" => '200',
            "status" => "success",
            "message" => "Logged In",
            "userId" => $userId,
            "email" => $email,
            "firstName" => $firstName,
            "lastName" => $lastName,
            "isProfileSet"=>$isProfileSet
        ]);
    } else {
        http_response_code(201); // Unauthorized
        echo json_encode(["status_code" => '401', "status" => "fail", "message" => "Password is incorrect"]);
    }
} else {
    http_response_code(201); // Not Found
    echo json_encode(["status_code" => '404', "status" => "fail", "message" => "Account does not exist"]);
}

// Close connection
$stmtCheck->close();
$conn->close();

?>
