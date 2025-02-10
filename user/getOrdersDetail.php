<?php
include '../dbconnect.php';

if (!$conn) {
    echo json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]);
    exit;
}

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data and decode it from JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Check for required fields
    if (!isset($data['firstname'], $data['lastname'], $data['email'], $data['limit'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    // Sanitize user input
    $firstname = mysqli_real_escape_string($conn, trim($data['firstname']));
    $lastname  = mysqli_real_escape_string($conn, trim($data['lastname']));
    $email     = mysqli_real_escape_string($conn, trim($data['email']));
    $limit     = intval($data['limit']);  // Convert limit to integer

    // Step 1: Get the user_id from the users table
    $sql = "SELECT user_id FROM users WHERE firstname = '$firstname' AND lastname = '$lastname' AND email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo json_encode(["error" => "User not found"]);
        exit;
    }

    $userRow = mysqli_fetch_assoc($result);
    $user_id = $userRow['user_id'];

    // Step 2: Retrieve orders for this user_id in descending order with a limit
    // Ensure to limit results based on the request
    $sqlOrders = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY order_time DESC LIMIT $limit";
    $resultOrders = mysqli_query($conn, $sqlOrders);

    if (!$resultOrders) {
        echo json_encode(["error" => "Failed to retrieve orders: " . mysqli_error($conn)]);
        exit;
    }

    // Gather all orders into an array
    $orders = [];
    while ($order = mysqli_fetch_assoc($resultOrders)) {
        $orders[] = $order;
    }

    // Step 3: Return the orders in a JSON response
    echo json_encode([
        "success" => true,
        "orders"  => $orders
    ]);

} else {
    echo json_encode(["error" => "Invalid request method"]);
}

// Close the database connection
mysqli_close($conn);
?>
