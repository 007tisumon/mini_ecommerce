<?php



function store_redirect($path)
{
    header('Location: ' . ltrim($path, '/'));
    exit;
}

function store_customer()
{
    start_app_session();
    $customer = $_SESSION['customer_user'] ?? null;

    return is_array($customer) && ($customer['role'] ?? '') === 'customer' ? $customer : null;
}

function store_customer_is_signed_in()
{
    return store_customer() !== null;
}

function store_sign_in($id, $name, $email, $phone, $role = 'customer')
{
    if ($role !== 'customer') {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['customer_user'] = [
        'id' => (int) $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => 'customer',
    ];
    return true;
}

function store_cart()
{
    $cart = $_SESSION['store_cart'] ?? [];
    if (!is_array($cart)) {
        return [];
    }

    $cleanCart = [];
    foreach ($cart as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;
        if ($productId > 0 && $quantity > 0) {
            $cleanCart[$productId] = min($quantity, 20);
        }
    }

    return $cleanCart;
}

function store_cart_count()
{
    return array_sum(store_cart());
}

function store_money($amount)
{
    return '৳' . number_format((float) $amount, 2);
}

function store_discounted_price($product)
{
    $price = (float) ($product['price'] ?? 0);
    $discount = max(0, min(100, (float) ($product['discount_percent'] ?? 0)));

    return round($price * (1 - $discount / 100), 2);
}

function store_product_image($product)
{
    $imagePath = str_replace('\\', '/', trim($product['image_path'] ?? ''));

    if (strpos($imagePath, 'assets/images/products/') === 0) {
        if (is_file('admin/' . $imagePath)) {
            return 'admin/' . $imagePath;
        }
    }

    $localImage = 'assets/images/' . ltrim($imagePath, '/');
    if ($imagePath !== '' && is_file($localImage)) {
        return 'assets/images/' . ltrim($imagePath, '/');
    }

    return store_product_fallback_image($product);
}

function store_product_fallback_image($product)
{
    $images = [
        'red-rose' => '1494972308805-463bc619d34e',
        'white-rose' => '1518895949257-7621c3c786d7',
        'pink-rose' => '1487530811176-3780de880c2d',
        'romantic-rose-bouquet' => '1525310072745-f49212b5ac6d',
        'mixed-flower-bouquet' => '1561181286-d3fee7d55364',
        'premium-flower-bouquet' => '1520763185298-1b434c919102',
        'wedding-rose-set' => '1494972308805-463bc619d34e',
        'wedding-flower-garland' => '1520763185298-1b434c919102',
        'peace-lily' => '1497250681960-ef046c08a56e',
        'snake-plant' => '1501004318641-b39e6451bec6',
        'birthday-flower-box' => '1487530811176-3780de880c2d',
        'anniversary-flower-box' => '1525310072745-f49212b5ac6d',
    ];

    $categoryImages = [
        'roses' => '1518895949257-7621c3c786d7',
        'bouquets' => '1525310072745-f49212b5ac6d',
        'wedding-flowers' => '1520763185298-1b434c919102',
        'indoor-plants' => '1497250681960-ef046c08a56e',
        'gift-flowers' => '1487530811176-3780de880c2d',
    ];

    $slug = $product['slug'] ?? '';
    $categorySlug = $product['category_slug'] ?? '';
    $photo = $images[$slug] ?? $categoryImages[$categorySlug] ?? '1490750967868-88aa4486c946';

    return 'https://images.unsplash.com/photo-' . $photo . '?auto=format&fit=crop&w=800&q=80';
}

function store_find_product_by_id($id)
{
    $id = (int) $id;
    $result = database()->query(
        "SELECT p.id, p.name, p.price, p.discount_percent, p.stock, p.is_active
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.id = $id AND p.is_active = 1 AND c.is_active = 1
         LIMIT 1"
    );

    return $result->fetch_assoc() ?: null;
}

function store_find_product_by_slug($slug)
{
    $slug = database()->real_escape_string($slug);
    $result = database()->query(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.slug = '$slug' AND p.is_active = 1 AND c.is_active = 1
         LIMIT 1"
    );

    return $result->fetch_assoc() ?: null;
}

function store_get_categories()
{
    $result = database()->query(
        "SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS product_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
         WHERE c.is_active = 1
         GROUP BY c.id, c.name, c.slug, c.description
         ORDER BY c.name"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}

function store_get_delivery_cities()
{
    $result = database()->query(
        "SELECT id, city_name, delivery_charge
         FROM delivery_cities
         WHERE is_active = 1
         ORDER BY city_name"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}

function store_find_delivery_city($cityId)
{
    $cityId = (int) $cityId;
    if ($cityId < 1) {
        return null;
    }

    $result = database()->query(
        "SELECT id, city_name, delivery_charge
         FROM delivery_cities
         WHERE id = $cityId AND is_active = 1
         LIMIT 1"
    );

    return $result->fetch_assoc() ?: null;
}

function store_get_products($search = '', $category = '', $featured = false, $excludeSlug = null)
{
    $conditions = ['p.is_active = 1', 'c.is_active = 1'];

    if ($search !== '') {
        $search = database()->real_escape_string($search);
        $conditions[] = "(p.name LIKE '%$search%'
            OR p.description LIKE '%$search%'
            OR p.sku LIKE '%$search%'
            OR c.name LIKE '%$search%')";
    }
    if ($category !== '') {
        $category = database()->real_escape_string($category);
        $conditions[] = "c.slug = '$category'";
    }
    if ($featured) {
        $conditions[] = 'p.is_featured = 1';
    }
    if ($excludeSlug !== null) {
        $excludeSlug = database()->real_escape_string($excludeSlug);
        $conditions[] = "p.slug <> '$excludeSlug'";
    }

    $result = database()->query(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE ' . implode(' AND ', $conditions) . '
         ORDER BY p.is_featured DESC, p.created_at DESC
         LIMIT 24'
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}

function store_get_cart_items()
{
    $cart = store_cart();
    if (!$cart) {
        return [];
    }

    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = database()->query(
        "SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1 AND c.is_active = 1 AND p.id IN ($ids)"
    );

    $items = [];
    while ($product = $result->fetch_assoc()) {
        $id = (int) $product['id'];
        $quantity = min($cart[$id] ?? 0, (int) $product['stock']);
        if ($quantity < 1) {
            continue;
        }

        $product['quantity'] = $quantity;
        $product['discounted_price'] = store_discounted_price($product);
        $product['line_total'] = $product['discounted_price'] * $quantity;
        $items[] = $product;
    }

    return $items;
}

function store_cart_subtotal($items)
{
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += (float) $item['line_total'];
    }
    return $subtotal;
}

function bkash_api_post(string $endpoint, array $headers, array $payload): array
{
    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required for bKash sandbox payments.');
    }

    $curl = curl_init($endpoint);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json', 'Accept: application/json'], $headers),
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
   

    if ($response === false) {
        throw new RuntimeException('Could not connect to bKash: ' . $curlError);
    }

    $decoded = json_decode((string) $response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('bKash returned an invalid response.');
    }
    if ($httpStatus >= 400) {
        throw new RuntimeException((string) ($decoded['errorMessageEn'] ?? $decoded['statusMessage'] ?? 'bKash rejected the payment request.'));
    }

    return $decoded;
}

function bkash_access_token(): string
{
    start_app_session();
    $now = time();
    $cached = $_SESSION['bkash_access_token'] ?? null;

    if (is_array($cached) && ($cached['expires_at'] ?? 0) > $now + 300 && !empty($cached['id_token'])) {
        return (string) $cached['id_token'];
    }

    $config = bkash_config();
    if ($config['username'] === '' || $config['password'] === '' || $config['app_key'] === '' || $config['app_secret'] === '') {
        throw new RuntimeException('bKash sandbox credentials are not configured.');
    }

    $response = bkash_api_post(
        $config['base_url'] . '/auth/grant-token',
        [
            'username: ' . $config['username'],
            'password: ' . $config['password'],
        ],
        [
            'app_key' => $config['app_key'],
            'app_secret' => $config['app_secret'],
        ]
    );

    $idToken = (string) ($response['id_token'] ?? '');
    if ($idToken === '') {
        throw new RuntimeException((string) ($response['statusMessage'] ?? 'bKash could not create an access token.'));
    }

    $_SESSION['bkash_access_token'] = [
        'id_token' => $idToken,
        'expires_at' => $now + (int) ($response['expires_in'] ?? 3600),
    ];

    return $idToken;
}

function bkash_callback_url(int $orderId): string
{
    $config = bkash_config();
    $callbackBaseUrl = rtrim($config['callback_base_url'], '/');
    $callbackHost = parse_url($callbackBaseUrl, PHP_URL_HOST);
    if (
        $callbackBaseUrl === ''
        || !is_string($callbackHost)
        || preg_match('/(^|\.)bka\.sh$/i', $callbackHost)
    ) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
        $callbackBaseUrl = $scheme . '://' . $host . ($scriptDir === '' ? '' : $scriptDir);
    }

    return $callbackBaseUrl . '/index.php?action=bkash-callback&order=' . $orderId;
}

function bkash_create_payment(float $amount, string $invoiceNumber, int $orderId): array
{
    $config = bkash_config();
    $idToken = bkash_access_token();
    $response = bkash_api_post(
        $config['base_url'] . '/payment/create',
        [
            'authorization: ' . $idToken,
            'x-app-key: ' . $config['app_key'],
        ],
        [
            'payerReference' => 'CUSTOMER-' . $orderId,
            'callbackURL' => bkash_callback_url($orderId),
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $invoiceNumber,
        ]
    );

    if (empty($response['paymentId']) || empty($response['bkashURL'])) {
        throw new RuntimeException((string) ($response['errorMessageEn'] ?? 'bKash could not start the sandbox payment.'));
    }

    return $response;
}

function bkash_execute_payment(string $paymentId): array
{
    $config = bkash_config();
    return bkash_api_post(
        $config['base_url'] . '/payment/execute',
        [
            'authorization: ' . bkash_access_token(),
            'x-app-key: ' . $config['app_key'],
        ],
        ['paymentId' => $paymentId]
    );
}

function bkash_query_payment(string $paymentId): array
{
    $config = bkash_config();
    return bkash_api_post(
        $config['base_url'] . '/query/payment',
        [
            'authorization: ' . bkash_access_token(),
            'x-app-key: ' . $config['app_key'],
        ],
        ['paymentId' => $paymentId]
    );
}

function store_update_payment_by_order(int $orderId, string $transactionId, string $status): void
{
    $connection = database();
    $orderId = (int) $orderId;
    $result = $connection->query(
        "SELECT id FROM payments WHERE order_id = $orderId ORDER BY created_at DESC, id DESC LIMIT 1"
    );
    $payment = $result ? $result->fetch_assoc() : null;
    if (!is_array($payment)) {
        throw new RuntimeException('Payment record was not found.');
    }

    $paymentId = (int) $payment['id'];
    $transactionIdSql = $connection->real_escape_string($transactionId);
    $statusSql = $connection->real_escape_string($status);
    $paidAt = $status === 'success' ? 'NOW()' : 'NULL';
    $connection->query(
        "UPDATE payments
         SET transaction_id = " . ($transactionId === '' ? 'NULL' : "'" . $transactionIdSql . "'") . ",
             status = '$statusSql', paid_at = $paidAt
         WHERE id = $paymentId"
    );
}

function store_find_bkash_callback_order(int $orderId): ?array
{
    $orderId = (int) $orderId;
    $result = database()->query(
        "SELECT o.id, o.user_id, o.order_number, o.total_amount,
                p.amount AS payment_amount, p.payment_method, p.transaction_id, p.status AS payment_status
         FROM orders o
         JOIN payments p ON p.order_id = o.id
         WHERE o.id = $orderId AND p.payment_method = 'bkash'
         ORDER BY p.created_at DESC, p.id DESC
         LIMIT 1"
    );

    return $result->fetch_assoc() ?: null;
}

function store_cancel_unpaid_order(int $orderId): void
{
    $connection = database();
    $orderId = (int) $orderId;
    $connection->begin_transaction();

    try {
        $connection->query(
            "UPDATE orders SET status = 'cancelled'
             WHERE id = $orderId AND status = 'pending'"
        );

        if ($connection->affected_rows === 1) {
            $items = $connection->query(
                "SELECT product_id, quantity FROM order_items
                 WHERE order_id = $orderId AND product_id IS NOT NULL"
            );
            if ($items) {
                while ($item = $items->fetch_assoc()) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];
                    $connection->query(
                        "UPDATE products SET stock = stock + $quantity
                         WHERE id = $productId"
                    );
                }
            }

            $connection->query(
                "UPDATE payments SET status = 'failed', transaction_id = NULL, paid_at = NULL
                 WHERE order_id = $orderId"
                );
        }
        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function store_place_order($customerId, $recipient, $phone, $address, $area, $city, $postalCode, $paymentMethod, $deliveryFee, $paymentStatus = 'success')
{
    $cart = store_cart();
    if (!$cart) {
        throw new DomainException('Your basket is empty. Add some flowers before checking out.');
    }

    $connection = database();
    $connection->begin_transaction();

    try {
        $products = [];
        $subtotal = 0;

        foreach ($cart as $productId => $quantity) {
            $product = store_find_product_by_id($productId);
            if (!$product || (int) $product['stock'] < $quantity) {
                throw new DomainException('A flower in your basket is no longer available in that quantity.');
            }

            $products[$productId] = $product;
            $subtotal += store_discounted_price($product) * $quantity;
        }

        $deliveryFee = (float) $deliveryFee;
        if (!is_finite($deliveryFee) || $deliveryFee < 0) {
            throw new InvalidArgumentException('Choose a valid delivery city.');
        }
        $total = $subtotal + $deliveryFee;
        $orderNumber = 'BB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $customerId = (int) $customerId;
        $orderNumberSql = $connection->real_escape_string($orderNumber);
        $recipient = $connection->real_escape_string($recipient);
        $phone = $connection->real_escape_string($phone);
        $address = $connection->real_escape_string($address);
        $areaSql = $area === '' ? 'NULL' : "'" . $connection->real_escape_string($area) . "'";
        $city = $connection->real_escape_string($city);
        $postalCodeSql = $postalCode === '' ? 'NULL' : "'" . $connection->real_escape_string($postalCode) . "'";

        $subtotalSql = number_format($subtotal, 2, '.', '');
        $totalSql = number_format($total, 2, '.', '');
        $connection->query(
            "INSERT INTO orders
                (user_id, order_number, recipient_name, phone, shipping_address, area, city,
                 postal_code, subtotal, delivery_fee, total_amount, status)
             VALUES
                ($customerId, '$orderNumberSql', '$recipient', '$phone', '$address', $areaSql, '$city',
                 $postalCodeSql, $subtotalSql, $deliveryFee, $totalSql, 'pending')"
        );
        $orderId = $connection->insert_id;

        foreach ($products as $productId => $product) {
            $quantity = $cart[$productId];
            $connection->query(
                "UPDATE products SET stock = stock - $quantity
                 WHERE id = $productId AND stock >= $quantity"
            );
            if ($connection->affected_rows !== 1) {
                throw new DomainException('Stock changed while you were checking out. Please try again.');
            }

            $productName = $connection->real_escape_string($product['name']);
            $discountedPrice = store_discounted_price($product);
            $unitPrice = number_format($discountedPrice, 2, '.', '');
            $lineTotal = number_format($discountedPrice * $quantity, 2, '.', '');
            $connection->query(
                "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
                 VALUES ($orderId, $productId, '$productName', $unitPrice, $quantity, $lineTotal)"
            );
        }

        if (!in_array($paymentStatus, ['pending', 'success', 'failed'], true)) {
            throw new InvalidArgumentException('Invalid payment status.');
        }
        $transactionId = 'NULL';
        $paymentMethod = $connection->real_escape_string($paymentMethod);
        $connection->query(
            "INSERT INTO payments (order_id, payment_method, transaction_id, amount, status, paid_at)
             VALUES ($orderId, '$paymentMethod', $transactionId, $totalSql, '$paymentStatus',
                     " . ($paymentStatus === 'success' ? 'NOW()' : 'NULL') . ')'
        );

        $connection->query(
            "INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, note)
             VALUES ($orderId, NULL, NULL, 'pending', 'Order placed by customer.')"
        );

        $connection->commit();
        return $orderId;
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function store_get_customer_orders($customerId)
{
    $customerId = (int) $customerId;
    $result = database()->query(
        "SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at,
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count,
                p.payment_method, p.status AS payment_status
         FROM orders o
         LEFT JOIN payments p ON p.id = (
             SELECT p2.id FROM payments p2
             WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
         )
         WHERE o.user_id = $customerId
         ORDER BY o.created_at DESC, o.id DESC"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}

function store_find_customer_order($orderId, $customerId)
{
    $orderId = (int) $orderId;
    $customerId = (int) $customerId;
    $result = database()->query(
        "SELECT o.id, o.order_number, o.recipient_name, o.phone, o.shipping_address, o.area,
                o.city, o.postal_code, o.subtotal, o.delivery_fee, o.total_amount, o.status, o.created_at,
                p.payment_method, p.status AS payment_status, p.transaction_id
         FROM orders o
         LEFT JOIN payments p ON p.id = (
             SELECT p2.id FROM payments p2
             WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
         )
         WHERE o.id = $orderId AND o.user_id = $customerId LIMIT 1"
    );

    return $result->fetch_assoc() ?: null;
}

function store_get_order_items($orderId, $customerId)
{
    if (!store_find_customer_order($orderId, $customerId)) {
        return null;
    }

    $orderId = (int) $orderId;
    $result = database()->query(
        "SELECT product_name, unit_price, quantity, line_total
         FROM order_items WHERE order_id = $orderId ORDER BY id"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
}
