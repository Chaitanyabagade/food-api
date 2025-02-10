<?php
include '../dbconnect.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Retrieve additional form fields
    $email = $_POST['email'] ?? null;
    $firstname = $_POST['firstname'] ?? null;
    $lastname = $_POST['lastname'] ?? null;

    // Check if a file was uploaded
    if (isset($_FILES['jsonData']) && $_FILES['jsonData']['error'] === UPLOAD_ERR_OK) {
        // Read the JSON data from the uploaded file
        $jsonData = file_get_contents($_FILES['jsonData']['tmp_name']);
        $cartItems = json_decode($jsonData, true);

        // Validate required fields
        if ($email && $firstname && $lastname ) {
            // Prepare a statement to find the user_id
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE firstname = ? AND lastname = ? AND email = ?");
            $stmt->bind_param("sss", $firstname, $lastname, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $user_id = $user['user_id'];

                // Update existing cart
                $stmt = $conn->prepare("UPDATE cart SET items = ?, added_at = CURRENT_TIMESTAMP WHERE user_id = ?");
                $items_json = json_encode($cartItems);
                $stmt->bind_param("si", $items_json, $user_id);
                $stmt->execute();

                // Send a success response
                echo json_encode(['status' => 'success', 'message' => 'Cart updated successfully']);
            } else {
                // User not found
                echo json_encode(['status' => 'error', 'message' => 'User not found']);
            }
        } else {
            // Missing required fields
            echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        }
    } else {
        // File upload error
        echo json_encode(['status' => 'error', 'message' => 'File upload error']);
    }
} else {
    // Invalid request method
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

// Close the connection
$conn->close();
?>