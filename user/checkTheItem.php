<?php

include '../dbconnect.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_name = $_POST['product_name']; // The product name sent in the request

// Check if product_name is provided
if (isset($product_name)) {
    // Perform the natural language search using FULLTEXT search
    $sql = "SELECT * FROM menu_items 
            WHERE MATCH(name, description) AGAINST(?) 
            ORDER BY MATCH(name, description) AGAINST(?) DESC LIMIT 1";
    
    // Prepare and bind
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $product_name, $product_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if a result is found
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            echo json_encode($item); // Return the item as JSON response
        } else {
            // No match found
            echo json_encode(["error" => "No matching item found"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["error" => "Database query error"]);
    }
} else {
    echo json_encode(["error" => "Product name is required"]);
}

$conn->close();
?>
