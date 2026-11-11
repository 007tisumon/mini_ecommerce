<?php
session_start();
require "functions.php";
role_check('admin');
require "../database/config.php";
// echo "create php";
// echo $_POST['name'];



if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $id = $_POST['id'];
       

    $errors = [];
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price'] ?? 0);
    $stock       = intval($_POST['stock'] ?? 0);
    $slug        = trim($_POST['slug'] ?? '');
    $sku         = trim($_POST['sku'] ?? '');
    $category_id = intval($_POST['categories'] ?? 0);
    $featured    = intval($_POST['featured'] ?? 1);
    $active      = intval($_POST['active'] ?? 1);

    //    validation check 

    if (empty($name))        $errors[] = "Product title is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($price <= 0)         $errors[] = "Price must be greater than 0.";
    if ($stock < 0)          $errors[] = "Stock cannot be negative.";
    if (empty($slug))        $errors[] = "Slug is required.";
    if (empty($sku))         $errors[] = "SKU is required.";
    if ($category_id <= 0)   $errors[] = "Please select a valid category.";

     // image handle 
    //     $image_path = '';

    // if (isset($_FILES['image']) && !empty($_FILES['image'])) {
    //     $img = $_FILES['image'];

    //     $upload_dir = 'assets/images/products/';
    //     if (!is_dir($upload_dir)) {
    //         mkdir($upload_dir, 0755, true);
    //     }
    //     $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    //     $max_size = 2 * 1024 * 1024; // 2MB per file
    //     $imgExt = pathinfo($img['name'], PATHINFO_EXTENSION);


    //     if ($img['size'] > $max_size) {
    //         $errors[] = "Image {$img['name']} exceeds the 2MB size limit.";
    //     }
    //     if (!in_array($img['type'], $allowed_types)) {
    //         $errors[] = "Image {$img['name']} has an invalid format. Allowed: JPG, PNG, WEBP, GIF.";
    //     }

    //     $new_file_name = uniqid('prod_', true) . '.' . $imgExt;
    //     $dest_path = $upload_dir . $new_file_name;
    //     // echo "destpath". $dest_path . "<br>";
    //     // echo "imgext" . $imgExt . "<br>";
    //     if (move_uploaded_file($img['tmp_name'], $dest_path)) {
    //         $image_path = $dest_path;
    //     } else {
    //         $errors[] = "Failed to upload image: {$img['name']}";
    //     }
    // }

   // insert into databse here  

    if (empty($errors)) {

        echo $sql = "UPDATE products SET name='$name', description='$description', price = '$price', stock = '$stock', slug = '$slug', sku = '$sku', category_id = '$category_id', is_featured= '$featured', is_active = '$active' WHERE id='$id';";
                    // -- VALUES (null, '$name', '$description', '$price', '$stock', '$slug', '$sku', '$category_id', '$featured', '$active', '$image_path')";

        $result =  $conn->query($sql);

        if ($result) {
            // FIX: Always append exit/die right after header() redirection
            header("Location: admin_products.php");
            exit;
        } else {
            $errors[] = "Database insertion failed: " . $conn->error;
        }
    }
}
