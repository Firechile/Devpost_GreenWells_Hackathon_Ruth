<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$email = $_SESSION['email'];
$companyname = $_SESSION['companyname'];
$avatar_letter = strtoupper(substr($firstname, 0, 1));

// Cylinder prices
$prices = [
    '3kg' => 500,
    '6kg' => 1000,
    '13kg' => 2000,
    '30kg' => 4000
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order</title>
    <link rel="stylesheet" href="place_order.css">
    <script type="text/javascript" src="order_validation.js" defer></script>
</head>
<body>    
<div class="sidebar">
        <div class="logo-section">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12.5 2C9.5 2 7 4.5 7 7.5c0 1.3.5 2.5 1.2 3.5L12 18l3.8-7c.7-1 1.2-2.2 1.2-3.5C17 4.5 14.5 2 12.5 2zm0 7.5c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
                    <path d="M12 22s-8-5-8-10h2c0 3.5 5 7.5 6 8.3.8-.6 6-4.8 6-8.3h2c0 5-8 10-8 10z"/>
                </svg>
                <span>GasConnect</span>
            </div>
        </div>

        <div class="nav-section">
            <a href="dashboard.php" class="nav-item active">
                <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 24 24" width="22px" fill="currentColor">
                    <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
                </svg>
                Dashboard
            </a>

            <a href="#" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 24 24" width="22px" fill="currentColor">
                    <path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/>
                </svg>
                Orders
            </a>

            <a href="#" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 24 24" width="22px" fill="currentColor">
                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                </svg>
                Invoices
            </a>

            <a href="#" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" height="22px" viewBox="0 0 24 24" width="22px" fill="currentColor">
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Profile
            </a>
        </div>

        <div class="logout-section">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>


<!-- Order Modal -->
    <div id="orderModal" class="modal active">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📦 Place Order</h2>
                <button class="close-btn" onclick="closeOrderModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="process_order.php" method="post" id="orderForm" onsubmit="return validateForm()">
                    <div class="form-group">
                        <label for="cylinder_type">Cylinder Size</label>
                        <select name="cylinder_type" id="cylinder_type" required >
                            <option value="">Select cylinder size</option>
                            <option value="3kg" data-price="500">3 KG - KSH 500</option>
                            <option value="6kg" data-price="1000">6 KG - KSH 1,000</option>
                            <option value="13kg" data-price="2000">13 KG - KSH 2,000</option>
                            <option value="30kg" data-price="4000">30 KG - KSH 4,000</option>
                            <option value="custom">Custom Order</option>
                        </select>
                    </div>

                    <div class="form-group" id="customOrderGroup" style="display: none;">
                        <label for="custom_details">Custom Order Details</label>
                        <input type="text" name="custom_details" id="custom_details" placeholder="Describe your custom order">
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" id="quantity" min="1" value="1">
                    </div>

                    <!-- Delivery Option -->
                <div class="form-group">
                    <label>Delivery Option</label>
                    <div style="display: flex; gap: 20px; margin-top: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="delivery_option" value="pickup" id="pickupOption" checked onchange="toggleDeliveryLocation()">
                            <span>I will pickup</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="delivery_option" value="delivery" id="deliveryOption" onchange="toggleDeliveryLocation()">
                            <span>Deliver to me</span>
                        </label>
                    </div>
                </div>

                    <div class="form-group" id="deliveryLocationGroup" style="display: none;">
                        <label for="delivery_location">Delivery Location</label>
                        <input type="text" name="delivery_location" id="delivery_location" placeholder="Enter your delivery address">
                        <small style="color: #64748b; font-size: 13px; margin-top: 5px; display: block;">
                            💡 Distance will be calculated automatically
                        </small>
                    </div>

                    <!-- Hidden input for distance - will be calculated by Python later -->
                    <input type="hidden" name="distance" id="distance" value="0">

                    <!-- Add this hidden input for delivery fee in your form -->
                    <input type="hidden" name="delivery_fee" id="deliveryFeeInput" value="0">

                    <div class="price-display" id="priceDisplay">
                        <div class="price-row">
                            <span>Cylinder Price:</span>
                            <span id="cylinderPrice">KSH 0</span>
                        </div>
                        <div class="price-row">
                            <span>Quantity:</span>
                            <span id="quantityDisplay">0</span>
                        </div>
                        <div class="price-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">KSH 0</span>
                        </div>
                        <div class="price-row" id="deliveryFeeRow" style="color: #64748b">
                            <span>Delivery Fee:</span>
                            <span id="deliveryFeeDisplay">KSH 0.00</span>
                        </div>
                        <div class="price-row total">
                            <span>Total:</span>
                            <span id="totalPrice">KSH 0</span>
                        </div>
                    </div>

                    <button type="button" class="submit-btn" onclick="handleFormSubmit()">Place Order</button>
                </form>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('order_validation.js loaded');
    });
</script>

</body>
</html>