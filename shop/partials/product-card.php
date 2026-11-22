<?php
declare(strict_types=1);

$product = $product ?? [];
$isOutOfStock = (int) ($product['stock'] ?? 0) < 1;
$discountPercent = (float) ($product['discount_percent'] ?? 0);
$discountedPrice = store_discounted_price($product);
?>
<article class="product-card">
    <a class="product-image-wrap" href="<?= h('index.php?product=' . rawurlencode((string) $product['slug'])) ?>">
        <?php if ((int) ($product['is_featured'] ?? 0) === 1): ?>
            <span class="product-badge">A FAVOURITE</span>
        <?php endif; ?>
        <?php if ($discountPercent > 0): ?>
            <span class="product-discount-badge">-<?= h(rtrim(rtrim(number_format($discountPercent, 2), '0'), '.')) ?>%</span>
        <?php endif; ?>
        <img class="product-image"
             src="<?= h(store_product_image($product)) ?>"
             alt="<?= h((string) $product['name']) ?>"
             loading="lazy"
             data-fallback-src="<?= h('assets/images/flower-placeholder.svg') ?>"
             data-image-fallback>
        <span class="product-quick-view">Discover this bloom <span aria-hidden="true">→</span></span>
    </a>
    <div class="product-card-info">
        <a class="product-category" href="<?= h('index.php?category=' . rawurlencode((string) $product['category_slug']) . '#shop') ?>">
            <?= h((string) $product['category_name']) ?>
        </a>
        <div class="product-title-row">
            <h3><a href="<?= h('index.php?product=' . rawurlencode((string) $product['slug'])) ?>"><?= h((string) $product['name']) ?></a></h3>
            <span class="product-price">
                <?php if ($discountPercent > 0): ?><del><?= h(store_money((string) $product['price'])) ?></del><?php endif; ?>
                <?= h(store_money($discountedPrice)) ?>
            </span>
        </div>
        <div class="product-card-bottom">
            <span class="product-stock"><?= $isOutOfStock ? 'Out of stock' : 'Freshly gathered' ?></span>
            <form action="<?= h('index.php?action=cart-add') ?>" method="post">
                <input type="hidden" name="product_id" value="<?= h((int) $product['id']) ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="product-add-button" type="submit" <?= $isOutOfStock ? 'disabled' : '' ?>
                        aria-label="Add <?= h((string) $product['name']) ?> to cart">
                    <?= $isOutOfStock ? 'Out of stock' : 'Add to cart' ?>
                </button>
            </form>
        </div>
    </div>
</article>
