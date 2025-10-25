<?php
session_start();
require_once 'config.php';

// Check if user is logged in and has an active order
if (!isset($_SESSION['user_id']) || !isset($_SESSION['current_order'])) {
    header("Location: place_order.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$firstname = $_SESSION['firstname'];
$email = $_SESSION['email'];
$companyname = $_SESSION['companyname'];
$order = $_SESSION['current_order'];

// Fetch user details from database using MySQLi
$stmt = $conn->prepare("SELECT * FROM sign_in_log WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: login.php");
    exit();
}

// Get user details
$lastname = $user['lastname'] ?? '';
$phone = $user['phone'] ?? '';
$county = $user['county'] ?? '';
$sub_county = $user['sub_county'] ?? '';
$location = $user['location'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GasConnect</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
    <div class="checkout-page">
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
        <div class="checkout-container">
            <!-- Progress Bar -->
            <div class="progress-bar">
                <div class="progress-step completed">
                    <span class="step-number">1</span>
                    <span class="step-label">Place Order</span>
                </div>
                <div class="progress-step completed">
                    <span class="step-number">2</span>
                    <span class="step-label">Order Details</span>
                </div>
                <div class="progress-step active">
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
                <h1>Secure Checkout</h1>
                <p>Complete your payment and delivery information</p>
            </div>

            <!-- Success/Error Messages -->
            <div id="messageContainer"></div>

            <div class="checkout-grid">
                <!-- Order Summary -->
                <div class="order-summary-section">
                    <h2>Order Summary</h2>
                    <div class="summary-card">
                        <div class="summary-item">
                            <span><?php echo htmlspecialchars(ucfirst($order['cylinder_type'])); ?> x <?php echo $order['quantity']; ?></span>
                            <span>KSH <?php echo number_format($order['subtotal'], 2); ?></span>
                        </div>
                        <?php if ($order['delivery_option'] === 'delivery'): ?>
                        <div class="summary-item">
                            <span>Delivery Fee</span>
                            <span>KSH <?php echo number_format($order['delivery_fee'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="summary-total">
                            <span>Total Amount</span>
                            <strong>KSH <?php echo number_format($order['total_amount'], 2); ?></strong>
                        </div>
                    </div>

                    <div class="order-details">
                        <h3>Order Details</h3>
                        <p><strong>Cylinder:</strong> <?php echo htmlspecialchars(ucfirst($order['cylinder_type'])); ?></p>
                        <p><strong>Quantity:</strong> <?php echo $order['quantity']; ?></p>
                        <p><strong>Delivery:</strong> <?php echo ucfirst($order['delivery_option']); ?></p>
                        <?php if ($order['delivery_option'] === 'delivery'): ?>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($order['delivery_location']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order['custom_details'])): ?>
                        <p><strong>Custom Details:</strong> <?php echo htmlspecialchars($order['custom_details']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="checkout-form-section">
                    <form action="process_checkout.php" method="POST" id="checkoutForm">
                        <h2>Payment Method</h2>
                        <div class="form-group">
                            <div class="payment-options">
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="mpesa" required checked>
                                    <div class="payment-content">
                                        <span class="payment-icon">📱</span>
                                        <div class="payment-text">
                                            <strong>M-Pesa</strong>
                                            <span>Pay via M-Pesa</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <div class="payment-content">
                                        <span class="payment-icon">🏦</span>
                                        <div class="payment-text">
                                            <strong>Bank Transfer</strong>
                                            <span>Direct bank transfer</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <h2>Billing Information</h2>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="firstname">First Name *</label>
                                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastname">Last Name *</label>
                                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required placeholder="e.g., 0712345678">
                            </div>
                            <div class="form-group">
                                <label for="county">County *</label>
                                <input type="text" id="county" name="county" value="<?php echo htmlspecialchars($county); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="sub_county">Sub County *</label>
                                <input type="text" id="sub_county" name="sub_county" value="<?php echo htmlspecialchars($sub_county); ?>" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="location">Full Address *</label>
                                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required placeholder="Enter your complete address">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="special_instructions">Special Instructions (Optional)</label>
                            <textarea id="special_instructions" name="special_instructions" rows="3" placeholder="Any special delivery instructions or notes..."></textarea>
                        </div>

                        <div class="terms-agreement">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" required>
                                <span>I agree to the <a href="#" style="color: #ff9800;">Terms & Conditions</a> and <a href="#" style="color: #ff9800;">Privacy Policy</a></span>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-large">
                            Complete Order & Pay KSH <?php echo number_format($order['total_amount'], 2); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            
            // Show loading state
            btn.innerHTML = 'Processing Payment...';
            btn.disabled = true;
        });

        // Add real-time validation
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[required], textarea[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (!this.value.trim()) {
                        this.style.borderColor = '#f44336';
                    } else {
                        this.style.borderColor = '#e0e6ed';
                    }
                });
            });
        });
    </script>
</body>
</html>