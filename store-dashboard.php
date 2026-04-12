<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

 $sessionRole = isset($_SESSION['user_role']) ? strtolower((string)$_SESSION['user_role']) : '';
 if (!isset($_SESSION['user_id']) || $sessionRole !== 'store') {
    header('Location: login.php');
    exit;
}

if ($sessionRole === 'store') {
    require_once __DIR__ . '/includes/db.php';
    $pdo = db();
    $stmt = $pdo->prepare('SELECT status FROM users WHERE id = ? AND role = "store" LIMIT 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $u = $stmt->fetch();
    if (!$u || (string)$u['status'] !== 'approved') {
        header('Location: login.php');
        exit;
    }
}

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

$flash = '';
$error = '';

$storeId = (int)$_SESSION['user_id'];

$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$editing = false;
$editProduct = [
    'id' => 0,
    'product_name' => '',
    'price' => '',
    'image_path' => ''
];

if ($editId > 0) {
    $stmt = db()->prepare('SELECT id, product_name, price, image_path FROM products WHERE id = ? AND store_id = ? LIMIT 1');
    $stmt->execute([$editId, $storeId]);
    $row = $stmt->fetch();
    if ($row) {
        $editing = true;
        $editProduct['id'] = (int)$row['id'];
        $editProduct['product_name'] = (string)$row['product_name'];
        $editProduct['price'] = (string)$row['price'];
        $editProduct['image_path'] = $row['image_path'] ? str_replace('\\', '/', (string)$row['image_path']) : '';
    }
}

function move_uploaded_product_image(array $file): string
{
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $maxSize = 5 * 1024 * 1024;

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please try again.');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Invalid upload. Please try again.');
    }

    if (!is_int($file['size']) || $file['size'] <= 0 || $file['size'] > $maxSize) {
        throw new RuntimeException('Image size must be under 5MB.');
    }

    $originalName = (string)($file['name'] ?? '');
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($ext === '' || !in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Unsupported image type.');
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false) {
        throw new RuntimeException('Uploaded file is not a valid image.');
    }

    // store-dashboard.php lives in MedPlus/, so uploads/ is at __DIR__ . '/uploads'
    $targetDir = realpath(__DIR__ . '/uploads');
    if ($targetDir === false) {
        $targetDir = __DIR__ . '/uploads';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $relativePath = 'uploads/' . $filename;
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Could not store the image. Please try again.');
    }

    return $relativePath;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!csrf_verify($token)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = (string)($_POST['action'] ?? 'create');

        if ($action === 'create') {
            $productName = trim((string)($_POST['product_name'] ?? ''));
            $price = trim((string)($_POST['price'] ?? ''));

            if ($productName === '' || mb_strlen($productName) < 2) {
                $error = 'Please enter a product name.';
            } elseif (!is_numeric($price) || (float)$price <= 0) {
                $error = 'Please enter a valid price.';
            } elseif (!isset($_FILES['image'])) {
                $error = 'Please upload a product image.';
            } else {
                try {
                    $imagePath = move_uploaded_product_image($_FILES['image']);
                    $stmt = db()->prepare('
                        INSERT INTO products (store_id, product_name, price, image_path)
                        VALUES (?, ?, ?, ?)
                    ');
                    $stmt->execute([$storeId, $productName, (float)$price, $imagePath]);
                    $flash = 'Product added successfully.';
                    header('Location: store-dashboard.php');
                    exit;
                } catch (RuntimeException $e) {
                    $error = $e->getMessage();
                }
            }
        } elseif ($action === 'update') {
            $productId = (int)($_POST['product_id'] ?? 0);
            $productName = trim((string)($_POST['product_name'] ?? ''));
            $price = trim((string)($_POST['price'] ?? ''));

            if ($productId <= 0) {
                $error = 'Invalid product selection.';
            } elseif ($productName === '' || mb_strlen($productName) < 2) {
                $error = 'Please enter a product name.';
            } elseif (!is_numeric($price) || (float)$price <= 0) {
                $error = 'Please enter a valid price.';
            } else {
                $stmt = db()->prepare('SELECT id, image_path FROM products WHERE id = ? AND store_id = ? LIMIT 1');
                $stmt->execute([$productId, $storeId]);
                $existing = $stmt->fetch();
                if (!$existing) {
                    $error = 'You cannot update this product.';
                } else {
                    $newImagePath = $existing['image_path'] ? (string)$existing['image_path'] : '';
                    if (isset($_FILES['image']) && isset($_FILES['image']['tmp_name']) && is_uploaded_file($_FILES['image']['tmp_name']) && ($_FILES['image']['size'] ?? 0) > 0) {
                        try {
                            $newImagePath = move_uploaded_product_image($_FILES['image']);
                        } catch (RuntimeException $e) {
                            $error = $e->getMessage();
                        }
                    }

                    if ($error === '') {
                        $stmt = db()->prepare('
                            UPDATE products
                            SET product_name = ?, price = ?, image_path = ?
                            WHERE id = ? AND store_id = ?
                        ');
                        $stmt->execute([$productName, (float)$price, $newImagePath, $productId, $storeId]);
                        $flash = 'Product updated successfully.';
                        header('Location: store-dashboard.php');
                        exit;
                    }
                }
            }
        } elseif ($action === 'delete') {
            $productId = (int)($_POST['product_id'] ?? 0);
            if ($productId <= 0) {
                $error = 'Invalid product selection.';
            } else {
                $stmt = db()->prepare('SELECT image_path FROM products WHERE id = ? AND store_id = ? LIMIT 1');
                $stmt->execute([$productId, $storeId]);
                $existing = $stmt->fetch();
                if (!$existing) {
                    $error = 'You cannot delete this product.';
                } else {
                    $stmt = db()->prepare('DELETE FROM products WHERE id = ? AND store_id = ?');
                    $stmt->execute([$productId, $storeId]);

                    $imagePath = $existing['image_path'] ? (string)$existing['image_path'] : '';
                    if ($imagePath !== '') {
                        $base = basename($imagePath);
                        $full = __DIR__ . '/uploads/' . $base;
                        if (is_file($full)) {
                            @unlink($full);
                        }
                    }

                    $flash = 'Product deleted successfully.';
                    header('Location: store-dashboard.php');
                    exit;
                }
            }
        } else {
            $error = 'Invalid action.';
        }
    }
}

