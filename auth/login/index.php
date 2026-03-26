<?php
//Hi Hello
/**
 * Unified Auth Controller (Login & Register)
 * PATH: /auth/login/index.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/session_auth.php';

// Security: Logged-in users shouldn't be here
guest_guard();

$error = $_GET['error'] ?? '';
$success = '';
$mode = $_GET['mode'] ?? 'login'; // 'login' or 'register'

// Shared values for forms
$email_val = '';
$name_val = '';
$phone_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $email_val = htmlspecialchars($email);
        $mode = 'login';

        if (empty($email) || empty($password)) {
            $error = 'Please fill in all fields.';
        }
        else {
            $login_success = false;
            $role = '';
            $user = null;

            // Check admins
            $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE email = ? LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($result && mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    // Check hashed password
                    if (password_verify($password, $user['password'])) {
                        $login_success = true;
                        $role = 'admin';
                    }
                    // Fallback: Check plain-text and auto-migrate if match
                    elseif ($password === $user['password']) {
                        $login_success = true;
                        $role = 'admin';
                        // Auto-migrate to hash
                        $new_hash = password_hash($password, PASSWORD_BCRYPT);
                        $update_stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE admin_id = ?");
                        if ($update_stmt) {
                            mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $user['admin_id']);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);
                        }
                    }
                }
                mysqli_stmt_close($stmt);
            }

            // Check customers
            if (!$login_success) {
                $stmt = mysqli_prepare($conn, "SELECT * FROM customers WHERE email = ? LIMIT 1");
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($result && mysqli_num_rows($result) > 0) {
                        $user = mysqli_fetch_assoc($result);
                        if (password_verify($password, $user['password']) || $password === $user['password']) {
                            $login_success = true;
                            $role = 'customer';
                        }
                    }
                    mysqli_stmt_close($stmt);
                }
            }

            if ($login_success) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $role === 'admin' ? $user['admin_id'] : $user['customer_id'];
                $_SESSION['username'] = $role === 'admin' ? $user['username'] : $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $role;
                $_SESSION['last_activity'] = time();
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                header('Location: ' . SITE_URL . '/' . ($role === 'admin' ? 'admin' : 'customer') . '/dashboard.php');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }
    elseif ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $mode = 'register';

        $name_val = htmlspecialchars($name);
        $email_val = htmlspecialchars($email);
        $phone_val = htmlspecialchars($phone);

        $errors = [];
        if (empty($name) || strlen($name) < 2)
            $errors[] = 'Full name required.';
        if (empty($email))
            $errors[] = 'Email required.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $errors[] = 'Invalid email.';

        if (empty($phone)) {
            $errors[] = 'Phone number required.';
        }
        elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $errors[] = 'Invalid phone. Please enter exactly 10 digits starting with 6-9.';
        }

        if (empty($password))
            $errors[] = 'Password required.';
        elseif (strlen($password) < 8)
            $errors[] = 'Password min 8 chars.';
        if ($password !== $confirm)
            $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE email = ? OR phone = ?");
            mysqli_stmt_bind_param($stmt, "ss", $email, $phone);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0)
                $errors[] = 'Email or phone already exists.';
            mysqli_stmt_close($stmt);
        }

        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $hashed);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created! Now login.';
                $mode = 'login'; // Switch back to login
            }
            else {
                $error = 'Registration failed.';
            }
            mysqli_stmt_close($stmt);
        }
        else {
            $error = implode(' ', $errors);
        }
    }
}

$page_title = $mode === 'login' ? 'Sign In' : 'Create Account';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Full Viewport Dual Card Container -->
<div class="w-full max-w-5xl mx-auto min-h-[700px] flex animate-fade-up glass-panel-v3 my-8 relative overflow-hidden">
    
    <!-- AUTH FORM CONTAINER (Silding logic handled by CSS classes) -->
    <div id="authContent" class="glass-panel-left flex flex-col transition-all duration-500 ease-in-out <?php echo $mode === 'register' ? 'translate-y-0' : 'translate-y-0'; ?>">
        
        <!-- Toggle Button Attached to the Card -->
        <div class="absolute top-6 right-6 z-20">
            <button id="modeToggle" class="bg-indigo-50 border border-indigo-100 text-indigo-600 text-[10px] font-bold px-4 py-2 rounded-full shadow-sm hover:bg-indigo-600 hover:text-white transition-all">
                <?php echo $mode === 'login' ? 'SWITCH TO REGISTER' : 'SWITCH TO LOGIN'; ?>
            </button>
        </div>

        <!-- LOGIN SECTION -->
        <div id="loginBlock" class="<?php echo $mode === 'login' ? '' : 'hidden'; ?> h-full flex flex-col justify-center animate-slide-in">
            <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex justify-center items-center mx-auto mb-6 shadow-sm">
                <i class="bi bi-lock text-xl text-indigo-600"></i>
            </div>
            <div class="text-center mb-8">
                <h1 class="text-3xl font-light text-slate-800 mb-2 tracking-wide uppercase">Welcome Back</h1>
                <p class="text-slate-500 text-sm font-light">Sign in to your account</p>
            </div>

            <?php if ($mode === 'login' && $error): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 text-sm shadow-sm flex items-center">
                    <i class="bi bi-exclamation-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php
endif; ?>

            <?php if ($success): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-sm shadow-sm flex items-center">
                    <i class="bi bi-check-circle mr-2"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php
endif; ?>

            <form method="POST" novalidate class="space-y-4">
                <input type="hidden" name="action" value="login">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Email address</label>
                    <div class="relative">
                        <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="email" name="email" value="<?php echo $email_val; ?>" required
                            class="glass-input w-full pl-11 pr-4 py-3 rounded-xl text-sm" placeholder="you@example.com">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Password</label>
                    <div class="relative">
                        <i class="bi bi-shield-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="password" name="password" required class="glass-input w-full pl-11 pr-10 py-3 rounded-xl text-sm" placeholder="••••••••">
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs pt-1">
                    <a href="<?php echo SITE_URL; ?>/auth/forgot-password/index.php" class="text-indigo-600 hover:underline">Forgot password?</a>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-indigo-200 text-sm">
                    Log In
                </button>
            </form>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <span class="relative flex justify-center text-[10px] text-slate-400 uppercase bg-white px-2 mx-auto">Social Login</span>
            </div>
            
            <a href="<?php echo SITE_URL; ?>/auth/google/login.php" class="flex justify-center items-center py-3 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 text-sm font-medium text-slate-700 transition-all shadow-sm">
                <img src="https://www.google.com/favicon.ico" class="w-4 h-4 mr-2"> Google
            </a>
        </div>

        <!-- REGISTER SECTION -->
        <div id="registerBlock" class="<?php echo $mode === 'register' ? '' : 'hidden'; ?> h-full flex flex-col justify-center animate-slide-in">
            <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex justify-center items-center mx-auto mb-4 shadow-sm">
                <i class="bi bi-person-plus text-xl text-indigo-600"></i>
            </div>
            <div class="text-center mb-6">
                <h1 class="text-3xl font-light text-slate-800 mb-1 tracking-wide uppercase">Join Us</h1>
                <p class="text-slate-500 text-sm font-light">Create your bike service account</p>
            </div>

            <?php if ($mode === 'register' && $error): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-4 text-[11px] shadow-sm">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php
endif; ?>

            <form method="POST" novalidate class="space-y-3">
                <input type="hidden" name="action" value="register">
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 ml-1">Full Name</label>
                        <input type="text" name="name" value="<?php echo $name_val; ?>" required class="glass-input w-full px-4 py-2 rounded-xl text-sm" placeholder="Full name">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 ml-1">Email</label>
                        <input type="email" name="email" value="<?php echo $email_val; ?>" required class="glass-input w-full px-4 py-2 rounded-xl text-sm" placeholder="Email contact">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 ml-1">Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo $phone_val ? $phone_val : '+91'; ?>" required 
                            class="glass-input phone-mask w-full px-4 py-2 rounded-xl text-sm" placeholder="+91XXXXXXXXXX">
                    </div>
                    <div class="relative">
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 ml-1">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password_reg" required 
                                class="glass-input password-strength-input w-full px-4 py-2 rounded-xl text-sm pr-10" 
                                placeholder="••••••••" data-meter="pass-meter-unified" data-text="pass-text-unified">
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="bi bi-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-slate-500 mb-1 ml-1">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password_reg" required 
                                class="glass-input w-full px-4 py-2 rounded-xl text-sm pr-10" 
                                placeholder="••••••••">
                            <button type="button" class="password-toggle absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                                <i class="bi bi-eye text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Strength Meter & Suggestion -->
                <div class="flex items-center justify-between pt-1 pb-1">
                    <div class="w-1/2">
                        <div class="h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div id="pass-meter-unified" class="h-full w-0 bg-red-500 transition-all duration-300"></div>
                        </div>
                        <div id="pass-text-unified" class="text-[9px] text-slate-400 mt-1 font-medium hidden">Strength: <span class="text-red-400">Weak</span></div>
                    </div>
                    <!-- Suggest Password Button -->
                    <button type="button" class="suggest-password-btn text-[9px] text-indigo-600 hover:text-indigo-800 transition-colors font-medium flex items-center" data-target="password_reg,confirm_password_reg">
                        <i class="bi bi-magic mr-1"></i> Suggest Strong Password
                    </button>
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-xl transition-all shadow-lg shadow-indigo-200 text-sm mt-2">
                    Create Account
                </button>
            </form>

            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <span class="relative flex justify-center text-[10px] text-slate-400 uppercase bg-white px-2 mx-auto">or</span>
            </div>

            <a href="<?php echo SITE_URL; ?>/auth/google/login.php" class="flex justify-center items-center py-2.5 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 text-sm font-medium text-slate-700 transition-all shadow-sm">
                <img src="https://www.google.com/favicon.ico" class="w-4 h-4 mr-2"> Sign up with Google
            </a>
            
            <div class="mt-4 text-[10px] text-center text-slate-500 px-6">
                By signing up, you agree to our <a href="#" class="text-indigo-600 hover:underline">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>.
            </div>
        </div>
    </div>

    <!-- Right Side: Features Panel -->
    <?php require_once __DIR__ . '/../../includes/auth_right_panel.php'; ?>
</div>



<script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
