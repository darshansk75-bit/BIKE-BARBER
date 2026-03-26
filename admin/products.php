<?php
/**
 * Admin — Products & Inventory Management
 * PATH: /admin/products.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
require_once __DIR__ . '/../config/db.php';
auth_guard('admin');

// Backend Handler for AJAX Product Addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['prodName']);
    $desc = mysqli_real_escape_string($conn, $_POST['prodDesc']);
    $price = (float)$_POST['prodPrice'];
    $stock = (int)$_POST['prodStock'];
    $category_name = mysqli_real_escape_string($conn, $_POST['prodCategory']);
    $expiry = !empty($_POST['prodExpiry']) ? mysqli_real_escape_string($conn, $_POST['prodExpiry']) : null;
    $status = mysqli_real_escape_string($conn, $_POST['prodStatus']);

    // 1. Handle Category (Find ID or Create)
    $cat_query = "SELECT category_id FROM categories WHERE category_name = '$category_name'";
    $cat_res = mysqli_query($conn, $cat_query);
    if ($cat_row = mysqli_fetch_assoc($cat_res)) {
        $category_id = $cat_row['category_id'];
    } else {
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$category_name')");
        $category_id = mysqli_insert_id($conn);
    }

    // 2. Handle Image Upload
    $image_path = null;
    if (isset($_FILES['prodImage']) && $_FILES['prodImage']['error'] === 0) {
        $ext = pathinfo($_FILES['prodImage']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('prod_') . '.' . $ext;
        $target = __DIR__ . '/../uploads/' . $new_name;
        if (move_uploaded_file($_FILES['prodImage']['tmp_name'], $target)) {
            $image_path = 'uploads/' . $new_name;
        }
    }

    // 3. Check for expiry_date and status columns and add them if missing
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'expiry_date'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE products ADD COLUMN expiry_date DATE DEFAULT NULL AFTER stock_quantity");
    }
    $check_status = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'status'");
    if (mysqli_num_rows($check_status) == 0) {
        mysqli_query($conn, "ALTER TABLE products ADD COLUMN status VARCHAR(50) DEFAULT 'Active' AFTER expiry_date");
    }

    // 4. Insert Product
    $sql = "INSERT INTO products (category_id, product_name, description, price, stock_quantity, expiry_date, status, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issdisss", $category_id, $name, $desc, $price, $stock, $expiry, $status, $image_path);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// Backend Handler for Updating Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $id = (int)$_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['prodName']);
    $desc = mysqli_real_escape_string($conn, $_POST['prodDesc']);
    $price = (float)$_POST['prodPrice'];
    $stock = (int)$_POST['prodStock'];
    $category_name = mysqli_real_escape_string($conn, $_POST['prodCategory']);
    $expiry = !empty($_POST['prodExpiry']) ? mysqli_real_escape_string($conn, $_POST['prodExpiry']) : null;
    $status = mysqli_real_escape_string($conn, $_POST['prodStatus']);

    // 0. Ensure columns exist
    $check_col = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'expiry_date'");
    if (mysqli_num_rows($check_col) == 0) {
        mysqli_query($conn, "ALTER TABLE products ADD COLUMN expiry_date DATE DEFAULT NULL AFTER stock_quantity");
    }
    $check_status = mysqli_query($conn, "SHOW COLUMNS FROM products LIKE 'status'");
    if (mysqli_num_rows($check_status) == 0) {
        mysqli_query($conn, "ALTER TABLE products ADD COLUMN status VARCHAR(50) DEFAULT 'Active' AFTER expiry_date");
    }

    // 1. Handle Category
    $cat_query = "SELECT category_id FROM categories WHERE category_name = '$category_name'";
    $cat_res = mysqli_query($conn, $cat_query);
    if ($cat_row = mysqli_fetch_assoc($cat_res)) {
        $category_id = $cat_row['category_id'];
    } else {
        mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$category_name')");
        $category_id = mysqli_insert_id($conn);
    }

    // 2. Update Basic Data
    $sql = "UPDATE products SET category_id=?, product_name=?, description=?, price=?, stock_quantity=?, expiry_date=?, status=? WHERE product_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issdissi", $category_id, $name, $desc, $price, $stock, $expiry, $status, $id);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        exit;
    }

    // 3. Handle Image Update (if new one provided)
    if (isset($_FILES['prodImage']) && $_FILES['prodImage']['error'] === 0) {
        $ext = pathinfo($_FILES['prodImage']['name'], PATHINFO_EXTENSION);
        $new_name = uniqid('prod_') . '.' . $ext;
        $target = __DIR__ . '/../uploads/' . $new_name;
        if (move_uploaded_file($_FILES['prodImage']['tmp_name'], $target)) {
            $image_path = 'uploads/' . $new_name;
            mysqli_query($conn, "UPDATE products SET image = '$image_path' WHERE product_id = $id");
        }
    }

    echo json_encode(['status' => 'success']);
    exit;
}

// Backend Handler for Restocking Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock_product'])) {
    $id = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];

    if ($id > 0 && $qty > 0) {
        $sql = "UPDATE products SET stock_quantity = stock_quantity + $qty WHERE product_id = $id";
        if (mysqli_query($conn, $sql)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity.']);
    }
    exit;
}

// Backend Handler for Deleting Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];
    
    // Optional: Get image path and delete file
    $res = mysqli_query($conn, "SELECT image FROM products WHERE product_id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['image']) && file_exists(__DIR__ . '/../' . $row['image'])) {
            unlink(__DIR__ . '/../' . $row['image']);
        }
    }

    if (mysqli_query($conn, "DELETE FROM products WHERE product_id = $id")) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// Backend Handler to Fetch Single Product
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_product'])) {
    $id = (int)$_GET['get_product'];
    $query = "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = $id";
    $res = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

$page_title = 'Products';
$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = htmlspecialchars($_SESSION['email'] ?? 'admin@bikebarber.com');

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <main class="admin-content">
            <!-- Page Header Row -->
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6 no-wrap-mobile animate-in slide-up">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight m-0">Products & Inventory</h2>
                    <p class="text-muted-foreground text-sm mt-1 mb-0">Manage your product catalog, monitor stock levels, and add new inventory.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="shadcn-btn shadcn-btn-outline" data-bs-toggle="modal" data-bs-target="#restockModal">
                        <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline">Restock</span>
                    </button>
                    <button class="shadcn-btn shadcn-btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Add Product</span>
                    </button>
                </div>
            </div>

            <!-- PRODUCT DATA TABLES -->
            <div class="shadcn-card animate-in slide-up delay-200">
                <!-- Tabs -->
                <div class="flex items-center border-b border-border" style="border-bottom: 1px solid var(--border)">
                    <button class="data-tab active" data-target="allProducts">All Products</button>
                    <button class="data-tab" data-target="lowStock">Low Stock</button>
                    <button class="data-tab" data-target="mostOrdered">Most Ordered</button>
                </div>

                <!-- Search & Filter Bar -->
                <div class="p-6 flex items-center justify-between gap-4">
                    <div class="flex flex-1 items-center gap-2 max-w-sm" style="position: relative;">
                        <i class="bi bi-search text-muted-foreground" style="position: absolute; left: 0.75rem;"></i>
                        <input type="text" id="productSearchInput" class="shadcn-input" placeholder="Search products..." style="padding-left: 2.25rem;" autocomplete="off">
                    </div>
                    <div class="flex items-center gap-2">
                        <select class="shadcn-input" id="categoryFilter" style="width: auto; padding-right: 2rem;">
                            <option value="">All Categories</option>
                            <option value="Helmets">Helmets</option>
                            <option value="Crash Guards">Crash Guards</option>
                            <option value="Riding Gear">Riding Gear</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Lubricants">Lubricants</option>
                        </select>
                    </div>
                </div>

                <!-- Tab: All Products -->
                <div class="data-tab-pane active" id="allProducts">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border-left: none; border-right: none; border-bottom: none;">
                        <table class="shadcn-table" id="allProductsTable">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, c.category_name 
                                          FROM products p 
                                          LEFT JOIN categories c ON p.category_id = c.category_id 
                                          ORDER BY p.created_at DESC";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)):
                                    $stock = (int)$row['stock_quantity'];
                                    if ($stock <= 0) {
                                        $status_badge = '<span class="shadcn-badge shadcn-badge-secondary bg-destructive-subtle">Out of Stock</span>';
                                    } elseif ($stock < 10) {
                                        $status_badge = '<span class="shadcn-badge shadcn-badge-secondary bg-warning-subtle">Low Stock</span>';
                                    } else {
                                        $status_badge = '<span class="shadcn-badge shadcn-badge-secondary bg-success-subtle">In Stock</span>';
                                    }
                                    
                                    $img_src = !empty($row['image']) ? SITE_URL . '/' . $row['image'] : null;
                                ?>
                                <tr>
                                    <td><span class="text-sm font-medium" style="font-family: monospace;">PRD<?= str_pad($row['product_id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                    <td>
                                        <?php if ($img_src): ?>
                                            <img src="<?= $img_src ?>" alt="Product" style="width:32px; height:32px; object-fit:cover; border-radius: var(--radius-sm);">
                                        <?php else: ?>
                                            <div class="glass-icon-box" style="width:32px; height:32px;"><i class="bi bi-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-medium text-foreground"><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><span class="text-muted-foreground text-xs"><?= htmlspecialchars($row['category_name']) ?></span></td>
                                    <td>₹<?= number_format($row['price'], 2) ?></td>
                                    <td><?= $stock ?></td>
                                    <td class="text-sm text-muted-foreground"><?= $row['expiry_date'] ? date('d M Y', strtotime($row['expiry_date'])) : '-' ?></td>
                                    <td><?= $status_badge ?></td>
                                    <td class="flex items-center gap-1">
                                        <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon btn-edit-product" data-id="<?= $row['product_id'] ?>"><i class="bi bi-pencil"></i></button>
                                        <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-destructive hover:bg-destructive/10 btn-delete-product" data-id="<?= $row['product_id'] ?>"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Low Stock -->
                <div class="data-tab-pane" id="lowStock">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border-left: none; border-right: none; border-bottom: none;">
                        <table class="shadcn-table">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Stock</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                mysqli_data_seek($result, 0); // Reset result pointer
                                while ($row = mysqli_fetch_assoc($result)):
                                    $stock = (int)$row['stock_quantity'];
                                    if ($stock >= 10) continue;
                                    
                                    $status_badge = ($stock <= 0) 
                                        ? '<span class="shadcn-badge shadcn-badge-secondary bg-destructive-subtle">Out of Stock</span>'
                                        : '<span class="shadcn-badge shadcn-badge-secondary bg-warning-subtle">Low Stock</span>';
                                    
                                    $stock_class = ($stock <= 0) ? 'text-destructive' : 'text-warning';
                                ?>
                                <tr>
                                    <td><span style="font-family: monospace;">PRD<?= str_pad($row['product_id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                    <td class="font-medium text-foreground"><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><span class="text-muted-foreground text-xs"><?= htmlspecialchars($row['category_name']) ?></span></td>
                                    <td class="font-bold <?= $stock_class ?>" style="color:var(--<?= str_replace('text-', '', $stock_class) ?>)"><?= $stock ?></td>
                                    <td class="text-sm text-muted-foreground"><?= $row['expiry_date'] ? date('d M Y', strtotime($row['expiry_date'])) : '-' ?></td>
                                    <td><?= $status_badge ?></td>
                                    <td class="flex items-center gap-1">
                                        <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon btn-edit-product" data-id="<?= $row['product_id'] ?>"><i class="bi bi-pencil"></i></button>
                                        <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-destructive hover:bg-destructive/10 btn-delete-product" data-id="<?= $row['product_id'] ?>"><i class="bi bi-trash3"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Most Ordered -->
                <div class="data-tab-pane" id="mostOrdered">
                    <div class="p-6 text-center text-muted-foreground"><p>Analytics connected to backend...</p></div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel"><i class="bi bi-box-seam me-2"></i>Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addProductForm" novalidate>
                    <div class="row g-4">
                        <div class="col-lg-8 col-12">
                            <label class="text-sm font-medium mb-2 block" for="prodName">Product Name <span class="text-destructive">*</span></label>
                            <input type="text" id="prodName" name="prodName" class="shadcn-input" placeholder="e.g. Premium Full-Face Helmet" required>
                            
                            <label class="text-sm font-medium mb-2 block mt-4" for="prodDesc">Description</label>
                            <textarea id="prodDesc" name="prodDesc" class="shadcn-input" style="height: 100px; padding-top: 0.5rem;" placeholder="Product description..."></textarea>
                            
                            <div class="row g-3 mt-1">
                                <div class="col-sm-6 col-12">
                                    <label class="text-sm font-medium mb-2 block" for="prodPrice">Price (₹) <span class="text-destructive">*</span></label>
                                    <input type="number" id="prodPrice" name="prodPrice" class="shadcn-input" placeholder="0.00" required>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="text-sm font-medium mb-2 block" for="prodComparePrice">Compare at Price (₹)</label>
                                    <input type="number" id="prodComparePrice" name="prodComparePrice" class="shadcn-input" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-12">
                            <div class="shadcn-card p-4 h-100" style="background: var(--muted)">
                                <label class="text-sm font-medium mb-2 block" for="prodStatus">Status</label>
                                <select id="prodStatus" name="prodStatus" class="shadcn-input mb-4">
                                    <option value="Active">Active</option>
                                    <option value="Draft">Draft</option>
                                </select>
                                
                                <label class="text-sm font-medium mb-2 block" for="prodCategory">Category <span class="text-destructive">*</span></label>
                                <select id="prodCategory" name="prodCategory" class="shadcn-input mb-4" required>
                                    <option value="">Select...</option>
                                    <option value="Helmets">Helmets</option>
                                    <option value="Crash Guards">Crash Guards</option>
                                    <option value="Riding Gear">Riding Gear</option>
                                    <option value="Accessories">Accessories</option>
                                </select>
                                
                                <label class="text-sm font-medium mb-2 block" for="prodStock">Initial Stock <span class="text-destructive">*</span></label>
                                <input type="number" id="prodStock" name="prodStock" class="shadcn-input" value="0" required>
                                <p class="text-[10px] text-muted-foreground mt-1">Items with stock < 10 show in Low Stock tab.</p>

                                <label class="text-sm font-medium mb-2 block mt-4" for="prodExpiry">Expiry Date (Optional)</label>
                                <input type="date" id="prodExpiry" name="prodExpiry" class="shadcn-input w-full" style="color-scheme: dark;">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block">Product Images</label>
                            <label style="display: block; width: 100%; cursor: pointer;">
                                <input type="file" id="productImageUpload" name="prodImage" accept="image/png, image/jpeg, image/webp" style="display: none;">
                                <div class="shadcn-card flex flex-col items-center justify-center p-6 text-center cursor-pointer bg-foreground-subtle" style="border-style: dashed; border-width: 2px;">
                                    <i class="bi bi-cloud-arrow-up text-4xl text-muted-foreground mb-3" style="font-size: 2rem;"></i>
                                    <span class="font-medium" id="fileNameDisplay">Click to upload or drag & drop</span>
                                    <span class="text-xs text-muted-foreground mt-1">PNG, JPG, WEBP up to 5MB</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addProductForm" class="shadcn-btn shadcn-btn-primary"><i class="bi bi-check-lg me-1"></i> Save Product</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal (Dedicated) -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProductForm" novalidate>
                    <input type="hidden" name="product_id" id="editProdId">
                    <div class="row g-4">
                        <div class="col-lg-8 col-12">
                            <label class="text-sm font-medium mb-2 block" for="editProdName">Product Name <span class="text-destructive">*</span></label>
                            <input type="text" id="editProdName" name="prodName" class="shadcn-input" required>
                            
                            <label class="text-sm font-medium mb-2 block mt-4" for="editProdDesc">Description</label>
                            <textarea id="editProdDesc" name="prodDesc" class="shadcn-input" style="height: 100px; padding-top: 0.5rem;"></textarea>
                            
                            <div class="row g-3 mt-1">
                                <div class="col-sm-6 col-12">
                                    <label class="text-sm font-medium mb-2 block" for="editProdPrice">Price (₹) <span class="text-destructive">*</span></label>
                                    <input type="number" id="editProdPrice" name="prodPrice" class="shadcn-input" required>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <label class="text-sm font-medium mb-2 block" for="editProdComparePrice">Compare at Price (₹)</label>
                                    <input type="number" id="editProdComparePrice" name="prodComparePrice" class="shadcn-input">
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-12">
                            <div class="shadcn-card p-4 h-100" style="background: var(--muted)">
                                <label class="text-sm font-medium mb-2 block" for="editProdStatus">Status</label>
                                <select id="editProdStatus" name="prodStatus" class="shadcn-input mb-4">
                                    <option value="Active">Active</option>
                                    <option value="Draft">Draft</option>
                                </select>
                                
                                <label class="text-sm font-medium mb-2 block" for="editProdCategory">Category <span class="text-destructive">*</span></label>
                                <select id="editProdCategory" name="prodCategory" class="shadcn-input mb-4" required>
                                    <option value="">Select...</option>
                                    <option value="Helmets">Helmets</option>
                                    <option value="Crash Guards">Crash Guards</option>
                                    <option value="Riding Gear">Riding Gear</option>
                                    <option value="Accessories">Accessories</option>
                                </select>
                                
                                <label class="text-sm font-medium mb-2 block" for="editProdStock">Stock <span class="text-destructive">*</span></label>
                                <input type="number" id="editProdStock" name="prodStock" class="shadcn-input" required>
                                <p class="text-[10px] text-muted-foreground mt-1">Items with stock < 10 show in Low Stock tab.</p>

                                <label class="text-sm font-medium mb-2 block mt-4" for="editProdExpiry">Expiry Date (Optional)</label>
                                <input type="date" id="editProdExpiry" name="prodExpiry" class="shadcn-input w-full" style="color-scheme: dark;">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block">Product Images (Leave empty to keep current)</label>
                            <label style="display: block; width: 100%; cursor: pointer;">
                                <input type="file" id="editProductImageUpload" name="prodImage" accept="image/png, image/jpeg, image/webp" style="display: none;">
                                <div class="shadcn-card flex flex-col items-center justify-center p-6 text-center cursor-pointer bg-foreground-subtle" style="border-style: dashed; border-width: 2px;">
                                    <i class="bi bi-cloud-arrow-up text-4xl text-muted-foreground mb-3" style="font-size: 2rem;"></i>
                                    <span class="font-medium" id="editFileNameDisplay">Click to change image</span>
                                    <span class="text-xs text-muted-foreground mt-1">PNG, JPG, WEBP up to 5MB</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editProductForm" class="shadcn-btn shadcn-btn-primary"><i class="bi bi-check-lg me-1"></i> Update Product</button>
            </div>
        </div>
    </div>
</div>

<!-- Restock Product Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="restockModalLabel"><i class="bi bi-arrow-repeat me-2"></i>Quick Restock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="restockForm" novalidate>
                    <div class="space-y-4">
                        <div class="mb-3">
                            <label class="text-sm font-medium mb-2 block" for="restockProductId">Select Product <span class="text-destructive">*</span></label>
                            <select id="restockProductId" name="product_id" class="shadcn-input w-full" required>
                                <option value="">Choose a product...</option>
                                <?php
                                mysqli_data_seek($result, 0);
                                while($p = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$p['product_id']}'>".htmlspecialchars($p['product_name'])." (Current: {$p['stock_quantity']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="text-sm font-medium mb-2 block" for="restockQuantity">Add Quantity <span class="text-destructive">*</span></label>
                            <input type="number" id="restockQuantity" name="quantity" class="shadcn-input w-full" placeholder="Enter amount to add..." min="1" required>
                        </div>
                        
                        <div class="p-3 bg-primary/5 rounded-lg border border-primary/10">
                            <p class="text-xs text-muted-foreground mb-0">Note: This will immediately increase the current stock level of the selected product.</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="restockForm" class="shadcn-btn shadcn-btn-primary"><i class="bi bi-plus-lg me-1"></i> Update Stock</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── ADD PRODUCT LOGIC ──
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('add_product', '1');
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("Product saved successfully!");
                        window.location.reload();
                    } else alert("Error: " + data.message);
                });
        });
    }

    // Image Preview for Add
    const imgInp = document.getElementById('productImageUpload');
    if(imgInp) {
        imgInp.addEventListener('change', function() {
            if(this.files && this.files[0]) document.getElementById('fileNameDisplay').textContent = this.files[0].name;
        });
    }

    // ── RESTOCK PRODUCT LOGIC ──
    const restockForm = document.getElementById('restockForm');
    if (restockForm) {
        restockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('restock_product', '1');
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("Inventory updated successfully!");
                        window.location.reload();
                    } else alert("Error: " + data.message);
                });
        });
    }

    // ── EDIT PRODUCT LOGIC ──
    const editProductForm = document.getElementById('editProductForm');
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('update_product', '1');
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("Product updated successfully!");
                        window.location.reload();
                    } else alert("Error: " + data.message);
                });
        });
    }

    // Image Preview for Edit
    const editImgInp = document.getElementById('editProductImageUpload');
    if(editImgInp) {
        editImgInp.addEventListener('change', function() {
            if(this.files && this.files[0]) document.getElementById('editFileNameDisplay').textContent = this.files[0].name;
        });
    }

    // Event Delegation for Table Actions
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit-product');
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`${window.location.pathname}?get_product=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const p = res.data;
                        document.getElementById('editProdId').value = p.product_id;
                        document.getElementById('editProdName').value = p.product_name;
                        document.getElementById('editProdDesc').value = p.description;
                        document.getElementById('editProdPrice').value = p.price;
                        document.getElementById('editProdStock').value = p.stock_quantity;
                        document.getElementById('editProdCategory').value = p.category_name;
                        document.getElementById('editProdExpiry').value = p.expiry_date || '';
                        document.getElementById('editProdStatus').value = p.status || 'Active';
                        
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('editProductModal')).show();
                    }
                });
            return;
        }

        const deleteBtn = e.target.closest('.btn-delete-product');
        if (deleteBtn) {
            if (!confirm('Are you sure you want to delete this product?')) return;
            const formData = new FormData();
            formData.append('delete_product', '1');
            formData.append('product_id', deleteBtn.dataset.id);
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(res => res.json()).then(data => {
                    if (data.status === 'success') window.location.reload();
                    else alert('Error deleting product');
                });
        }
    });

    // Tab Switching Logic
    const tabs = document.querySelectorAll('.data-tab');
    const panes = document.querySelectorAll('.data-tab-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            
            tab.classList.add('active');
            const targetId = tab.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if(target) target.classList.add('active');
        });
    });

    // Image Upload Preview
    const imageUpload = document.getElementById('productImageUpload');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    if (imageUpload && fileNameDisplay) {
        imageUpload.addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
                fileNameDisplay.classList.add('text-primary');
            }
        });
    }

    // Product Search Filtering
    const searchInput = document.getElementById('productSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('#allProductsTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
