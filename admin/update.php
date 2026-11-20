<?php
session_start();
require "functions.php";
role_check('admin');
require "../database/config.php";

header('Content-Type: application/json; charset=utf-8');

$respond = static function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $respond(405, ['success' => false, 'message' => 'This endpoint only accepts product updates.']);
}

$getString = static function (string $key): string {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
};

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$name = $getString('name');
$description = $getString('description');
$priceInput = $getString('price');
$stockInput = $getString('stock');
$slug = $getString('slug');
$sku = $getString('sku');
$categoryInput = $getString('categories');
$featuredInput = $getString('featured');
$activeInput = $getString('active');

$errors = [];
$price = is_numeric($priceInput) ? (float) $priceInput : 0;
$stock = filter_var($stockInput, FILTER_VALIDATE_INT);
$categoryId = filter_var($categoryInput, FILTER_VALIDATE_INT);
$featured = filter_var($featuredInput, FILTER_VALIDATE_INT);
$active = filter_var($activeInput, FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $errors[] = "A valid product ID is required.";
}
if ($name === '') {
    $errors[] = "Product title is required.";
} elseif (strlen($name) > 150) {
    $errors[] = "Product title must be 150 characters or fewer.";
}
if ($description === '') {
    $errors[] = "Description is required.";
}
if (!is_numeric($priceInput) || $price <= 0 || $price > 99999999.99 || !is_finite($price)) {
    $errors[] = "Price must be greater than 0 and no more than 99,999,999.99.";
}
if ($stock === false || $stock < 0) {
    $errors[] = "Stock must be a whole number that is 0 or greater.";
}
if ($slug === '') {
    $errors[] = "Slug is required.";
} elseif (strlen($slug) > 170) {
    $errors[] = "Slug must be 170 characters or fewer.";
}
if ($sku === '') {
    $errors[] = "SKU is required.";
} elseif (strlen($sku) > 50) {
    $errors[] = "SKU must be 50 characters or fewer.";
}
if ($categoryId === false || $categoryId < 1) {
    $errors[] = "Please select a valid category.";
}
if (!in_array($featured, [0, 1], true)) {
    $errors[] = "Featured must be Yes or No.";
}
if (!in_array($active, [0, 1], true)) {
    $errors[] = "Active must be Yes or No.";
}

if (!$errors) {
    $productQuery = $conn->prepare("SELECT id FROM products WHERE id = ?");
    if (!$productQuery) {
        error_log("Product update record check could not be prepared: " . $conn->error);
        $respond(500, ['success' => false, 'message' => 'Unable to load the product right now.']);
    }
    $productQuery->bind_param("i", $id);
    if (!$productQuery->execute()) {
        error_log("Product update record check failed: " . $productQuery->error);
        $productQuery->close();
        $respond(500, ['success' => false, 'message' => 'Unable to load the product right now.']);
    }
    $productQuery->store_result();
    $productExists = $productQuery->num_rows > 0;
    $productQuery->close();
    if (!$productExists) {
        $respond(404, ['success' => false, 'message' => 'Product not found.']);
    }

    $categoryQuery = $conn->prepare("SELECT id FROM categories WHERE id = ?");
    if (!$categoryQuery) {
        error_log("Product update category check could not be prepared: " . $conn->error);
        $respond(500, ['success' => false, 'message' => 'Unable to validate the selected category.']);
    }
    $categoryQuery->bind_param("i", $categoryId);
    if (!$categoryQuery->execute()) {
        error_log("Product update category check failed: " . $categoryQuery->error);
        $categoryQuery->close();
        $respond(500, ['success' => false, 'message' => 'Unable to validate the selected category.']);
    }
    $categoryQuery->store_result();
    if ($categoryQuery->num_rows === 0) {
        $errors[] = "Please select a valid category.";
    }
    $categoryQuery->close();
}

$imagePath = null;
$uploadedFile = null;
$image = $_FILES['image'] ?? null;
if (!$errors && is_array($image) && ($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $uploadError = $image['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($uploadError !== UPLOAD_ERR_OK) {
        $errors[] = "The image could not be uploaded.";
    } elseif (($image['size'] ?? 0) > 2 * 1024 * 1024) {
        $errors[] = "The image must be 2 MB or smaller.";
    } elseif (!isset($image['tmp_name']) || !is_uploaded_file($image['tmp_name'])) {
        $errors[] = "The selected image is invalid.";
    } else {
        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($image['tmp_name']);
        $allowedTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowedTypes[$mimeType])) {
            $errors[] = "Choose a JPG, PNG, WEBP or GIF image.";
        } else {
            $uploadDirectory = 'admin/assets/images/products';
            if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                $errors[] = "The image could not be saved. Please try again.";
            } else {
                $fileName = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mimeType];
                $uploadedFile = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
                $imagePath = 'assets/images/products/' . $fileName;
                if (!move_uploaded_file($image['tmp_name'], $uploadedFile)) {
                    $uploadedFile = null;
                    $imagePath = null;
                    $errors[] = "The image could not be saved. Please try again.";
                }
            }
        }
    }
}

if ($errors) {
    $respond(422, ['success' => false, 'errors' => $errors]);
}

if ($imagePath === null) {
    $statement = $conn->prepare(
        "UPDATE products
         SET name = ?, description = ?, price = ?, stock = ?, slug = ?, sku = ?,
             category_id = ?, is_featured = ?, is_active = ?
         WHERE id = ?"
    );
    if ($statement) {
        $statement->bind_param(
            "ssdissiiii",
            $name,
            $description,
            $price,
            $stock,
            $slug,
            $sku,
            $categoryId,
            $featured,
            $active,
            $id
        );
    }
} else {
    $statement = $conn->prepare(
        "UPDATE products
         SET name = ?, description = ?, price = ?, stock = ?, slug = ?, sku = ?,
             category_id = ?, is_featured = ?, is_active = ?, image_path = ?
         WHERE id = ?"
    );
    if ($statement) {
        $statement->bind_param(
            "ssdissiiisi",
            $name,
            $description,
            $price,
            $stock,
            $slug,
            $sku,
            $categoryId,
            $featured,
            $active,
            $imagePath,
            $id
        );
    }
}

if (!$statement) {
    if ($uploadedFile && is_file($uploadedFile)) {
        unlink($uploadedFile);
    }
    error_log("Product update query could not be prepared: " . $conn->error);
    $respond(500, ['success' => false, 'message' => 'Unable to update the product right now.']);
}

if (!$statement->execute()) {
    $databaseError = $statement->error;
    $errorCode = $statement->errno;
    $statement->close();
    if ($uploadedFile && is_file($uploadedFile)) {
        unlink($uploadedFile);
    }
    if ($errorCode === 1062 || strpos($databaseError, 'Duplicate entry') !== false) {
        $respond(422, ['success' => false, 'errors' => ['That SKU or slug is already in use.']]);
    }
    error_log("Product update failed: " . $databaseError);
    $respond(500, ['success' => false, 'message' => 'Unable to update the product right now.']);
}

$statement->close();
$respond(200, [
    'success' => true,
    'message' => 'Product updated successfully.',
    'redirect' => 'admin_products.php',
]);
