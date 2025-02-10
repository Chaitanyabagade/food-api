<?php
 
include '../dbconnect.php';
 
if (!$conn) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}
if($_POST['adminEmail']===$adminEmail){
$query = "SELECT * FROM orders WHERE status NOT IN ('delivered', 'canceled')  ";
$result = mysqli_query($conn, $query);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

echo json_encode($orders);
}
else{
    echo json_encode([]);
}
$conn->close();
?>
