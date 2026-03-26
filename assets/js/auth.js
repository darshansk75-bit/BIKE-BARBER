// Password Strength and Generator Utilities

document.addEventListener('DOMContentLoaded', () => {

    // Toggle Password Visibility
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    });

    // Password Generation
    const suggestBtns = document.querySelectorAll('.suggest-password-btn');
    suggestBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targets = this.getAttribute('data-target').split(',');
            
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
            let password = "";
            // Ensure at least one of each required type for "Strong"
            password += "ABCDEFGHIJKLMNOPQRSTUVWXYZ".charAt(Math.floor(Math.random() * 26));
            password += "abcdefghijklmnopqrstuvwxyz".charAt(Math.floor(Math.random() * 26));
            password += "0123456789".charAt(Math.floor(Math.random() * 10));
            password += "!@#$%^&*()".charAt(Math.floor(Math.random() * 10));
            
            const remainingChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
            for (let i = 0; i < 10; i++) {
                password += remainingChars.charAt(Math.floor(Math.random() * remainingChars.length));
            }
            
            // Shuffle password
            password = password.split('').sort(() => 0.5 - Math.random()).join('');

            targets.forEach(id => {
                const targetInput = document.getElementById(id.trim());
                if (targetInput) {
                    targetInput.value = password;
                    targetInput.type = 'text'; // Show the generated password

                    // Trigger input event for strength meter
                    const event = new Event('input', { bubbles: true });
                    targetInput.dispatchEvent(event);

                    // Update toggle icon
                    const container = targetInput.parentElement;
                    if (container) {
                        const icon = container.querySelector('.password-toggle i');
                        if (icon) {
                            icon.classList.remove('bi-eye');
                            icon.classList.add('bi-eye-slash');
                        }
                    }
                }
            });
        });
    });

    // Password Strength Meter
    const passwordInputs = document.querySelectorAll('.password-strength-input');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function () {
            const val = this.value;
            const meterId = this.getAttribute('data-meter');
            const textId = this.getAttribute('data-text');

            const meter = document.getElementById(meterId);
            const text = document.getElementById(textId);

            if (!meter || !text) return;

            if (val.length === 0) {
                meter.style.width = '0%';
                text.classList.add('hidden');
                return;
            }

            text.classList.remove('hidden');
            let strength = 0;
            
            // Length check
            if (val.length >= 8) strength += 1;
            if (val.length >= 12) strength += 1;
            
            // Complexity checks
            if (val.match(/[a-z]/) && val.match(/[A-Z]/)) strength += 1;
            if (val.match(/[0-9]/)) strength += 1;
            if (val.match(/[^a-zA-Z0-9]/)) strength += 1;

            // Map strength to labels and colors
            let label = '';
            let colorClass = '';
            let width = '0%';

            if (strength <= 2) {
                label = 'Fair';
                colorClass = 'bg-orange-400';
                width = '33%';
            } else if (strength <= 4) {
                label = 'Good';
                colorClass = 'bg-yellow-400';
                width = '66%';
            } else {
                label = 'Strong';
                colorClass = 'bg-emerald-500';
                width = '100%';
            }

            meter.className = `h-full transition-all duration-300 ${colorClass}`;
            meter.style.width = width;
            text.innerHTML = `Strength: <span class="${colorClass.replace('bg-', 'text-')}">${label}</span>`;
            text.className = 'text-[10px] text-slate-400 mt-1 font-medium';
        });
    });
    // Phone Number Restriction
    const phoneInputs = document.querySelectorAll('input[name="phone"], .phone-restrict');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }
        });
    });

    // Unified Mode Toggle (Login/Register)
    const modeToggle = document.getElementById('modeToggle');
    const loginBlock = document.getElementById('loginBlock');
    const registerBlock = document.getElementById('registerBlock');
    const authContent = document.getElementById('authContent');

    modeToggle?.addEventListener('click', () => {
        const isLogin = !loginBlock.classList.contains('hidden');
        
        if (isLogin) {
            loginBlock.classList.add('hidden');
            registerBlock.classList.remove('hidden');
            modeToggle.textContent = 'SWITCH TO LOGIN';
            document.title = 'Create Account | BIKE BARBER';
        } else {
            registerBlock.classList.add('hidden');
            loginBlock.classList.remove('hidden');
            modeToggle.textContent = 'SWITCH TO REGISTER';
            document.title = 'Sign In | BIKE BARBER';
        }
        
        // Push state to URL without refresh
        const newMode = isLogin ? 'register' : 'login';
        window.history.pushState({mode: newMode}, '', '?mode=' + newMode);
    });

    // Handle initial state from URL if needed
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.mode) {
             window.location.reload();
        }
    });

    // Forgot Password Loading State
    const forgotForm = document.getElementById('forgotForm');
    forgotForm?.addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        const loadingState = document.getElementById('loadingState');
        if (submitBtn && loadingState) {
            submitBtn.classList.add('hidden');
            loadingState.classList.remove('hidden');
            loadingState.classList.add('flex');
        }
    });

});
