<?php
declare(strict_types=1);

$isOutOfStock = (int) $selectedProduct['stock'] < 1;
$discountPercent = (float) ($selectedProduct['discount_percent'] ?? 0);
$relatedProducts = store_get_products('', '', false, (string) $selectedProduct['slug']);
?>
<nav class="store-breadcrumb" aria-label="Breadcrumb">
    <a href="<?= h('index.php') ?>">Home</a><span aria-hidden="true">/</span>
    <a href="<?= h('index.php#shop') ?>">Flowers</a><span aria-hidden="true">/</span>
    <span><?= h((string) $selectedProduct['name']) ?></span>
</nav>
<section class="product-detail-layout">
    <div class="product-detail-image">
        <img src="<?= h(store_product_image($selectedProduct)) ?>"
             alt="<?= h((string) $selectedProduct['name']) ?>"
             data-fallback-src="<?= h('assets/images/flower-placeholder.svg') ?>"
             data-image-fallback>
        <span class="detail-image-stamp">ARRANGED<br>WITH LOVE <i>✿</i></span>
    </div>
    <div class="product-detail-copy">
        <a class="product-category" href="<?= h('index.php?category=' . rawurlencode((string) $selectedProduct['category_slug'])) ?>">
            <?= h((string) $selectedProduct['category_name']) ?>
        </a>
        <h1><?= h((string) $selectedProduct['name']) ?></h1>
        <div class="detail-product-price">
            <?php if ($discountPercent > 0): ?>
                <del><?= h(store_money((string) $selectedProduct['price'])) ?></del>
                <span class="product-discount-label">-<?= h(rtrim(rtrim(number_format($discountPercent, 2), '0'), '.')) ?>%</span>
            <?php endif; ?>
            <?= h(store_money(store_discounted_price($selectedProduct))) ?>
        </div>
        <div class="detail-divider"></div>
        <p class="detail-description">
            <?= h((string) ($selectedProduct['description'] ?: 'A thoughtful arrangement of fresh blooms, gathered and prepared with care for the moments that matter.')) ?>
        </p>
        <div class="detail-stock-note"><span></span> <?= $isOutOfStock ? 'Out of stock' : 'Fresh flowers, ready for your order' ?></div>
        <form class="detail-add-form" action="<?= h('index.php?action=cart-add') ?>" method="post">
            <input type="hidden" name="product_id" value="<?= h((int) $selectedProduct['id']) ?>">
            <label class="visually-hidden" for="detailQuantity">Quantity</label>
            <input id="detailQuantity" type="number" name="quantity" min="1"
                   max="<?= h(min((int) $selectedProduct['stock'], 20)) ?>" value="1" <?= $isOutOfStock ? 'disabled' : '' ?>>
            <button class="hero-shop-button" type="submit" <?= $isOutOfStock ? 'disabled' : '' ?>>
                <?= $isOutOfStock ? 'Out of stock' : 'Add to cart' ?> <span aria-hidden="true">→</span>
            </button>
        </form>
        <div class="detail-promises">
            <span><i>✿</i> Carefully arranged</span>
            <span><i>⌂</i> Delivered across Dhaka</span>
            <span><i>♡</i> Made to make someone smile</span>
        </div>
        <p class="detail-delivery-note">Local delivery from ৳80 · Delivery is arranged at checkout</p>
    </div>
</section>
<?php if ($relatedProducts !== []): ?>
    <section class="product-section related-section">
        <div class="section-heading">
            <div><span class="section-eyebrow">A FEW MORE GOOD THINGS</span><h2>You might also <em>love.</em></h2></div>
        </div>
        <div class="product-grid">
            <?php foreach (array_slice($relatedProducts, 0, 4) as $product): ?>
                <?php require 'shop/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
