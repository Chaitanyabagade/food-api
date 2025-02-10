<?php
// Define the allowed origins
$allowedOrigins = [
    'http://localhost:3000',
    'https://blindfoodorder.netlify.app'
];

// Get the Origin header from the request
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Check if the origin is in the allowed list
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

$servername = "localhost"; // Change to your database host
$username = "u617164498_food";        // Replace with your database username
$password = "Chaitanya@701";            // Replace with your database password
$dbname = "u617164498_food";    // Replace with your database name
$adminEmail="handekaustubh16@gmail.com";
$adminPass="12345";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo $e->getMessage();
    $conn = null; // Set $conn to null to indicate failure
}
?>