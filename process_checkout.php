<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has an active order
if (!isset($_SESSION['user_id']) || !isset($_SESSION['current_order'])) {
    header("Location: place_order.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $order_data = $_SESSION['current_order'];
    
    // Get form data
    $payment_method = $_POST['payment_method'];
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $county = trim($_POST['county']);
    $sub_county = trim($_POST['sub_county']);
    $location = trim($_POST['location']);
    $special_instructions = trim($_POST['special_instructions'] ?? '');
// Get delivery option from form (if provided by the user)
    $delivery_option = $_POST['delivery_option'] ?? 'pickup'; // default value if not set
        if (!in_array($delivery_option, ['pickup', 'delivery'])) {
            $delivery_option = 'pickup';
        }

// Store delivery option in session so it can be accessed later (e.g. on order_complete.php)
$_SESSION['delivery_option'] = $delivery_option;

    
    $errors = [];

    // Validate required fields
    if (empty($firstname)) $errors[] = "First name is required";
    if (empty($lastname)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($phone)) $errors[] = "Phone number is required";
    if (empty($county)) $errors[] = "County is required";
    if (empty($sub_county)) $errors[] = "Sub county is required";
    if (empty($location)) $errors[] = "Address is required";

    if (!empty($errors)) {
        $_SESSION['checkout_error'] = implode('. ', $errors);
        header("Location: checkout.php");
        exit();
    }

//Update order table and customer profile table
$order_id = $order_data['order_id'] ?? 0;
$order_status = 'confirmed';

if ($order_id > 0) {
    // update order status
    $order_stmt = $conn->prepare("
        UPDATE orders 
        SET order_status = ?, delivery_option = ?, updated_at = NOW()
        WHERE id = ? AND user_id = ?
    ");

    $order_stmt->bind_param("ssii", $order_status, $delivery_option, $order_id, $user_id);
    $order_success = $order_stmt->execute();

// Update or insert customer profile details
$profile_stmt = $conn->prepare("
    INSERT INTO customer_profile (
        user_id, 
        last_name,
        phone, 
        county, 
        sub_county, 
        full_address, 
        payment_method, 
        special_instructions) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        last_name = VALUES(last_name),
        phone = VALUES(phone),
        county = VALUES(county),
        sub_county = VALUES(sub_county),
        full_address = VALUES(full_address),
        payment_method = VALUES(payment_method),
        special_instructions = VALUES(special_instructions),
        updated_at = NOW()
");

$profile_stmt->bind_param(
    "isssssss",
    $user_id,
    $lastname,
    $phone,
    $county,
    $sub_county,
    $location,
    $payment_method,
    $special_instructions
);

$profile_success = $profile_stmt->execute();

//Check bother operations succeeded      
if ($order_success && $profile_success) {
            // Store final order ID in session for order_complete.php
            $_SESSION['last_order_id'] = $order_id;
            
            // Clear current order from session
            unset($_SESSION['current_order']);
            
            // Redirect to order complete page
            header("Location: order_complete.php");
            exit();
        } else {
            $_SESSION['checkout_error'] = "Failed to process payment. Please try again.";
            header("Location: checkout.php");
            exit();
        }
    } 

} else {
    $_SESSION['checkout_error'] = "Order not found. Please start over";
    header("Location: checkout.php");
    exit();
}
?>