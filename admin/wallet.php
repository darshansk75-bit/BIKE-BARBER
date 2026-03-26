<?php
/**
 * Admin — Wallet & Financials
 * PATH: /admin/wallet.php
 */
require_once __DIR__ . '/../includes/session_auth.php';
auth_guard('admin');

$page_title = 'Wallet';
$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = htmlspecialchars($_SESSION['email'] ?? 'admin@bikebarber.com');

require_once __DIR__ . '/../config/db.php';

// Fetch Wallet Profile (Balance & Card details)
$wallet_sql = "SELECT * FROM admin_wallet ORDER BY wallet_id DESC LIMIT 1";
$wallet_res = mysqli_query($conn, $wallet_sql);

$wallet = ($wallet_res) ? mysqli_fetch_assoc($wallet_res) : null;
$wallet = $wallet ?: [
    'bank_balance' => 0.00,
    'card_holder' => 'Admin User',
    'card_number' => '•••• •••• •••• 4521',
    'card_expiry' => '12/28'
];

// Calculate Dynamic KPIs from real transaction history
$inv_res = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM purchases WHERE request_status='COMPLETED'");
$inv_total = mysqli_fetch_assoc($inv_res)['total'] ?: 0;

$sales_res = mysqli_query($conn, "SELECT SUM(amount) as total FROM wallet_transactions WHERE type IN ('SALE', 'INVESTMENT')");
$sales_total = mysqli_fetch_assoc($sales_res)['total'] ?: 0;

$exp_res = mysqli_query($conn, "SELECT SUM(amount) as total FROM wallet_transactions WHERE type='EXPENSE'");
$exp_total = mysqli_fetch_assoc($exp_res)['total'] ?: 0;

