<?php
declare(strict_types=1);

$hasFilters = $query !== '' || $selectedCategory !== '' || isset($_GET['featured']);
?>
<section class="shop-hero">
    <div class="shop-hero-copy">
        <span class="hero-eyebrow"><span></span> GOOD THINGS GROW HERE</span>
        <h1>A little bloom<br>goes a <em>long way.</em></h1>
        <p>Flowers for the big days, the little days, and all the ordinary moments in between.</p>
        <a class="hero-shop-button" href="#shop">Find your flowers <span aria-hidden="true">↘</span></a>
        <div class="hero-footnote"><span>FRESHLY GATHERED</span><i></i><span>DELIVERED WITH CARE</span></div>
    </div>
    <div class="shop-hero-art" role="img" aria-label="A thoughtfully gathered bouquet of fresh flowers">
        <div class="hero-photo">
            <img src="https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&amp;fit=crop&amp;w=1100&amp;q=85"
                 alt="A bouquet of fresh pastel flowers" fetchpriority="high">
        </div>
        <div class="hero-flower-doodle" aria-hidden="true">✿</div>
        <div class="hero-note">
            <span class="hero-note-flower" aria-hidden="true">✿</span>
            <span><strong>Made to make<br>someone smile.</strong><small>ALWAYS WITH LOVE</small></span>
        </div>
        <div class="hero-photo-label"><span>01</span> A SEASON IN BLOOM</div>
    </div>
    <div class="hero-scroll-cue"><span></span> SCROLL TO EXPLORE</div>
</section>

<section class="shop-trust-row" aria-label="Our promises">
    <span><i aria-hidden="true">✿</i> Flowers picked fresh</span>
    <span><i aria-hidden="true">♡</i> Arranged by hand</span>
    <span><i aria-hidden="true">⌂</i> Delivered with care</span>
    <span class="trust-bangladesh">PROUDLY GROWN FOR DHAKA</span>
</section>

<section class="category-section" aria-labelledby="categoryHeading">
    <div class="section-heading category-heading">
        <div>
            <span class="section-eyebrow">A THOUGHTFUL PLACE TO START</span>
            <h2 id="categoryHeading">Flowers for every <em>feeling.</em></h2>
        </div>
        <a class="text-link" href="#shop">Find your favourite <span aria-hidden="true">→</span></a>
    </div>
    <?php if ($categories === []): ?>
        <div class="store-empty-note">New flower collections are on their way. Come back soon.</div>
    <?php else: ?>
        <div class="category-chips">
            <a class="category-chip" href="<?= h(app_url('index.php#shop')) ?>">
                <span class="category-chip-icon chip-all" aria-hidden="true">✿</span>
                <span><strong>Everything lovely</strong><small>All the flowers</small></span>
                <span class="category-arrow" aria-hidden="true">↗</span>
            </a>
            <?php foreach (array_slice($categories, 0, 4) as $index => $category): ?>
                <?php
                $chipClasses = ['chip-rose', 'chip-bouquet', 'chip-wedding', 'chip-green'];
                $chipIcons = ['✿', '❀', '❋', '❦'];
                ?>
                <a class="category-chip" href="<?= h(app_url('index.php?category=' . rawurlencode((string) $category['slug']) . '#shop')) ?>">
                    <span class="category-chip-icon <?= h($chipClasses[$index % count($chipClasses)]) ?>" aria-hidden="true"><?= h($chipIcons[$index % count($chipIcons)]) ?></span>
                    <span><strong><?= h((string) $category['name']) ?></strong><small><?= h((int) $category['product_count']) ?> beautiful things</small></span>
                    <span class="category-arrow" aria-hidden="true">↗</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="product-section" id="shop" aria-labelledby="shopHeading">
    <div class="section-heading product-section-heading">
        <div>
            <span class="section-eyebrow"><?= $hasFilters ? 'JUST WHAT YOU WERE LOOKING FOR' : 'A FEW CUSTOMER FAVOURITES' ?></span>
            <h2 id="shopHeading"><?= $hasFilters ? 'Your flower edit.' : 'The lovely little ' ?><em><?= $hasFilters ? '' : 'favourites.' ?></em></h2>
        </div>
        <form class="shop-search" method="get" action="<?= h(app_url('index.php')) ?>" role="search">
            <label class="visually-hidden" for="flowerSearch">Search flowers</label>
            <span class="search-icon" aria-hidden="true">⌕</span>
            <input id="flowerSearch" type="search" name="q" value="<?= h($query) ?>" placeholder="Find a flower...">
            <?php if ($selectedCategory !== ''): ?>
                <input type="hidden" name="category" value="<?= h($selectedCategory) ?>">
            <?php endif; ?>
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="product-toolbar">
        <div class="product-filter-links">
            <a class="<?= $selectedCategory === '' && !isset($_GET['featured']) ? 'is-selected' : '' ?>"
               href="<?= h(app_url('index.php#shop')) ?>">All flowers</a>
            <a class="<?= isset($_GET['featured']) ? 'is-selected' : '' ?>"
               href="<?= h(app_url('index.php?featured=1#shop')) ?>">Bestsellers</a>
            <?php foreach ($categories as $category): ?>
                <a class="<?= $selectedCategory === $category['slug'] ? 'is-selected' : '' ?>"
                   href="<?= h(app_url('index.php?category=' . rawurlencode((string) $category['slug']) . '#shop')) ?>">
                    <?= h((string) $category['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if ($hasFilters): ?>
            <a class="clear-filters" href="<?= h(app_url('index.php#shop')) ?>">Clear filters <span aria-hidden="true">×</span></a>
        <?php else: ?>
            <span class="shop-result-count"><?= count($products) ?> lovely finds</span>
        <?php endif; ?>
    </div>

    <?php if ($products === []): ?>
        <div class="no-products">
            <span aria-hidden="true">✿</span>
            <h3>Nothing in bloom just yet.</h3>
            <p>Try a different search, or explore all of our fresh flowers.</p>
            <a class="text-link" href="<?= h(app_url('index.php#shop')) ?>">Browse every flower <span aria-hidden="true">→</span></a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php require __DIR__ . '/partials/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php if (count($products) === 24): ?>
            <p class="product-limit-note">Showing the newest 24 flowers. Use search or a category to find something specific.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<section class="story-banner">
    <div class="story-banner-photo">
        <img src="https://images.unsplash.com/photo-1520763185298-1b434c919102?auto=format&amp;fit=crop&amp;w=1000&amp;q=80"
             alt="Colourful garden flowers in the sunshine" loading="lazy" data-image-fallback>
        <span class="story-photo-caption">GROWN WITH A LITTLE MORE CARE</span>
    </div>
    <div class="story-banner-copy">
        <span class="section-eyebrow">A NOTE FROM OUR LITTLE SHOP</span>
        <h2>It’s never <em>just</em><br>a bunch of flowers.</h2>
        <p>It’s a quiet “I’m thinking of you.” A very loud “Congratulations!” Or an ordinary Tuesday that deserves a little colour. Whatever the reason, we’re here for it.</p>
        <a class="text-link" href="#shop">Find your something lovely <span aria-hidden="true">→</span></a>
        <span class="story-signature">A little love, <i>Bloom Basket</i></span>
    </div>
</section>
