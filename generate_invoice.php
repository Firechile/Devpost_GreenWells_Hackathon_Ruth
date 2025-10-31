<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Define the FPDF font path before including fpdf.php
define('FPDF_FONTPATH', 'fpdf/font/');

require 'fpdf/fpdf.php'; // Ensure fpdf.php is inside /fpdf folder

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    die("Order ID not provided or invalid.");
}

// Fetch order + customer info
$stmt = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.cylinder_type,
        o.quantity,
        o.total_price,
        o.order_status,
        o.order_date,
        o.delivery_option,
        c.full_address,
        c.payment_method,
        c.special_instructions,
        u.companyname
    FROM orders o
    JOIN sign_in_log u ON o.user_id = u.id
    LEFT JOIN customer_profile c ON o.user_id = c.user_id
    WHERE o.id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// Create PDF
class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('images/gasconnect_ylogo.png', 10, 8, 25); // optional: place your logo in /images/
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(30, 58, 95); // Navy
        $this->Cell(0, 10, 'GasConnect Invoice', 0, 1, 'C');
        $this->Ln(8);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Thank you for choosing GasConnect — Delivering Safety, Energy & Reliability.', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);

// Header Info
$pdf->SetFillColor(255, 152, 0); // Orange header block
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, 'Order Summary', 0, 1, 'L', true);
$pdf->Ln(3);

$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, 'Order #: GC' . str_pad($order['order_id'], 6, '0', STR_PAD_LEFT), 0, 1);
$pdf->Cell(0, 8, 'Company: ' . $order['companyname'], 0, 1);
$pdf->Cell(0, 8, 'Order Date: ' . date('F j, Y, g:i a', strtotime($order['order_date'])), 0, 1);
$pdf->Ln(5);

// Billing Section
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(30, 58, 95);
$pdf->Cell(0, 10, 'Billing Details', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);

$pdf->Cell(0, 8, 'Address: ' . ($order['full_address'] ?? 'N/A'), 0, 1);
$pdf->Cell(0, 8, 'Payment Method: ' . ucfirst(str_replace('_', ' ', $order['payment_method'] ?? 'N/A')), 0, 1);
// Add this after payment method in the PDF:
$pdf->Cell(0, 8, 'Delivery Option: ' . ucfirst($order['delivery_option'] ?? 'N/A'), 0, 1);
if (!empty($order['special_instructions'])) {
    $pdf->MultiCell(0, 8, 'Notes: ' . $order['special_instructions']);
}
$pdf->Ln(5);

// Order Details
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(30, 58, 95);
$pdf->Cell(0, 10, 'Order Details', 0, 1);
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(60, 10, 'Cylinder Type', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Quantity', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Total (KSH)', 1, 1, 'C', true);

$pdf->Cell(60, 10, $order['cylinder_type'], 1, 0, 'C');
$pdf->Cell(40, 10, $order['quantity'], 1, 0, 'C');
$pdf->Cell(50, 10, number_format($order['total_price'], 2), 1, 1, 'C');
$pdf->Ln(10);

// Order Status
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(30, 58, 95);
$pdf->Cell(0, 10, 'Order Status: ' . ucfirst($order['order_status']), 0, 1);
$pdf->Ln(10);

// Footer contact info
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(0, 6,
    "GasConnect | Customer Service: +254 700 000 000 | support@gasconnect.co.ke\n" .
    "Physical Address: Industrial Area, Nairobi | www.gasconnect.co.ke"
);

// Output
$pdf->Output('D', 'GasConnect_Invoice_' . $order['order_id'] . '.pdf');
exit;
?>
