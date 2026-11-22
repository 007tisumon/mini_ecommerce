<?php
session_start();
require 'functions.php';
role_check('admin');
require '../database/config.php';
$conn->set_charset('utf8mb4');

$statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
$badgeClasses = [
    'pending' => 'bg-light text-warning',
    'confirmed' => 'bg-light text-primary',
    'processing' => 'bg-light text-info',
    'shipped' => 'bg-light text-info',
    'delivered' => 'bg-light text-success',
    'cancelled' => 'bg-light text-danger',
];

function order_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function order_money($value)
{
    return '৳' . number_format((float) $value, 2);
}

$search = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$statusFilter = $_GET['status'] ?? '';
if (!in_array($statusFilter, $statuses, true)) {
    $statusFilter = '';
}
$searchSql = $conn->real_escape_string($search);
$statusSql = $conn->real_escape_string($statusFilter);
$where = 'WHERE 1';

if ($search !== '') {
    $where .= " AND (o.order_number LIKE '%$searchSql%'
        OR o.recipient_name LIKE '%$searchSql%'
        OR o.phone LIKE '%$searchSql%'
        OR u.email LIKE '%$searchSql%')";
}
if ($statusFilter !== '') {
    $where .= " AND o.status = '$statusSql'";
}

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';

    if ($orderId > 0 && in_array($newStatus, $statuses, true)) {
        $result = $conn->query("SELECT status FROM orders WHERE id = $orderId");
        $order = $result->fetch_assoc();

        if ($order) {
            $oldStatus = $order['status'];
            if ($oldStatus !== $newStatus) {
                $oldStatusSql = $conn->real_escape_string($oldStatus);
                $newStatusSql = $conn->real_escape_string($newStatus);
                $adminName = $conn->real_escape_string($_SESSION['name'] ?? 'Admin');
                $note = $conn->real_escape_string("Status changed from $oldStatus to $newStatus by $adminName.");

                $conn->query("UPDATE orders SET status = '$newStatusSql' WHERE id = $orderId");
                $conn->query("INSERT INTO order_status_history
                    (order_id, changed_by, old_status, new_status, note)
                    VALUES ($orderId, NULL, '$oldStatusSql', '$newStatusSql', '$note')");
            }

            $message = $oldStatus === $newStatus
                ? 'The order already has that status.'
                : 'Order status updated to ' . ucfirst($newStatus) . '.';

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'status' => $newStatus,
                    'status_label' => ucfirst($newStatus),
                    'badge_class' => $badgeClasses[$newStatus],
                ]);
                exit;
            }
        }
    }

    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Could not update the order status.']);
        exit;
    }

    $query = http_build_query(['q' => $search, 'status' => $statusFilter]);
    header('Location: orders.php?' . $query);
    exit;
}

$ordersResult = $conn->query(
    "SELECT o.id, o.order_number, o.recipient_name, o.phone, o.total_amount,
            o.status, o.created_at, u.email AS customer_email,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
            p.payment_method, p.status AS payment_status
     FROM orders o
     LEFT JOIN users u ON u.id = o.user_id
     LEFT JOIN payments p ON p.id = (
         SELECT p2.id FROM payments p2
         WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
     )
     $where
     ORDER BY o.created_at DESC, o.id DESC"
);

$statusCounts = array_fill_keys($statuses, 0);
$countsResult = $conn->query('SELECT status, COUNT(*) AS total FROM orders GROUP BY status');
while ($row = $countsResult->fetch_assoc()) {
    $statusCounts[$row['status']] = (int) $row['total'];
}

$viewId = (int) ($_GET['view'] ?? 0);
$selectedOrder = null;
$itemsResult = null;
$historyResult = null;
if ($viewId > 0) {
    $selectedOrder = $conn->query(
        "SELECT o.*, u.email AS customer_email,
                p.payment_method, p.transaction_id, p.status AS payment_status
         FROM orders o
         LEFT JOIN users u ON u.id = o.user_id
         LEFT JOIN payments p ON p.id = (
             SELECT p2.id FROM payments p2
             WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
         )
         WHERE o.id = $viewId LIMIT 1"
    )->fetch_assoc();

    if ($selectedOrder) {
        $itemsResult = $conn->query(
            "SELECT product_name, unit_price, quantity, line_total
             FROM order_items WHERE order_id = $viewId ORDER BY id"
        );
        $historyResult = $conn->query(
            "SELECT h.old_status, h.new_status, h.note, h.created_at, u.name AS admin_name
             FROM order_status_history h
             LEFT JOIN users u ON u.id = h.changed_by
             WHERE h.order_id = $viewId ORDER BY h.created_at DESC, h.id DESC"
        );
    }
}

