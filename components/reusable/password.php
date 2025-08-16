<?php
/**
 * Reusable Password Input Component with See Password Toggle
 * 
 * Usage:
 * <?php include 'components/reusable/password.php'; ?>
 * 
 * Or with parameters:
 * <?php 
 *   $passwordConfig = [
 *     'id' => 'passwordInput',
 *     'name' => 'password',
 *     'class' => 'form-control-lg',
 *     'placeholder' => 'Enter password...',
 *     'showToggle' => true,
 *     'strengthMeter' => true
 *   ];
 *   include 'components/reusable/password.php'; 
 * ?>
 */

// Default configuration
$defaultConfig = [
    'id' => 'passwordInput',
    'name' => 'password',
    'placeholder' => 'Enter password...',
    'class' => 'form-control shadow-none',
    'showToggle' => true,      // Show see/hide password toggle
    'strengthMeter' => false,  // Show password strength meter
    'minLength' => 6,          // Minimum password length
    'ariaLabel' => 'Password',
    'required' => false,
    'autocomplete' => 'current-password'
];

// Merge with provided configuration
$config = isset($passwordConfig) ? array_merge($defaultConfig, $passwordConfig) : $defaultConfig;

// Generate unique ID if not provided
if (empty($config['id'])) {
    $config['id'] = 'password_' . uniqid();
}

// Build CSS classes
$cssClasses = $config['class'];
if ($config['required']) {
    $cssClasses .= ' required';
}
?>

<div class="password-container position-relative">
    <!-- Password Input -->
    <input 
        type="password" 
        class="<?php echo $cssClasses; ?> pe-5" 
        id="<?php echo $config['id']; ?>"
        name="<?php echo $config['name']; ?>"
        placeholder="<?php echo htmlspecialchars($config['placeholder']); ?>"
        aria-label="<?php echo htmlspecialchars($config['ariaLabel']); ?>"
        minlength="<?php echo $config['minLength']; ?>"
        autocomplete="<?php echo $config['autocomplete']; ?>"
        <?php if ($config['required']): ?>required<?php endif; ?>
    >
    
    <!-- See/Hide Password Toggle -->
    <?php if ($config['showToggle']): ?>
    <button 
        type="button" 
        class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 password-toggle" 
        id="<?php echo $config['id']; ?>_toggle"
        aria-label="Toggle password visibility"
        style="z-index: 10; border: none; background: none; color: #6c757d;"
    >
        <i class="bi bi-eye" id="<?php echo $config['id']; ?>_icon"></i>
    </button>
    <?php endif; ?>
</div>

<!-- Password Strength Meter -->
<?php if ($config['strengthMeter']): ?>
<div class="password-strength mt-2" id="<?php echo $config['id']; ?>_strength" style="display: none;">
    <div class="progress" style="height: 4px;">
        <div class="progress-bar" id="<?php echo $config['id']; ?>_strength_bar" role="progressbar" style="width: 0%"></div>
    </div>
    <small class="text-muted mt-1" id="<?php echo $config['id']; ?>_strength_text">Password strength</small>
</div>
<?php endif; ?>

<style>
.password-container {
    position: relative;
}

.password-container .form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.password-toggle {
    transition: color 0.15s ease-in-out;
}

.password-toggle:hover {
    color: #495057 !important;
}

.password-toggle:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
}

.password-toggle.active {
    color: #0d6efd !important;
}

/* Password strength colors */
.password-strength .progress-bar.weak {
    background-color: #dc3545;
}

.password-strength .progress-bar.fair {
    background-color: #ffc107;
}

.password-strength .progress-bar.good {
    background-color: #17a2b8;
}

.password-strength .progress-bar.strong {
    background-color: #28a745;
}

/* Animation for password toggle */
.password-toggle i {
    transition: transform 0.2s ease-in-out;
}

