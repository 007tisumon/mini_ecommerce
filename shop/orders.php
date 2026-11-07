<?php
declare(strict_types=1);

if ($customer === null): ?>
    <section class="cart-empty order-login-empty">
        <span class="empty-flower" aria-hidden="true">✿</span>
        <h1>Your flower stories live here.</h1>
        <p>Sign in to see order updates and remember all the lovely things you’ve sent.</p>
        <a class="hero-shop-button" href="<?= h(app_url('index.php?page=login')) ?>">Sign in to see my orders <span aria-hidden="true">→</span></a>
    </section>
<?php elseif ($orderNotice !== '' && isset($selectedOrder) && is_array($selectedOrder)): ?>
    <section class="order-confirmation">
        <div class="order-confirmation-flower" aria-hidden="true">✿</div>
        <span class="section-eyebrow">THAT’S SOMETHING TO SMILE ABOUT</span>
        <h1>Your flowers are <em>on their way.</em></h1>
        <p>We’ve received your order. Thank you for choosing something lovely.</p>
        <div class="confirmation-number"><span>YOUR ORDER NUMBER</span><strong><?= h((string) $selectedOrder['order_number']) ?></strong></div>
    </section>
    <section class="order-detail-card">
        <div class="order-detail-heading"><div><span class="section-eyebrow">ORDER DETAILS</span><h2>Everything, just so.</h2></div><a class="text-link" href="<?= h(app_url('index.php?page=orders')) ?>">All my orders →</a></div>
        <div class="order-detail-items">
            <?php foreach ($orderItems as $item): ?>
                <div class="order-detail-item"><span><?= h((string) $item['product_name']) ?> <i>× <?= h((int) $item['quantity']) ?></i></span><strong><?= h(store_money((string) $item['line_total'])) ?></strong></div>
            <?php endforeach; ?>
        </div>
        <div class="summary-line"><span>Flowers</span><span><?= h(store_money((string) $selectedOrder['subtotal'])) ?></span></div>
        <div class="summary-line"><span>Delivery</span><span><?= h(store_money((string) $selectedOrder['delivery_fee'])) ?></span></div>
        <div class="summary-total"><span>Order total</span><strong><?= h(store_money((string) $selectedOrder['total_amount'])) ?></strong></div>
        <p class="order-payment-note">Payment: <?= h(ucfirst((string) $selectedOrder['payment_method'])) ?> · <?= h(ucfirst((string) $selectedOrder['payment_status'])) ?><?= $selectedOrder['transaction_id'] ? ' · ' . h((string) $selectedOrder['transaction_id']) : '' ?></p>
    </section>
<?php else: ?>
    <section class="shop-page-heading orders-page-heading">
        <span class="section-eyebrow">ALL YOUR LOVELY LITTLE SURPRISES</span>
        <h1>Your orders<span class="heading-dot">.</span></h1>
        <p>It’s nice to look back on the good things.</p>
    </section>
    <?php if ($orders === []): ?>
        <section class="cart-empty">
            <span class="empty-flower" aria-hidden="true">✿</span>
            <h2>No orders just yet.</h2>
            <p>When you send a little something lovely, you’ll find it here.</p>
            <a class="hero-shop-button" href="<?= h(app_url('index.php#shop')) ?>">Find some flowers <span aria-hidden="true">→</span></a>
        </section>
    <?php else: ?>
        <section class="orders-list">
            <?php foreach ($orders as $order): ?>
                <?php
                $statusClass = match ((string) $order['status']) {
                    'delivered' => 'order-status-delivered',
                    'confirmed', 'processing', 'shipped' => 'order-status-progress',
                    'cancelled' => 'order-status-cancelled',
                    default => 'order-status-pending',
                };
                ?>
                <article class="customer-order-card">
                    <div class="customer-order-top">
                        <div><span class="order-card-label">ORDER NUMBER</span><strong><?= h((string) $order['order_number']) ?></strong></div>
                        <span class="order-status-pill <?= h($statusClass) ?>"><?= h(ucfirst((string) $order['status'])) ?></span>
                    </div>
                    <div class="customer-order-bottom">
                        <span><?= h(date('M j, Y', strtotime((string) $order['created_at']))) ?></span>
                        <span><?= h((int) $order['item_count']) ?> <?= (int) $order['item_count'] === 1 ? 'lovely thing' : 'lovely things' ?></span>
                        <strong><?= h(store_money((string) $order['total_amount'])) ?></strong>
                        <a href="<?= h(app_url('index.php?page=orders&order=' . (int) $order['id'])) ?>">See the details <span aria-hidden="true">→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
<?php endif; ?>
