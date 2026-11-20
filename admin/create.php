<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');

session_start();
require "functions.php";

header('Content-Type: application/json; charset=utf-8');

function respond_json(int $status, array $response)
{
    http_response_code($status);
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['email'])) {
    respond_json(401, ['success' => false, 'message' => 'Your session has expired. Sign in again, then retry.']);
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    respond_json(403, ['success' => false, 'message' => 'You are not authorized to create products.']);
}

require "../database/config.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(405, ['success' => false, 'message' => 'Use the product form to submit this request.']);
}

if (!$conn) {
    error_log('Product insert failed: database connection is unavailable.');
    respond_json(500, ['success' => false, 'message' => 'The database is unavailable. Please try again later.']);
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$price_input = trim($_POST['price'] ?? '');
$stock_input = $_POST['stock'] ?? '';
$slug = trim($_POST['slug'] ?? '');
$sku = trim($_POST['sku'] ?? '');
$category_input = $_POST['categories'] ?? '';
$featured = $_POST['featured'] ?? '';
$active = $_POST['active'] ?? '';
$errors = [];

if ($name === '') {
    $errors[] = 'Product title is required.';
}
if ($description === '') {
    $errors[] = 'Description is required.';
}
if (!is_numeric($price_input) || (float) $price_input <= 0 || !is_finite((float) $price_input)) {
    $errors[] = 'Price must be a valid number greater than 0.';
}

$stock = filter_var($stock_input, FILTER_VALIDATE_INT);
if ($stock === false || $stock < 0) {
    $errors[] = 'Stock must be a non-negative whole number.';
}
if ($slug === '') {
    $errors[] = 'Slug is required.';
}
if ($sku === '') {
    $errors[] = 'SKU is required.';
}

$category_id = filter_var($category_input, FILTER_VALIDATE_INT);
if ($category_id === false || $category_id <= 0) {
    $errors[] = 'Please select a valid category.';
}
if (!in_array($featured, ['0', '1'], true)) {
    $errors[] = 'Please select whether the product is featured.';
}
if (!in_array($active, ['0', '1'], true)) {
    $errors[] = 'Please select whether the product is active.';
}

$category_check = false;
try {
    $category_check = $conn->prepare('SELECT id FROM categories WHERE id = ?');
} catch (mysqli_sql_exception $exception) {
    error_log('Category lookup preparation failed: ' . $exception->getMessage());
    respond_json(500, ['success' => false, 'message' => 'Unable to validate the selected category.']);
}
if (!$category_check) {
    error_log('Category lookup failed: ' . $conn->error);
    respond_json(500, ['success' => false, 'message' => 'Unable to validate the selected category.']);
}
$category_check->bind_param('i', $category_id);
try {
    $category_checked = $category_check->execute();
} catch (mysqli_sql_exception $exception) {
    error_log('Category lookup failed: ' . $exception->getMessage());
    $category_check->close();
    respond_json(500, ['success' => false, 'message' => 'Unable to validate the selected category.']);
}
if (!$category_checked) {
    error_log('Category lookup failed: ' . $category_check->error);
    $category_check->close();
    respond_json(500, ['success' => false, 'message' => 'Unable to validate the selected category.']);
}
$category_check->store_result();
if ($category_check->num_rows !== 1) {
    $errors[] = 'Please select an existing category.';
}
$category_check->close();

if ($errors) {
    respond_json(422, ['success' => false, 'message' => implode(' ', $errors)]);
}

$image_path = null;
$image = $_FILES['image'] ?? null;
if ($image !== null && $image['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($image['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'The image upload failed. Please choose the image again.';
    } elseif ($image['size'] > 2 * 1024 * 1024) {
        $errors[] = 'The image must be no larger than 2 MB.';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = $finfo ? finfo_file($finfo, $image['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($extensions[$mime_type])) {
            $errors[] = 'Image format must be JPG, PNG, WEBP, or GIF.';
        } else {
            $upload_directory = 'admin/assets/images/products';
            if (!is_dir($upload_directory) && !mkdir($upload_directory, 0755, true) && !is_dir($upload_directory)) {
                $errors[] = 'The image upload folder could not be created.';
            } else {
                $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime_type];
                $destination = $upload_directory . '/' . $filename;
                if (!move_uploaded_file($image['tmp_name'], $destination)) {
                    $errors[] = 'The image could not be saved. Please try again.';
                } else {
                    $image_path = 'assets/images/products/' . $filename;
                }
            }
        }
    }
}

if ($errors) {
    respond_json(422, ['success' => false, 'message' => implode(' ', $errors)]);
}

$price = (float) $price_input;
$insert = false;
try {
    $insert = $conn->prepare(
        'INSERT INTO products (name, description, price, stock, slug, sku, category_id, is_featured, is_active, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
} catch (mysqli_sql_exception $exception) {
    if ($image_path !== null) {
        unlink('admin/' . $image_path);
    }
    error_log('Product insert preparation failed: ' . $exception->getMessage());
    respond_json(500, ['success' => false, 'message' => 'Could not prepare the product record. Check the database schema and try again.']);
}
if (!$insert) {
    if ($image_path !== null) {
        unlink('admin/' . $image_path);
    }
    error_log('Product insert preparation failed: ' . $conn->error);
    respond_json(500, ['success' => false, 'message' => 'The database could not prepare the product insert. Check the PHP error log.']);
}

$insert->bind_param(
    'ssdissiiis',
    $name,
    $description,
    $price,
    $stock,
    $slug,
    $sku,
    $category_id,
    $featured,
    $active,
    $image_path
);

$inserted = false;
$insert_error = null;
$insert_error_code = 0;
try {
    $inserted = $insert->execute();
} catch (mysqli_sql_exception $exception) {
    $insert_error = $exception->getMessage();
    $insert_error_code = (int) $exception->getCode();
}

if (!$inserted) {
    if ($image_path !== null) {
        unlink('admin/' . $image_path);
    }
    $insert_error_code = $insert_error_code ?: $insert->errno;
    error_log('Product insert failed: ' . ($insert_error ?? $insert->error));
    $insert->close();
    if ($insert_error_code === 1062) {
        respond_json(409, ['success' => false, 'message' => 'That SKU or slug is already in use. Choose a unique value and try again.']);
    }
    respond_json(500, ['success' => false, 'message' => 'The database rejected the product. Check the PHP error log for details.']);
}

$insert->close();
respond_json(200, ['success' => true, 'redirect' => 'admin_products.php']);
