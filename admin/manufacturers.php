<?php
/**
 * Admin — Manufacturers Directory
 * PATH: /admin/manufacturers.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

auth_guard('admin');

$page_title = 'Manufacturers';
?>
<style>
    /* Ensure date picker icon is visible in dark theme */
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0.8);
        cursor: pointer;
    }
    /* If the app specifically uses light theme, we might need to reset it, 
       but typically invert(0.8) works well for dark inputs. 
       Let's use a more robust approach if light theme is active. */
    [data-theme='light'] input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0);
    }
</style>
<?php
$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = $_SESSION['email'] ?? 'admin@bikebarber.com';


// ── Backend Handler: Add Manufacturer ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mfr'])) {
    $comp_name = mysqli_real_escape_string($conn, $_POST['mfrName']);
    $category = mysqli_real_escape_string($conn, $_POST['mfrCategory']);
    $location = mysqli_real_escape_string($conn, $_POST['mfrLocation']);
    $phone = mysqli_real_escape_string($conn, $_POST['mfrPhone']);
    $email = mysqli_real_escape_string($conn, $_POST['mfrEmail']);
    $website = mysqli_real_escape_string($conn, $_POST['mfrWebsite']);

    $sql = "INSERT INTO manufacturers (name, company_name, category, location, phone, email, website, status) 
            VALUES ('$comp_name', '$comp_name', '$category', '$location', '$phone', '$email', '$website', 'Active')";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// ── Backend Handler: Update Manufacturer ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_mfr'])) {
    $id = (int)$_POST['manufacturer_id'];
    $comp_name = mysqli_real_escape_string($conn, $_POST['mfrName']);
    $category = mysqli_real_escape_string($conn, $_POST['mfrCategory']);
    $location = mysqli_real_escape_string($conn, $_POST['mfrLocation']);
    $phone = mysqli_real_escape_string($conn, $_POST['mfrPhone']);
    $email = mysqli_real_escape_string($conn, $_POST['mfrEmail']);
    $website = mysqli_real_escape_string($conn, $_POST['mfrWebsite']);
    $status = mysqli_real_escape_string($conn, $_POST['mfrStatus']);

    $sql = "UPDATE manufacturers SET 
            company_name='$comp_name', name='$comp_name', category='$category', 
            location='$location', phone='$phone', email='$email', website='$website',
            status='$status'
            WHERE manufacturer_id=$id";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// ── Backend Handler: Delete Manufacturer (Now via GET with feedback) ──
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    if (mysqli_query($conn, "DELETE FROM manufacturers WHERE manufacturer_id = $id")) {
        header("Location: manufacturers.php?msg=deleted");
    } else {
        $err = mysqli_error($conn);
        header("Location: manufacturers.php?msg=error&err=" . urlencode($err));
    }
    exit;
}

// ── Backend Handler: Fetch Manufacturer ──
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_mfr'])) {
    $id = (int)$_GET['get_mfr'];
    $res = mysqli_query($conn, "SELECT * FROM manufacturers WHERE manufacturer_id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

// ── Backend Handler: SAVE RECORDED PRODUCTS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_record'])) {
    $mfr_id = (int)$_POST['mfr_id'];
    $details = mysqli_real_escape_string($conn, $_POST['product_details']);
    $date = mysqli_real_escape_string($conn, $_POST['order_date']);

    $sql = "INSERT INTO manufacturer_recordings (manufacturer_id, product_details, order_date) 
            VALUES ($mfr_id, '$details', '$date')";
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($conn)]);
    }
    exit;
}

// ── Backend Handler: FETCH ORDER HISTORY ──
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_history'])) {
    $id = (int)$_GET['get_history'];
    $sql = "SELECT * FROM manufacturer_recordings WHERE manufacturer_id = $id ORDER BY order_date DESC";
    $res = mysqli_query($conn, $sql);
    $history = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $history[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $history]);
    exit;
}