require 'partials/head.php';
require 'partials/left_bar.php';
require 'partials/header.php';
?>
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">eCommerce</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="index.php"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Orders</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div id="orders-feedback" class="alert d-none" role="status" aria-live="polite"></div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-6 g-3 mb-3">
            <?php foreach ($statuses as $status): ?>
                <div class="col">
                    <div class="card mb-0">
                        <div class="card-body py-3">
                            <div class="text-muted text-uppercase small"><?= order_e(ucfirst($status)) ?></div>
                            <div class="fs-4 fw-semibold" id="status-count-<?= order_e($status) ?>"><?= $statusCounts[$status] ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center gap-3 mb-4">
                    <div>
                        <h5 class="mb-1">Order management</h5>
                        <div class="text-muted small">
                            <span id="matching-order-count"><?= $ordersResult->num_rows ?></span>
                            matching order<?= $ordersResult->num_rows === 1 ? '' : 's' ?>
                        </div>
                    </div>
                    <form class="ms-lg-auto d-flex flex-column flex-sm-row gap-2" method="get" action="orders.php">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="search" class="form-control" name="q" value="<?= order_e($search) ?>" placeholder="Order, customer, phone or email" aria-label="Search orders">
                        </div>
                        <select class="form-select" name="status" aria-label="Filter by status">
                            <option value="">All statuses</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= order_e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= order_e(ucfirst($status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-light" type="submit">Search</button>
                        <?php if ($search !== '' || $statusFilter !== ''): ?>
                            <a class="btn btn-outline-secondary" href="orders.php">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Details</th>
                                <th>Update status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ordersResult->num_rows === 0): ?>
                                <tr id="empty-orders-row"><td colspan="8" class="text-center text-muted py-5">No orders match your search.</td></tr>
                            <?php endif; ?>
                            <?php while ($order = $ordersResult->fetch_assoc()): ?>
                                <?php
                                $orderId = (int) $order['id'];
                                $orderStatus = $order['status'];
                                $detailsQuery = ['view' => $orderId, 'q' => $search, 'status' => $statusFilter];
                                ?>
                                <tr data-order-row data-status="<?= order_e($orderStatus) ?>">
                                    <td>
                                        <a class="fw-semibold text-body" href="orders.php?<?= order_e(http_build_query($detailsQuery)) ?>">#<?= order_e($order['order_number']) ?></a>
                                        <div class="text-muted small"><?= (int) $order['item_count'] ?> item<?= (int) $order['item_count'] === 1 ? '' : 's' ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= order_e($order['recipient_name']) ?></div>
                                        <div class="text-muted small"><?= order_e($order['phone']) ?></div>
                                        <?php if ($order['customer_email']): ?><div class="text-muted small"><?= order_e($order['customer_email']) ?></div><?php endif; ?>
                                    </td>
                                    <td>
                                        <span id="order-status-badge-<?= $orderId ?>" class="badge rounded-pill px-3 py-2 <?= $badgeClasses[$orderStatus] ?>"><?= order_e(ucfirst($orderStatus)) ?></span>
                                    </td>
                                    <td>
                                        <span class="text-capitalize"><?= order_e($order['payment_method'] ?? '—') ?></span>
                                        <?php if ($order['payment_status']): ?><div class="text-muted small"><?= order_e(ucfirst($order['payment_status'])) ?></div><?php endif; ?>
                                    </td>
                                    <td class="fw-semibold"><?= order_money($order['total_amount']) ?></td>
                                    <td><?= order_e(date('M j, Y', strtotime($order['created_at']))) ?></td>
                                    <td><a class="btn btn-light btn-sm radius-30 px-3" href="orders.php?<?= order_e(http_build_query($detailsQuery)) ?>">View details</a></td>
                                    <td>
                                        <form class="order-status-form d-flex gap-2" method="post" action="orders.php">
                                            <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                            <input type="hidden" name="return_search" value="<?= order_e($search) ?>">
                                            <input type="hidden" name="return_status" value="<?= order_e($statusFilter) ?>">
                                            <select class="form-select form-select-sm" name="new_status" aria-label="New status for order <?= order_e($order['order_number']) ?>">
                                                <?php foreach ($statuses as $status): ?>
                                                    <option value="<?= order_e($status) ?>" <?= $orderStatus === $status ? 'selected' : '' ?>><?= order_e(ucfirst($status)) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-light btn-sm" type="submit">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($selectedOrder): ?>
    <?php
    $address = array_filter([
        $selectedOrder['shipping_address'],
        $selectedOrder['area'],
        $selectedOrder['city'],
        $selectedOrder['postal_code'],
    ]);
    ?>
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="orderDetailsTitle">Order #<?= order_e($selectedOrder['order_number']) ?></h5>
                        <div class="text-muted small">Placed <?= order_e(date('M j, Y g:i A', strtotime($selectedOrder['created_at']))) ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <h6>Recipient</h6>
                            <div><?= order_e($selectedOrder['recipient_name']) ?></div>
                            <div><?= order_e($selectedOrder['phone']) ?></div>
                            <div><?= order_e(implode(', ', $address)) ?></div>
                            <div><?= order_e($selectedOrder['customer_email']) ?></div>
                        </div>
                        <div class="col-md-6">
                            <h6>Payment</h6>
                            <div>Method: <span class="text-capitalize"><?= order_e($selectedOrder['payment_method'] ?? 'Not recorded') ?></span></div>
                            <div>Status: <?= order_e(ucfirst($selectedOrder['payment_status'] ?? 'Unknown')) ?></div>
                            <?php if ($selectedOrder['transaction_id']): ?><div>Transaction: <?= order_e($selectedOrder['transaction_id']) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <h6>Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light"><tr><th>Product</th><th>Unit price</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                <?php while ($item = $itemsResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= order_e($item['product_name']) ?></td>
                                        <td><?= order_money($item['unit_price']) ?></td>
                                        <td><?= (int) $item['quantity'] ?></td>
                                        <td class="text-end"><?= order_money($item['line_total']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="ms-auto" style="max-width: 280px">
                        <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= order_money($selectedOrder['subtotal']) ?></span></div>
                        <div class="d-flex justify-content-between"><span>Delivery</span><span><?= order_money($selectedOrder['delivery_fee']) ?></span></div>
                        <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2"><span>Total</span><span><?= order_money($selectedOrder['total_amount']) ?></span></div>
                    </div>

                    <h6 class="mt-4">Status history</h6>
                    <ul class="list-group list-group-flush">
                        <?php while ($history = $historyResult->fetch_assoc()): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between gap-3">
                                    <strong>
                                        <?= $history['old_status'] ? order_e(ucfirst($history['old_status'])) . ' &rarr; ' . order_e(ucfirst($history['new_status'])) : 'Order placed' ?>
                                    </strong>
                                    <small class="text-muted text-nowrap"><?= order_e(date('M j, Y g:i A', strtotime($history['created_at']))) ?></small>
                                </div>
                                <?php if ($history['note']): ?><div><?= order_e($history['note']) ?></div><?php endif; ?>
                                <?php if ($history['admin_name']): ?><small class="text-muted">By <?= order_e($history['admin_name']) ?></small><?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-light" href="invoice.php?id=<?= (int) $selectedOrder['id'] ?>" target="_blank" rel="noopener">Print invoice</a>
                    <a class="btn btn-light" href="orders.php?<?= order_e(http_build_query(['q' => $search, 'status' => $statusFilter])) ?>">Back to orders</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
require 'partials/footer.php';
require 'partials/scripts.php';
?>
<script>
    $(function () {
        $('.order-status-form').on('submit', function (event) {
            event.preventDefault();

            const form = $(this);
            const select = form.find('select[name="new_status"]');
            const button = form.find('button[type="submit"]');
            const row = form.closest('[data-order-row]');
            const previousStatus = row.attr('data-status');
            const formData = form.serialize();

            button.prop('disabled', true).text('Saving...');
            select.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: formData,
                dataType: 'json'
            }).done(function (response) {
                if (!response.success) {
                    $('#orders-feedback').removeClass('d-none alert-success').addClass('alert-danger').text(response.message);
                    return;
                }

                row.attr('data-status', response.status);
                select.val(response.status);
                $('#order-status-badge-' + form.find('[name="order_id"]').val())
                    .text(response.status_label)
                    .attr('class', 'badge rounded-pill px-3 py-2 ' + response.badge_class);

                if (previousStatus !== response.status) {
                    $('#status-count-' + previousStatus).text(Number($('#status-count-' + previousStatus).text()) - 1);
                    $('#status-count-' + response.status).text(Number($('#status-count-' + response.status).text()) + 1);
                }

                $('#orders-feedback').removeClass('d-none alert-danger').addClass('alert-success').text(response.message);
                if ($('select[name="status"]').val() && $('select[name="status"]').val() !== response.status) {
                    row.fadeOut(200, function () {
                        $(this).remove();
                        if ($('[data-order-row]').length === 0) {
                            $('tbody').html('<tr><td colspan="8" class="text-center text-muted py-5">No orders match your search.</td></tr>');
                        }
                    });
                    $('#matching-order-count').text(Number($('#matching-order-count').text()) - 1);
                }
            }).fail(function () {
                $('#orders-feedback').removeClass('d-none alert-success').addClass('alert-danger')
                    .text('Could not update the order status. Please try again.');
            }).always(function () {
                button.prop('disabled', false).text('Save');
                select.prop('disabled', false);
            });
        });

        const modal = document.getElementById('orderDetailsModal');
        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    });
</script>
