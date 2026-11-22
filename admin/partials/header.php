<?php
$headerAdminName = trim((string) ($_SESSION['name'] ?? 'Admin'));
$headerAdminEmail = trim((string) ($_SESSION['email'] ?? ''));
$headerSearch = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$headerPendingCount = (int) $conn->query(
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'pending'"
)->fetch_assoc()['total'];
$headerPendingOrders = $conn->query(
    "SELECT id, order_number, recipient_name, created_at
     FROM orders
     WHERE status = 'pending'
     ORDER BY created_at DESC, id DESC
     LIMIT 5"
);
?>
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class="bx bx-menu"></i></div>
            <form class="search-bar flex-grow-1" method="get" action="orders.php">
                <div class="position-relative search-bar-box">
                    <input type="search" name="q" class="form-control search-control"
                           value="<?= htmlspecialchars($headerSearch, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Search orders by number, customer or phone">
                    <button class="position-absolute top-50 search-show translate-middle-y border-0 bg-transparent"
                            type="submit" aria-label="Search orders"><i class="bx bx-search"></i></button>
                </div>
            </form>

            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
                           href="#" data-bs-toggle="dropdown" aria-label="<?= $headerPendingCount ?> pending orders">
                            <?php if ($headerPendingCount > 0): ?>
                                <span class="alert-count"><?= $headerPendingCount > 99 ? '99+' : $headerPendingCount ?></span>
                            <?php endif; ?>
                            <i class="bx bx-bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="msg-header">
                                <p class="msg-header-title">Pending Orders</p>
                                <p class="msg-header-badge"><?= $headerPendingCount ?> new</p>
                            </div>
                            <div class="header-notifications-list">
                                <?php if ($headerPendingCount === 0): ?>
                                    <div class="dropdown-item text-muted">No pending orders.</div>
                                <?php else: ?>
                                    <?php while ($order = $headerPendingOrders->fetch_assoc()): ?>
                                        <a class="dropdown-item" href="orders.php?view=<?= (int) $order['id'] ?>&status=pending">
                                            <div class="d-flex align-items-center">
                                                <div class="notify bg-light-warning text-warning"><i class="bx bx-receipt"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name"><?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?>
                                                        <span class="msg-time float-end"><?= htmlspecialchars(date('M j', strtotime($order['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
                                                    </h6>
                                                    <p class="msg-info"><?= htmlspecialchars($order['recipient_name'], ENT_QUOTES, 'UTF-8') ?></p>
                                                </div>
                                            </div>
                                        </a>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                            <a href="orders.php?status=pending">
                                <div class="text-center msg-footer">
                                    <span class="btn btn-light w-100">View pending orders</span>
                                </div>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="user-box dropdown px-3">
                <a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="assets/images/avatars/avatar-2.png" class="user-img" alt="Admin">
                    <div class="user-info">
                        <p class="user-name mb-0"><?= htmlspecialchars($headerAdminName, ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="designattion mb-0"><?= htmlspecialchars($headerAdminEmail, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item d-flex align-items-center" href="index.php"><i class="bx bx-home fs-5"></i><span>Dashboard</span></a></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="orders.php"><i class="bx bx-receipt fs-5"></i><span>Orders</span></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item d-flex align-items-center" href="signout.php"><i class="bx bx-log-out-circle"></i><span>Sign out</span></a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