$admin_email = $_SESSION['email'] ?? 'admin@bikebarber.com';

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <main class="admin-content">
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] === 'deleted'): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-4 animate-in fade-in" role="alert" style="background: rgba(22, 163, 74, 0.1); border: 1px solid var(--success); color: var(--success); border-radius: var(--radius); padding: 1rem; border-left: 4px solid var(--success);">
                        <i class="bi bi-check-circle-fill me-2"></i> Manufacturer deleted successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif ($_GET['msg'] === 'error'): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-4 animate-in fade-in" role="alert" style="background: rgba(153, 27, 27, 0.1); border: 1px solid var(--destructive); color: var(--foreground); border-radius: var(--radius); padding: 1rem; border-left: 4px solid var(--destructive);">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Operation Failed:</strong> <?= htmlspecialchars($_GET['err'] ?? 'Access denied or database error.') ?>
                        <?php if (isset($_GET['context']) && $_GET['context'] === 'email'): ?>
                            <br><small class="text-muted-foreground mt-2 block">Troubleshoot: Ensure your SMTP server is running or configure real credentials (like Gmail) in the source code of this page.</small>
                        <?php else: ?>
                            <br><small class="text-muted-foreground mt-2 block">Tip: A manufacturer cannot be deleted if they are currently linked to purchase orders or active stock items.</small>
                        <?php endif; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1)"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="flex items-center justify-between mb-6 animate-in slide-up">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight m-0">Manufacturers & Suppliers</h2>
                    <p class="text-muted-foreground text-sm mt-1 mb-0">Manage your supplier network, contact information, and purchase history.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="shadcn-btn shadcn-btn-outline" data-bs-toggle="modal" data-bs-target="#recordProductsModal">
                        <i class="bi bi-journal-plus"></i> Record Products
                    </button>
                    <button class="shadcn-btn shadcn-btn-primary" data-bs-toggle="modal" data-bs-target="#addManufacturerModal">
                        <i class="bi bi-plus-lg"></i> Add Manufacturer
                    </button>
                </div>
            </div>

            <!-- Summary KPIs -->

            <!-- Manufacturer Cards Grid -->
            <div class="row g-4 mb-6 animate-in slide-up delay-500">
                <?php
                $mfr_query = "SELECT * FROM manufacturers ORDER BY manufacturer_id DESC";
                $mfr_res = mysqli_query($conn, $mfr_query);
                while ($m = mysqli_fetch_assoc($mfr_res)):
                    $first_letter = strtoupper(substr($m['company_name'], 0, 1));
                ?>
                    <div class="col-xl-4 col-lg-6 col-md-6">
                        <div class="shadcn-card h-100 flex flex-col">
                            <div class="p-5 flex items-start justify-between border-b border-border" style="border-bottom: 1px solid var(--border)">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full flex items-center justify-center font-bold text-white shadow-sm" style="width: 44px; height: 44px; background: #2563eb; border-radius: 50%;"><?= $first_letter ?></div>
                                    <div>
                                        <h6 class="font-semibold text-foreground m-0 text-base leading-none"><?= htmlspecialchars($m['company_name']) ?></h6>
                                        <span class="text-xs text-muted-foreground mt-1 block"><?= htmlspecialchars($m['category'] ?? 'General') ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col gap-3">
                                <div class="flex items-center gap-3 text-sm text-muted-foreground"><i class="bi bi-geo-alt"></i><span><?= htmlspecialchars($m['location'] ?? 'Unknown') ?></span></div>
                                <div class="flex items-center gap-3 text-sm text-muted-foreground"><i class="bi bi-telephone"></i><span><?= htmlspecialchars($m['phone']) ?></span></div>
                                <div class="flex items-center gap-3 text-sm text-muted-foreground"><i class="bi bi-envelope"></i><span><?= htmlspecialchars($m['email'] ?? '-') ?></span></div>
                            </div>
                            <div class="p-3 border-t border-border flex items-center justify-end gap-1" style="border-top: 1px solid var(--border); background: var(--muted)">
                                <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon btn-history-mfr" data-id="<?= $m['manufacturer_id'] ?>" title="History"><i class="bi bi-clock-history"></i></button>
                                <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon btn-edit-mfr" data-id="<?= $m['manufacturer_id'] ?>" title="Edit"><i class="bi bi-pencil"></i></button>
                                <a href="manufacturers.php?delete_id=<?= $m['manufacturer_id'] ?>" 
                                   class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-destructive hover:bg-destructive/10" 
                                   onclick="return confirmDeletion(event)" 
                                   title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <!-- Add New Button -->
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="shadcn-card h-100 flex flex-col items-center justify-center cursor-pointer hover:bg-muted/50 transition-colors" data-bs-toggle="modal" data-bs-target="#addManufacturerModal" style="min-height: 250px; border-style: dashed; border-width: 2px;">
                        <i class="bi bi-plus-circle text-4xl text-muted-foreground mb-3" style="font-size: 2.5rem;"></i>
                        <span class="font-medium text-foreground">Add New Manufacturer</span>
                    </div>
                </div>
            </div>
            
        </main>
    </div>
