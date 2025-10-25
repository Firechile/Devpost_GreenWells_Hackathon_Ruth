<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has an active order
if (!isset($_SESSION['user_id']) || !isset($_SESSION['current_order'])) {
    header("Location: place_order.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$email = $_SESSION['email'];
$companyname = $_SESSION['companyname'];

// Get order from session
$order = $_SESSION['current_order'];

// Calculate delivery fee if delivery option is selected
$delivery_fee = 0;
if ($order['delivery_option'] === 'delivery') {
    // Simple delivery fee calculation
    $delivery_fee = $order['distance'] * 50; // KSH 50 per km
    $delivery_fee = max(200, min($delivery_fee, 2000)); // Min 200, Max 2000
}

$total_amount = $order['subtotal'] + $delivery_fee;

// Update order with calculated values
$_SESSION['current_order']['delivery_fee'] = $delivery_fee;
$_SESSION['current_order']['total_amount'] = $total_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - GasConnect</title>
    <link rel="stylesheet" href="order_details.css">
</head>
<body>
    <div class="order-details-page">
        <!-- Header -->
        <div class="order-header">
            <div class="header-content">
                <div class="logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32">
                        <path d="M12.5 2C9.5 2 7 4.5 7 7.5c0 1.3.5 2.5 1.2 3.5L12 18l3.8-7c.7-1 1.2-2.2 1.2-3.5C17 4.5 14.5 2 12.5 2zm0 7.5c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" fill="#ff9800"/>
                        <path d="M12 22s-8-5-8-10h2c0 3.5 5 7.5 6 8.3.8-.6 6-4.8 6-8.3h2c0 5-8 10-8 10z" fill="#1e3a5f"/>
                    </svg>
                    <span>GasConnect</span>
                </div>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($firstname); ?></span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="order-details-container">
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Place Order</span>
                </div>
                <div class="progress-step active">
                    <span class="step-number">2</span>
                    <span class="step-label">Order Details</span>
                </div>
                <div class="progress-step">
                    <span class="step-number">3</span>
                    <span class="step-label">Checkout</span>
                </div>
                <div class="progress-step">
                    <span class="step-number">4</span>
                    <span class="step-label">Complete</span>
                </div>
            </div>

            <!-- Page Title -->
            <div class="page-title">
                <h1>Order Details</h1>
                <p>Review your order before proceeding to checkout</p>
            </div>

            <!-- Success/Error Messages -->
            <div id="messageContainer"></div>

            <!-- Section 1: Order Summary -->
            <div class="order-section">
                <h2>📦 Order Summary</h2>
                <div class="order-summary">
                    <div class="summary-item">
                        <span>Cylinder Type:</span>
                        <strong><?php echo htmlspecialchars(ucfirst($order['cylinder_type'])); ?></strong>
                    </div>
                    <?php if ($order['cylinder_type'] === 'custom' && !empty($order['custom_details'])): ?>
                    <div class="summary-item">
                        <span>Custom Details:</span>
                        <strong><?php echo htmlspecialchars($order['custom_details']); ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span>Quantity:</span>
                        <strong><?php echo $order['quantity']; ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Price per Cylinder:</span>
                        <strong>KSH <?php echo number_format($order['cylinder_price'], 2); ?></strong>
                    </div>
                    <div class="summary-item total">
                        <span>Subtotal:</span>
                        <strong>KSH <?php echo number_format($order['subtotal'], 2); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Section 2: Delivery Information -->
            <?php if ($order['delivery_option'] === 'delivery'): ?>
            <div class="order-section">
                <h2>🚚 Delivery Information</h2>
                <div class="delivery-summary">
                    <div class="summary-item">
                        <span>Delivery Option:</span>
                        <strong>Home Delivery</strong>
                    </div>
                    <div class="summary-item">
                        <span>Delivery Location:</span>
                        <strong><?php echo htmlspecialchars($order['delivery_location']); ?></strong>
                    </div>
                    <div class="summary-item">
                        <span>Distance:</span>
                        <strong><?php echo number_format($order['distance'], 2); ?> km</strong>
                    </div>
                    <div class="summary-item">
                        <span>Delivery Fee:</span>
                        <strong>KSH <?php echo number_format($delivery_fee, 2); ?></strong>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="order-section">
                <h2>🚗 Pickup Information</h2>
                <div class="pickup-summary">
                    <div class="summary-item">
                        <span>Delivery Option:</span>
                        <strong>Customer Pickup</strong>
                    </div>
                    <div class="summary-item">
                        <span>Pickup Location:</span>
                        <strong>Our Main Office - Nairobi CBD</strong>
                    </div>
                    <div class="summary-item">
                        <span>Pickup Hours:</span>
                        <strong>Monday - Friday, 8:00 AM - 5:00 PM</strong>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section 3: Total Amount -->
            <div class="order-section total-section">
                <h2>💰 Total Amount</h2>
                <div class="total-summary">
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span>KSH <?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <?php if ($order['delivery_option'] === 'delivery'): ?>
                    <div class="summary-item">
                        <span>Delivery Fee:</span>
                        <span>KSH <?php echo number_format($delivery_fee, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-item final-total">
                        <span>Total Amount:</span>
                        <strong>KSH <?php echo number_format($total_amount, 2); ?></strong>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="place_order.php" class="btn btn-secondary">← Edit Order</a>
                <button type="button" class="btn btn-primary" onclick="proceedToCheckout()">Proceed to Checkout →</button>
            </div>
        </div>
    </div>

    <script>
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
            messageDiv.textContent = message;
            
            messageContainer.innerHTML = '';
            messageContainer.appendChild(messageDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }

        function proceedToCheckout() {
            // Show loading state
            const btn = document.querySelector('.btn-primary');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Processing...';
            btn.disabled = true;

            // Simple validation
            const order = <?php echo json_encode($order); ?>;
            
            if (!order.cylinder_type) {
                showMessage('Please select a cylinder type', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            if (order.cylinder_type === 'custom' && !order.custom_details) {
                showMessage('Please provide custom order details', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            if (order.quantity < 1) {
                showMessage('Quantity must be at least 1', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
                return;
            }

            // If all validations pass, redirect to checkout
            showMessage('Order confirmed! Redirecting to checkout...', 'success');
            
            setTimeout(() => {
                window.location.href = 'checkout.php';
            }, 1000);
        }

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to total amount
            const totalElement = document.querySelector('.final-total strong');
            if (totalElement) {
                totalElement.style.transition = 'all 0.3s ease';
                totalElement.style.display = 'inline-block';
                
                setTimeout(() => {
                    totalElement.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        totalElement.style.transform = 'scale(1)';
                    }, 300);
                }, 500);
            }

            // Add confirmation dialog for going back
            const backButton = document.querySelector('.btn-secondary');
            backButton.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to edit your order? Any changes will require re-confirmation.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>