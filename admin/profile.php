<?php
/**
 * Admin Profile view and edit
 */
require_once __DIR__ . '/../includes/session_auth.php';
require_once __DIR__ . '/../config/db.php';
auth_guard('admin');

$page_title = 'My Profile';
$admin_id = $_SESSION['user_id'] ?? 0;
$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = htmlspecialchars($_SESSION['email'] ?? 'admin@bikebarber.com');
$admin_image = htmlspecialchars($_SESSION['profile_image'] ?? '');

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = trim($_POST['email']);
    $new_username = trim($_POST['username']);
    
    if ($new_email && $new_username && $admin_id) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$new_username, $new_email, $admin_id])) {
                $_SESSION['username'] = $new_username;
                $_SESSION['email'] = $new_email;
                $admin_username = htmlspecialchars($new_username);
                $admin_email = htmlspecialchars($new_email);
                $success_msg = "Profile updated successfully!";
            } else {
                $error_msg = "Failed to update profile.";
            }
        } catch(PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    } else {
        $error_msg = "Both fields are required.";
    }
}

require_once __DIR__ . '/../includes/admin_header.php';
require_once __DIR__ . '/../includes/admin_sidebar.php';
require_once __DIR__ . '/../includes/admin_topbar.php';
?>

        <!-- PAGE CONTENT -->
        <main class="admin-content">
            <div class="flex items-center justify-between flex-wrap gap-4 mb-6 animate-in slide-up">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight" style="margin-bottom:0.25rem;">My Profile</h2>
                    <p class="text-muted-foreground text-sm m-0">View and manage your personal admin account.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-in slide-up delay-100">
                <!-- Profile View Card -->
                <div class="shadcn-card col-span-1 border border-border bg-card">
                    <div class="shadcn-card-header text-center flex flex-col items-center pt-8">
                        <?php if ($admin_image): ?>
                            <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $admin_image; ?>" class="rounded-full mb-4" style="width:120px; height:120px; object-fit:cover; border: 4px solid var(--background);" alt="Admin">
                        <?php else: ?>
                            <div class="rounded-full mb-4" style="width:120px; height:120px; background:var(--primary); color:var(--primary-foreground); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:3rem; border: 4px solid var(--background); box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <h3 class="shadcn-card-title text-xl mt-2"><?php echo $admin_username; ?></h3>
                        <p class="shadcn-card-description mt-1"><?php echo $admin_email; ?></p>
                        
                        <div class="mt-4 px-3 py-1 text-xs font-semibold rounded-full bg-primary/10 text-primary border border-primary/20">
                            Administrator
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form -->
                <div class="shadcn-card col-span-1 md:col-span-2 border border-border bg-card">
                    <div class="shadcn-card-header border-b border-border pb-4">
                        <h3 class="shadcn-card-title">Edit Details</h3>
                        <p class="shadcn-card-description">Update your login credentials and personal details.</p>
                    </div>
                    <div class="shadcn-card-content pt-6">
                        <?php if ($success_msg): ?>
                            <div class="mb-6 p-4 rounded-md bg-green-500/10 border border-green-500/20 text-green-600 flex items-center gap-2 text-sm font-medium">
                                <i class="bi bi-check-circle-fill"></i> <?php echo $success_msg; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_msg): ?>
                            <div class="mb-6 p-4 rounded-md bg-destructive/10 border border-destructive/20 text-destructive flex items-center gap-2 text-sm font-medium">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error_msg; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="grid gap-5">
                                <div class="grid gap-2">
                                    <label class="text-sm font-medium leading-none" for="username">Display Name</label>
                                    <input type="text" id="username" name="username" class="shadcn-input w-full" value="<?php echo $admin_username; ?>" required>
                                </div>
                                <div class="grid gap-2">
                                    <label class="text-sm font-medium leading-none" for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="shadcn-input w-full md:w-2/3" value="<?php echo $admin_email; ?>" required>
                                </div>
                                <div class="grid gap-2 opacity-60">
                                    <label class="text-sm font-medium leading-none flex items-center justify-between" for="password">
                                        <span>Change Password</span>
                                        <span class="text-xs bg-muted text-muted-foreground px-2 py-0.5 rounded-full">Disabled in UI Demo</span>
                                    </label>
                                    <input type="password" id="password" name="password" class="shadcn-input w-full md:w-2/3" placeholder="••••••••" disabled>
                                </div>
                            </div>
                            
                            <div class="mt-8 pt-6 border-t border-border flex justify-end gap-3">
                                <button type="button" class="shadcn-btn shadcn-btn-outline" onclick="window.location.href='dashboard.php'">Cancel</button>
                                <button type="submit" class="shadcn-btn shadcn-btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </main>
    </div> <!-- Close Wrapper inside admin_sidebar -->

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
