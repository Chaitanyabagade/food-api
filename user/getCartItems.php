<?php

include '../dbconnect.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user information from POST data
    $firstname = $_POST['firstname'] ?? null;
    $lastname = $_POST['lastname'] ?? null;
    $email = $_POST['email'] ?? null;

    // Validate required fields
    if ($firstname && $lastname && $email) {
        // Sanitize inputs to prevent SQL injection
        $firstname = $conn->real_escape_string($firstname);
        $lastname = $conn->real_escape_string($lastname);
        $email = $conn->real_escape_string($email);

        // Prepare a statement to find the user_id
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE firstname = ? AND lastname = ? AND email = ?");
        $stmt->bind_param("sss", $firstname, $lastname, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $user_id = $user['user_id'];

            // Prepare a statement to fetch the cart items
            $stmt = $conn->prepare("SELECT items FROM cart WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $cart = $result->fetch_assoc();

            if ($cart) {
                // Return the cart items as JSON
                echo json_encode(['status' => 'success', 'cart' => json_decode($cart['items'])]);
            } else {
                // Cart not found
                echo json_encode(['status' => 'error', 'message' => 'Cart not found']);
            }
        } else {
            // User not found
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } else {
        // Missing required fields
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    }
} else {
    // Invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Close the connection
$conn->close();
?>
