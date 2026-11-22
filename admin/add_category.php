<?php
session_start();
require 'functions.php';
role_check('admin');
require '../database/config.php';
$conn->set_charset('utf8mb4');

$message = '';
$messageType = 'danger';
$name = '';
$description = '';
$slug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $slug = strtolower(trim($_POST['slug'] ?? ''));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    if ($name === '' || strlen($name) > 100) {
        $message = 'Enter a category name up to 100 characters.';
    } elseif ($slug === '' || strlen($slug) > 120) {
        $message = 'Enter a valid category slug using English letters and numbers.';
    } else {
        $statement = $conn->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
        $statement->bind_param('sss', $name, $slug, $description);

        if ($statement->execute()) {
            header('Location: add_category.php?added=1');
            exit;
        }

        $message = $conn->errno === 1062
            ? 'A category with this name or slug already exists.'
            : 'Could not save the category. Please try again.';
    }
}

$categories = $conn->query(
    "SELECT c.id, c.name, c.slug, c.description, c.is_active, COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name, c.slug, c.description, c.is_active
     ORDER BY c.name"
);

require 'partials/head.php';
require 'partials/left_bar.php';
require 'partials/header.php';
?>
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0">Categories</h5>
            <a class="btn btn-light ms-auto" href="admin_products.php">Products</a>
        </div>

        <?php if (isset($_GET['added'])): ?>
            <div class="alert alert-success">Category added successfully.</div>
        <?php endif; ?>
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-5">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Add Category</h5>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label" for="category-name">Category name</label>
                                <input class="form-control" id="category-name" name="name" maxlength="100" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="category-slug">Slug</label>
                                <input class="form-control" id="category-slug" name="slug" maxlength="120" pattern="[A-Za-z0-9-]+" value="<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. seasonal-flowers" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="category-description">Description</label>
                                <textarea class="form-control" id="category-description" name="description" rows="3"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                            <button class="btn btn-light" type="submit">Save Category</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">All Categories</h5>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light"><tr><th>Name</th><th>Slug</th><th>Products</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($category['slug'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= (int) $category['product_count'] ?></td>
                                            <td><?= (int) $category['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'partials/footer.php'; ?>
<?php require 'partials/scripts.php'; ?>
<script>
    $('#category-name').on('input', function () {
        if (!$('#category-slug').data('edited')) {
            $('#category-slug').val($(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''));
        }
    });
    $('#category-slug').on('input', function () {
        $(this).data('edited', true);
    });
</script>
