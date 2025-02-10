<?php
include '../dbconnect.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Only POST requests are allowed."]);
    exit;
}


// Check connection
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$email = $_POST['email'];
$mobile = $_POST['mobile'];
$otp = $_POST['otp'];
$user_password = $_POST['password'];

if (!$email || !$user_password || !$firstName || !$lastName || !$mobile || !$otp) {
    http_response_code(201); // Bad Request
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Check if the email already exists
$sqlCheck = "SELECT * FROM users WHERE email = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    http_response_code(201); // Conflict
    echo json_encode(["status" => "error", "message" => "Email already exists."]);
    $stmtCheck->close();
    $conn->close();
    exit;
}
$stmtCheck->close();

// Prepare SQL query to validate OTP and check expiry
$sql = "SELECT otp, expires_at FROM otp_table WHERE email = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to prepare SQL statement."]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(201);
    echo json_encode(["status" => "error", "message" => "Send OTP First."]);
    $stmt->close();
    $conn->close();
    exit;
}

// Fetch data
$data = $result->fetch_assoc();
$storedOtp = $data['otp'];
$expiresAt = $data['expires_at'];

// Validate OTP and expiry
$currentDateTime = date('Y-m-d H:i:s');
if ($otp === $storedOtp) {
    if ($currentDateTime <= $expiresAt) {
        // OTP is valid, proceed with signup
        $sql3 = "DELETE FROM otp_table WHERE email = ?";
        $stmtDelete = $conn->prepare($sql3);
        $stmtDelete->bind_param("s", $email);
        $stmtDelete->execute();

        // Hash the password
        $hashpass = password_hash($user_password, PASSWORD_BCRYPT);

        // Insert the user
        $sqltosavedata = "INSERT INTO users (firstname, lastname, email, password_hash, phone, isProfileSet, created_at) VALUES (?, ?, ?, ?, ?, '0', CURRENT_TIMESTAMP())";
        $stmtInsert = $conn->prepare($sqltosavedata);
        $stmtInsert->bind_param("sssss", $firstName, $lastName, $email, $hashpass, $mobile);
        $stmtInsert->execute();

        $sqlforuserid = "SELECT user_id FROM users WHERE email = '$email' AND phone = '$mobile'";
        $result = $conn->query($sqlforuserid);
     
        // Check if user exists and extract the user_id from the first row
        if ($result->num_rows > 0) {
            // Fetch the first row
            $data = $result->fetch_assoc();
            $user_id = $data['user_id']; // Extract the user_id
  
            $sqlcart = "INSERT INTO `cart` (`cart_id`, `user_id`, `items`, `added_at`) VALUES (NULL, '$user_id', '[]', current_timestamp())";
            $sqladdress = "INSERT INTO `address` (`address_id`, `user_id`, `street`, `city`, `state`, `postal_code`, `country`, `is_default`) VALUES (NULL, '$user_id', '', '', '', '', '', '0')";

            $conn->query($sqlcart);
            $conn->query($sqladdress);
        }



      


        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "SignUp Success"]);
    } else {
        http_response_code(201); // Bad Request
        echo json_encode(["status" => "error", "message" => "OTP has expired."]);
    }
} else {
    http_response_code(201); // Invalid OTP
    echo json_encode(["status" => "error", "message" => "Invalid OTP."]);
}

// Close connections
$stmt->close();
$conn->close();
?>