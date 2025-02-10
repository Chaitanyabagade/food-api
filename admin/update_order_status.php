<?php
include '../dbconnect.php';
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_id = $data["order_id"] ?? '';
$status = $data["status"] ?? '';
$adminem = $data["adminEmail"] ?? '';
if($data['adminEmail']===$adminEmail){
    
if (empty($order_id) || empty($status) || empty($adminem)) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$updateQuery = "UPDATE orders SET status = '$status' WHERE order_id = '$order_id'";
if (mysqli_query($conn, $updateQuery)) {
    echo json_encode(["success" => true, "message" => "Order status updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update order"]);
}
}else {
    echo json_encode(["success" => false, "message" => "Admin is Not Valid"]);
}
mysqli_close($conn);
?>
