<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';

// --- ADD THIS BLOCK HERE ---
// If the user is a guest, redirect them to the login page
if ($role === 'guest') {
    header('Location: login.php');
    exit;
}
// ---------------------------

$pdo = db();
// ... rest of your code

$q = trim((string)($_GET['q'] ?? ''));
$products = [];

if ($q !== '') {
    $stmt = $pdo->prepare('
        SELECT
            p.id,
            p.product_name,
            p.price,
            p.image_path,
            u.name AS store_name,
            u.phone AS store_phone,
            u.address AS store_address
        FROM products p
        INNER JOIN users u ON u.id = p.store_id
        WHERE u.role = "store"
          AND u.status = "approved"
          AND p.product_name LIKE ?
        ORDER BY p.created_at DESC
        LIMIT 24
    ');
    $stmt->execute(['%' . $q . '%']);
    $products = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare('
        SELECT
            p.id,
            p.product_name,
            p.price,
            p.image_path,
            u.name AS store_name,
            u.phone AS store_phone,
            u.address AS store_address
        FROM products p
        INNER JOIN users u ON u.id = p.store_id
        WHERE u.role = "store"
          AND u.status = "approved"
        ORDER BY p.created_at DESC
        LIMIT 24
    ');
    $stmt->execute();
    $products = $stmt->fetchAll();
}
?>

<style>
    .medpro-product-card {
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        border-color: rgba(13, 110, 253, .12) !important;
    }

    .medpro-product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 35px rgba(13, 110, 253, .14) !important;
        border-color: rgba(13, 110, 253, .35) !important;
    }
</style>

<div class="row g-4">
    <div class="col-12">
        <div class="p-4 rounded-4 bg-white shadow-sm border">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h2 class="h4 mb-1 fw-bold">
                        <i data-lucide="search" class="text-primary me-2"></i>Search Medical Products
                    </h2>
                    <div class="text-muted">Find products from approved medical stores.</div>
                </div>
                <span class="badge medpro-badge rounded-pill px-3 py-2">
                    <?php echo $q !== '' ? 'Results' : 'Latest Products'; ?>
                </span>
            </div>

            <form class="row g-2 mt-3" method="get" action="search.php" autocomplete="off">
                <div class="col-12 col-md-8">
                    <input
                        class="form-control form-control-lg"
                        type="text"
                        name="q"
                        placeholder="Search by product name (e.g., Paracetamol, Gloves...)"
                        value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-4 d-grid">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i data-lucide="arrow-right-circle" class="me-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12">
        <?php if (count($products) === 0) : ?>
            <div class="p-5 rounded-4 bg-white shadow-sm border text-center">
                <div class="mb-3">
                    <i data-lucide="info" class="text-primary" style="width: 36px; height: 36px;"></i>
                </div>
                <div class="fw-semibold mb-2">No products found</div>
                <div class="text-muted">Try a different keyword or browse the latest items.</div>
                <a class="btn btn-outline-primary mt-3" href="search.php">
                    Clear search
                </a>
            </div>
        <?php else : ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($products as $p) : ?>
                    <?php
                    $img = $p['image_path'] ? (string)$p['image_path'] : '';
                    // Normalize Windows backslashes to forward slashes for URLs.
                    $img = $img !== '' ? str_replace('\\', '/', $img) : '';
                    $storeName = (string)$p['store_name'];
                    $storePhone = (string)$p['store_phone'];
                    $storeAddress = $p['store_address'] ? (string)$p['store_address'] : 'N/A';
                    $productName = (string)$p['product_name'];
                    $price = (float)$p['price'];
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border rounded-4 medpro-product-card">
                            <?php
                            $imgExists = $img !== '' && is_string($img) && file_exists(__DIR__ . '/' . $img);
                            ?>
                            <?php if ($imgExists) : ?>
                                <img
                                    src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="card-img-top img-fluid"
                                    alt="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>"
                                    loading="lazy"
                                    style="height: 170px; object-fit: cover; background: #f1f5f9;">
                            <?php else : ?>
                                <div
                                    class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted"
                                    style="height: 170px;">
                                    <i data-lucide="image-off"></i>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <span class="badge text-bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 mb-2 d-inline-flex align-items-center">
                                    <i data-lucide="shield-check" class="me-1"></i>Verified
                                </span>

                                <h5 class="card-title fw-semibold mb-2 text-truncate" title="<?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($productName, ENT_QUOTES, 'UTF-8'); ?>
                                </h5>

                                <p class="text-muted small mb-0 text-truncate" title="<?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?>">
                                    <i data-lucide="building-2" class="me-1" style="width:14px;height:14px;"></i>
                                    <?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                    <div class="fw-bold text-primary fs-5">
                                        ₹<?php echo number_format($price, 2); ?>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#storeModal"
                                        data-store-name="<?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-store-phone="<?php echo htmlspecialchars($storePhone, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-store-address="<?php echo htmlspecialchars($storeAddress, ENT_QUOTES, 'UTF-8'); ?>">
                                        View Store
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="storeModal" tabindex="-1" aria-labelledby="storeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="storeModalLabel">Store Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="text-muted small">Store</div>
                    <div class="fw-semibold" id="storeModalName"></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Phone</div>
                    <div class="fw-semibold" id="storeModalPhone"></div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Address</div>
                    <div class="fw-semibold" id="storeModalAddress"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('storeModal');
        if (!modal) return;

        modal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            if (!button) return;

            var name = button.getAttribute('data-store-name') || 'N/A';
            var phone = button.getAttribute('data-store-phone') || 'N/A';
            var address = button.getAttribute('data-store-address') || 'N/A';

            document.getElementById('storeModalName').textContent = name;
            document.getElementById('storeModalPhone').textContent = phone;
            document.getElementById('storeModalAddress').textContent = address;
        });
    })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>