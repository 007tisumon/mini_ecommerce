<?php
declare(strict_types=1);
?>
<section class="shop-page-heading">
    <span class="section-eyebrow">A FEW GOOD THINGS, ALL IN ONE PLACE</span>
    <h1>Your basket<span class="heading-dot">.</span></h1>
    <p>One step closer to making someone’s day.</p>
</section>
<?php if ($cartItems === []): ?>
    <section class="cart-empty">
        <span class="empty-flower" aria-hidden="true">✿</span>
        <h2>Your basket is waiting to bloom.</h2>
        <p>It’s looking a little lonely in here. Add a few fresh flowers and we’ll take it from there.</p>
        <a class="hero-shop-button" href="<?= h('index.php#shop') ?>">Explore the flowers <span aria-hidden="true">→</span></a>
    </section>
<?php else: ?>
    <div class="cart-layout">
        <section class="cart-items-panel" aria-label="Basket items">
            <div class="cart-panel-title"><span>YOUR FLOWERS</span><span><?= count($cartItems) ?> <?= count($cartItems) === 1 ? 'arrangement' : 'arrangements' ?></span></div>
            <div id="cart-update-message" class="store-alert store-alert-warning d-none" role="alert"></div>
            <form action="<?= h('index.php?page=cart&action=cart-update') ?>" method="post" data-cart-form>
                <?php foreach ($cartItems as $item): ?>
                    <article class="cart-item" data-cart-item="<?= h((int) $item['id']) ?>">
                        <a class="cart-item-image" href="<?= h('index.php?product=' . rawurlencode((string) $item['slug'])) ?>">
                            <img src="<?= h(store_product_image($item)) ?>" alt=""
                                 data-fallback-src="<?= h('assets/images/flower-placeholder.svg') ?>"
                                 data-image-fallback>
                        </a>
                        <div class="cart-item-copy">
                            <span class="product-category"><?= h((string) $item['category_name']) ?></span>
                            <h2><a href="<?= h('index.php?product=' . rawurlencode((string) $item['slug'])) ?>"><?= h((string) $item['name']) ?></a></h2>
                            <span class="cart-item-price">
                                <?php if ((float) $item['discount_percent'] > 0): ?>
                                    <del><?= h(store_money((string) $item['price'])) ?></del>
                                    <span>-<?= h(rtrim(rtrim(number_format((float) $item['discount_percent'], 2), '0'), '.')) ?>%</span>
                                <?php endif; ?>
                                <?= h(store_money((float) $item['discounted_price'])) ?> each
                            </span>
                            <button class="remove-item" type="submit" name="remove_product_id" value="<?= h((int) $item['id']) ?>">Remove</button>
                        </div>
                        <div class="cart-item-quantity">
                            <label for="quantity-<?= h((int) $item['id']) ?>">Quantity</label>
                            <input id="quantity-<?= h((int) $item['id']) ?>" type="number"
                                   name="quantity[<?= h((int) $item['id']) ?>]"
                                   value="<?= h((int) $item['quantity']) ?>" min="0"
                                   data-unit-price="<?= h((float) $item['discounted_price']) ?>"
                                   max="<?= h(min((int) $item['stock'], 20)) ?>">
                        </div>
                        <strong class="cart-line-total" data-line-total><?= h(store_money((float) $item['line_total'])) ?></strong>
                    </article>
                <?php endforeach; ?>
                <div class="cart-update-row">
                    <a class="text-link" href="<?= h('index.php#shop') ?>">← Keep exploring</a>
                </div>
            </form>
        </section>
        <aside class="cart-summary">
            <span class="summary-eyebrow">THE LITTLE DETAILS</span>
            <h2>Your order, so far.</h2>
            <div class="summary-line"><span>Flowers</span><span><?= h(store_money($cartSubtotal)) ?></span></div>
            <div class="summary-line"><span>Delivery</span><span>Calculated at checkout</span></div>
            <div class="summary-total"><span>Flowers total</span><strong data-cart-subtotal><?= h(store_money($cartSubtotal)) ?></strong></div>
            <p class="summary-small-note">Choose your city at checkout to see the delivery charge and final total.</p>
            <a class="checkout-button" href="<?= h('index.php?page=checkout') ?>">A little something, on its way <span aria-hidden="true">→</span></a>
            <div class="summary-secure"><span>✿</span> Thoughtfully packaged, always.</div>
            <div class="summary-payments">
                <span>PAY YOUR WAY</span>
                <strong>Cash on delivery</strong>
                <strong>bKash <i>DEMO</i></strong>
            </div>
        </aside>
    </div>
<?php endif; ?>
