<?php
session_start();
require "functions.php";
role_check('admin');
require '../database/config.php';

$product = null;
$pageError = null;
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $pageError = "A valid product ID is required.";
} else {
    $productQuery = $conn->prepare("SELECT * FROM products WHERE id = ?");
    if (!$productQuery) {
        $pageError = "Unable to load this product right now.";
    } else {
        $productQuery->bind_param("i", $id);
        if ($productQuery->execute()) {
            $product = $productQuery->get_result()->fetch_assoc();
            if (!$product) {
                $pageError = "Product not found.";
            }
        } else {
            $pageError = "Unable to load this product right now.";
        }
        $productQuery->close();
    }
}

$categories = [];
$categoryQuery = $conn->query("SELECT id, name FROM categories ORDER BY name");
if ($categoryQuery) {
    while ($category = $categoryQuery->fetch_assoc()) {
        $categories[] = $category;
    }
} elseif (!$pageError) {
    $pageError = "Unable to load product categories right now.";
}

require 'partials/head.php';
require 'partials/left_bar.php';
require 'partials/header.php';
?>
<div class="page-wrapper">
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Products</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="admin_products.php">Products</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Product</li>
                    </ol>
                </nav>
            </div>
        </div>

        <?php if ($pageError) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?>
                <a class="alert-link ms-2" href="admin_products.php">Back to products</a>
            </div>
        <?php } elseif ($product) { ?>
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-1">Edit Product</h5>
                            <p class="text-muted mb-0">Update product details and inventory.</p>
                        </div>
                        <a href="admin_products.php" class="btn btn-outline-secondary">Back to products</a>
                    </div>
                    <hr>

                    <div id="update-feedback" class="alert d-none" role="alert" aria-live="polite"></div>
                    <form id="update-product-form" action="update.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="border p-4 rounded h-100">
                                    <div class="mb-3">
                                        <label for="product-name" class="form-label">Product title</label>
                                        <input type="text" name="name" class="form-control" id="product-name"
                                            value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                            maxlength="150" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-description" class="form-label">Description</label>
                                        <textarea name="description" class="form-control" id="product-description"
                                            rows="5" required><?php echo htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="product-image" class="form-label">Product image</label>
                                        <input name="image" type="file" class="form-control" id="product-image"
                                            accept="image/jpeg,image/png,image/webp,image/gif">
                                        <div class="form-text">Optional. JPG, PNG, WEBP or GIF; maximum 2 MB.</div>
                                    </div>
                                    <img id="product-image-preview"
                                        src="<?php echo htmlspecialchars($product['image_path'] ?: 'assets/images/products/flower.webp', ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="Current product image" class="img-thumbnail"
                                        style="max-width: 180px; max-height: 180px; object-fit: cover;">
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="border p-4 rounded h-100">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="product-price" class="form-label">Price</label>
                                            <input name="price" type="number" min="0.01" max="99999999.99" step="0.01"
                                                class="form-control" id="product-price"
                                                value="<?php echo htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="product-stock" class="form-label">Stock</label>
                                            <input name="stock" type="number" min="0" step="1"
                                                class="form-control" id="product-stock"
                                                value="<?php echo (int) $product['stock']; ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="product-slug" class="form-label">Slug</label>
                                            <input name="slug" type="text" maxlength="170"
                                                class="form-control" id="product-slug"
                                                value="<?php echo htmlspecialchars($product['slug'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                            <div class="form-text">Suggested from the product title; you can edit it.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="product-sku" class="form-label">SKU</label>
                                            <input name="sku" type="text" maxlength="50"
                                                class="form-control" id="product-sku"
                                                value="<?php echo htmlspecialchars($product['sku'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                        </div>
                                        <div class="col-12">
                                            <label for="product-category" class="form-label">Category</label>
                                            <select name="categories" class="form-select" id="product-category" required>
                                                <option value="">Select a category</option>
                                                <?php foreach ($categories as $category) { ?>
                                                    <option value="<?php echo (int) $category['id']; ?>"
                                                        <?php echo ((int) $category['id'] === (int) $product['category_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="product-featured" class="form-label">Featured</label>
                                            <select name="featured" class="form-select" id="product-featured">
                                                <option value="1" <?php echo ((int) $product['is_featured'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                <option value="0" <?php echo ((int) $product['is_featured'] === 0) ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="product-active" class="form-label">Active</label>
                                            <select name="active" class="form-select" id="product-active">
                                                <option value="1" <?php echo ((int) $product['is_active'] === 1) ? 'selected' : ''; ?>>Yes</option>
                                                <option value="0" <?php echo ((int) $product['is_active'] === 0) ? 'selected' : ''; ?>>No</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex gap-2">
                                                <a href="admin_products.php" class="btn btn-outline-secondary w-50">Cancel</a>
                                                <button type="submit" class="btn btn-primary w-50" id="save-product">
                                                    Save changes
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php
require 'partials/footer.php';
require 'partials/scripts.php';
?>
<script>
    $(function () {
        var $form = $('#update-product-form');
        var $feedback = $('#update-feedback');
        var $saveButton = $('#save-product');
        var slugEdited = false;
        var previewUrl = null;

        $('#product-slug').on('input', function () {
            slugEdited = true;
        });

        $('#product-name').on('input', function () {
            if (!slugEdited) {
                $('#product-slug').val($(this).val()
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, ''));
            }
        });

        $('#product-image').on('change', function () {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
            }
            if (this.files && this.files[0]) {
                previewUrl = URL.createObjectURL(this.files[0]);
                $('#product-image-preview').attr('src', previewUrl);
            }
        });

        $form.on('submit', function (event) {
            event.preventDefault();
            $feedback.addClass('d-none').removeClass('alert-danger alert-success').empty();
            $saveButton.prop('disabled', true).text('Saving...');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: new FormData($form[0]),
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (response) {
                if (!response.success) {
                    showErrors(response.errors || [response.message || 'Unable to update the product.']);
                    return;
                }
                window.location.href = response.redirect;
            }).fail(function (xhr) {
                var response = xhr.responseJSON;
                showErrors(response && response.errors
                    ? response.errors
                    : [response && response.message ? response.message : 'Unable to update the product. Please try again.']);
            }).always(function () {
                $saveButton.prop('disabled', false).text('Save changes');
            });
        });

        function showErrors(messages) {
            var $list = $('<ul class="mb-0"></ul>');
            $.each(messages, function (_, message) {
                $('<li></li>').text(message).appendTo($list);
            });
            $feedback.addClass('alert-danger').removeClass('d-none alert-success').append($list);
        }
    });
</script>