.password-toggle.active i {
    transform: scale(1.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .password-container .form-control {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>

<script>
(function() {
    'use strict';
    
    const passwordId = '<?php echo $config['id']; ?>';
    const passwordInput = document.getElementById(passwordId);
    const toggleBtn = document.getElementById(passwordId + '_toggle');
    const toggleIcon = document.getElementById(passwordId + '_icon');
    const strengthMeter = document.getElementById(passwordId + '_strength');
    const strengthBar = document.getElementById(passwordId + '_strength_bar');
    const strengthText = document.getElementById(passwordId + '_strength_text');
    
    if (!passwordInput) return;
    
    // Initialize password functionality
    function initPassword() {
        // Toggle password visibility
        if (toggleBtn) {
            toggleBtn.addEventListener('click', togglePasswordVisibility);
        }
        
        // Password strength meter
        if (strengthMeter) {
            passwordInput.addEventListener('input', checkPasswordStrength);
        }
        
        // Focus/blur events
        passwordInput.addEventListener('focus', handleFocus);
        passwordInput.addEventListener('blur', handleBlur);
    }
    
    // Toggle password visibility
    function togglePasswordVisibility() {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        
        // Update icon
        if (toggleIcon) {
            toggleIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }
        
        // Update button state
        toggleBtn.classList.toggle('active', type === 'text');
        
        // Update aria-label
        toggleBtn.setAttribute('aria-label', 
            type === 'password' ? 'Show password' : 'Hide password'
        );
    }
    
    // Check password strength
    function checkPasswordStrength() {
        const password = passwordInput.value;
        
        if (password.length === 0) {
            strengthMeter.style.display = 'none';
            return;
        }
        
        strengthMeter.style.display = 'block';
        
        // Calculate strength
        let strength = 0;
        let feedback = '';
        
        // Length check
        if (password.length >= 8) strength += 25;
        if (password.length >= 12) strength += 25;
        
        // Character variety checks
        if (/[a-z]/.test(password)) strength += 10;
        if (/[A-Z]/.test(password)) strength += 10;
        if (/[0-9]/.test(password)) strength += 10;
        if (/[^A-Za-z0-9]/.test(password)) strength += 10;
        
        // Common patterns penalty
        if (/(.)\1{2,}/.test(password)) strength -= 10; // Repeated characters
        if (/123|abc|qwe/i.test(password)) strength -= 10; // Common sequences
        
        // Ensure strength is between 0 and 100
        strength = Math.max(0, Math.min(100, strength));
        
        // Update strength meter
        strengthBar.style.width = strength + '%';
        
        // Set strength level and color
        let strengthLevel = '';
        let strengthClass = '';
        
        if (strength < 25) {
            strengthLevel = 'Weak';
            strengthClass = 'weak';
            feedback = 'Add more characters and variety';
        } else if (strength < 50) {
            strengthLevel = 'Fair';
            strengthClass = 'fair';
            feedback = 'Add uppercase letters and numbers';
        } else if (strength < 75) {
            strengthLevel = 'Good';
            strengthClass = 'good';
            feedback = 'Add special characters for better security';
        } else {
            strengthLevel = 'Strong';
            strengthClass = 'strong';
            feedback = 'Excellent password strength';
        }
        
        // Update UI
        strengthBar.className = 'progress-bar ' + strengthClass;
        strengthText.textContent = strengthLevel + ' - ' + feedback;
    }
    
    // Handle focus
    function handleFocus() {
        passwordInput.parentElement.classList.add('focused');
    }
    
    // Handle blur
    function handleBlur() {
        passwordInput.parentElement.classList.remove('focused');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPassword);
    } else {
        initPassword();
    }
    
    // Expose functions globally for external use
    window.passwordFunctions = window.passwordFunctions || {};
    window.passwordFunctions[passwordId] = {
        toggle: togglePasswordVisibility,
        getValue: () => passwordInput.value,
        setValue: (value) => {
            passwordInput.value = value;
            if (strengthMeter) {
                checkPasswordStrength();
            }
        },
        show: () => {
            passwordInput.type = 'text';
            if (toggleIcon) toggleIcon.className = 'bi bi-eye-slash';
            if (toggleBtn) toggleBtn.classList.add('active');
        },
        hide: () => {
            passwordInput.type = 'password';
            if (toggleIcon) toggleIcon.className = 'bi bi-eye';
            if (toggleBtn) toggleBtn.classList.remove('active');
        }
    };
    
})();
</script>