</div>

<!-- MODAL: ADD MANUFACTURER -->
<div class="modal fade" id="addManufacturerModal" tabindex="-1" aria-labelledby="addManufacturerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="addManufacturerModalLabel"><i class="bi bi-building-add me-2"></i>Add Manufacturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addMfrForm" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block" for="mfrName">Company Name <span class="text-destructive">*</span></label>
                            <input type="text" name="mfrName" class="shadcn-input w-full" id="mfrName" placeholder="e.g. Motul India Pvt Ltd" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="mfrCategory">Category <span class="text-destructive">*</span></label>
                            <select name="mfrCategory" class="shadcn-input w-full" id="mfrCategory" required>
                                <option value="">Select category...</option>
                                <option>Helmets & Safety Gear</option>
                                <option>Lubricants & Oils</option>
                                <option>Riding Gear & Apparel</option>
                                <option>Accessories & Parts</option>
                                <option>Crash Guards & Protection</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="mfrLocation">Location <span class="text-destructive">*</span></label>
                            <input type="text" name="mfrLocation" class="shadcn-input w-full" id="mfrLocation" placeholder="e.g. Mumbai, Maharashtra" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="mfrPhone">Phone <span class="text-destructive">*</span></label>
                            <input type="text" name="mfrPhone" class="shadcn-input w-full" id="mfrPhone" placeholder="Numeric only, max 14 digits" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="mfrEmail">Email <span class="text-destructive">*</span></label>
                            <input type="email" name="mfrEmail" class="shadcn-input w-full" id="mfrEmail" placeholder="supply@company.com" required>
                        </div>
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block" for="mfrWebsite">Website <span class="text-xs text-muted-foreground ml-2">(Optional)</span></label>
                            <input type="url" name="mfrWebsite" class="shadcn-input w-full" id="mfrWebsite" placeholder="https://www.company.com">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addMfrForm" class="shadcn-btn shadcn-btn-primary" id="submitMfr"><i class="bi bi-check-lg me-1"></i> Add Manufacturer</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: EDIT MANUFACTURER -->
