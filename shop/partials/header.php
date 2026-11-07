<?php
declare(strict_types=1);

$brandMark = '<span class="store-brand-mark" aria-hidden="true"><span></span></span>';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fffdf8">
    <meta name="description" content="Fresh flowers and thoughtful bouquets for every occasion. Discover your next favourite at Bloom Basket.">
    <title><?= h($pageTitle) ?> | Bloom Basket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="<?= h(app_url('assets/css/style.css')) ?>">
</head>
<body class="store-body">
<div class="store-announcement">
    <span>✿</span> A little joy, delivered <span class="announcement-divider">·</span> Thoughtfully arranged in Dhaka
</div>
<header class="store-header">
    <nav class="store-nav" aria-label="Main navigation">
        <button class="store-menu-toggle" type="button" aria-label="Open menu" aria-expanded="false"
                aria-controls="storeNavLinks" data-store-menu-toggle>
            <span></span><span></span><span></span>
        </button>
        <a class="store-brand" href="<?= h(app_url('index.php')) ?>">
            <?= $brandMark ?>
            <span>Bloom Basket<small>FLOWERS, WITH FEELING</small></span>
        </a>
        <div class="store-nav-links" id="storeNavLinks">
            <a class="store-nav-link" href="<?= h(app_url('index.php#shop')) ?>">Shop all</a>
            <a class="store-nav-link" href="<?= h(app_url('index.php?featured=1#shop')) ?>">Bestsellers</a>
            <a class="store-nav-link" href="<?= h(app_url('index.php#our-story')) ?>">Our story</a>
            <?php if ($customer !== null): ?>
                <form action="<?= h(app_url('index.php?action=logout')) ?>" method="post" class="mobile-store-logout">
                    <?= csrf_field() ?>
                    <button type="submit">Sign out</button>
                </form>
            <?php endif; ?>
        </div>
        <div class="store-nav-actions">
            <a class="store-account-link" href="<?= h(app_url($customer !== null ? 'index.php?page=orders' : 'index.php?page=login')) ?>">
                <span class="account-icon" aria-hidden="true">♙</span>
                <span><?= $customer !== null ? h((string) $customer['name']) : 'Sign in' ?></span>
            </a>
            <?php if ($customer !== null): ?>
                <form action="<?= h(app_url('index.php?action=logout')) ?>" method="post" class="store-logout-form">
                    <?= csrf_field() ?>
                    <button class="store-logout-link" type="submit">Sign out</button>
                </form>
            <?php endif; ?>
            <a class="store-bag-link" href="<?= h(app_url('index.php?page=cart')) ?>" aria-label="Shopping basket, <?= h($cartCount) ?> items">
                <span class="bag-icon" aria-hidden="true">♧</span>
                <span class="bag-label">Bag</span>
                <span class="bag-count"><?= h($cartCount) ?></span>
            </a>
        </div>
    </nav>
</header>
<main class="store-main">