$inventoryStmt = db()->prepare('
    SELECT id, product_name, price, image_path, created_at
    FROM products
    WHERE store_id = ?
    ORDER BY created_at DESC
');
$inventoryStmt->execute([$storeId]);
$inventory = $inventoryStmt->fetchAll();
?>

<div class="row g-4">
    <div class="col-12 col-lg-5">
        <div class="p-4 rounded-4 bg-white shadow-sm border">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">
                        <i data-lucide="store" class="text-primary me-2"></i>
                        <?php echo $editing ? 'Edit Product' : 'Add New Product'; ?>
                    </h2>
                    <div class="text-muted">Manage your store inventory.</div>
                </div>
                <?php if ($editing) : ?>
                    <a class="btn btn-outline-secondary btn-sm" href="store-dashboard.php">
                        <i data-lucide="x-circle" class="me-1"></i>Cancel
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($flash !== '') : ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($error !== '') : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="action" value="<?php echo $editing ? 'update' : 'create'; ?>">
                <?php if ($editing) : ?>
                    <input type="hidden" name="product_id" value="<?php echo (int)$editProduct['id']; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label" for="product_name">Product Name</label>
                    <input
                        class="form-control"
                        id="product_name"
                        name="product_name"
                        type="text"
                        required
                        value="<?php echo htmlspecialchars($editing ? (string)$editProduct['product_name'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="price">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input
                            class="form-control"
                            id="price"
                            name="price"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            value="<?php echo htmlspecialchars($editing ? (string)$editProduct['price'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-text">Enter price in rupees (INR).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="image">Product Image<?php echo $editing ? ' (optional)' : ''; ?></label>
                    <input class="form-control" id="image" name="image" type="file" accept="image/*" <?php echo $editing ? '' : 'required'; ?>>
                    <?php if ($editing && $editProduct['image_path'] !== '') : ?>
                        <div class="mt-3">
                            <div class="text-muted small mb-2">Current image</div>
                            <img
                                src="<?php echo htmlspecialchars((string)$editProduct['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Current product image"
                                class="rounded-3 border"
                                style="width: 120px; height: 90px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                </div>

                <button class="btn btn-primary w-100" type="submit">
                    <?php echo $editing ? '<i data-lucide="save" class="me-2"></i>Update Product' : '<i data-lucide="plus-circle" class="me-2"></i>Add Product'; ?>
                </button>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="p-4 rounded-4 bg-white shadow-sm border">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">
                        <i data-lucide="list-checks" class="text-primary me-2"></i>Your Inventory
                    </h2>
                    <div class="text-muted">Only products added by your store account.</div>
                </div>
                <span class="badge medpro-badge rounded-pill px-3 py-2">
                    <?php echo (int)count($inventory); ?> items
                </span>
            </div>

            <?php if (count($inventory) === 0) : ?>
                <div class="p-4 rounded-3 bg-light border text-center text-muted">
                    No products yet. Add your first item using the form on the left.
                </div>
            <?php else : ?>
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr class="text-muted">
                                <th>Product</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item) : ?>
                                <?php
                                $img = $item['image_path'] ? str_replace('\\', '/', (string)$item['image_path']) : '';
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars((string)$item['product_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="fw-bold text-primary">₹<?php echo number_format((float)$item['price'], 2); ?></td>
                                    <td>
                                        <?php
                                        $imgExists = $img !== '' && is_string($img) && file_exists(__DIR__ . '/' . $img);
                                        ?>
                                        <?php if ($imgExists) : ?>
                                            <img
                                                src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                                                alt=""
                                                class="rounded-3 border"
                                                style="width: 52px; height: 40px; object-fit: cover;">
                                        <?php else : ?>
                                            <span class="text-muted small">Image not found</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="store-dashboard.php?edit_id=<?php echo (int)$item['id']; ?>">
                                            <i data-lucide="edit-3" class="me-1"></i>Edit
                                        </a>

                                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger ms-1">
                                                <i data-lucide="trash-2" class="me-1"></i>Delete
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

