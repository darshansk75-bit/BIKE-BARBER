<?php
// Ensure we have current admin data
$uid = $_SESSION['user_id'] ?? 0;
$query = mysqli_query($conn, "SELECT * FROM admins WHERE admin_id = $uid");
$admin_data = mysqli_fetch_assoc($query);

// Update local session caches if needed (for other parts of UI)
if ($admin_data) {
    $_SESSION['username'] = $admin_data['username'];
    $_SESSION['email'] = $admin_data['email'];
    $_SESSION['admin_name'] = $admin_data['name'] ?? '';
    $_SESSION['admin_phone'] = $admin_data['phone'] ?? '';
    $_SESSION['profile_image'] = $admin_data['profile_image'] ?? '';
}

$admin_username = htmlspecialchars($_SESSION['username'] ?? 'Admin');
$admin_email = htmlspecialchars($_SESSION['email'] ?? 'admin@bikebarber.com');
$admin_image = htmlspecialchars($_SESSION['profile_image'] ?? '');
$page_title = $page_title ?? 'Dashboard';
?>
        <!-- HEADER -->
        <header class="admin-header">
            <div class="header-left">
                <h1 class="header-page-title font-semibold"><?php echo $page_title; ?></h1>
            </div>
            
            <div class="header-right">
                <div class="flex items-center gap-2">
                    <div class="period-selector flex items-center" id="themeToggleGroup">
                        <button type="button" class="period-btn" data-theme="light" title="Light Mode">
                            <i class="bi bi-sun"></i>
                        </button>
                        <button type="button" class="period-btn" data-theme="system" title="System Mode">
                            <i class="bi bi-display"></i>
                        </button>
                        <button type="button" class="period-btn active" data-theme="dark" title="Dark Mode">
                            <i class="bi bi-moon"></i>
                        </button>
                    </div>
                    <div class="dropdown">
                        <button class="shadcn-btn shadcn-btn-ghost shadcn-btn-icon text-muted-foreground" style="position:relative;" title="Notifications" id="notificationBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span style="position:absolute; top:8px; right:8px; width:8px; height:8px; background:var(--destructive); border-radius:50%;"></span>
                        </button>
                        <!-- Notification Dropdown -->
                        <ul class="dropdown-menu dropdown-menu-end admin-modal shadow-lg" aria-labelledby="notificationBtn" style="margin-top: 0.5rem; background: var(--card); border: 1px solid var(--border); backdrop-filter: var(--glass-blur); border-radius: var(--radius); z-index: 1050; width: 240px; padding: 0;">
                            <div class="p-3 border-b border-border font-semibold" style="color: var(--foreground);">Notifications</div>
                            <div class="p-4 text-sm text-muted-foreground text-center">No new notifications</div>
                        </ul>
                    </div>
                    
                    <div style="width: 1px; height: 1.5rem; background: var(--border); margin: 0 0.5rem;"></div>
                    
                    <div class="dropdown">
                        <button class="shadcn-btn shadcn-btn-ghost flex items-center gap-3" style="padding: 0.25rem 0.5rem; height: auto;" id="profileBtn" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($admin_image): ?>
                                <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $admin_image; ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover;" alt="Admin">
                            <?php else: ?>
                                <div style="width:32px; height:32px; border-radius:50%; background:var(--primary); color:var(--primary-foreground); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:0.875rem;">
                                    <?php echo strtoupper(substr($admin_username, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-col items-start" style="display: flex;">
                                <span class="text-sm font-medium leading-none text-foreground"><?php echo $admin_username; ?></span>
                                <span class="text-xs text-muted-foreground" style="margin-top: 0.25rem;">Admin</span>
                            </div>
                        </button>
                        <!-- Profile Dropdown -->
                        <ul class="dropdown-menu dropdown-menu-end admin-modal shadow-lg" aria-labelledby="profileBtn" style="margin-top: 0.5rem; background: var(--card); border: 1px solid var(--border); backdrop-filter: var(--glass-blur); border-radius: var(--radius); z-index: 1050; width: 240px; padding: 0;">
                            <div class="p-3 border-b border-border">
                                <div class="font-medium" style="color: var(--foreground);"><?php echo $admin_username; ?></div>
                                <div class="text-xs text-muted-foreground"><?php echo $admin_email; ?></div>
                            </div>
                            <div class="p-1">
                                <li><a class="dropdown-item text-sm py-2 flex items-center gap-2" href="#" data-bs-toggle="modal" data-bs-target="#adminProfileModal" style="color: var(--foreground); border-radius: var(--radius-sm);"><i class="bi bi-person-gear"></i> View / Edit Profile</a></li>
                            </div>
                            <div class="p-1 border-t border-border">
                                <li><a class="dropdown-item text-sm py-2 flex items-center gap-2 text-destructive hover:bg-destructive/10" href="<?php echo SITE_URL; ?>/auth/logout.php" style="border-radius: var(--radius-sm);"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </div>
                        </ul>
                    </div>
                </div>
            </div>

</header>

<!-- Admin Profile Modal -->
<div class="modal fade" id="adminProfileModal" tabindex="-1" aria-labelledby="adminProfileLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content admin-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="adminProfileLabel"><i class="bi bi-person-gear me-2"></i>Administration Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="adminProfileForm" enctype="multipart/form-data">
                    <div class="row g-4">
                        <!-- Profile Sidebar -->
                        <div class="col-lg-4 border-e border-border flex flex-col items-center justify-start pt-8 pb-4" style="border-right: 1px solid var(--border);">
                            <div class="mx-auto flex flex-col items-center" style="width: 140px; min-width: 140px;">
                                <?php if ($admin_image): ?>
                                    <label for="profileImgInput" class="cursor-pointer group block">
                                        <div id="profileImagePreview" style="width:140px; height:140px; border-radius:50%; overflow:hidden; border:2px solid var(--primary); background: var(--muted); display:flex; align-items:center; justify-content:center; position:relative; box-shadow: var(--shadow-sm);">
                                            <img src="<?php echo SITE_URL; ?>/assets/uploads/profiles/<?php echo $admin_image; ?>" style="width:100%; height:100%; object-fit:cover;">
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="bi bi-camera text-2xl text-white"></i>
                                            </div>
                                        </div>
                                    </label>
                                    <button type="button" id="removeProfilePic" class="shadcn-btn shadcn-btn-ghost text-destructive text-sm mt-5 w-full flex items-center justify-center gap-2">
                                        <i class="bi bi-trash"></i> Remove Picture
                                    </button>
                                    <input type="hidden" name="remove_image" id="removeImageInput" value="0">
                                    <input type="file" id="profileImgInput" name="profile_image" accept=".jpg,.jpeg,.png,.webp" hidden>
                                <?php else: ?>
                                    <div class="w-full flex flex-col items-center" style="width: 200px; margin-left: -30px; margin-right: -30px;">
                                        <label for="profileImgInput" class="block w-full">
                                            <div id="profileImagePreview" class="border-2 border-dashed border-border rounded-xl p-8 hover:border-primary/50 transition-colors cursor-pointer bg-muted/30 group w-full flex flex-col items-center justify-center">
                                                <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors mb-3">
                                                    <i class="bi bi-cloud-arrow-up text-2xl text-primary"></i>
                                                </div>
                                                <div class="text-sm font-medium text-center" style="color: var(--foreground) !important;">Upload Photo</div>
                                                <div class="text-[11px] text-muted-foreground text-center" style="color: var(--muted-foreground) !important;">Drag & Drop</div>
                                            </div>
                                            <input type="file" id="profileImgInput" name="profile_image" accept=".jpg,.jpeg,.png,.webp" hidden>
                                        </label>
                                        <button type="button" class="shadcn-btn shadcn-btn-ghost text-destructive text-sm mt-4 w-full flex items-center justify-center gap-2" onclick="alert('Profile picture does not exist')">
                                            <i class="bi bi-trash"></i> Remove Picture
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-muted-foreground mt-auto pt-6 text-center px-4" style="color: var(--muted-foreground) !important;">Manage your account details and security settings.</p>
                        </div>

                        <!-- Form Fields -->
                        <div class="col-lg-8">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="text-xs font-semibold mb-1 uppercase tracking-wider" style="color: var(--primary) !important; opacity: 0.8;">Personal Details</label>
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-sm font-medium mb-1.5 block" for="admin_name" style="color: var(--foreground) !important;">Full Name</label>
                                    <input type="text" id="admin_name" name="name" class="shadcn-input" value="<?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?>" placeholder="Enter your full name" style="background: var(--muted) !important; color: var(--foreground) !important;">
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-sm font-medium mb-1.5 block" for="admin_phone" style="color: var(--foreground) !important;">Phone Number</label>
                                    <input type="text" id="admin_phone" name="phone" class="shadcn-input" value="<?php echo htmlspecialchars($_SESSION['admin_phone'] ?? ''); ?>" placeholder="+91 XXXXX XXXXX" style="background: var(--muted) !important; color: var(--foreground) !important;">
                                </div>
                                <div class="col-12">
                                    <label class="text-sm font-medium mb-1.5 block" for="admin_email" style="color: var(--foreground) !important;">Email Address</label>
                                    <input type="email" id="admin_email" name="email" class="shadcn-input" value="<?php echo $admin_email; ?>" placeholder="admin@example.com" style="background: var(--muted) !important; color: var(--foreground) !important;">
                                </div>

                                <div class="col-12 mt-3">
                                    <label class="text-xs font-semibold mb-1 uppercase tracking-wider" style="color: var(--primary) !important; opacity: 0.8;">Security & Login</label>
                                </div>
                                <div class="col-sm-12">
                                    <label class="text-sm font-medium mb-1.5 block" for="admin_username" style="color: var(--foreground) !important;">Username</label>
                                    <input type="text" id="admin_username" name="username" class="shadcn-input" value="<?php echo $admin_username; ?>" required style="background: var(--muted) !important; color: var(--foreground) !important;">
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-sm font-medium mb-1.5 block" for="admin_password" style="color: var(--foreground) !important;">New Password</label>
                                    <input type="password" id="admin_password" name="password" class="shadcn-input" placeholder="••••••••" style="background: var(--muted) !important; color: var(--foreground) !important;">
                                </div>
                                <div class="col-sm-6">
                                    <label class="text-sm font-medium mb-1.5 block" for="admin_confirm_password" style="color: var(--foreground) !important;">Confirm Password</label>
                                    <input type="password" id="admin_confirm_password" name="confirm_password" class="shadcn-input" placeholder="••••••••" style="background: var(--muted) !important; color: var(--foreground) !important;">
                                </div>
                                <div class="col-12">
                                    <p class="text-[10px]" style="color: var(--muted-foreground) !important;">Leave password fields blank if you don't wish to change it.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="shadcn-btn shadcn-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="adminProfileForm" class="shadcn-btn shadcn-btn-primary"><i class="bi bi-save me-2"></i> Update Profile</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // ── FIX STACKING CONTEXT (Move modal to body) ──
    const modalEl = document.getElementById('adminProfileModal');
    if (modalEl) {
        document.body.appendChild(modalEl);
    }

    const removeProfilePic = document.getElementById('removeProfilePic');
    const removeImageInput = document.getElementById('removeImageInput');

    if (removeProfilePic) {
        removeProfilePic.addEventListener('click', function() {
            if (confirm('Are you sure you want to remove your profile picture?')) {
                removeImageInput.value = "1";
                if (profileImagePreview) {
                    profileImagePreview.innerHTML = '<i class="bi bi-person text-4xl text-muted-foreground" style="font-size: 3rem;"></i>';
                }
                this.classList.add('hidden');
            }
        });
    }

    // ── THEME TOGGLE LOGIC (Existing) ──
    const themeBtns = document.querySelectorAll('#themeToggleGroup .period-btn');
    const htmlEl = document.documentElement;
    const currentTheme = localStorage.getItem('bikebarber_theme') || 'dark';
    themeBtns.forEach(b => b.classList.toggle('active', b.getAttribute('data-theme') === currentTheme));

    const applyTheme = (theme) => {
        if (theme === 'system') {
            const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            htmlEl.setAttribute('data-theme', isDark ? 'dark' : 'light');
        } else htmlEl.setAttribute('data-theme', theme);
    };

    themeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            themeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const selectedTheme = this.getAttribute('data-theme');
            localStorage.setItem('bikebarber_theme', selectedTheme);
            applyTheme(selectedTheme);
        });
    });

    // ── PROFILE UPDATE LOGIC ──
    const profileImgInput = document.getElementById('profileImgInput');
    const profileImagePreview = document.getElementById('profileImagePreview');
    const profileForm = document.getElementById('adminProfileForm');

    // Image Preview
    if (profileImgInput) {
        profileImgInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (!confirm('Would you like to upload this profile picture?')) {
                    this.value = '';
                    return;
                }
                // Validation
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    alert('Invalid format. Only JPEG, JPG, and PNG are allowed.');
                    this.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File too large. Max size 5MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    profileImagePreview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Submit handler
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const pass = document.getElementById('admin_password').value;
            const confirm = document.getElementById('admin_confirm_password').value;
            if (pass !== confirm) {
                alert('Passwords do not match!');
                return;
            }

            const formData = new FormData(this);
            fetch('<?php echo SITE_URL; ?>/admin/ajax/ajax_update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Profile updated successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Profile Update Error:', err);
                alert('Something went wrong. Please try again.');
            });
        });
    }

    // Listen for OS theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if(localStorage.getItem('bikebarber_theme') === 'system') {
            htmlEl.setAttribute('data-theme', e.matches ? 'dark' : 'light');
        }
    });
});
</script>
