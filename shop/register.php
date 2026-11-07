<?php
declare(strict_types=1);

if ($customer !== null) {
    ?>
    <section class="auth-page">
        <div class="auth-card auth-success-card">
            <span class="auth-flower" aria-hidden="true">✿</span>
            <span class="section-eyebrow">YOU’RE ALREADY PART OF THE GARDEN</span>
            <h1>You’re in, <em><?= h((string) $customer['name']) ?>.</em></h1>
            <p>Shop your favourite fresh flowers or check on an order.</p>
            <a class="hero-shop-button" href="<?= h(app_url('index.php#shop')) ?>">Explore the flowers <span aria-hidden="true">→</span></a>
        </div>
    </section>
    <?php
} else {
    $authNext = ($_GET['next'] ?? '') === 'checkout' ? 'checkout' : '';
    $registerAction = 'index.php?page=register&action=register'
        . ($authNext === 'checkout' ? '&next=checkout' : '');
    $loginLink = 'index.php?page=login'
        . ($authNext === 'checkout' ? '&next=checkout' : '');
    ?>
    <section class="auth-page register-page">
        <div class="auth-aside">
            <span class="auth-aside-flower" aria-hidden="true">✿</span>
            <span class="section-eyebrow">GOOD THINGS GROW HERE</span>
            <h2>Your own little<br><em>corner of bloom.</em></h2>
            <p>One account for your thoughtful surprises, order updates, and favourite flowers.</p>
            <span class="auth-aside-caption">FRESH FLOWERS. THOUGHTFUL MOMENTS.</span>
        </div>
        <div class="auth-card">
            <span class="section-eyebrow">COME ON IN</span>
            <h1>Let’s get<br><em>to know you.</em></h1>
            <p>Creating an account takes just a moment.</p>
            <form class="auth-form" action="<?= h(app_url($registerAction)) ?>" method="post">
                <?= csrf_field() ?>
                <div class="checkout-field">
                    <label for="registerName">Your name</label>
                    <input id="registerName" name="name" type="text" maxlength="100"
                           value="<?= h((string) ($_POST['name'] ?? '')) ?>" autocomplete="name" required>
                </div>
                <div class="checkout-field">
                    <label for="registerEmail">Email address</label>
                    <input id="registerEmail" name="email" type="email" maxlength="150"
                           value="<?= h((string) ($_POST['email'] ?? '')) ?>" autocomplete="email" required>
                </div>
                <div class="checkout-field">
                    <label for="registerPhone">Phone number <span>OPTIONAL</span></label>
                    <input id="registerPhone" name="phone" type="tel" maxlength="20"
                           value="<?= h((string) ($_POST['phone'] ?? '')) ?>" autocomplete="tel">
                </div>
                <div class="checkout-field">
                    <label for="registerPassword">Password</label>
                    <div class="store-password-field">
                        <input id="registerPassword" name="password" type="password" minlength="8"
                               autocomplete="new-password" required>
                        <button type="button" data-store-password aria-label="Show password">Show</button>
                    </div>
                </div>
                <div class="checkout-field">
                    <label for="registerPasswordConfirm">Confirm password</label>
                    <input id="registerPasswordConfirm" name="password_confirmation" type="password"
                           minlength="8" autocomplete="new-password" required>
                </div>
                <button class="place-order-button" type="submit">Create my account <span aria-hidden="true">→</span></button>
            </form>
            <div class="auth-switch">Already have an account? <a href="<?= h(app_url($loginLink)) ?>">Sign in</a></div>
        </div>
    </section>
    <?php
}
