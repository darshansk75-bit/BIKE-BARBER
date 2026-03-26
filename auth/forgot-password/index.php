<?php
/**
 * Request OTP via Email
 * PATH: /auth/forgot-password/index.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Security: Logged-in users shouldn't be here
guest_guard();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $user_id = null;
        $phone_number = null;

        // Check if email exists
        $stmt = mysqli_prepare($conn, "SELECT customer_id, phone FROM customers WHERE email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                $user_id = $row['customer_id'];
                $phone_number = $row['phone'];
            }
            mysqli_stmt_close($stmt);
        }

        if (!$user_id) {
            $error = 'No account found with that email address.';
        } else {
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));
            
            // Save to session
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_phone'] = $phone_number; // For masking later
            $_SESSION['reset_otp_expiry'] = time() + (15 * 60);

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;

                $mail->setFrom(SMTP_USER, 'BIKE BARBER Support');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP - BIKE BARBER';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; text-align: center; padding: 20px;'>
                        <h2>Password Reset Request</h2>
                        <p>We received a request to reset your password. Use the code below to complete the process.</p>
                        <div style='background-color: #f1f5f9; padding: 15px; border-radius: 8px; display: inline-block; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #4361EE;'>
                            {$otp}
                        </div>
                        <p style='color: #64748b; font-size: 14px; margin-top: 20px;'>This code will expire in 15 minutes.</p>
                    </div>";
                $mail->AltBody = "Your code is: {$otp}";
                $mail->send();
                
                $success = "OTP has been sent to your registered email.";
                header("refresh:2;url=" . SITE_URL . "/auth/forgot-password/verify.php");

            } catch (Exception $e) {
                $error = "Mailer Error: " . $mail->ErrorInfo;
            }
        }
    }
}

$page_title = 'Forgot Password';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Full Viewport Container (Single Centered Card) -->
<div class="w-full max-w-md mx-auto flex items-center justify-center min-h-[600px] my-8 animate-fade-up">
    
    <div class="glass-panel w-full p-8 sm:p-10 rounded-2xl relative overflow-hidden">
        
        <!-- Logo/Icon acting as avatar -->
        <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex justify-center items-center mx-auto mb-6 shadow-sm">
            <i class="bi bi-shield-lock text-xl text-indigo-600"></i>
        </div>
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-light text-slate-800 mb-2 tracking-wide uppercase">Reset Password</h1>
            <p class="text-slate-500 text-sm font-light">Step 1: Request Password Reset</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg flex items-center mb-6 text-sm shadow-sm">
                <i class="bi bi-exclamation-circle mr-2 flex-shrink-0"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center justify-between text-sm shadow-sm mb-6">
                <div class="flex items-center">
                    <i class="bi text-lg bi-check-circle mr-3"></i>
                    <div>
                        <div class="font-medium text-emerald-800">OTP Sent Successfully!</div>
                        <div class="text-emerald-700/80 mt-0.5 text-xs">Redirecting to verification...</div>
                    </div>
                </div>
            </div>
        <?php else: ?>
        <form method="POST" id="forgotForm" class="space-y-5">
            <div>
                <label for="email" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Registered Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="bi bi-envelope text-slate-400"></i>
                    </div>
                    <input type="email" name="email" id="email" required
                        class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-sm" placeholder="you@example.com">
                </div>
                <p class="text-[10px] text-slate-400 mt-2 ml-1">We'll send a 6-digit One-Time Passcode (OTP) to verify your identity.</p>
            </div>

            <button type="submit" id="submitBtn" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm flex items-center justify-center relative overflow-hidden group">
                <span class="relative z-10 flex items-center justify-center submit-text">
                    Send Reset Code <i class="bi bi-arrow-right ml-2 text-xs"></i>
                </span>
                <span class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out"></span>
            </button>
            
            <!-- Loading State for Button -->
            <div id="loadingState" class="hidden w-full bg-indigo-600/50 text-white/50 font-medium py-3.5 px-4 rounded-xl text-sm flex items-center justify-center cursor-not-allowed border border-white/5">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Sending Email...
            </div>
        </form>
        <?php endif; ?>

        <div class="mt-8 pt-6 border-t border-slate-200 text-center">
            <a href="<?php echo SITE_URL; ?>/auth/login/index.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors inline-flex items-center">
                <i class="bi bi-arrow-left mr-2 text-xs"></i> Back to login
            </a>
        </div>
    </div>
</div>



<script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
