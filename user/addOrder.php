<?php
include '../dbconnect.php';
if (!$conn) {
    die(json_encode(["error" => "Database connection failed: " . mysqli_connect_error()]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['firstname'], $data['lastname'], $data['email'])) {
        echo json_encode(["error" => "Missing required fields"]);
        exit;
    }

    $firstname = mysqli_real_escape_string($conn, trim($data['firstname']));
    $lastname = mysqli_real_escape_string($conn, trim($data['lastname']));
    $email = mysqli_real_escape_string($conn, trim($data['email']));

    // Step 1: Get the user_id
    $query = "SELECT user_id, isProfileSet FROM users WHERE firstname = '$firstname' AND lastname = '$lastname' AND email = '$email'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo json_encode(["error" => "User not found"]);
        exit;
    }
    
    $user_id = $user['user_id'];
    
    if ($user['isProfileSet'] == 0) {
        echo json_encode(["error" => "Please add the address first"]);
        exit;
    }

    // Step 2: Get default address_id
    $query = "SELECT address_id FROM address WHERE user_id = '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $address = mysqli_fetch_assoc($result);

    if (!$address) {
        echo json_encode(["error" => "No default address found for user"]);
        exit;
    }

    $address_id = $address['address_id'];

    // Step 3: Get items from the cart
    $query = "SELECT items FROM cart WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $cart = mysqli_fetch_assoc($result);

    if (!$cart) {
        echo json_encode(["error" => "Cart is empty"]);
        exit;
    }

    $items_json = $cart['items'];
    $items = json_decode($items_json, true);

    if (empty($items)) {
        echo json_encode(["error" => "Your cart is empty"]);
        exit;
    }

    // Step 4: Calculate total price
    $total_price = 0;
    foreach ($items as $item) {
        $total_price += (isset($item['price']) ? floatval($item['price']) : 0) * $item["qty"];
    }
    $total_price += 49; // Delivery fees

    // Step 5: Insert order into orders table
    $query = "INSERT INTO orders (user_id, address_id, items, total_price) VALUES ('$user_id', '$address_id', '$items_json', '$total_price')";
    if (!mysqli_query($conn, $query)) {
        echo json_encode(["error" => "Failed to place order: " . mysqli_error($conn)]);
        exit;
    }

    $order_id = mysqli_insert_id($conn);

    // Step 6: Clear the cart after order placement
    $query = "UPDATE `cart` SET `items` = '[]' WHERE `user_id` = '$user_id'";
    mysqli_query($conn, $query);

    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "message" => "Order placed successfully",
        "total_price" => $total_price
    ]);
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

$conn->close();
?>
