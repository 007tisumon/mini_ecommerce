<?php
session_start();
require 'functions.php';
role_check('admin');
require '../database/config.php';
$conn->set_charset('utf8mb4');

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT);
    $active = filter_var($_POST['active'] ?? null, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1 || $stock === false || $stock < 0 || !in_array($active, [0, 1], true)) {
        $message = 'Enter a valid stock amount and product status.';
        $messageType = 'danger';
    } else {
        $statement = $conn->prepare('UPDATE products SET stock = ?, is_active = ? WHERE id = ?');
        $statement->bind_param('iii', $stock, $active, $id);

        if ($statement->execute()) {
            header('Location: stock_out.php?updated=1');
            exit;
        }

        $message = 'Could not update the product. Please try again.';
        $messageType = 'danger';
    }
}

$products = $conn->query(
    "SELECT id, name, sku, stock, is_active
     FROM products
     WHERE stock = 0
     ORDER BY is_active DESC, name"
);

require 'partials/head.php';
require 'partials/left_bar.php';
require 'partials/header.php';
?>
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-1">Out of Stock</h5>
                <div class="text-muted small">Restock products or change their storefront status.</div>
            </div>
            <a class="btn btn-light ms-auto" href="admin_products.php">All Products</a>
        </div>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Product inventory updated.</div>
        <?php endif; ?>
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card radius-10">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Product</th><th>SKU</th><th>Stock</th><th>Storefront</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if ($products->num_rows === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted py-4">All products have stock.</td></tr>
                            <?php else: ?>
                                <?php while ($product = $products->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td colspan="3">
                                            <form method="post" class="row g-2 align-items-center">
                                                <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
                                                <div class="col-sm-3">
                                                    <label class="visually-hidden" for="stock-<?= (int) $product['id'] ?>">Stock</label>
                                                    <input class="form-control form-control-sm" id="stock-<?= (int) $product['id'] ?>" type="number" name="stock" min="0" value="<?= (int) $product['stock'] ?>" required>
                                                </div>
                                                <div class="col-sm-4">
                                                    <label class="visually-hidden" for="active-<?= (int) $product['id'] ?>">Storefront status</label>
                                                    <select class="form-select form-select-sm" id="active-<?= (int) $product['id'] ?>" name="active">
                                                        <option value="1" <?= (int) $product['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
                                                        <option value="0" <?= (int) $product['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-5 d-flex gap-2">
                                                    <button class="btn btn-light btn-sm" type="submit">Update</button>
                                                    <a class="btn btn-outline-secondary btn-sm" href="update_product.php?id=<?= (int) $product['id'] ?>">Edit product</a>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'partials/footer.php'; ?>
<?php require 'partials/scripts.php'; ?>
