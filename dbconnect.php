<?php

$servername = "localhost"; // Change to your database host
$username = "root";        // Replace with your database username
$password = "";            // Replace with your database password
$dbname = "food";    // Replace with your database name

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