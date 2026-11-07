<?php
declare(strict_types=1);

$relatedProducts = store_get_products('', '', false, (string) $selectedProduct['slug']);
?>
<nav class="store-breadcrumb" aria-label="Breadcrumb">
    <a href="<?= h(app_url('index.php')) ?>">Home</a><span aria-hidden="true">/</span>
    <a href="<?= h(app_url('index.php#shop')) ?>">Flowers</a><span aria-hidden="true">/</span>
    <span><?= h((string) $selectedProduct['name']) ?></span>
</nav>
<section class="product-detail-layout">
    <div class="product-detail-image">
        <img src="<?= h(store_product_image($selectedProduct)) ?>"
             alt="<?= h((string) $selectedProduct['name']) ?>"
             data-fallback-src="<?= h(app_url('assets/images/flower-placeholder.svg')) ?>"
             data-image-fallback>
        <span class="detail-image-stamp">ARRANGED<br>WITH LOVE <i>✿</i></span>
    </div>
    <div class="product-detail-copy">
        <a class="product-category" href="<?= h(app_url('index.php?category=' . rawurlencode((string) $selectedProduct['category_slug']))) ?>">
            <?= h((string) $selectedProduct['category_name']) ?>
        </a>
        <h1><?= h((string) $selectedProduct['name']) ?></h1>
        <div class="detail-product-price"><?= h(store_money((string) $selectedProduct['price'])) ?></div>
        <div class="detail-divider"></div>
        <p class="detail-description">
            <?= h((string) ($selectedProduct['description'] ?: 'A thoughtful arrangement of fresh blooms, gathered and prepared with care for the moments that matter.')) ?>
        </p>
        <div class="detail-stock-note"><span></span> Fresh flowers, ready for your order</div>
        <form class="detail-add-form" action="<?= h(app_url('index.php?action=cart-add')) ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= h((int) $selectedProduct['id']) ?>">
            <label class="visually-hidden" for="detailQuantity">Quantity</label>
            <input id="detailQuantity" type="number" name="quantity" min="1"
                   max="<?= h(min((int) $selectedProduct['stock'], 20)) ?>" value="1">
            <button class="hero-shop-button" type="submit">Add to your basket <span aria-hidden="true">→</span></button>
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
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
