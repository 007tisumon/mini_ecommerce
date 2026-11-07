<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

const STORE_DELIVERY_FEE = 80.0;

function store_redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function store_customer(): ?array
{
    start_app_session();
    $customer = $_SESSION['customer_user'] ?? null;

    return is_array($customer) && ($customer['role'] ?? null) === 'customer'
        ? $customer
        : null;
}

function store_customer_is_signed_in(): bool
{
    return store_customer() !== null;
}

function store_sign_in(int $id, string $name, string $email, string $phone, string $role): void
{
    if ($role !== 'customer') {
        throw new LogicException('Only customer accounts can sign in to the storefront.');
    }

    if (!session_regenerate_id(true)) {
        throw new RuntimeException('Could not renew the customer session.');
    }

    $_SESSION['customer_user'] = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => 'customer',
    ];
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}

function store_cart(): array
{
    start_app_session();
    $rawCart = $_SESSION['store_cart'] ?? [];

    if (!is_array($rawCart)) {
        return [];
    }

    $cart = [];
    foreach ($rawCart as $productId => $quantity) {
        if (
            (is_int($productId) || (is_string($productId) && ctype_digit($productId)))
            && filter_var($quantity, FILTER_VALIDATE_INT) !== false
            && (int) $productId > 0
            && (int) $quantity > 0
        ) {
            $cart[(int) $productId] = min((int) $quantity, 20);
        }
    }

    return $cart;
}

function store_cart_count(): int
{
    return array_sum(store_cart());
}

function store_money(float|int|string $amount): string
{
    return '৳' . number_format((float) $amount, 2);
}

function store_product_image(array $product): string
{
    $imagePath = str_replace('\\', '/', trim((string) ($product['image_path'] ?? '')));

    if (
        $imagePath !== ''
        && preg_match('/^[a-zA-Z0-9._\/-]+$/D', $imagePath)
        && !str_contains($imagePath, '..')
        && is_file(__DIR__ . '/../assets/images/' . ltrim($imagePath, '/'))
    ) {
        return app_url('assets/images/' . ltrim($imagePath, '/'));
    }

    return store_product_fallback_image($product);
}

function store_product_fallback_image(array $product): string
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
    $slug = (string) ($product['slug'] ?? '');
    $categorySlug = (string) ($product['category_slug'] ?? '');
    $photo = $images[$slug] ?? $categoryImages[$categorySlug] ?? '1490750967868-88aa4486c946';

    return 'https://images.unsplash.com/photo-' . $photo . '?auto=format&fit=crop&w=800&q=80';
}

function store_find_product_by_id(int $id): ?array
{
    $statement = database()->prepare(
        'SELECT p.id, p.name, p.price, p.stock, p.is_active
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id AND c.is_active = 1
         WHERE p.id = :id LIMIT 1'
    );
    $statement->execute(['id' => $id]);
    $product = $statement->fetch();

    return is_array($product) && (int) $product['is_active'] === 1 ? $product : null;
}

function store_find_product_by_slug(string $slug): ?array
{
    $statement = database()->prepare(
        'SELECT p.id, p.category_id, p.sku, p.name, p.slug, p.description, p.price,
                p.stock, p.image_path, p.is_featured, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id AND c.is_active = 1
         WHERE p.slug = :slug AND p.is_active = 1
         LIMIT 1'
    );
    $statement->execute(['slug' => $slug]);
    $product = $statement->fetch();

    return is_array($product) ? $product : null;
}

function store_get_categories(): array
{
    return database()->query(
        'SELECT c.id, c.name, c.slug, c.description, COUNT(p.id) AS product_count
         FROM categories c
         LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
         WHERE c.is_active = 1
         GROUP BY c.id, c.name, c.slug, c.description
         ORDER BY c.name'
    )->fetchAll();
}

