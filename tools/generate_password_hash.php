<?php
declare(strict_types=1);

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$hash = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    if ($password === '' || mb_strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate password_hash - MedPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-3">Generate `password_hash()`</h1>
                    <p class="text-muted mb-4">
                        Use this one-time tool to create the value that must be stored in `users.password_hash`.
                    </p>
                    <?php if ($error !== '') : ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo h($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" id="password" name="password" type="text" required>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Generate Hash</button>
                    </form>

                    <?php if ($hash !== '') : ?>
                        <div class="mt-4">
                            <div class="text-muted small mb-2">Copy this hash and paste into phpMyAdmin</div>
                            <textarea class="form-control font-monospace" rows="4" readonly><?php echo h($hash); ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-muted small mt-3">
                After you update the database, delete this file from `tools/` for safety.
            </div>
        </div>
    </div>
</div>
</body>
</html>

