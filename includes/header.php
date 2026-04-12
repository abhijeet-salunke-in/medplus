<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['user_role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? '';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MedPro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="uploads/fevicon.png">


    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --medpro-blue: #0d6efd;
            --medpro-sky: #e8f1ff;
            --medpro-gray: #f6f7fb;
            --medpro-text: #1b2b4b;
        }

        body {
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            color: var(--medpro-text);
            background: linear-gradient(180deg, #ffffff, #f3f7ff 60%, #ffffff);
            min-height: 100vh;
        }

        .medpro-badge {
            background: var(--medpro-sky);
            color: #0b4aa2;
            border: 1px solid rgba(13, 110, 253, .15);
        }

        .navbar-brand {
            font-weight: 800;
            letter-spacing: .2px;
        }

        .nav-link {
            color: rgba(27, 43, 75, .85);
        }

        .nav-link:hover {
            color: rgba(13, 110, 253, .95);
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php" aria-label="MedPro home">
                <i data-lucide="hospital" class="text-primary"></i>
                <span>MedPro</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#medproNav"
                aria-controls="medproNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="medproNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                    <?php if (in_array($role, ['guest', 'patient', 'admin'], true)) : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i data-lucide="home" class="me-1"></i>Home
                            </a>
                        </li>

                        <?php if ($role !== 'guest' && basename($_SERVER['PHP_SELF']) !== 'index.php') : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="search.php">
                                    <i data-lucide="search" class="me-1"></i>Search
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php endif; ?>

                    <?php if ($role === 'guest') : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i data-lucide="log-in" class="me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i data-lucide="user-plus" class="me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($role === 'patient') : ?>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link" href="logout.php">
                                <i data-lucide="log-out" class="me-1"></i>Logout
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($role === 'store') : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="store-dashboard.php">
                                <i data-lucide="store" class="me-1"></i>
                                <?php echo $userName !== '' ? htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') : 'Store Dashboard'; ?>
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link" href="logout.php">
                                <i data-lucide="log-out" class="me-1"></i>Logout
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($role === 'admin') : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-dashboard.php">
                                <i data-lucide="shield-check" class="me-1"></i>Admin Panel
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link" href="logout.php">
                                <i data-lucide="log-out" class="me-1"></i>Logout
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container my-4">