$wallet['total_investment'] = (float)$inv_total;
$wallet['total_sales'] = (float)$sales_total;
$wallet['total_expense'] = (float)$exp_total;
$wallet['total_profit'] = (float)($sales_total - $exp_total);

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <main class="admin-content">

            <div class="flex items-center justify-between mb-6 animate-in slide-up">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight m-0">Wallet & Financials</h2>
                    <p class="text-muted-foreground text-sm mt-1 mb-0">Overview of your business financial health and transaction history.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="shadcn-btn shadcn-btn-outline" data-bs-toggle="modal" data-bs-target="#editWalletModal">
                        <i class="bi bi-pencil-square"></i> Manage Funds
                    </button>
                    <div class="dropdown">
                        <button class="shadcn-btn shadcn-btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-download"></i> Export
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end admin-modal shadow-lg">
                            <li><a class="dropdown-item export-trigger" data-module="wallet" data-type="csv" href="#"><i class="bi bi-filetype-csv me-2"></i> CSV</a></li>
                            <li><a class="dropdown-item export-trigger" data-module="wallet" data-type="excel" href="#"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Excel</a></li>
                            <li><a class="dropdown-item export-trigger" data-module="wallet" data-type="pdf" href="#"><i class="bi bi-file-earmark-pdf me-2"></i> PDF Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Visual Wallet Card + Financial KPIs -->
            <div class="row g-4 mb-6">
                <!-- Visual Debit/Credit Card -->
                <div class="col-lg-5 col-md-6 animate-in slide-up delay-100">
                    <style>
                        .glass-cc {
                            background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 10%, transparent), transparent);
                            backdrop-filter: blur(10px);
                            -webkit-backdrop-filter: blur(10px);
                            border: 1px solid color-mix(in srgb, var(--primary) 18%, transparent);
                            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
                            border-radius: var(--radius-lg);
                            padding: 2rem;
                            position: relative;
                            overflow: hidden;
                            color: #fff;
                            height: 100%;
                            display: flex;
                            flex-direction: column;
                            min-height: 220px;
                        }
                        .glass-cc::before {
                            content: '';
                            position: absolute;
                            top: -50px; right: -50px;
                            width: 150px; height: 150px;
                            background: var(--primary);
                            filter: blur(60px); opacity: 0.5; z-index: 0;
                        }
                        .glass-cc::after {
                            content: '';
                            position: absolute;
                            bottom: -50px; left: -50px;
                            width: 150px; height: 150px;
                            background: #a855f7;
                            filter: blur(60px); opacity: 0.5; z-index: 0;
                        }
                        .glass-cc > * { position: relative; z-index: 1; }
                    </style>
                    <div class="glass-cc">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium opacity-80 uppercase tracking-widest">Business Wallet</span>
                            <i class="bi bi-credit-card-2-front text-2xl opacity-80"></i>
                        </div>
                        <div class="mt-auto mb-4">
                            <div class="text-xs opacity-70 mb-1 tracking-wider uppercase">Available Balance</div>
                            <div class="text-4xl font-bold tracking-tight">₹<?= number_format($wallet['bank_balance'], 2) ?></div>
                        </div>
                        <div class="flex items-center justify-between opacity-80 text-sm mt-auto" style="font-family: monospace; font-size: 1rem;">
                            <span><?= htmlspecialchars($wallet['card_number']) ?></span>
                            <span><?= htmlspecialchars($wallet['card_expiry']) ?></span>
                        </div>
                        <div class="mt-2 text-[10px] opacity-60 uppercase tracking-widest font-bold">
                            <?= htmlspecialchars($wallet['card_holder']) ?>
                        </div>
                    </div>
                </div>

                <!-- Financial Stats -->
                <div class="col-lg-7 col-md-6">
                    <div class="row g-3 h-100">
                        <!-- Inventory Invested -->
                        <div class="col-sm-6 animate-in slide-up delay-200">
                            <div class="shadcn-card h-100 p-4 flex gap-4 items-center">
                                <div class="glass-icon-box bg-purple-subtle" style="width: 48px; height: 48px; min-width: 48px;">
                                    <i class="bi bi-box-seam text-xl"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-muted-foreground uppercase tracking-wider font-semibold">Inventory Invested</span>
                                    <div class="text-xl font-bold text-foreground leading-none mt-1 mb-1">₹<?= number_format($wallet['total_investment'], 2) ?></div>
                                    <span class="text-[10px] text-muted-foreground block leading-tight">Total cost of stock</span>
                                </div>
                            </div>
                        </div>
                        <!-- Amount Burned -->
                        <div class="col-sm-6 animate-in slide-up delay-300">
                            <div class="shadcn-card h-100 p-4 flex gap-4 items-center">
                                <div class="glass-icon-box bg-destructive-subtle" style="width: 48px; height: 48px; min-width: 48px;">
                                    <i class="bi bi-fire text-xl"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-muted-foreground uppercase tracking-wider font-semibold">Amount Burned</span>
                                    <div class="text-xl font-bold text-foreground leading-none mt-1 mb-1">₹<?= number_format($wallet['total_expense'], 2) ?></div>
                                    <span class="text-[10px] text-muted-foreground block leading-tight">Expenses & costs</span>
                                </div>
                            </div>
                        </div>
                        <!-- Profit Gained -->
                        <div class="col-sm-6 animate-in slide-up delay-400">
                            <div class="shadcn-card h-100 p-4 flex gap-4 items-center">
                                <div class="glass-icon-box bg-success-subtle" style="width: 48px; height: 48px; min-width: 48px;">
                                    <i class="bi bi-graph-up-arrow text-xl"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-muted-foreground uppercase tracking-wider font-semibold">Profit Gained</span>
                                    <div class="text-xl font-bold text-foreground leading-none mt-1 mb-1">₹<?= number_format($wallet['total_profit'], 2) ?></div>
                                    <span class="text-[10px] text-muted-foreground block leading-tight">Net profitability</span>
                                </div>
                            </div>
                        </div>
                        <!-- Total Revenue -->
                        <div class="col-sm-6 animate-in slide-up delay-500">
                            <div class="shadcn-card h-100 p-4 flex gap-4 items-center">
                                <div class="glass-icon-box bg-primary-subtle" style="width: 48px; height: 48px; min-width: 48px;">
                                    <i class="bi bi-cash-stack text-xl"></i>
                                </div>
                                <div>
                                    <span class="text-xs text-muted-foreground uppercase tracking-wider font-semibold">Total Revenue</span>
                                    <div class="text-xl font-bold text-foreground leading-none mt-1 mb-1">₹<?= number_format($wallet['total_sales'], 2) ?></div>
                                    <span class="text-[10px] text-muted-foreground block leading-tight">Gross revenue</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="shadcn-card animate-in slide-up delay-600">
                <!-- Tabs -->
                <div class="flex items-center border-b border-border" style="border-bottom: 1px solid var(--border)">
                    <button class="data-tab active" data-target="allTransactions">
                        <i class="bi bi-arrow-left-right me-1"></i> All Transactions
                    </button>
                    <button class="data-tab" data-target="credits">
                        <i class="bi bi-arrow-down-circle me-1"></i> Credits
                    </button>
                    <button class="data-tab" data-target="debits">
                        <i class="bi bi-arrow-up-circle me-1"></i> Debits
                    </button>
                </div>

                <!-- Tab: All Transactions -->
                <div class="data-tab-pane active" id="allTransactions">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <table class="shadcn-table">
                            <thead>
                                <tr>
                                    <th>Txn ID</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $all_q = mysqli_query($conn, "SELECT * FROM wallet_transactions ORDER BY created_at DESC");
                                if (mysqli_num_rows($all_q) > 0) {
                                    while ($t = mysqli_fetch_assoc($all_q)) {
                                        $id = $t['transaction_id'];
                                        $type = $t['type'];
                                        $is_credit = in_array($type, ['INVESTMENT', 'SALE']);
                                ?>
                                <tr>
                                    <td><span style="font-family: monospace;">TXN<?= str_pad($id, 4, '0', STR_PAD_LEFT) ?></span></td>
                                    <td><span class="text-muted-foreground"><?= date('d M Y', strtotime($t['created_at'])) ?></span></td>
                                    <td class="font-medium text-foreground"><?= htmlspecialchars($t['description']) ?></td>
                                    <td>
                                        <span class="shadcn-badge shadcn-badge-secondary <?= $is_credit ? 'bg-success-subtle' : 'bg-destructive-subtle' ?>">
                                            <i class="bi <?= $is_credit ? 'bi-arrow-down-short' : 'bi-arrow-up-short' ?>"></i> 
                                            <?= $is_credit ? 'Credit' : 'Debit' ?>
                                        </span>
                                    </td>
                                    <td class="text-right font-medium" style="color: <?= $is_credit ? 'var(--success)' : 'var(--destructive)' ?>">
                                        <?= $is_credit ? '+' : '-' ?> ₹<?= number_format($t['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="5" class="text-center p-8 text-muted-foreground">No transactions found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Credits -->
                <div class="data-tab-pane" id="credits">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <table class="shadcn-table">
                            <thead>
                                <tr>
                                    <th>Txn ID</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $credit_q = mysqli_query($conn, "SELECT * FROM wallet_transactions WHERE type IN ('INVESTMENT', 'SALE') ORDER BY created_at DESC");
                                if (mysqli_num_rows($credit_q) > 0) {
                                    while ($t = mysqli_fetch_assoc($credit_q)) {
                                ?>
                                <tr>
                                    <td><span style="font-family: monospace;">TXN<?= str_pad($t['transaction_id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                    <td><span class="text-muted-foreground"><?= date('d M Y', strtotime($t['created_at'])) ?></span></td>
                                    <td class="font-medium text-foreground"><?= htmlspecialchars($t['description']) ?></td>
                                    <td class="text-right font-medium text-success">+ ₹<?= number_format($t['amount'], 2) ?></td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="4" class="text-center p-8 text-muted-foreground">No credit transactions found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab: Debits -->
                <div class="data-tab-pane" id="debits">
                    <div class="shadcn-table-wrapper" style="border-radius: 0; border: none;">
                        <table class="shadcn-table">
                            <thead>
                                <tr>
                                    <th>Txn ID</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $debit_q = mysqli_query($conn, "SELECT * FROM wallet_transactions WHERE type = 'EXPENSE' ORDER BY created_at DESC");
                                if (mysqli_num_rows($debit_q) > 0) {
                                    while ($t = mysqli_fetch_assoc($debit_q)) {
                                ?>
                                <tr>
                                    <td><span style="font-family: monospace;">TXN<?= str_pad($t['transaction_id'], 4, '0', STR_PAD_LEFT) ?></span></td>
                                    <td><span class="text-muted-foreground"><?= date('d M Y', strtotime($t['created_at'])) ?></span></td>
                                    <td class="font-medium text-foreground"><?= htmlspecialchars($t['description']) ?></td>
                                    <td class="text-right font-medium text-destructive">- ₹<?= number_format($t['amount'], 2) ?></td>
                                </tr>
                                <?php } } else { ?>
                                <tr><td colspan="4" class="text-center p-8 text-muted-foreground">No debit transactions found.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- MODAL: EDIT WALLET DETAILS -->
<div class="modal fade" id="editWalletModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title font-bold"><i class="bi bi-wallet2 text-primary me-2"></i>Edit Wallet Details</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="filter: var(--theme-icon-filter);"></button>
            </div>
            <div class="modal-body">
                <form id="editWalletForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block">Bank Balance (Manual) <span class="text-destructive">*</span></label>
                            <input type="number" name="bank_balance" class="shadcn-input w-full" step="0.01" value="<?= $wallet['bank_balance'] ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="text-sm font-medium mb-2 block">Card Holder Name</label>
                            <input type="text" name="card_holder" class="shadcn-input w-full" value="<?= htmlspecialchars($wallet['card_holder']) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="text-sm font-medium mb-2 block">Card Number</label>
                            <input type="text" name="card_number" class="shadcn-input w-full" value="<?= htmlspecialchars($wallet['card_number']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="text-sm font-medium mb-2 block">Expiry</label>
                            <input type="text" name="card_expiry" class="shadcn-input w-full" placeholder="MM/YY" value="<?= htmlspecialchars($wallet['card_expiry']) ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 border-0 mt-4">
                        <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="shadcn-btn shadcn-btn-primary"><i class="bi bi-check-lg me-1"></i> Update Wallet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
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

    // Handle Wallet Update
    const walletForm = document.getElementById('editWalletForm');
    if (walletForm) {
        walletForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            const formData = new FormData(this);
            fetch('ajax/ajax_update_wallet.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    location.reload();
                } else {
                    alert('Update failed: ' + res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update Wallet';
                }
            })
            .catch(err => {
                alert('Connection error.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Update Wallet';
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
