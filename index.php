<?php

require_once 'database/config.php';
require_once 'config/database.php';
require_once 'config/bkash.php';
require_once  'includes/helpers.php';
require_once 'includes/store.php';

start_app_session();

$page = (string) ($_GET['page'] ?? 'home');
$action = (string) ($_GET['action'] ?? '');
$allowedPages = ['home', 'cart', 'checkout', 'login', 'register', 'orders'];

if (!in_array($page, $allowedPages, true)) {
    http_response_code(404);
    $page = 'home';
}

$flash = $_SESSION['_store_flash'] ?? null;
unset($_SESSION['_store_flash']);
$pageError = '';
$selectedDeliveryCityId = 0;

if ($action === 'bkash-callback') {
    $status = strtolower(trim((string) ($_GET['status'] ?? '')));
    $paymentId = trim((string) ($_GET['paymentID'] ?? $_GET['paymentId'] ?? ''));
    $orderId = filter_var($_GET['order'] ?? null, FILTER_VALIDATE_INT);
    $paymentOrder = ($orderId !== false && $orderId !== null)
        ? store_find_bkash_callback_order((int) $orderId)
        : null;

    if (
        !is_array($paymentOrder)
        || $paymentId === ''
        || (
            $paymentOrder['payment_status'] !== 'success'
            && (string) $paymentOrder['transaction_id'] !== $paymentId
        )
    ) {
        $_SESSION['_store_flash'] = ['type' => 'warning', 'message' => 'The bKash sandbox payment could not be verified.'];
        store_redirect('index.php?page=checkout');
    }

    try {
        $paymentResult = null;
        if ($status === 'success' || $paymentOrder['payment_status'] === 'success') {
            try {
                $paymentResult = $paymentOrder['payment_status'] === 'success'
                    ? bkash_query_payment($paymentId)
                    : bkash_execute_payment($paymentId);
            } catch (Throwable $executeException) {
                $paymentResult = bkash_query_payment($paymentId);
            }
            if (($paymentResult['transactionStatus'] ?? '') !== 'Completed') {
                $paymentResult = bkash_query_payment($paymentId);
            }
        }

        $resultAmount = is_array($paymentResult)
            ? (string) ($paymentResult['amount'] ?? '')
            : '';
        $resultInvoice = is_array($paymentResult)
            ? (string) ($paymentResult['merchantInvoiceNumber'] ?? $paymentResult['merchantInvoice'] ?? '')
            : '';
        $amountMatches = $resultAmount !== ''
            && number_format((float) $resultAmount, 2, '.', '') === number_format((float) $paymentOrder['payment_amount'], 2, '.', '')
            && number_format((float) $paymentOrder['payment_amount'], 2, '.', '') === number_format((float) $paymentOrder['total_amount'], 2, '.', '');
        $invoiceMatches = $resultInvoice === '' || hash_equals((string) $paymentOrder['order_number'], $resultInvoice);
        $transactionMatches = $paymentOrder['payment_status'] !== 'success'
            || hash_equals(
                (string) $paymentOrder['transaction_id'],
                (string) ($paymentResult['trxId'] ?? '')
            );

        if (
            is_array($paymentResult)
            && ($paymentResult['transactionStatus'] ?? '') === 'Completed'
            && $amountMatches
            && $invoiceMatches
            && $transactionMatches
        ) {
            $transactionId = (string) ($paymentResult['trxId'] ?? $paymentId);
            store_update_payment_by_order((int) $orderId, $transactionId, 'success');
            $_SESSION['store_cart'] = [];
            $_SESSION['last_order_id'] = (int) $orderId;
            $_SESSION['_store_flash'] = ['type' => 'success', 'message' => 'Your bKash sandbox payment was successful.'];
            store_redirect('index.php?page=orders&order=' . (int) $orderId);
        }

        if (in_array($status, ['failure', 'failed', 'cancel', 'cancelled', 'canceled'], true)
            && $paymentOrder['payment_status'] !== 'success') {
            store_cancel_unpaid_order((int) $orderId);
            $_SESSION['_store_flash'] = ['type' => 'warning', 'message' => 'The bKash sandbox payment was cancelled or failed.'];
            store_redirect('index.php?page=checkout');
        }

        $_SESSION['_store_flash'] = [
            'type' => 'warning',
            'message' => 'bKash returned to the shop, but the payment is still being verified. Check your order history shortly.',
        ];
        store_redirect('index.php?page=orders&order=' . (int) $orderId);
    } catch (Throwable $exception) {
        $_SESSION['_store_flash'] = ['type' => 'warning', 'message' => $exception->getMessage()];
        store_redirect('index.php?page=checkout');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($action === 'cart-add') {
        $productId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);



        $product = store_find_product_by_id($productId);

        if ($product === null || (int) $product['stock'] < 1) {
            $pageError = 'That flower is currently unavailable.';
        } else {
            $cart = store_cart();
            $currentQuantity = (int) ($cart[$productId] ?? 0);
            $cart[$productId] = min($currentQuantity + $quantity, (int) $product['stock']);
            $_SESSION['store_cart'] = $cart;
            $_SESSION['_store_flash'] = [
                'type' => 'success',
                'message' => $product['name'] . ' is in your basket.',
            ];
            store_redirect('index.php');
        }
    } elseif ($action === 'cart-update') {

        $cart = store_cart();
        $removeProductId = filter_var($_POST['remove_product_id'] ?? null, FILTER_VALIDATE_INT);

        if ($removeProductId !== false && $removeProductId !== null) {
            unset($cart[$removeProductId]);
            $_SESSION['store_cart'] = $cart;
            $_SESSION['_store_flash'] = ['type' => 'success', 'message' => 'That flower was removed from your basket.'];
            store_redirect('index.php?page=cart');
        }

        $submittedQuantities = $_POST['quantity'] ?? [];

        if (!is_array($submittedQuantities)) {
            throw new InvalidArgumentException('Invalid cart quantities.');
        }

        foreach ($cart as $productId => $oldQuantity) {
            $submitted = $submittedQuantities[$productId] ?? '0';

            $quantity = (int) $submitted;
            if ($quantity === 0) {
                unset($cart[$productId]);
                continue;
            }

            $product = store_find_product_by_id($productId);
            if ($product === null || (int) $product['stock'] < 1) {
                unset($cart[$productId]);
                continue;
            }

            $cart[$productId] = min($quantity, (int) $product['stock']);
        }

        $_SESSION['store_cart'] = $cart;
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            $updatedItems = store_get_cart_items();
            $responseItems = [];
            foreach ($updatedItems as $item) {
                $responseItems[] = [
                    'id' => (int) $item['id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['discounted_price'],
                    'line_total' => (float) $item['line_total'],
                ];
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'items' => $responseItems,
                'subtotal' => store_cart_subtotal($updatedItems),
            ]);
            exit;
        }
        $_SESSION['_store_flash'] = ['type' => 'success', 'message' => 'Your basket has been updated.'];
        store_redirect('index.php?page=cart');
    } elseif ($action === 'register') {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        if ($name === '' || strlen($name) > 100) {
            $pageError = 'Please enter your name (up to 100 characters).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $pageError = 'Please enter a valid email address.';
        } elseif ($phone !== '' && strlen($phone) > 20) {
            $pageError = 'Please enter a phone number no longer than 20 characters.';
        } elseif (strlen($password) < 8) {
            $pageError = 'Choose a password with at least 8 characters.';
        } elseif ($password !== $passwordConfirmation) {
            $pageError = 'Your password confirmation does not match.';
        } else {


            $password = password_hash($password, PASSWORD_DEFAULT);
          
            $conn->query(
                "INSERT INTO users (name, email, password, phone, role)
                     VALUES ('$name', '$email', '$password', $phone, 'customer')"
            );
            store_sign_in($conn->insert_id, $name, $email, $phone, 'customer');
            $_SESSION['_store_flash'] = ['type' => 'success', 'message' => 'Your account is ready. Welcome to Bloom Basket!'];
            store_redirect(($_GET['next'] ?? '') === 'checkout' ? 'index.php?page=checkout' : 'index.php');
        }
    } elseif ($action === 'login') {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $pageError = 'Enter a valid email address and password.';
        } else {

            $result = $conn->query(
                "SELECT id, name, email, password, phone, role
                     FROM users WHERE email = '$email' LIMIT 1"
            );
            $user = $result->fetch_assoc();

            if (
                is_array($user)
                && $user['role'] === 'customer'
                && password_verify($password, (string) $user['password'])
            ) {
                store_sign_in(
                    (int) $user['id'],
                    (string) $user['name'],
                    (string) $user['email'],
                    (string) ($user['phone'] ?? ''),
                    'customer'
                );
                $_SESSION['_store_flash'] = ['type' => 'success', 'message' => 'Welcome back!'];
                store_redirect(($_GET['next'] ?? '') === 'checkout' ? 'index.php?page=checkout' : 'index.php');
            }

        }
    } elseif ($action === 'logout') {
        unset($_SESSION['customer_user']);
        session_regenerate_id(true);
        $_SESSION['_store_flash'] = ['type' => 'success', 'message' => 'You have signed out. See you again soon.'];
        store_redirect('index.php');
    } elseif ($action === 'checkout') {
        if (!store_customer_is_signed_in()) {
            $_SESSION['_store_flash'] = ['type' => 'warning', 'message' => 'Sign in or create an account to place your order.'];
            store_redirect('index.php?page=login');
        }

        $recipient = trim((string) ($_POST['recipient_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['shipping_address'] ?? ''));
        $area = trim((string) ($_POST['area'] ?? ''));
        $selectedDeliveryCityId = filter_var($_POST['city_id'] ?? null, FILTER_VALIDATE_INT);
        if ($selectedDeliveryCityId === false || $selectedDeliveryCityId === null) {
            $selectedDeliveryCityId = 0;
        }
        $deliveryCity = $selectedDeliveryCityId !== false && $selectedDeliveryCityId !== null
            ? store_find_delivery_city($selectedDeliveryCityId)
            : null;
        $city = is_array($deliveryCity) ? (string) $deliveryCity['city_name'] : '';
        $deliveryFee = is_array($deliveryCity) ? (float) $deliveryCity['delivery_charge'] : 0.0;
        $postalCode = trim((string) ($_POST['postal_code'] ?? ''));
        $paymentMethod = (string) ($_POST['payment_method'] ?? '');

        if ($recipient === '' || strlen($recipient) > 100) {
            $pageError = 'Enter the recipient’s name (up to 100 characters).';
        } elseif ($phone === '' || strlen($phone) > 20) {
            $pageError = 'Enter a valid contact phone number.';
        } elseif ($address === '' || strlen($address) > 255) {
            $pageError = 'Enter a delivery address (up to 255 characters).';
        } elseif (strlen($area) > 100 || strlen($postalCode) > 20) {
            $pageError = 'Check the area and postal code fields.';
        } elseif (!is_array($deliveryCity)) {
            $pageError = 'Choose a city where we deliver.';
        } elseif (!in_array($paymentMethod, ['cod', 'bkash'], true)) {
            $pageError = 'Choose one of the available payment methods.';
        } else {
            $orderId = null;
            try {
                if ($paymentMethod === 'bkash') {
                    $missingCredentials = bkash_credentials_missing();
                    if ($missingCredentials !== []) {
                        throw new RuntimeException(
                            'bKash sandbox credentials are not configured. Add BKASH_USERNAME, BKASH_PASSWORD, BKASH_APP_KEY, and BKASH_APP_SECRET to config/bkash.local.php.'
                        );
                    }
                }

                $orderId = store_place_order(
                    (int) $_SESSION['customer_user']['id'],
                    $recipient,
                    $phone,
                    $address,
                    $area,
                    $city,
                    $postalCode,
                    $paymentMethod,
                    $deliveryFee,
                    'pending'
                );

                if ($paymentMethod === 'bkash') {
                    $customerId = (int) $_SESSION['customer_user']['id'];
                    $order = store_find_customer_order($orderId, $customerId);
                    if (!is_array($order)) {
                        throw new RuntimeException('The pending order could not be found.');
                    }

                    $payment = bkash_create_payment(
                        (float) $order['total_amount'],
                        (string) $order['order_number'],
                        $orderId
                    );
                    store_update_payment_by_order($orderId, (string) $payment['paymentId'], 'pending');
                    store_redirect((string) $payment['bkashURL']);
                }

                $_SESSION['store_cart'] = [];
                $_SESSION['last_order_id'] = $orderId;
                store_redirect('index.php?page=orders');
            } catch (Throwable $exception) {
                if (is_int($orderId) && $orderId > 0 && $paymentMethod === 'bkash') {
                    try {
                        store_cancel_unpaid_order($orderId);
                    } catch (Throwable $cleanupException) {
                        // Keep the original bKash error visible if cleanup also fails.
                    }
                }
                $pageError = $exception->getMessage();
            }
        }
    } else {

        $pageError = 'That action is not available.';
    }
}

$customer = store_customer();
$cartCount = store_cart_count();
$cartItems = [];
$deliveryCities = [];
$categories = [];
$products = [];
$selectedProduct = null;
$orders = [];
$orderItems = [];
$orderNotice = '';
$catalogError = false;

if (in_array($page, ['home', 'cart', 'checkout', 'orders'], true)) {
   
        if ($page === 'home') {
            $categories = store_get_categories();
            $products = store_get_products(
                trim((string) ($_GET['q'] ?? '')),
                trim((string) ($_GET['category'] ?? '')),
                isset($_GET['featured']) && $_GET['featured'] === '1',
                isset($_GET['product']) ? trim((string) $_GET['product']) : null
            );

            if (isset($_GET['product'])) {
                $selectedProduct = store_find_product_by_slug(trim((string) $_GET['product']));
            }
        }

        if ($page === 'cart' || $page === 'checkout') {
            $cartItems = store_get_cart_items();
        }
        if ($page === 'checkout' && $cartItems !== []) {
            $deliveryCities = store_get_delivery_cities();
            if ($selectedDeliveryCityId === 0 && $deliveryCities !== []) {
                $selectedDeliveryCityId = (int) $deliveryCities[0]['id'];
            }
        }

        if ($page === 'orders' && store_customer_is_signed_in()) {
            $orders = store_get_customer_orders((int) $customer['id']);
            if (isset($_GET['order']) && ctype_digit((string) $_GET['order'])) {
                $orderItems = store_get_order_items((int) $_GET['order'], (int) $customer['id']);
                if ($orderItems !== null) {
                    $selectedOrder = store_find_customer_order((int) $_GET['order'], (int) $customer['id']);
                    $orderNotice = is_array($selectedOrder) ? (string) $selectedOrder['order_number'] : '';
                }
            } elseif (isset($_SESSION['last_order_id'])) {
                $lastOrderId = (int) $_SESSION['last_order_id'];
                unset($_SESSION['last_order_id']);
                $orderItems = store_get_order_items($lastOrderId, (int) $customer['id']);
                if ($orderItems !== null) {
                    $selectedOrder = store_find_customer_order($lastOrderId, (int) $customer['id']);
                    $orderNotice = is_array($selectedOrder) ? (string) $selectedOrder['order_number'] : '';
                }
            }
        }
    }

$pageTitles = [
    'home' => $selectedProduct !== null ? (string) $selectedProduct['name'] : 'Fresh flowers, thoughtfully delivered',
    'cart' => 'Your basket',
    'checkout' => 'A little something, on its way',
    'login' => 'Welcome back',
    'register' => 'Join the garden',
    'orders' => 'Your orders',
];
$pageTitle = $pageTitles[$page] ?? $pageTitles['home'];
$query = trim((string) ($_GET['q'] ?? ''));
$selectedCategory = trim((string) ($_GET['category'] ?? ''));
$cartSubtotal = store_cart_subtotal($cartItems);
$selectedDeliveryCity = null;
foreach ($deliveryCities as $deliveryCity) {
    if ((int) $deliveryCity['id'] === (int) $selectedDeliveryCityId) {
        $selectedDeliveryCity = $deliveryCity;
        break;
    }
}
$deliveryFee = $page === 'checkout' && is_array($selectedDeliveryCity)
    ? (float) $selectedDeliveryCity['delivery_charge']
    : 0.0;

require 'shop/partials/header.php';
?>

<?php if ($page === 'home'): ?>
    <?php if ($selectedProduct !== null): ?>
        <?php require 'shop/product-detail.php'; ?>
    <?php else: ?>
        <?php require 'shop/home.php'; ?>
    <?php endif; ?>
<?php elseif ($page === 'cart'): ?>
    <?php require 'shop/cart.php'; ?>
<?php elseif ($page === 'checkout'): ?>
    <?php require 'shop/checkout.php'; ?>
<?php elseif ($page === 'login'): ?>
    <?php require 'shop/login.php'; ?>
<?php elseif ($page === 'register'): ?>
    <?php require 'shop/register.php'; ?>
<?php elseif ($page === 'orders'): ?>
    <?php require 'shop/orders.php'; ?>
<?php endif; ?>

<?php require 'shop/partials/footer.php'; ?>