<?php
session_start();
require "functions.php";
role_check('admin');
require '../database/config.php';
require 'partials/head.php';
require 'partials/left_bar.php';
require  'partials/header.php';
?>
<div class="page-wrapper">
    <div class="page-content">

        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">Prodcuts</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Add New Product</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-light">Settings</button>
                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item" href="javascript:;">Action</a>
                        <a class="dropdown-item" href="javascript:;">Another action</a>
                        <a class="dropdown-item" href="javascript:;">Something else here</a>
                        <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated link</a>
                    </div>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title">Add New Product</h5>
                <hr />
                <div class="form-body mt-4">
                    <?php


                    $id = $_GET['id'];

                    $result = $conn->query("SELECT * FROM products WHERE id='$id'");

                    $product = $result->fetch_assoc();


                    ?>
                    <form action="create.php" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="border border-3 p-4 rounded">
                                    <div class="mb-3">
                                        <label for="inputProductTitle" class="form-label">Product Title</label>
                                        <input type="text" name="name" class="form-control" id="inputProductTitle" placeholder="Enter product title"
                                        value="<?php echo $product['name'] ?>"
                                        >
                                        
                                    </div>
                                    <div class="mb-3">
                                        <label for="inputProductDescription" class="form-label">Description</label>
                                        <textarea name="description" class="form-control" id="inputProductDescription" placeholder="Example........." rows="3">
                                            <?php echo $product['description'] ?>
                                        </textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="inputProductDescription" class="form-label">Product Images</label>
                                        <input name="image" type="file" accept="image/*" >
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border border-3 p-4 rounded">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="inputPrice" class="form-label">Price</label>
                                            <input name="price" type="text" class="form-control"
                                            value="<?php echo $product['price'] ?>"
                                            id="inputPrice" placeholder="price">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputCompareatprice" class="form-label">Stock</label>
                                            <input name="stock" type="number" class="form-control" id="inputCompareatprice" 
                                            value="<?php echo $product['stock'] ?>"
                                            placeholder="stock">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputCostPerPrice" class="form-label">Slug</label>
                                            <input name="slug" type="text" class="form-control" id="inputCostPerPrice" 
                                            value="<?php echo $product['slug'] ?>"
                                            placeholder="Slug">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="inputStarPoints" class="form-label">SKU</label>
                                            <input name="sku" type="text" class="form-control" 
                                            value="<?php echo $product['sku'] ?>"
                                            id="inputStarPoints" placeholder="SKU">
                                        </div>
                                        <div class="col-12">
                                            <label for="inputProductType" class="form-label">Category</label>
                                            <select name="categories" class="form-select" id="inputProductType">
                                                <option selected disabled>Select One</option>
                                                <?php
                                                $categories = $conn->query("SELECT * FROM categories");
                                                while ($ctg = $categories->fetch_assoc()) {
                                                ?>
                                                    <option value="<?php echo $ctg['id']; ?>">
                                                        <?php echo $ctg['name']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="inputVendor" class="form-label">Featured</label>
                                            <select name="featured" class="form-select" id="inputVendor">

                                                <option selected value="1">YES</option>
                                                <option value="0">No</option>

                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="inputCollection" class="form-label">Active</label>
                                            <select name="active" class="form-select" id="inputCollection">

                                                <option selected value="1">YES</option>
                                                <option value="0">No</option>


                                            </select>
                                        </div>
                                        <!-- <div class="col-12">
                                        <label for="inputProductTags" class="form-label">Product Tags</label>
                                        <input type="text" class="form-control" id="inputProductTags" placeholder="Enter Product Tags">
                                    </div> -->
                                        <div class="col-12">
                                            <div class="d-grid">
                                                <input type="submit" value="Save Product" class="btn btn-light">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end row-->
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>


<?php

require 'partials/footer.php';
require   'partials/scripts.php';


?>