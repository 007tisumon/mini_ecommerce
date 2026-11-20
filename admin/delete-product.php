<?php
session_start();
require "functions.php";
role_check('admin');
require "../database/config.php";



$id = $_POST['id'];


$select = $conn->query("SELECT image_path FROM products WHERE id = '$id'");

$product = $select->fetch_assoc();

if (!$product) {
    header('Location: admin_products.php');
    exit();
}

$delete = $conn->query("DELETE FROM products WHERE id = '$id'");
if (!$delete) {
    error_log('Product delete preparation failed: ' . $conn->error);
    header('Location: admin_products.php');
    exit();
}

$deleted = $delete;


if ($deleted && !empty($product['image_path'])) {
    $imagePath = basename($product['image_path']);
    $imageFile = 'admin/assets/images/products/' . $imagePath;
    if (is_file($imageFile) && strpos($product['image_path'], 'assets/images/products/') === 0) {
        unlink($imageFile);
    }
}

header('Location: admin_products.php');
exit();