<div class="modal fade" id="editManufacturerModal" tabindex="-1" aria-labelledby="editManufacturerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="editManufacturerModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Manufacturer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMfrForm" novalidate>
                    <input type="hidden" name="manufacturer_id" id="editMfrId">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block" for="editMfrName">Company Name <span class="text-destructive">*</span></label>
                            <input type="text" name="mfrName" class="shadcn-input w-full" id="editMfrName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="editMfrCategory">Category <span class="text-destructive">*</span></label>
                            <select name="mfrCategory" class="shadcn-input w-full" id="editMfrCategory" required>
                                <option value="">Select category...</option>
                                <option>Helmets & Safety Gear</option>
                                <option>Lubricants & Oils</option>
                                <option>Riding Gear & Apparel</option>
                                <option>Accessories & Parts</option>
                                <option>Crash Guards & Protection</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="editMfrLocation">Location <span class="text-destructive">*</span></label>
                            <input type="text" name="mfrLocation" class="shadcn-input w-full" id="editMfrLocation" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="editMfrPhone">Phone <span class="text-destructive">*</span></label>
                            <input type="text" name="mfrPhone" class="shadcn-input w-full" id="editMfrPhone" maxlength="14" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="editMfrEmail">Email <span class="text-destructive">*</span></label>
                            <input type="email" name="mfrEmail" class="shadcn-input w-full" id="editMfrEmail" required>
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="editMfrWebsite">Website</label>
                            <input type="url" name="mfrWebsite" class="shadcn-input w-full" id="editMfrWebsite">
                        </div>
                        <div class="col-md-6">
                            <label class="text-sm font-medium mb-2 block" for="editMfrStatus">Status</label>
                            <select name="mfrStatus" class="shadcn-input w-full" id="editMfrStatus">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editMfrForm" class="shadcn-btn shadcn-btn-primary"><i class="bi bi-check-lg me-1"></i> Update Details</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── ADD MANUFACTURER ──
    const addMfrForm = document.getElementById('addMfrForm');
    if (addMfrForm) {
        addMfrForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('add_mfr', '1');
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("Manufacturer added successfully!");
                        window.location.reload();
                    } else alert("Error: " + data.message);
                });
        });
    }

    // ── EDIT MANUFACTURER ──
    const editMfrForm = document.getElementById('editMfrForm');
    if (editMfrForm) {
        editMfrForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('update_mfr', '1');
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("Manufacturer updated successfully!");
                        window.location.reload();
                    } else alert("Error: " + data.message);
                });
        });
    }

    // ── ACTIONS (EDIT/DELETE) ──
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.btn-edit-mfr');
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`${window.location.pathname}?get_mfr=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const m = res.data;
                        document.getElementById('editMfrId').value = m.manufacturer_id;
                        document.getElementById('editMfrName').value = m.company_name;
                        document.getElementById('editMfrCategory').value = m.category || '';
                        document.getElementById('editMfrLocation').value = m.location || '';
                        document.getElementById('editMfrPhone').value = m.phone;
                        document.getElementById('editMfrEmail').value = m.email || '';
                        document.getElementById('editMfrWebsite').value = m.website || '';
                        document.getElementById('editMfrStatus').value = m.status || 'Active';
                        
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('editManufacturerModal')).show();
                    }
                });
            return;
        }
    });

    // ── RECORD PRODUCTS ──
    const recordProductsForm = document.getElementById('recordProductsForm');
    if (recordProductsForm) {
        recordProductsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('save_record', '1');
            fetch(window.location.pathname, { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    if (data.status === 'success') {
                        alert("Product record saved successfully!");
                        window.location.reload();
                    } else alert("Error: " + data.message);
                });
        });
    }

    // ── VIEW HISTORY ──
    document.addEventListener('click', function(e) {
        const historyBtn = e.target.closest('.btn-history-mfr');
        if (historyBtn) {
            const id = historyBtn.dataset.id;
            fetch(`${window.location.pathname}?get_history=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const historyList = document.getElementById('historyList');
                        historyList.innerHTML = '';
                        if (res.data.length === 0) {
                            historyList.innerHTML = '<div class="text-center p-4 text-muted-foreground">No records found for this manufacturer.</div>';
                        } else {
                            res.data.forEach(item => {
                                historyList.innerHTML += `
                                    <div class="border rounded-md p-3 mb-3 bg-muted/20">
                                        <div class="flex justify-between mb-2">
                                            <span class="font-bold text-sm">${item.order_date}</span>
                                        </div>
                                        <div class="text-sm text-foreground whitespace-pre-wrap">${item.product_details}</div>
                                    </div>
                                `;
                            });
                        }
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('historyModal')).show();
                    }
                });
        }
    });

});

// Clean URL parameters to prevent messages reappearing on manual refresh
if (window.history.replaceState && window.location.search) {
    const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
    window.history.replaceState({ path: cleanUrl }, '', cleanUrl);
}

function confirmDeletion(e) {
    return confirm('Are you sure you want to delete this manufacturer?');
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>

<!-- MODAL: RECORD PRODUCTS -->
<div class="modal fade" id="recordProductsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal shadow-2xl border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-bold flex items-center gap-2"><i class="bi bi-journal-plus text-primary"></i> Record Ordered Products</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-icon-filter);"></button>
            </div>
            <form id="recordProductsForm" novalidate>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-muted-foreground uppercase tracking-wider">Select Manufacturer *</label>
                        <select name="mfr_id" class="shadcn-input w-full" required>
                            <option value="">Select a manufacturer...</option>
                            <?php 
                            $mfr_select = mysqli_query($conn, "SELECT manufacturer_id, company_name FROM manufacturers ORDER BY company_name ASC");
                            while($row = mysqli_fetch_assoc($mfr_select)): 
                            ?>
                                <option value="<?= $row['manufacturer_id'] ?>"><?= htmlspecialchars($row['company_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-muted-foreground uppercase tracking-wider">Order Date *</label>
                        <input type="date" name="order_date" class="shadcn-input w-full" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-xs font-semibold text-muted-foreground uppercase tracking-wider">Product Details *</label>
                        <textarea name="product_details" class="shadcn-input w-full h-auto py-3" rows="8" placeholder="Enter products, quantities, prices, etc." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="shadcn-btn shadcn-btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="shadcn-btn shadcn-btn-primary px-4">
                        <i class="bi bi-check2-circle me-2"></i> Save Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: ORDER HISTORY -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-modal shadow-2xl border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title font-bold flex items-center gap-2"><i class="bi bi-clock-history text-primary"></i> Order History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-0" id="historyList">
                <!-- Data populated via JS -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="shadcn-btn shadcn-btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

