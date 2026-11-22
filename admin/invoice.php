<?php
session_start();
require 'functions.php';
role_check('admin');
require '../database/config.php';
$conn->set_charset('utf8mb4');

$orderId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($orderId === false || $orderId === null || $orderId < 1) {
    http_response_code(400);
    exit('Invalid order number.');
}

$order = $conn->query(
    "SELECT o.*, u.name AS customer_name, u.email AS customer_email,
            p.payment_method, p.transaction_id, p.status AS payment_status
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     LEFT JOIN payments p ON p.id = (
         SELECT p2.id FROM payments p2
         WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
     )
     WHERE o.id = " . (int) $orderId . " LIMIT 1"
)->fetch_assoc();

if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

$items = $conn->query(
    "SELECT product_name, unit_price, quantity, line_total
     FROM order_items WHERE order_id = " . (int) $orderId . " ORDER BY id"
);

function invoice_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function invoice_money($value)
{
    return '৳' . number_format((float) $value, 2);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice <?= invoice_e($order['order_number']) ?> · Bloom Basket</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; color: #242424; }
        .invoice { max-width: 850px; margin: 32px auto; padding: 40px; background: #fff; }
        @media print { body { background: #fff; } .invoice { margin: 0 auto; padding: 0; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <main class="invoice">
        <div class="d-flex justify-content-between align-items-start mb-5">
            <div><h2 class="mb-1">Bloom Basket</h2><div>Order invoice</div></div>
            <div class="text-end">
                <strong><?= invoice_e($order['order_number']) ?></strong>
                <div><?= invoice_e(date('M j, Y', strtotime($order['created_at']))) ?></div>
                <button class="btn btn-dark btn-sm mt-3 no-print" onclick="window.print()">Print Invoice</button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-sm-6">
                <h6>Recipient</h6>
                <div><?= invoice_e($order['recipient_name']) ?></div>
                <div><?= invoice_e($order['phone']) ?></div>
                <div><?= invoice_e(implode(', ', array_filter([$order['shipping_address'], $order['area'], $order['city'], $order['postal_code']]))) ?></div>
            </div>
            <div class="col-sm-6">
                <h6>Customer & payment</h6>
                <div><?= invoice_e($order['customer_name']) ?></div>
                <div><?= invoice_e($order['customer_email']) ?></div>
                <div>Method: <?= invoice_e(ucfirst($order['payment_method'] ?? '—')) ?></div>
                <div>Status: <?= invoice_e(ucfirst($order['payment_status'] ?? '—')) ?></div>
                <?php if ($order['transaction_id']): ?><div>Transaction: <?= invoice_e($order['transaction_id']) ?></div><?php endif; ?>
            </div>
        </div>

        <table class="table">
            <thead><tr><th>Product</th><th class="text-end">Unit price</th><th class="text-end">Qty</th><th class="text-end">Total</th></tr></thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?= invoice_e($item['product_name']) ?></td>
                        <td class="text-end"><?= invoice_money($item['unit_price']) ?></td>
                        <td class="text-end"><?= (int) $item['quantity'] ?></td>
                        <td class="text-end"><?= invoice_money($item['line_total']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="ms-auto" style="max-width: 280px">
            <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= invoice_money($order['subtotal']) ?></span></div>
            <div class="d-flex justify-content-between"><span>Delivery</span><span><?= invoice_money($order['delivery_fee']) ?></span></div>
            <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>Total</span><span><?= invoice_money($order['total_amount']) ?></span></div>
        </div>
        <p class="text-center text-muted mt-5 mb-0">Thank you for choosing Bloom Basket.</p>
    </main>
</body>
</html>
