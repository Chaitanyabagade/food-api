<?php
include 'dbconnect.php';
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to fetch all menu items
$sql = "SELECT * FROM menu_items";
$result = mysqli_query($conn, $sql);

// Check if the query was successful
if ($result) {
    // Initialize an array to hold the menu items
    $menuItems = array();

    // Fetch each row as an associative array
    while ($row = mysqli_fetch_assoc($result)) {
        $menuItems[] = $row;
    }

    // Set the Content-Type header to application/json
    header('Content-Type: application/json');

    // Output the data as JSON
    echo json_encode($menuItems);
} else {
    // Handle query error
    echo json_encode(["error" => "Error executing query: " . mysqli_error($conn)]);
}

// Close the database connection
mysqli_close($conn);
?>