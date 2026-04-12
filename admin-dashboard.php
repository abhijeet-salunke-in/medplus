<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

 $sessionRole = isset($_SESSION['user_role']) ? strtolower((string)$_SESSION['user_role']) : '';
 if (!isset($_SESSION['user_id']) || $sessionRole !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return (string)$_SESSION['csrf_token'];
    }

    function csrf_verify(?string $token): bool
    {
        return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], $token);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!csrf_verify($token)) {
        $_SESSION['admin_flash_error'] = 'Invalid request. Please try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        $storeId = (int)($_POST['store_id'] ?? 0);

        if ($storeId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
            $_SESSION['admin_flash_error'] = 'Invalid action.';
        } else {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id, role, status FROM users WHERE id = ? AND role = "store" LIMIT 1');
            $stmt->execute([$storeId]);
            $store = $stmt->fetch();

            if (!$store || (string)$store['status'] !== 'pending') {
                $_SESSION['admin_flash_error'] = 'Store is not pending or does not exist.';
            } else {
                $newStatus = $action === 'approve' ? 'approved' : 'rejected';
                $stmt = $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND role = "store"');
                $stmt->execute([$newStatus, $storeId]);

                if ($action === 'approve') {
                    $_SESSION['admin_flash_success'] = 'Store approved successfully.';
                } else {
                    $_SESSION['admin_flash_success'] = 'Store rejected successfully.';
                }
            }
        }
    }

    header('Location: admin-dashboard.php');
    exit;
}

$flashError = $_SESSION['admin_flash_error'] ?? '';
$flashSuccess = $_SESSION['admin_flash_success'] ?? '';
unset($_SESSION['admin_flash_error'], $_SESSION['admin_flash_success']);

require_once __DIR__ . '/includes/header.php';

$pdo = db();

$storesCountStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE role = "store" AND status = "approved"');
$storesCountStmt->execute();
$totalStores = (int)($storesCountStmt->fetch()['cnt'] ?? 0);

$patientsCountStmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE role = "patient" AND status = "approved"');
$patientsCountStmt->execute();
$totalPatients = (int)($patientsCountStmt->fetch()['cnt'] ?? 0);

$pendingStoresStmt = $pdo->prepare('
    SELECT id, name, email, phone, license_no, address, created_at
    FROM users
    WHERE role = "store" AND status = "pending"
    ORDER BY created_at DESC
');
$pendingStoresStmt->execute();
$pendingStores = $pendingStoresStmt->fetchAll();
?>

<div class="row g-4">
    <div class="col-12">
        <?php if ($flashError !== '') : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <?php if ($flashSuccess !== '') : ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="p-4 rounded-4 bg-white shadow-sm border h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted">Total Stores</div>
                            <div class="display-6 fw-bold text-primary">
                                <?php echo $totalStores; ?>
                            </div>
                        </div>
                        <div class="p-3 rounded-3" style="background: rgba(13,110,253,.08);">
                            <i data-lucide="store" class="text-primary" style="width: 28px; height: 28px;"></i>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">Approved store accounts only.</div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-4 rounded-4 bg-white shadow-sm border h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted">Total Patients</div>
                            <div class="display-6 fw-bold text-primary">
                                <?php echo $totalPatients; ?>
                            </div>
                        </div>
                        <div class="p-3 rounded-3" style="background: rgba(13,110,253,.08);">
                            <i data-lucide="users" class="text-primary" style="width: 28px; height: 28px;"></i>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">Approved patient accounts only.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="p-4 rounded-4 bg-white shadow-sm border">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">
                        <i data-lucide="shield-check" class="text-primary me-2"></i>Pending Store Approvals
                    </h2>
                    <div class="text-muted">Approve or reject newly registered medical stores.</div>
                </div>
                <span class="badge medpro-badge rounded-pill px-3 py-2">
                    <?php echo (int)count($pendingStores); ?> pending
                </span>
            </div>

            <?php if (count($pendingStores) === 0) : ?>
                <div class="p-4 rounded-3 bg-light border text-center text-muted">
                    No pending store registrations at the moment.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted">
                                <th>Store</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th>License</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingStores as $s) : ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars((string)$s['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <div class="fw-semibold"><?php echo htmlspecialchars((string)$s['phone'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars((string)$s['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="small text-muted">
                                        <?php
                                        $addr = $s['address'] ? (string)$s['address'] : '';
                                        echo $addr !== '' ? htmlspecialchars($addr, ENT_QUOTES, 'UTF-8') : 'N/A';
                                        ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?php
                                        $lic = $s['license_no'] ? (string)$s['license_no'] : '';
                                        echo $lic !== '' ? htmlspecialchars($lic, ENT_QUOTES, 'UTF-8') : 'N/A';
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="store_id" value="<?php echo (int)$s['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success me-1" title="Approve store">
                                                <i data-lucide="check" class="me-1"></i>Approve
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Reject this store?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <input type="hidden" name="store_id" value="<?php echo (int)$s['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject store">
                                                <i data-lucide="x" class="me-1"></i>Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

