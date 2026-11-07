<?php
declare(strict_types=1);

if ($customer !== null) {
    $signedInOrders = app_url('index.php?page=orders');
    ?>
    <section class="auth-page">
        <div class="auth-card auth-success-card">
            <span class="auth-flower" aria-hidden="true">✿</span>
            <span class="section-eyebrow">YOU’RE ALREADY IN</span>
            <h1>Welcome back, <em><?= h((string) $customer['name']) ?>.</em></h1>
            <p>You’re all signed in. Your flowers and orders are just a click away.</p>
            <a class="hero-shop-button" href="<?= h($signedInOrders) ?>">See my orders <span aria-hidden="true">→</span></a>
        </div>
    </section>
    <?php
} else {
    $authNext = ($_GET['next'] ?? '') === 'checkout' ? 'checkout' : '';
    $loginAction = 'index.php?page=login&action=login'
        . ($authNext === 'checkout' ? '&next=checkout' : '');
    $registerLink = 'index.php?page=register'
        . ($authNext === 'checkout' ? '&next=checkout' : '');
    ?>
    <section class="auth-page">
        <div class="auth-aside">
            <span class="auth-aside-flower" aria-hidden="true">✿</span>
            <span class="section-eyebrow">A LITTLE SOMETHING LOVELY</span>
            <h2>Flowers look<br>better <em>together.</em></h2>
            <p>Sign in to keep track of the good things you’ve sent.</p>
            <span class="auth-aside-caption">FRESH FLOWERS. THOUGHTFUL MOMENTS.</span>
        </div>
        <div class="auth-card">
            <span class="section-eyebrow">WELCOME BACK</span>
            <h1>Let’s pick up<br><em>where we left off.</em></h1>
            <p>Sign in to your Bloom Basket account.</p>
            <form class="auth-form" action="<?= h(app_url($loginAction)) ?>" method="post">
                <?= csrf_field() ?>
                <div class="checkout-field">
                    <label for="loginEmail">Email address</label>
                    <input id="loginEmail" type="email" name="email" maxlength="150"
                           value="<?= h((string) ($_POST['email'] ?? '')) ?>" autocomplete="email" required>
                </div>
                <div class="checkout-field">
                    <label for="loginPassword">Password</label>
                    <div class="store-password-field">
                        <input id="loginPassword" type="password" name="password"
                               autocomplete="current-password" required>
                        <button type="button" data-store-password aria-label="Show password">Show</button>
                    </div>
                </div>
                <button class="place-order-button" type="submit">Sign in <span aria-hidden="true">→</span></button>
            </form>
            <div class="auth-switch">New around here? <a href="<?= h(app_url($registerLink)) ?>">Create your account</a></div>
            <div class="auth-demo-note">Customer accounts only. <a href="<?= h(app_url('admin/login.php')) ?>">Admin sign in →</a></div>
        </div>
    </section>
    <?php
}
