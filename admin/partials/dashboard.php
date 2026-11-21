<?php
$stats = $conn->query(
    "SELECT
        (SELECT COUNT(*) FROM orders) AS total_orders,
        (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success') AS revenue,
        (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers,
        (SELECT COUNT(*) FROM products WHERE is_active = 1) AS products"
)->fetch_assoc();

$monthlyOrders = [];
$monthlyResult = $conn->query(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total
     FROM orders
     WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 11 MONTH), '%Y-%m-01')
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')"
);
while ($row = $monthlyResult->fetch_assoc()) {
    $monthlyOrders[$row['month_key']] = (int) $row['total'];
}

$months = [];
$orderCounts = [];
$monthStart = new DateTimeImmutable('first day of this month');
for ($i = 11; $i >= 0; $i--) {
    $month = $monthStart->modify("-$i months");
    $key = $month->format('Y-m');
    $months[] = $month->format('M');
    $orderCounts[] = $monthlyOrders[$key] ?? 0;
}

$paymentLabels = [];
$paymentCounts = [];
$paymentResult = $conn->query(
    "SELECT payment_method, COUNT(*) AS total
     FROM payments
     GROUP BY payment_method
     ORDER BY total DESC"
);
while ($row = $paymentResult->fetch_assoc()) {
    $paymentLabels[] = ucfirst($row['payment_method']);
    $paymentCounts[] = (int) $row['total'];
}

$recentOrders = $conn->query(
    "SELECT o.id, o.order_number, o.recipient_name, o.total_amount, o.status, o.created_at,
            u.name AS customer_name,
            GROUP_CONCAT(DISTINCT oi.product_name ORDER BY oi.id SEPARATOR ', ') AS products
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     LEFT JOIN order_items oi ON oi.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC, o.id DESC
     LIMIT 8"
);

$topProducts = $conn->query(
    "SELECT product_name, SUM(quantity) AS sold
     FROM order_items
     GROUP BY product_name
     ORDER BY sold DESC, product_name
     LIMIT 5"
);

$stockProducts = $conn->query(
    "SELECT name, stock
     FROM products
     WHERE is_active = 1
     ORDER BY stock ASC, name
     LIMIT 5"
);

$statusClasses = [
    'pending' => 'bg-light text-warning',
    'confirmed' => 'bg-light text-primary',
    'processing' => 'bg-light text-info',
    'shipped' => 'bg-light text-info',
    'delivered' => 'bg-light text-success',
    'cancelled' => 'bg-light text-danger',
];

function dashboard_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function dashboard_money($value)
{
    return '৳' . number_format((float) $value, 2);
}
?>
<script>
window.dashboardData = <?= json_encode([
    'months' => $months,
    'orders' => $orderCounts,
    'paymentLabels' => $paymentLabels,
    'paymentCounts' => $paymentCounts,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>

<div class="page-wrapper">
    <div class="page-content">
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">
            <div class="col">
                <div class="card radius-10"><div class="card-body">
                    <p class="mb-1">Total Orders</p>
                    <h4 class="mb-0"><?= number_format((int) $stats['total_orders']) ?></h4>
                </div></div>
            </div>
            <div class="col">
                <div class="card radius-10"><div class="card-body">
                    <p class="mb-1">Paid Revenue</p>
                    <h4 class="mb-0"><?= dashboard_money($stats['revenue']) ?></h4>
                </div></div>
            </div>
            <div class="col">
                <div class="card radius-10"><div class="card-body">
                    <p class="mb-1">Customers</p>
                    <h4 class="mb-0"><?= number_format((int) $stats['customers']) ?></h4>
                </div></div>
            </div>
            <div class="col">
                <div class="card radius-10"><div class="card-body">
                    <p class="mb-1">Active Products</p>
                    <h4 class="mb-0"><?= number_format((int) $stats['products']) ?></h4>
                </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Orders · Last 12 Months</h5>
                        <div style="height:280px"><canvas id="chart1"></canvas></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Payment Methods</h5>
                        <?php if ($paymentLabels !== []): ?>
                            <div style="height:220px"><canvas id="chart2"></canvas></div>
                        <?php else: ?>
                            <p class="mb-0">No payments yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-8">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Recent Orders</h5>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>Order</th><th>Products</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php if ($recentOrders->num_rows === 0): ?>
                                        <tr><td colspan="6" class="text-center">No orders yet.</td></tr>
                                    <?php else: ?>
                                        <?php while ($order = $recentOrders->fetch_assoc()): ?>
                                            <?php $status = (string) $order['status']; ?>
                                            <tr>
                                                <td><a href="orders.php?view=<?= (int) $order['id'] ?>"><?= dashboard_e($order['order_number']) ?></a></td>
                                                <td><?= dashboard_e($order['products'] ?: '—') ?></td>
                                                <td><?= dashboard_e($order['customer_name'] ?: $order['recipient_name']) ?></td>
                                                <td><?= dashboard_e(date('M j, Y', strtotime($order['created_at']))) ?></td>
                                                <td><?= dashboard_money($order['total_amount']) ?></td>
                                                <td><span class="badge rounded-pill <?= dashboard_e($statusClasses[$status] ?? 'bg-light text-secondary') ?>"><?= dashboard_e(ucfirst($status)) ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Best Sellers</h5>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light"><tr><th>Product</th><th>Sold</th></tr></thead>
                                <tbody>
                                    <?php if ($topProducts->num_rows === 0): ?>
                                        <tr><td colspan="2">No sales yet.</td></tr>
                                    <?php else: ?>
                                        <?php while ($product = $topProducts->fetch_assoc()): ?>
                                            <tr><td><?= dashboard_e($product['product_name']) ?></td><td><?= number_format((int) $product['sold']) ?></td></tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Stock Levels</h5>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light"><tr><th>Product</th><th>Stock</th></tr></thead>
                                <tbody>
                                    <?php while ($product = $stockProducts->fetch_assoc()): ?>
                                        <tr><td><?= dashboard_e($product['name']) ?></td><td><?= number_format((int) $product['stock']) ?></td></tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
