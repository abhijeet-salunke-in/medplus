<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
$info = '';

if (isset($_GET['registered']) && (string)$_GET['registered'] === '1') {
    $info = 'Your account was created successfully. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!csrf_verify($token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif ($password === '') {
            $error = 'Please enter your password.';
        } else {
            $pdo = db();
            $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'Invalid email or password.';
            } else {
                $ok = password_verify($password, (string)$user['password_hash']);
                if (!$ok) {
                    $error = 'Invalid email or password.';
                } else {
                    // Normalize values to be resilient to casing differences in the DB.
                    $role = strtolower((string)$user['role']);
                    $status = strtolower((string)$user['status']);

                    if ($role === 'store' && $status !== 'approved') {
                        $error = 'Your store account is pending approval. Please check back later.';
                    } else {
                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['user_name'] = (string)$user['name'];
                        $_SESSION['user_role'] = $role;

                        if ($role === 'admin') {
                            header('Location: admin-dashboard.php');
                            exit;
                        }
                        if ($role === 'store') {
                            header('Location: store-dashboard.php');
                            exit;
                        }

                        header('Location: search.php');
                        exit;
                    }
                }
            }
        }
    }
}
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center gap-2 medpro-badge rounded-pill px-3 py-2">
                        <i data-lucide="log-in"></i>
                        <span class="fw-semibold">Welcome back</span>
                    </div>
                </div>

                <?php if ($info !== '') : ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($info, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error !== '') : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" autocomplete="email" required
                               value="<?php echo htmlspecialchars((string)($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">
                        <i data-lucide="arrow-right-circle" class="me-2"></i>Login
                    </button>

                    <div class="text-center mt-3">
                        <a class="text-decoration-none" href="register.php">Create an account</a>
                    </div>
                </form>

                <div class="mt-3 small text-muted">
                    Tip: Store accounts may require admin approval before you can add products.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