function store_get_products(string $search, string $category, bool $featured, ?string $excludeSlug = null): array
{
    $conditions = ['p.is_active = 1', 'c.is_active = 1', 'p.stock > 0'];
    $parameters = [];

    if ($search !== '') {
        $conditions[] = 'p.name LIKE :search';
        $parameters['search'] = '%' . $search . '%';
    }

    if ($category !== '') {
        $conditions[] = 'c.slug = :category';
        $parameters['category'] = $category;
    }

    if ($featured) {
        $conditions[] = 'p.is_featured = 1';
    }

    if ($excludeSlug !== null) {
        $conditions[] = 'p.slug <> :exclude_slug';
        $parameters['exclude_slug'] = $excludeSlug;
    }

    $statement = database()->prepare(
        'SELECT p.id, p.category_id, p.sku, p.name, p.slug, p.description, p.price,
                p.stock, p.image_path, p.is_featured, c.name AS category_name, c.slug AS category_slug
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE ' . implode(' AND ', $conditions) . '
         ORDER BY p.is_featured DESC, p.created_at DESC
         LIMIT 24'
    );
    $statement->execute($parameters);

    return $statement->fetchAll();
}

function store_get_cart_items(): array
{
    $cart = store_cart();
    if ($cart === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($cart), '?'));
    $statement = database()->prepare(
        'SELECT p.id, p.name, p.slug, p.price, p.stock, p.image_path, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id AND c.is_active = 1
         WHERE p.is_active = 1 AND p.id IN (' . $placeholders . ')'
    );
    $statement->execute(array_keys($cart));
    $products = $statement->fetchAll();
    $items = [];

    foreach ($products as $product) {
        $id = (int) $product['id'];
        $quantity = min($cart[$id] ?? 0, (int) $product['stock']);
        if ($quantity < 1) {
            continue;
        }

        $product['quantity'] = $quantity;
        $product['line_total'] = (float) $product['price'] * $quantity;
        $items[] = $product;
    }

    return $items;
}

function store_cart_subtotal(array $items): float
{
    return array_reduce(
        $items,
        static fn (float $subtotal, array $item): float => $subtotal + (float) $item['line_total'],
        0.0
    );
}

