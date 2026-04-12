<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';
?>

<div class="row align-items-center g-4">
    <div class="col-12 col-lg-7">
        <div class="p-4 p-lg-5 rounded-4 bg-white shadow-sm border">
            <div class="d-inline-flex align-items-center gap-2 medpro-badge rounded-pill px-3 py-2 mb-3">
                <i data-lucide="shield-check"></i>
                <span class="fw-semibold">Verified medical suppliers & products</span>
            </div>
            <h1 class="display-5 fw-bold mb-3">
                Shop trusted healthcare essentials with MedPro
            </h1>
            <p class="lead text-muted mb-4">
                Search for medical products from approved stores, view store details instantly, and manage your inventory through a dedicated vendor dashboard.
            </p>

            <div class="d-flex flex-column flex-sm-row gap-2">
                <a class="btn btn-outline-primary btn-lg" href="register.php">
                    <i data-lucide="user-plus" class="me-2"></i>Create Account
                </a>
            </div>

            <div class="row mt-4 g-3">
                <div class="col-6 col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(13,110,253,.06);">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="truck" class="text-primary"></i>
                            <div class="fw-semibold">Fast Access</div>
                        </div>
                        <div class="text-muted small">Store info in seconds</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(13,110,253,.06);">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="lock" class="text-primary"></i>
                            <div class="fw-semibold">Secure Login</div>
                        </div>
                        <div class="text-muted small">Password hashing + sessions</div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(13,110,253,.06);">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="building-2" class="text-primary"></i>
                            <div class="fw-semibold">Vendor Control</div>
                        </div>
                        <div class="text-muted small">Add, edit, delete inventory</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="rounded-4 bg-white shadow-sm border p-3 p-lg-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-semibold">
                    <i data-lucide="heart-pulse" class="me-2 text-primary"></i>Health-First Marketplace
                </div>
                <span class="badge medpro-badge rounded-pill">MedPro</span>
            </div>

            <div class="ratio ratio-16x9 rounded-3 overflow-hidden border">
                <img
                    src="./uploads/bg.png"
                    alt="Healthcare hero"
                    class="w-100 h-100"
                    style="object-fit: cover;">
            </div>

            <div class="mt-3 small text-muted">
                Built for clarity, trust, and secure access across patients, stores, and administrators.
            </div>

            <div class="mt-4 d-grid gap-2">
                <?php $role = $_SESSION['user_role'] ?? 'guest'; ?>
                <?php if ($role === 'patient') : ?>
                    <a class="btn btn-outline-primary" href="logout.php"><i data-lucide="log-out" class="me-2"></i>Logout</a>
                <?php elseif ($role === 'store') : ?>
                    <a class="btn btn-primary" href="store-dashboard.php"><i data-lucide="store" class="me-2"></i>Store Dashboard</a>
                    <a class="btn btn-outline-primary" href="logout.php"><i data-lucide="log-out" class="me-2"></i>Logout</a>
                <?php elseif ($role === 'admin') : ?>
                    <a class="btn btn-primary" href="admin-dashboard.php"><i data-lucide="shield-check" class="me-2"></i>Admin Panel</a>
                    <a class="btn btn-outline-primary" href="logout.php"><i data-lucide="log-out" class="me-2"></i>Logout</a>
                <?php else : ?>
                    <a class="btn btn-primary" href="login.php"><i data-lucide="log-in" class="me-2"></i>Login</a>
                    <a class="btn btn-outline-primary" href="register.php"><i data-lucide="user-plus" class="me-2"></i>Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>