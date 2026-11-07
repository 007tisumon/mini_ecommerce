<?php
declare(strict_types=1);

$product = $product ?? [];
$isOutOfStock = (int) ($product['stock'] ?? 0) < 1;
?>
<article class="product-card">
    <a class="product-image-wrap" href="<?= h(app_url('index.php?product=' . rawurlencode((string) $product['slug']))) ?>">
        <?php if ((int) ($product['is_featured'] ?? 0) === 1): ?>
            <span class="product-badge">A FAVOURITE</span>
        <?php endif; ?>
        <img class="product-image"
             src="<?= h(store_product_image($product)) ?>"
             alt="<?= h((string) $product['name']) ?>"
             loading="lazy"
             data-fallback-src="<?= h(app_url('assets/images/flower-placeholder.svg')) ?>"
             data-image-fallback>
        <span class="product-quick-view">Discover this bloom <span aria-hidden="true">→</span></span>
    </a>
    <div class="product-card-info">
        <a class="product-category" href="<?= h(app_url('index.php?category=' . rawurlencode((string) $product['category_slug']) . '#shop')) ?>">
            <?= h((string) $product['category_name']) ?>
        </a>
        <div class="product-title-row">
            <h3><a href="<?= h(app_url('index.php?product=' . rawurlencode((string) $product['slug']))) ?>"><?= h((string) $product['name']) ?></a></h3>
            <span class="product-price"><?= h(store_money((string) $product['price'])) ?></span>
        </div>
        <div class="product-card-bottom">
            <span class="product-stock"><?= $isOutOfStock ? 'Temporarily unavailable' : 'Freshly gathered' ?></span>
            <form action="<?= h(app_url('index.php?action=cart-add')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= h((int) $product['id']) ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="product-add-button" type="submit" <?= $isOutOfStock ? 'disabled' : '' ?>
                        aria-label="Add <?= h((string) $product['name']) ?> to your bag">
                    <span aria-hidden="true">+</span>
                </button>
            </form>
        </div>
    </div>
</article>