function store_place_order(
    int $customerId,
    string $recipient,
    string $phone,
    string $address,
    string $area,
    string $city,
    string $postalCode,
    string $paymentMethod,
    string $testResult
): int {
    $cart = store_cart();
    if ($cart === []) {
        throw new DomainException('Your basket is empty. Add some flowers before checking out.');
    }

    $pdo = database();
    $pdo->beginTransaction();

    try {
        $productIds = array_keys($cart);
        sort($productIds);
        $products = [];
        $findProduct = $pdo->prepare(
            'SELECT p.id, p.name, p.price, p.stock, p.is_active
             FROM products p
             INNER JOIN categories c ON c.id = p.category_id AND c.is_active = 1
             WHERE p.id = :id FOR UPDATE'
        );

        foreach ($productIds as $productId) {
            $findProduct->execute(['id' => $productId]);
            $product = $findProduct->fetch();

            if (!is_array($product) || (int) $product['is_active'] !== 1) {
                throw new DomainException('A flower in your basket is no longer available. Please review your basket.');
            }

            if ((int) $product['stock'] < $cart[$productId]) {
                throw new DomainException(
                    $product['name'] . ' has only ' . (int) $product['stock']
                    . ' left. Please update your basket and try again.'
                );
            }

            $products[$productId] = $product;
        }

        $subtotal = 0.0;
        foreach ($products as $productId => $product) {
            $subtotal += (float) $product['price'] * $cart[$productId];
        }
        $deliveryFee = STORE_DELIVERY_FEE;
        $total = $subtotal + $deliveryFee;
        $orderNumber = 'BB-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $insertOrder = $pdo->prepare(
            'INSERT INTO orders
                (user_id, order_number, recipient_name, phone, shipping_address, area, city,
                 postal_code, subtotal, delivery_fee, total_amount, status)
             VALUES
                (:user_id, :order_number, :recipient_name, :phone, :shipping_address, :area, :city,
                 :postal_code, :subtotal, :delivery_fee, :total_amount, \'pending\')'
        );
        $insertOrder->execute([
            'user_id' => $customerId,
            'order_number' => $orderNumber,
            'recipient_name' => $recipient,
            'phone' => $phone,
            'shipping_address' => $address,
            'area' => $area !== '' ? $area : null,
            'city' => $city,
            'postal_code' => $postalCode !== '' ? $postalCode : null,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'delivery_fee' => number_format($deliveryFee, 2, '.', ''),
            'total_amount' => number_format($total, 2, '.', ''),
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $insertItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, line_total)
             VALUES (:order_id, :product_id, :product_name, :unit_price, :quantity, :line_total)'
        );
        $reduceStock = $pdo->prepare(
            'UPDATE products SET stock = stock - :quantity
             WHERE id = :id AND stock >= :minimum_stock'
        );

        foreach ($products as $productId => $product) {
            $quantity = $cart[$productId];
            $unitPrice = (float) $product['price'];
            $reduceStock->execute([
                'quantity' => $quantity,
                'id' => $productId,
                'minimum_stock' => $quantity,
            ]);

            if ($reduceStock->rowCount() !== 1) {
                throw new DomainException('Stock changed while your order was being placed. Please try again.');
            }

            $insertItem->execute([
                'order_id' => $orderId,
                'product_id' => $productId,
                'product_name' => $product['name'],
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'quantity' => $quantity,
                'line_total' => number_format($unitPrice * $quantity, 2, '.', ''),
            ]);
        }

        $paymentStatus = $paymentMethod === 'cod'
            ? 'pending'
            : ($testResult === 'success' ? 'success' : 'failed');
        $transactionId = $paymentMethod !== 'cod' && $paymentStatus === 'success'
            ? 'DEMO-' . strtoupper(bin2hex(random_bytes(6)))
            : null;
        $insertPayment = $pdo->prepare(
            'INSERT INTO payments (order_id, payment_method, transaction_id, amount, status, paid_at)
             VALUES (:order_id, :payment_method, :transaction_id, :amount, :status, :paid_at)'
        );
        $insertPayment->execute([
            'order_id' => $orderId,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'amount' => number_format($total, 2, '.', ''),
            'status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'success' ? date('Y-m-d H:i:s') : null,
        ]);

        $insertHistory = $pdo->prepare(
            'INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, note)
             VALUES (:order_id, :changed_by, NULL, \'pending\', :note)'
        );
        $insertHistory->execute([
            'order_id' => $orderId,
            'changed_by' => null,
            'note' => 'Order placed by customer.',
        ]);

        $pdo->commit();

        return $orderId;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $exception;
    }
}

function store_get_customer_orders(int $customerId): array
{
    $statement = database()->prepare(
        'SELECT o.id, o.order_number, o.total_amount, o.status, o.created_at,
                COUNT(oi.id) AS item_count, p.payment_method, p.status AS payment_status
         FROM orders o
         LEFT JOIN order_items oi ON oi.order_id = o.id
         LEFT JOIN payments p ON p.id = (
             SELECT p2.id FROM payments p2
             WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
         )
         WHERE o.user_id = :user_id
         GROUP BY o.id, o.order_number, o.total_amount, o.status, o.created_at,
                  p.payment_method, p.status
         ORDER BY o.created_at DESC, o.id DESC'
    );
    $statement->execute(['user_id' => $customerId]);

    return $statement->fetchAll();
}

function store_find_customer_order(int $orderId, int $customerId): ?array
{
    $statement = database()->prepare(
        'SELECT o.id, o.order_number, o.recipient_name, o.phone, o.shipping_address, o.area,
                o.city, o.postal_code, o.subtotal, o.delivery_fee, o.total_amount, o.status, o.created_at,
                p.payment_method, p.status AS payment_status, p.transaction_id
         FROM orders o
         LEFT JOIN payments p ON p.id = (
             SELECT p2.id FROM payments p2
             WHERE p2.order_id = o.id ORDER BY p2.created_at DESC, p2.id DESC LIMIT 1
         )
         WHERE o.id = :id AND o.user_id = :user_id LIMIT 1'
    );
    $statement->execute(['id' => $orderId, 'user_id' => $customerId]);
    $order = $statement->fetch();

    return is_array($order) ? $order : null;
}

function store_get_order_items(int $orderId, int $customerId): ?array
{
    if (store_find_customer_order($orderId, $customerId) === null) {
        return null;
    }

    $statement = database()->prepare(
        'SELECT oi.product_name, oi.unit_price, oi.quantity, oi.line_total
         FROM order_items oi
         INNER JOIN orders o ON o.id = oi.order_id
         WHERE oi.order_id = :order_id AND o.user_id = :user_id
         ORDER BY oi.id'
    );
    $statement->execute(['order_id' => $orderId, 'user_id' => $customerId]);

    return $statement->fetchAll();
}
