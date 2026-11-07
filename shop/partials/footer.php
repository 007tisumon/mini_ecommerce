</main>
<footer class="store-footer" id="our-story">
    <div class="store-footer-main">
        <div class="footer-brand-column">
            <a class="store-brand footer-brand" href="<?= h(app_url('index.php')) ?>">
                <span class="store-brand-mark" aria-hidden="true"><span></span></span>
                <span>Bloom Basket<small>FLOWERS, WITH FEELING</small></span>
            </a>
            <p>Small gestures. Big feelings.<br>Fresh flowers, thoughtfully delivered.</p>
        </div>
        <div class="footer-links-column">
            <span class="footer-column-title">EXPLORE</span>
            <a href="<?= h(app_url('index.php#shop')) ?>">Shop all flowers</a>
            <a href="<?= h(app_url('index.php?featured=1#shop')) ?>">Bestsellers</a>
        </div>
        <div class="footer-links-column">
            <span class="footer-column-title">YOUR ACCOUNT</span>
            <a href="<?= h(app_url('index.php?page=orders')) ?>">Order history</a>
            <a href="<?= h(app_url('admin/login.php')) ?>">Admin sign in</a>
        </div>
        <div class="footer-flower" aria-hidden="true">✿</div>
    </div>
    <div class="store-footer-bottom">
        <span>© <?= date('Y') ?> Bloom Basket. Made with a little extra love.</span>
        <span>Fresh blooms, happy hearts <i>✿</i></span>
    </div>
</footer>
<script src="<?= h(app_url('assets/js/store.js')) ?>" defer></script>
</body>
</html>
