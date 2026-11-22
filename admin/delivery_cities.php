<?php
session_start();
require 'functions.php';
role_check('admin');
require '../database/config.php';
$conn->set_charset('utf8mb4');

$message = '';
$messageType = 'danger';
$cityName = '';
$charge = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cityName = trim($_POST['city_name'] ?? '');
    $chargeInput = trim($_POST['delivery_charge'] ?? '');
    $charge = is_numeric($chargeInput) ? (float) $chargeInput : -1;

    if ($cityName === '' || strlen($cityName) > 100 || !is_finite($charge) || $charge < 0 || $charge > 99999999.99) {
        $message = 'Enter a city name and a valid delivery charge.';
    } elseif ($action === 'add') {
        $statement = $conn->prepare('INSERT INTO delivery_cities (city_name, delivery_charge) VALUES (?, ?)');
        $statement->bind_param('sd', $cityName, $charge);
        try {
            $statement->execute();
            header('Location: delivery_cities.php?saved=1');
            exit;
        } catch (mysqli_sql_exception $exception) {
            if ($exception->getCode() !== 1062) {
                throw $exception;
            }
            $message = 'That city already exists.';
        }
    } elseif ($action === 'update') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1 || !in_array($active, [0, 1], true)) {
            $message = 'Choose a valid city and status.';
        } else {
            $statement = $conn->prepare(
                'UPDATE delivery_cities SET city_name = ?, delivery_charge = ?, is_active = ? WHERE id = ?'
            );
            $statement->bind_param('sdii', $cityName, $charge, $active, $id);
            try {
                $statement->execute();
                header('Location: delivery_cities.php?saved=1');
                exit;
            } catch (mysqli_sql_exception $exception) {
                if ($exception->getCode() !== 1062) {
                    throw $exception;
                }
                $message = 'That city name already exists.';
            }
        }
    } else {
        $message = 'Invalid request.';
    }
}

$cities = $conn->query('SELECT id, city_name, delivery_charge, is_active FROM delivery_cities ORDER BY city_name');

require 'partials/head.php';
require 'partials/left_bar.php';
require 'partials/header.php';
?>
<div class="page-wrapper">
    <div class="page-content">
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-1">Delivery Cities</h5>
                <div class="text-muted small">Set a delivery charge for each city available at checkout.</div>
            </div>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success">Delivery city saved.</div>
        <?php endif; ?>
        <?php if ($message !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="card radius-10">
            <div class="card-body">
                <h5 class="mb-3">Add City</h5>
                <form method="post" class="row g-2 align-items-end">
                    <input type="hidden" name="action" value="add">
                    <div class="col-md-5">
                        <label class="form-label" for="new-city">City name</label>
                        <input class="form-control" id="new-city" name="city_name" maxlength="100" value="<?= htmlspecialchars($cityName, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="new-charge">Delivery charge (৳)</label>
                        <input class="form-control" id="new-charge" name="delivery_charge" type="number" min="0" max="99999999.99" step="0.01" value="<?= htmlspecialchars((string) $charge, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-3"><button class="btn btn-light" type="submit">Add City</button></div>
                </form>
            </div>
        </div>

        <div class="card radius-10">
            <div class="card-body">
                <h5 class="mb-3">Manage Cities</h5>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>City</th><th>Delivery charge</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <?php if ($cities->num_rows === 0): ?>
                                <tr><td colspan="4" class="text-center text-muted">No delivery cities added.</td></tr>
                            <?php else: ?>
                                <?php while ($city = $cities->fetch_assoc()): ?>
                                    <?php $formId = 'delivery-city-' . (int) $city['id']; ?>
                                    <tr>
                                        <td><input form="<?= $formId ?>" class="form-control" name="city_name" maxlength="100" value="<?= htmlspecialchars($city['city_name'], ENT_QUOTES, 'UTF-8') ?>" required></td>
                                        <td><input form="<?= $formId ?>" class="form-control" name="delivery_charge" type="number" min="0" max="99999999.99" step="0.01" value="<?= htmlspecialchars((string) $city['delivery_charge'], ENT_QUOTES, 'UTF-8') ?>" required></td>
                                        <td>
                                            <select form="<?= $formId ?>" class="form-select" name="is_active">
                                                <option value="1" <?= (int) $city['is_active'] === 1 ? 'selected' : '' ?>>Active</option>
                                                <option value="0" <?= (int) $city['is_active'] === 0 ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </td>
                                        <td>
                                            <form id="<?= $formId ?>" method="post">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= (int) $city['id'] ?>">
                                            </form>
                                            <button form="<?= $formId ?>" class="btn btn-light btn-sm" type="submit">Save</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require 'partials/footer.php'; ?>
<?php require 'partials/scripts.php'; ?>
