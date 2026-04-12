<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

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

$error = '';
$success = '';
$postedRole = (string)($_POST['role'] ?? 'patient');
$address = trim((string)($_POST['address'] ?? ''));
$licenseNo = trim((string)($_POST['license_no'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!csrf_verify($token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $phone = trim((string)($_POST['phone'] ?? ''));
        $role = (string)($_POST['role'] ?? 'patient');

        $allowedRoles = ['patient', 'store'];
        if (!in_array($role, $allowedRoles, true)) {
            $error = 'Invalid role selected.';
        } elseif ($name === '' || mb_strlen($name) < 2) {
            $error = 'Please enter your name.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (mb_strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $error = 'Please enter a valid phone number.';
        } elseif ($role === 'store') {
            if ($address === '' || mb_strlen($address) < 6) {
                $error = 'Please select the store address from the map.';
            } elseif ($licenseNo === '' || mb_strlen($licenseNo) < 3) {
                $error = 'Please enter your license number.';
            } elseif (!preg_match('/^[A-Za-z0-9][A-Za-z0-9\\-\\/\\. ]{2,100}$/', $licenseNo)) {
                $error = 'Please enter a valid license number.';
            }
        }

        if ($error === '') {
            $pdo = db();

            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $status = $role === 'patient' ? 'approved' : 'pending';

                $stmt = $pdo->prepare('
                    INSERT INTO users (name, email, password_hash, role, phone, license_no, address, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $name,
                    $email,
                    $passwordHash,
                    $role,
                    $phone,
                    $role === 'store' ? $licenseNo : null,
                    $role === 'store' ? $address : null,
                    $status
                ]);

                $success = 'Account created successfully. Please log in.';
            }
        }
    }
}

$registered = isset($_GET['registered']) ? (string)$_GET['registered'] : '';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center gap-2 medpro-badge rounded-pill px-3 py-2">
                        <i data-lucide="heart-pulse"></i>
                        <span class="fw-semibold">Create your MedPro account</span>
                    </div>
                </div>

                <?php if ($error !== '') : ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if ($success !== '' || $registered === '1') : ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($success !== '' ? $success : 'You can now log in.', ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="mb-3">
                        <label class="form-label" for="name">Name</label>
                        <input class="form-control" id="name" name="name" type="text" autocomplete="name" required
                               value="<?php echo htmlspecialchars((string)($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" autocomplete="email" required
                               value="<?php echo htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-control" id="phone" name="phone" type="tel" autocomplete="tel" required
                               value="<?php echo htmlspecialchars((string)($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="role">I am registering as</label>
                        <select class="form-select" id="role" name="role" required>
                            <?php
                            $postedRole = (string)($_POST['role'] ?? 'patient');
                            ?>
                            <option value="patient" <?php echo $postedRole === 'patient' ? 'selected' : ''; ?>>Patient / Customer</option>
                            <option value="store" <?php echo $postedRole === 'store' ? 'selected' : ''; ?>>Medical Store</option>
                        </select>
                        <div class="form-text">Store accounts require admin approval before you can add products.</div>
                    </div>

                    <div class="mb-4" id="storeAddressWrap" style="<?php echo $postedRole === 'store' ? '' : 'display:none;'; ?>">
                        <label class="form-label" for="address">Store Address</label>
                        <div class="border rounded-4 p-3 bg-white">
                            <div class="small text-muted mb-2">
                                Enter your store address as it should appear to patients.
                            </div>

                            <div class="mb-3">
                                <div class="form-floating">
                                    <textarea
                                        class="form-control"
                                        id="address"
                                        name="address"
                                        style="height: 90px; resize: none;"
                                        <?php echo $postedRole === 'store' ? 'required' : ''; ?>><?php
                                        echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
                                        ?></textarea>
                                    <label for="address">Selected address</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-text">We save this address to show your store to patients.</div>
                    </div>

                    <div class="mb-4" id="storeLicenseWrap" style="<?php echo $postedRole === 'store' ? '' : 'display:none;'; ?>">
                        <label class="form-label" for="license_no">License Number</label>
                        <input
                            class="form-control"
                            id="license_no"
                            name="license_no"
                            type="text"
                            required
                            value="<?php echo htmlspecialchars($licenseNo, ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="e.g., LIC/2026/XXXX"
                            aria-describedby="licenseHelp"
                        >
                        <div id="licenseHelp" class="form-text">
                            Please enter your medical store license number.
                        </div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <i data-lucide="user-plus" class="me-2"></i>Register
                    </button>

                    <div class="text-center mt-3">
                        <a class="text-decoration-none" href="login.php">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var roleSelect = document.getElementById('role');
        var storeWrap = document.getElementById('storeAddressWrap');
        var licenseWrap = document.getElementById('storeLicenseWrap');
        var addressInput = document.getElementById('address');

        if (!roleSelect || !storeWrap || !licenseWrap || !addressInput) return;

        function syncVisibility() {
            var role = (roleSelect.value || '').toLowerCase();
            var show = role === 'store';
            storeWrap.style.display = show ? '' : 'none';
            licenseWrap.style.display = show ? '' : 'none';
            addressInput.required = show;

            var licenseInput = document.getElementById('license_no');
            if (licenseInput) licenseInput.required = show;
        }

        roleSelect.addEventListener('change', function () {
            syncVisibility();
        });

        syncVisibility();
    })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

