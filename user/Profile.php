<?php
include "../dbconnect.php"; // Your database connection file

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["message" => "Invalid request"]);
    exit;
}

$firstname = $data['firstname'];
$lastname = $data['lastname'];
$email = $data['email'];
$street = $data['street'];
$city = $data['city'];
$state = $data['state'];
$postal_code = $data['postal_code'];
$country = $data['country'];
$is_default = $data['is_default'] ? 1 : 0;

$sqlUser = "SELECT user_id FROM users WHERE firstname = ? AND lastname = ? AND email = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("sss", $firstname, $lastname, $email);
$stmtUser->execute();
$result = $stmtUser->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["message" => "User not found"]);
    exit;
}

$user_id = $user['user_id'];

// Update existing address for the user
$sqlUpdate = "UPDATE address SET street = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE user_id = ?";
$stmtUpdate = $conn->prepare($sqlUpdate);
$stmtUpdate->bind_param("ssssssi", $street, $city, $state, $postal_code, $country, $is_default, $user_id);

if ($stmtUpdate->execute()) {
    // Update isProfileSet to 1 in users table
    $sqlProfileUpdate = "UPDATE users SET isProfileSet = 1 WHERE user_id = ?";
    $stmtProfileUpdate = $conn->prepare($sqlProfileUpdate);
    $stmtProfileUpdate->bind_param("i", $user_id);
    
    if ($stmtProfileUpdate->execute()) {
        echo json_encode(["message" => "Address updated and profile set successfully"]);
    } else {
        echo json_encode(["message" => "Address updated, but failed to update profile status"]);
    }

    $stmtProfileUpdate->close();
} else {
    echo json_encode(["message" => "Error updating address"]);
}

$stmtUser->close();
$stmtUpdate->close();
$conn->close();
?>
