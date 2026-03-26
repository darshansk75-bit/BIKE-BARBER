<?php
/**
 * Verify OTP
 * PATH: /auth/forgot-password/verify.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';

// Security: Logged-in users shouldn't be here
guest_guard();

// Ensure user actually requested an OTP first
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email'])) {
    header('Location: ' . SITE_URL . '/auth/forgot-password/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_otp = trim($_POST['otp'] ?? '');

    // Check expiry
    if (time() > $_SESSION['reset_otp_expiry']) {
        $error = 'Your OTP has expired. Please request a new one.';
        // Clean up
        unset($_SESSION['reset_otp']);
        unset($_SESSION['reset_otp_expiry']);
        unset($_SESSION['reset_email']);
    } 
    // Verify code
    elseif ($submitted_otp === $_SESSION['reset_otp']) {
        // Mark as verified
        $_SESSION['reset_verified'] = true;
        // Don't destroy email/verified yet, we need it for reset.php
        unset($_SESSION['reset_otp']); // Prevent reuse
        unset($_SESSION['reset_otp_expiry']);
        
        header('Location: ' . SITE_URL . '/auth/forgot-password/reset.php');
        exit;
    } else {
        $error = 'Incorrect OTP code. Please try again.';
    }
}

$page_title = 'Verify OTP';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Full Viewport Container (Single Centered Card) -->
<div class="w-full max-w-md mx-auto flex items-center justify-center min-h-[600px] my-8 animate-fade-up">
    
    <div class="glass-panel w-full p-8 sm:p-10 rounded-2xl relative overflow-hidden">
        
        <!-- Logo/Icon acting as avatar -->
        <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex justify-center items-center mx-auto mb-6 shadow-sm">
             <i class="bi bi-shield-check text-xl text-indigo-600"></i>
        </div>
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-light text-slate-800 mb-2 tracking-wide uppercase">Verify Identity</h1>
            <p class="text-slate-500 text-sm font-light">Step 2: Enter Secure Code</p>
        </div>

        <div class="mb-6 text-center">
            <p class="text-[11px] text-slate-500 leading-relaxed font-light mb-4">
                We've sent a 6-digit secure code to your registered identifiers:
            </p>
            
            <div class="space-y-2">
                <!-- Masked Email -->
                <?php 
                $email = $_SESSION['reset_email'];
                $parts = explode('@', $email);
                $masked_email = substr($parts[0], 0, 1) . str_repeat('*', max(0, strlen($parts[0]) - 2)) . substr($parts[0], -1) . '@' . $parts[1];
                ?>
                <div class="flex items-center justify-center space-x-2 text-slate-700 font-medium">
                    <i class="bi bi-envelope text-indigo-400"></i>
                    <span><?php echo htmlspecialchars($masked_email); ?></span>
                </div>

                <!-- Masked Phone -->
                <?php if (isset($_SESSION['reset_phone']) && !empty($_SESSION['reset_phone'])): ?>
                    <?php 
                    $phone = $_SESSION['reset_phone'];
                    $masked_phone = substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 5) . substr($phone, -2);
                    ?>
                    <div class="flex items-center justify-center space-x-2 text-slate-700 font-medium">
                        <i class="bi bi-phone text-indigo-400"></i>
                        <span><?php echo htmlspecialchars($masked_phone); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-center mb-6 text-sm shadow-sm">
                <i class="bi bi-exclamation-circle mr-2 flex-shrink-0"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" class="space-y-4">
            <div>
                <label for="otp" class="block text-[11px] font-semibold text-slate-500 mb-1.5 text-center">Enter 6-Digit OTP</label>
                <input type="text" name="otp" id="otp" required maxlength="6" autofocus
                    class="glass-input w-full px-4 py-4 rounded-xl text-center text-2xl tracking-[0.5em] font-mono text-indigo-600 font-bold uppercase transition-all focus:tracking-[0.6em]" 
                    placeholder="------" pattern="[0-9]{6}">
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm mt-2">
                Verify Code
            </button>
        </form>

        <div class="mt-8 text-center pt-6 border-t border-slate-200 flex flex-col space-y-3">
            <a href="<?php echo SITE_URL; ?>/auth/forgot-password/index.php" class="text-xs text-indigo-600 hover:text-indigo-800 transition-colors font-medium">
                Didn't receive a code? Request again
            </a>
            <a href="<?php echo SITE_URL; ?>/auth/login/index.php" class="text-[10px] text-slate-400 hover:text-slate-600 transition-colors">
                 Cancel and return to login
            </a>
        </div>
    </div>
</div>


<script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
