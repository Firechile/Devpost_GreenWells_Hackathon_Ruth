<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $cylinder_type = trim($_POST['cylinder_type']);
    $quantity = intval($_POST['quantity']);
    $delivery_option = $_POST['delivery_option'];
    
    // Initialize variables
    $custom_details = '';
    $cylinder_price = 0;
    $delivery_location = '';
    $distance = 0;
    $delivery_fee = 0;
    $errors = [];

    // Handle delivery option
    if ($delivery_option === 'delivery') {
        $delivery_location = trim($_POST['delivery_location']);
        $delivery_fee = floatval($_POST['delivery_fee'] ?? 0);
        
        if (empty($delivery_location)) {
            $errors[] = "Please enter delivery location";
        }
    } else {
        // For pickup option
        $delivery_fee = 0;
        $delivery_location = 'Store Pickup';
    }

    // Validate inputs
    if (empty($cylinder_type)) {
        $errors[] = "Please select a cylinder type";
    }

    if ($quantity < 1) {
        $errors[] = "Quantity must be at least 1";
    }

    // Cylinder prices
    $prices = [
        '3kg' => 500,
        '6kg' => 1000,
        '13kg' => 2000,
        '30kg' => 4000
    ];

    // Calculate cylinder price
    if ($cylinder_type === 'custom') {
        $cylinder_price = 0;
        $custom_details = isset($_POST['custom_details']) ? trim($_POST['custom_details']) : '';
        if (empty($custom_details)) {
            $errors[] = "Please provide custom order details";
        }
    } else {
        if (!isset($prices[$cylinder_type])) {
            $errors[] = "Invalid cylinder type";
        } else {
            $cylinder_price = $prices[$cylinder_type];
        }
    }

    // Calculate total
    $subtotal = $cylinder_price * $quantity;
    $total_price = $subtotal + $delivery_fee;

    // If there are errors, redirect back
    if (!empty($errors)) {
        $_SESSION['order_error'] = implode('. ', $errors);
        header("Location: place_order.php");
        exit();
    }

    // Store order data in session for order_details.php
    $_SESSION['current_order'] = [
        'cylinder_type' => $cylinder_type,
        'quantity' => $quantity,
        'cylinder_price' => $cylinder_price,
        'subtotal' => $subtotal,
        'delivery_option' => $delivery_option,
        'delivery_location' => $delivery_location,
        'distance' => $distance,
        'delivery_fee' => $delivery_fee,
        'total_amount' => $total_price,
        'custom_details' => $custom_details
    ];

    // Insert order into database
    $order_status = 'Pending';
    
    $sql = "INSERT INTO orders (user_id, cylinder_type, quantity, cylinder_price, delivery_location, distance_km, delivery_fee, total_price, order_status, custom_details) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isidddddss", $user_id, 
                                    $cylinder_type, 
                                    $quantity, 
                                    $cylinder_price, 
                                    $delivery_location, 
                                    $distance, 
                                    $delivery_fee, 
                                    $total_price, 
                                    $order_status, 
                                    $custom_details);
    
    if ($stmt->execute()) {
        // Store the order ID in session for order_details.php
        $_SESSION['current_order']['order_id'] = $stmt->insert_id;
        
        // Redirect to order details page
        header("Location: order_details.php");
        exit();
    } else {
        $_SESSION['order_error'] = "Failed to place order. Please try again.";
        header("Location: place_order.php");
        exit();
    }
} else {
    header("Location: place_order.php");
    exit();
}

$conn->close();
?>