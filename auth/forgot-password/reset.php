<?php
/**
 * Reset Password
 * PATH: /auth/forgot-password/reset.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';

// Security: Logged-in users shouldn't be here
guest_guard();

// Ensure user actually requested an OTP AND verified it
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header('Location: ' . SITE_URL . '/auth/forgot-password/index.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($password))                                 $errors[] = 'Password is required.';
    elseif (strlen($password) < 8)                        $errors[] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[0-9]/', $password))            $errors[] = 'Password must contain at least 1 number.';
    if ($password !== $confirm)                           $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $email = $_SESSION['reset_email'];
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        
        // Determine if user is admin or customer
        $table = 'customers';
        $check_stmt = mysqli_prepare($conn, "SELECT admin_id FROM admins WHERE email = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $table = 'admins';
        }
        mysqli_stmt_close($check_stmt);

        // Update password in appropriate table
        $stmt = mysqli_prepare($conn, "UPDATE $table SET password = ? WHERE email = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $hashed, $email);
            if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                // Success! Clean up session
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_verified']);
                
                $success = 'Password successfully reset!';
            } else {
                $errors[] = 'Failed to update password. It may be the same as your old password or the account no longer exists.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Database error during update.";
        }
    }
}

$page_title = 'Create New Password';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Full Viewport Container (Single Centered Card) -->
<div class="w-full max-w-md mx-auto flex items-center justify-center min-h-[600px] my-8 animate-fade-up">
    
    <div class="glass-panel w-full p-8 sm:p-10 rounded-2xl relative overflow-hidden">
        
        <!-- Logo/Icon acting as avatar -->
        <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex justify-center items-center mx-auto mb-6 shadow-sm">
             <i class="bi bi-key text-xl text-indigo-600"></i>
        </div>
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-light text-slate-800 mb-2 tracking-wide uppercase">Secure Account</h1>
            <p class="text-slate-500 text-sm font-light">Step 3: Create New Password</p>
        </div>

        <?php if ($errors): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 flex flex-col shadow-sm">
                <div class="flex items-center mb-1">
                    <i class="bi bi-exclamation-circle mr-2"></i>
                    <span class="text-sm font-semibold">Please fix the following errors:</span>
                </div>
                <ul class="list-disc pl-8 text-xs space-y-1 mt-1">
                    <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="text-center py-6">
                <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-100 shadow-sm">
                    <i class="bi bi-check-lg text-4xl text-emerald-600"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-800 mb-2">Success!</h3>
                <p class="text-slate-500 text-sm mb-8 font-light leading-relaxed"><?php echo htmlspecialchars($success); ?><br>You can now log in with your new password.</p>
                <a href="<?php echo SITE_URL; ?>/auth/login/index.php" class="inline-flex w-full items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm">
                    Return to Log In <i class="bi bi-arrow-right ml-2 text-xs"></i>
                </a>
            </div>
        <?php else: ?>
            <div class="mb-6">
                <div class="flex items-center text-xs text-slate-600 font-light bg-slate-50 p-3 rounded-xl border border-slate-200 shadow-sm">
                    <i class="bi bi-person-check text-indigo-600 mr-3 text-lg"></i>
                    <div>
                        Identity verified for<br>
                        <strong class="text-slate-800 font-medium"><?php echo htmlspecialchars($_SESSION['reset_email']); ?></strong>
                    </div>
                </div>
            </div>

            <form method="POST" novalidate id="resetForm" class="space-y-4">
                <div>
                    <label for="password" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="bi bi-lock text-slate-400"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                            class="glass-input password-strength-input w-full pl-11 pr-10 py-3 rounded-xl text-sm" 
                            placeholder="••••••••" data-meter="pass-meter-reset" data-text="pass-text-reset">
                        <button type="button" class="password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="bi bi-eye text-base"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-1">
                    <label for="confirm_password" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Confirm New Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="bi bi-lock-fill text-slate-400"></i>
                        </div>
                        <input type="password" name="confirm_password" id="confirm_password" required
                            class="glass-input w-full pl-11 pr-10 py-3 rounded-xl text-sm" 
                            placeholder="••••••••">
                        <button type="button" class="password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                            <i class="bi bi-eye text-base"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-1 pb-2">
                    <div class="w-1/2">
                        <div class="h-1 w-full bg-slate-200 rounded-full overflow-hidden">
                            <div id="pass-meter-reset" class="h-full w-0 bg-red-500 transition-all duration-300"></div>
                        </div>
                        <div id="pass-text-reset" class="text-[10px] text-slate-400 mt-1 font-medium hidden">Strength: <span class="text-red-400">Weak</span></div>
                    </div>
                    <!-- Suggest Password Button -->
                    <button type="button" class="suggest-password-btn text-[10px] text-indigo-600 hover:text-indigo-800 transition-colors font-medium flex items-center" data-target="password,confirm_password">
                        <i class="bi bi-magic mr-1"></i> Suggest Strong Password
                    </button>
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm mt-4">
                    Update Password <i class="bi bi-arrow-right ml-2 text-xs"></i>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>


<script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
