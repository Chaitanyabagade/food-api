<?php
include '../dbconnect.php';

if (!$conn) {
    echo "Database connection error. Please try again later.";
    exit; // Stop further execution
}
else{
    echo "database is connected successfully";
}
?>