<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_order_id'])) {
    header("Location: place_order.php");
    exit();
}

$order_id = $_SESSION['last_order_id'];
$user_id = $_SESSION['user_id'];
$firstname = $_SESSION['firstname'];

// Fetch order details using MySQLi
$stmt = $conn->prepare("
    SELECT o.id AS order_id,
        o.total_price,
        o.order_status,
        o.cylinder_type,
        o.quantity,
        o.delivery_location,
        o.custom_details,
        o.updated_at,
        o.delivery_option,
        u.companyname,
        c.full_address,
        c.payment_method,
        c.special_instructions
    FROM orders o
    JOIN sign_in_log u ON o.user_id = u.id
    LEFT JOIN customer_profile c ON o.user_id = c.user_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: dashboard.php");
    exit();
}

// Add delivery option handling after fetching the order
$delivery_option = $order['delivery_option'] ?? $_SESSION['delivery_option'] ?? 'delivery';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Complete - GasConnect</title>
    <link rel="stylesheet" href="order_complete.css">
</head>
<body>
    <div class="order-complete-page">
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
        <div class="order-complete-container">
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
                <div class="progress-step completed">
                    <span class="step-number">3</span>
                    <span class="step-label">Checkout</span>
                </div>
                <div class="progress-step active">
                    <span class="step-number">4</span>
                    <span class="step-label">Complete</span>
                </div>
            </div>

            <div class="success-content">
                <div class="success-icon">🎉</div>
                <h1>Order Placed Successfully!</h1>
                <p class="success-message">Thank you for your order. Your gas cylinders will be processed shortly.</p>

            <div class="auto-redirect-message" 
                style="margin-top: 40px; text-align: center; font-size: 0.95rem; color: #1e3a5f;">
                <p>
                    You will be redirected to your dashboard in 
                    <span id="countdown" style="font-weight: bold; color: #ff9800;">30</span> seconds.
                </p>
                <button onclick="cancelRedirect()" 
                    style="background-color: #1e3a5f; color: white; border: none; 
                 padding: 10px 18px; border-radius: 8px; cursor: pointer; 
                 font-weight: 500;">
                Stay on this page
                 </button>
            </div>

                <div class="order-info-grid">
                    <div class="info-card">
                        <h3>Order Details</h3>
                        <div class="info-item">
                            <span>Order ID:</span>
                            <strong>#GC<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                        </div>
                        <div class="info-item">
                            <span>Total Amount:</span>
                            <strong>KSH <?php echo number_format($order['total_price'], 2); ?></strong>
                        </div>
                        <div class="info-item">
                            <span>Payment Method:</span>
                            <strong><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></strong>
                        </div>
                        <div class="info-item">
                            <span>Delivery Option:</span>
                            <strong><?php echo ucfirst($delivery_option); ?></strong>
                        </div>
                    </div>

                    <div class="timeline-card">
                        <h3>Order Timeline</h3>
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div class="timeline-marker">✓</div>
                                <div class="timeline-content">
                                    <strong>Order Confirmed</strong>
                                    <span>We've received your order</span>
                                </div>
                            </div>
                            <div class="timeline-item active">
                                <div class="timeline-marker">2</div>
                                <div class="timeline-content">
                                    <strong>Processing</strong>
                                    <span>Preparing your order</span>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker">3</div>
                                <div class="timeline-content">
                                    <strong>On the Way</strong>
                                    <span>Your order is out for delivery</span>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-marker">4</div>
                                <div class="timeline-content">
                                    <strong>Delivered</strong>
                                    <span>Order successfully delivered</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="notification-card">
                    <div class="notification-icon">⏰</div>
                    <div class="notification-content">
                        <h4>What Happens Next?</h4>
                        <p>You will receive order confirmation and updates within <strong>30 minutes to 1 hour</strong> via SMS and email.</p>
                        <?php if ($delivery_option === 'delivery'): ?>
                        <p>You can track your package once it's on its way to you.</p>
                        <?php else: ?>
                        <p>You will be notified when your order is ready for pickup.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="generate_invoice.php?order_id=<?php echo $order['order_id']; ?>" ...>
                        📄 Download Invoice
                    </a>
                    <?php if ($delivery_option === 'delivery'): ?>
                    <a href="track_order.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary">
                        🚚 Track Package
                    </a>
                    <?php endif; ?>
                    <a href="dashboard.php" class="btn btn-outline">
                        ← Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

<!-- Optional auto-redirect notice -->


<script>
  let countdown = 600;
  const timer = setInterval(() => {
    countdown--;
    document.getElementById('countdown').textContent = countdown;
    if (countdown === 0) {
      clearInterval(timer);
      window.location.href = 'dashboard.php';
    }
  }, 1000);

  function cancelRedirect() {
    clearInterval(timer);
    const msg = document.querySelector('.auto-redirect-message');
    msg.innerHTML = "<p style='color: green;'>Auto-redirect canceled ✅ You can stay on this page.</p>";
  }
</script>

</body>
</html>