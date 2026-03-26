<?php
/**
 * Registration Controller
 * PATH: /auth/register/index.php
 */
require_once __DIR__ . '/../../includes/session_auth.php';

// Security: Logged-in users shouldn't be here
guest_guard();

$errors = [];
$success = '';
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $old = [
        'name' => htmlspecialchars($name), 
        'email' => htmlspecialchars($email), 
        'phone' => htmlspecialchars($phone)
    ];

    if (empty($name) || strlen($name) < 2)                $errors[] = 'Full name is required (min 2 chars).';
    if (empty($email))                                    $errors[] = 'Email is required.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'Invalid email format.';
    
    // Phone validation: 10 digits starting with 6-9
    if (empty($phone)) {
        $errors[] = 'Phone number is required.';
    } elseif (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        $errors[] = 'Invalid phone format. Please enter exactly 10 digits starting with 6-9.';
    }

    if (empty($password))                                 $errors[] = 'Password is required.';
    elseif (strlen($password) < 8)                        $errors[] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[0-9]/', $password))            $errors[] = 'Password must contain at least 1 number.';
    if ($password !== $confirm)                           $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Checking if email or phone exists
        $stmt = mysqli_prepare($conn, "SELECT customer_id FROM customers WHERE email = ? OR phone = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $email, $phone);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                // Determine which one exists
                $errors[] = 'Email or phone number already registered.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = 'Database error checking availability.';
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        // Assuming customers table has name, email, phone, password
        $stmt = mysqli_prepare($conn, "INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $phone, $hashed);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Account created! Redirecting to login...';
                $old = ['name' => '', 'email' => '', 'phone' => ''];
                echo '<meta http-equiv="refresh" content="2;url=' . SITE_URL . '/auth/login/index.php">';
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Error preparing statement for insert.";
        }
    }
}

$page_title = 'Create Account';
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Full Viewport Container -->
<div class="w-full max-w-5xl mx-auto min-h-[600px] flex animate-fade-up glass-panel-v3 my-8">
    
    <!-- Left Side: Registration Form -->
    <div class="glass-panel-left">
        <!-- Logo/Icon acting as avatar -->
        <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex justify-center items-center mx-auto mb-6 shadow-sm">
            <i class="bi bi-person-plus text-xl text-indigo-600"></i>
        </div>
        
        <div class="text-center mb-6">
            <h1 class="text-3xl font-light text-slate-800 mb-2 tracking-wide uppercase">Create Account</h1>
            <p class="text-slate-500 text-sm font-light">Join BIKE BARBER to get started</p>
        </div>

        <?php if ($errors): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg mb-6 flex items-center text-sm shadow-sm">
                <i class="bi bi-exclamation-circle mr-2 flex-shrink-0"></i>
                <div class="flex flex-col">
                    <span class="font-semibold">Please fix the following errors:</span>
                    <ul class="list-disc pl-5 text-xs space-y-1 mt-1">
                        <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg flex items-center justify-between text-sm shadow-sm">
                <div class="flex items-center">
                    <i class="bi text-lg bi-check-circle mr-3"></i>
                    <div>
                        <div class="font-medium text-emerald-800">Account Created Successfully!</div>
                        <div class="text-emerald-700/80 mt-0.5 text-xs">Redirecting to login page...</div>
                    </div>
                </div>
            </div>
        <?php else: ?>

        <form method="POST" novalidate id="registerForm" class="space-y-4">
            <!-- Full Name -->
            <div>
                <label for="name" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Full Name</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="bi bi-person text-slate-400"></i>
                    </div>
                    <input type="text" name="name" id="name" value="<?php echo $old['name']; ?>" required
                        class="glass-input w-full pl-11 pr-4 py-2.5 rounded-xl text-sm" placeholder="John Doe">
                </div>
            </div>

            <!-- Phone Number -->
            <div>
                <label for="phone" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Phone Number</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="bi bi-telephone text-slate-400"></i>
                    </div>
                    <input type="tel" name="phone" id="phone" value="<?php echo $old['phone']; ?>" required
                        class="glass-input phone-mask w-full pl-11 pr-4 py-2.5 rounded-xl text-sm" placeholder="+91XXXXXXXXXX">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="bi bi-lock text-slate-400"></i>
                    </div>
                    <input type="password" name="password" id="password" required
                        class="glass-input password-strength-input w-full pl-11 pr-10 py-2.5 rounded-xl text-sm" 
                        placeholder="••••••••" data-meter="pass-meter-reg" data-text="pass-text-reg">
                    <button type="button" class="password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="bi bi-eye text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="confirm_password" class="block text-[11px] font-semibold text-slate-500 mb-1.5 ml-1">Confirm Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="bi bi-lock-fill text-slate-400"></i>
                    </div>
                    <input type="password" name="confirm_password" id="confirm_password" required
                        class="glass-input w-full pl-11 pr-10 py-2.5 rounded-xl text-sm" 
                        placeholder="••••••••">
                    <button type="button" class="password-toggle absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="bi bi-eye text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Strength Meter & Suggestion -->
            <div class="flex items-center justify-between pt-1 pb-2">
                <div class="w-1/2">
                    <div class="h-1 w-full bg-slate-200 rounded-full overflow-hidden">
                        <div id="pass-meter-reg" class="h-full w-0 bg-red-500 transition-all duration-300"></div>
                    </div>
                    <div id="pass-text-reg" class="text-[10px] text-slate-400 mt-1 font-medium hidden">Strength: <span class="text-red-400">Weak</span></div>
                </div>
                <!-- Suggest Password Button -->
                <button type="button" class="suggest-password-btn text-[10px] text-indigo-600 hover:text-indigo-800 transition-colors font-medium flex items-center" data-target="password,confirm_password">
                    <i class="bi bi-magic mr-1"></i> Suggest Strong Password
                </button>
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 px-4 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-200 text-sm flex items-center justify-center">
                Create Account <i class="bi bi-arrow-right ml-2 text-xs"></i>
            </button>
            
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="px-2 bg-transparent text-slate-500">or</span>
                </div>
            </div>

            <a href="<?php echo SITE_URL; ?>/auth/google/login.php" class="w-full flex justify-center items-center px-4 py-3 border border-slate-200 rounded-xl shadow-sm bg-white hover:bg-slate-50 text-sm font-medium text-slate-700 transition-all duration-200">
                <img src="https://www.google.com/favicon.ico" alt="Google" class="w-4 h-4 mr-2">
                Sign up with Google
            </a>

            <div class="text-[10px] text-center text-slate-500 mt-4 px-4 leading-relaxed">
                By signing up, you agree to our <a href="#" class="text-indigo-600 hover:underline">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:underline">Privacy Policy</a>.
            </div>
        <div class="mt-4 text-center text-xs text-slate-500 pt-4 border-t border-slate-200">
            Already have an account? 
            <a href="<?php echo SITE_URL; ?>/auth/login/index.php" class="text-indigo-600 font-semibold hover:text-indigo-800 transition-colors">Log in</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Simple inline CSS for animation -->
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>

<script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
