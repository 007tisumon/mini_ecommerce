<?php
declare(strict_types=1);

?>
<section class="shop-page-heading checkout-heading">
    <span class="section-eyebrow">THE FINAL LITTLE DETAILS</span>
    <h1>On its way to someone <em>special.</em></h1>
    <p>Tell us where to deliver your flowers and how you’d like to pay.</p>
</section>
<?php if ($cartItems === []): ?>
    <section class="cart-empty">
        <span class="empty-flower" aria-hidden="true">✿</span>
        <h2>Nothing to check out just yet.</h2>
        <p>Choose your flowers first, then we’ll get them on their way.</p>
        <a class="hero-shop-button" href="<?= h(app_url('index.php#shop')) ?>">Explore the flowers <span aria-hidden="true">→</span></a>
    </section>
<?php elseif ($customer === null): ?>
    <section class="checkout-signin-note">
        <span class="checkout-note-icon" aria-hidden="true">✿</span>
        <div><strong>Let’s get to know you first.</strong><p>Sign in or create a free customer account to keep your order history in one place.</p></div>
        <a class="hero-shop-button" href="<?= h(app_url('index.php?page=login&next=checkout')) ?>">Sign in to continue <span aria-hidden="true">→</span></a>
        <a class="checkout-create-account" href="<?= h(app_url('index.php?page=register&next=checkout')) ?>">New here? Create an account</a>
    </section>
<?php else: ?>
    <div class="checkout-layout">
        <form action="<?= h(app_url('index.php?page=checkout&action=checkout')) ?>" method="post" class="checkout-form">
            <?= csrf_field() ?>
            <section class="checkout-section">
                <div class="checkout-step"><span>01</span><div><h2>Where should we deliver?</h2><p>A real address helps your flowers find their way home.</p></div></div>
                <div class="checkout-fields">
                    <div class="checkout-field">
                        <label for="recipientName">Recipient name</label>
                        <input id="recipientName" name="recipient_name" type="text" maxlength="100"
                               value="<?= h((string) ($customer['name'] ?? '')) ?>" required autocomplete="name">
                    </div>
                    <div class="checkout-field">
                        <label for="deliveryPhone">Phone number</label>
                        <input id="deliveryPhone" name="phone" type="tel" maxlength="20"
                               value="<?= h((string) ($customer['phone'] ?? '')) ?>" required autocomplete="tel">
                    </div>
                    <div class="checkout-field checkout-field-full">
                        <label for="shippingAddress">Street address</label>
                        <input id="shippingAddress" name="shipping_address" type="text" maxlength="255"
                               placeholder="House, road, and anything else that helps" required autocomplete="street-address">
                    </div>
                    <div class="checkout-field">
                        <label for="deliveryArea">Area <span>OPTIONAL</span></label>
                        <input id="deliveryArea" name="area" type="text" maxlength="100"
                               placeholder="e.g. Dhanmondi" autocomplete="address-level3">
                    </div>
                    <div class="checkout-field">
                        <label for="deliveryCity">City</label>
                        <input id="deliveryCity" name="city" type="text" maxlength="100"
                               value="Dhaka" required autocomplete="address-level2">
                    </div>
                    <div class="checkout-field">
                        <label for="deliveryPostal">Postal code <span>OPTIONAL</span></label>
                        <input id="deliveryPostal" name="postal_code" type="text" maxlength="20"
                               placeholder="e.g. 1205" autocomplete="postal-code">
                    </div>
                </div>
            </section>

            <section class="checkout-section">
                <div class="checkout-step"><span>02</span><div><h2>How would you like to pay?</h2><p>Pick what feels right for you.</p></div></div>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked data-payment-method>
                        <span class="payment-radio"></span>
                        <span class="payment-icon payment-cod" aria-hidden="true">৳</span>
                        <span class="payment-copy"><strong>Cash on delivery</strong><small>Pay when your flowers arrive</small></span>
                        <span class="payment-tag">EASY</span>
                    </label>
                    <?php foreach (['bkash' => 'bKash', 'nagad' => 'Nagad', 'rocket' => 'Rocket'] as $method => $label): ?>
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="<?= h($method) ?>" data-payment-method>
                            <span class="payment-radio"></span>
                            <span class="payment-icon payment-<?= h($method) ?>" aria-hidden="true"><?= h(strtoupper(substr($label, 0, 1))) ?></span>
                            <span class="payment-copy"><strong><?= h($label) ?></strong><small>Demo payment for testing only</small></span>
                            <span class="payment-tag">SIMULATED</span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="simulated-payment-choice" hidden data-simulated-payment>
                    <span>For this demo, choose a test result. No payment gateway is connected.</span>
                    <label for="testResult">Test payment result</label>
                    <select id="testResult" name="test_result">
                        <option value="success">Simulate successful payment</option>
                        <option value="failed">Simulate failed payment</option>
                    </select>
                </div>
            </section>
            <button class="place-order-button" type="submit">Place my flower order <span aria-hidden="true">→</span></button>
            <p class="checkout-privacy-note"><span aria-hidden="true">♧</span> Your order details stay safe. We never ask for your payment PIN or OTP.</p>
        </form>

        <aside class="checkout-summary">
            <div class="checkout-summary-title"><span>YOUR LITTLE BOUQUET</span><span><?= count($cartItems) ?> <?= count($cartItems) === 1 ? 'item' : 'items' ?></span></div>
            <div class="checkout-summary-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="checkout-summary-item">
                        <div class="checkout-thumb">
                            <img src="<?= h(store_product_image($item)) ?>" alt=""
                                 data-fallback-src="<?= h(app_url('assets/images/flower-placeholder.svg')) ?>"
                                 data-image-fallback>
                            <span><?= h((int) $item['quantity']) ?></span>
                        </div>
                        <div class="checkout-summary-copy"><strong><?= h((string) $item['name']) ?></strong><small><?= h((string) $item['category_name']) ?></small></div>
                        <span><?= h(store_money((float) $item['line_total'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="checkout-summary-totals">
                <div class="summary-line"><span>Flowers</span><span><?= h(store_money($cartSubtotal)) ?></span></div>
                <div class="summary-line"><span>Dhaka delivery</span><span><?= h(store_money($deliveryFee)) ?></span></div>
                <div class="summary-total"><span>Total</span><strong><?= h(store_money($cartSubtotal + $deliveryFee)) ?></strong></div>
            </div>
            <a class="checkout-edit-link" href="<?= h(app_url('index.php?page=cart')) ?>">← Change something in my basket</a>
            <div class="checkout-mini-note"><span aria-hidden="true">✿</span> Arranged by hand, delivered with care.</div>
        </aside>
    </div>
<?php endif; ?>
