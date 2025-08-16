<?php
/**
 * Batch Form Fields Component
 * 
 * Usage:
 * <?php 
 *   $fieldsConfig = [
 *     'fields' => [
 *       [
 *         'type' => 'text',
 *         'id' => 'username',
 *         'name' => 'username',
 *         'label' => 'Username *',
 *         'required' => true
 *       ],
 *       [
 *         'type' => 'password',
 *         'id' => 'password',
 *         'name' => 'password',
 *         'label' => 'Password *',
 *         'showToggle' => true
 *       ]
 *     ],
 *     'layout' => '2-columns', // 1-column, 2-columns, 3-columns
 *     'wrapperClass' => 'mb-3'
 *   ];
 *   include '../components/reusable/form-fields.php'; 
 * ?>
 */

// Default configuration
$defaultConfig = [
    'fields' => [],
    'layout' => '1-column', // 1-column, 2-columns, 3-columns
    'wrapperClass' => 'mb-3',
    'rowClass' => 'row',
    'colClass' => 'col-md-6'
];

// Merge with provided configuration
$config = isset($fieldsConfig) ? array_merge($defaultConfig, $fieldsConfig) : $defaultConfig;

// Set column class based on layout
switch ($config['layout']) {
    case '2-columns':
        $colClass = 'col-md-6';
        break;
    case '3-columns':
        $colClass = 'col-md-4';
        break;
    default:
        $colClass = 'col-12';
        break;
}

// Function to render a single field
if (!function_exists('renderFormField')) {
    function renderFormField($fieldConfig) {
    $defaultFieldConfig = [
        'type' => 'text',
        'id' => 'field_' . uniqid(),
        'name' => '',
        'label' => '',
        'placeholder' => '',
        'class' => 'form-control shadow-none',
        'required' => false,
        'value' => '',
        'autocomplete' => '',
        'minlength' => '',
        'maxlength' => '',
        'pattern' => '',
        'readonly' => false,
        'disabled' => false,
        'showToggle' => false,
        'strengthMeter' => false,
        'options' => [],
        'rows' => 4,
        'cols' => '',
        'multiple' => false,
        'size' => '',
        'helpText' => '',
        'errorText' => '',
        'ariaLabel' => '',
        'wrapperClass' => 'mb-3'
    ];

    $field = array_merge($defaultFieldConfig, $fieldConfig);
    
    // Generate unique ID if not provided
    if (empty($field['id'])) {
        $field['id'] = 'field_' . uniqid();
    }

    // Build CSS classes
    $cssClasses = $field['class'];
    if (!empty($field['size'])) {
        $cssClasses .= ' form-control-' . $field['size'];
    }
    if ($field['required']) {
        $cssClasses .= ' required';
    }

    // Build wrapper classes
    $wrapperClasses = $field['wrapperClass'];
    if ($field['errorText']) {
        $wrapperClasses .= ' has-error';
    }

    ob_start();
    ?>
    <div class="<?php echo $wrapperClasses; ?>">
        <?php if (!empty($field['label'])): ?>
        <label class="form-label mb-0" for="<?php echo $field['id']; ?>">
            <?php echo htmlspecialchars($field['label']); ?>
            <?php if ($field['required']): ?><span class="text-danger">*</span><?php endif; ?>
        </label>
        <?php endif; ?>

        <?php if ($field['type'] === 'password'): ?>
            <!-- Password Field with Toggle -->
            <div class="password-container position-relative">
                <input 
                    type="password" 
                    class="<?php echo $cssClasses; ?> pe-5" 
                    id="<?php echo $field['id']; ?>"
                    name="<?php echo $field['name']; ?>"
                    placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                    aria-label="<?php echo htmlspecialchars($field['ariaLabel'] ?: $field['label']); ?>"
                    minlength="<?php echo $field['minlength']; ?>"
                    maxlength="<?php echo $field['maxlength']; ?>"
                    pattern="<?php echo $field['pattern']; ?>"
                    autocomplete="<?php echo $field['autocomplete']; ?>"
                    value="<?php echo htmlspecialchars($field['value']); ?>"
                    <?php if ($field['required']): ?>required<?php endif; ?>
                    <?php if ($field['readonly']): ?>readonly<?php endif; ?>
                    <?php if ($field['disabled']): ?>disabled<?php endif; ?>
                >
                
                <?php if ($field['showToggle']): ?>
                <button 
                    type="button" 
                    class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 password-toggle" 
                    id="<?php echo $field['id']; ?>_toggle"
                    aria-label="Toggle password visibility"
                    style="z-index: 10; border: none; background: none; color: #6c757d;"
                >
                    <i class="bi bi-eye" id="<?php echo $field['id']; ?>_icon"></i>
                </button>
                <?php endif; ?>
            </div>

            <?php if ($field['strengthMeter']): ?>
            <div class="password-strength mt-2" id="<?php echo $field['id']; ?>_strength" style="display: none;">
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar" id="<?php echo $field['id']; ?>_strength_bar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted mt-1" id="<?php echo $field['id']; ?>_strength_text">Password strength</small>
            </div>
            <?php endif; ?>

        <?php elseif ($field['type'] === 'textarea'): ?>
            <!-- Textarea Field -->
            <textarea 
                class="<?php echo $cssClasses; ?>" 
                id="<?php echo $field['id']; ?>"
                name="<?php echo $field['name']; ?>"
                placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                aria-label="<?php echo htmlspecialchars($field['ariaLabel'] ?: $field['label']); ?>"
                rows="<?php echo $field['rows']; ?>"
                cols="<?php echo $field['cols']; ?>"
                minlength="<?php echo $field['minlength']; ?>"
                maxlength="<?php echo $field['maxlength']; ?>"
                pattern="<?php echo $field['pattern']; ?>"
                <?php if ($field['required']): ?>required<?php endif; ?>
                <?php if ($field['readonly']): ?>readonly<?php endif; ?>
                <?php if ($field['disabled']): ?>disabled<?php endif; ?>
            ><?php echo htmlspecialchars($field['value']); ?></textarea>

        <?php elseif ($field['type'] === 'select'): ?>
            <!-- Select Field -->
            <select 
                class="<?php echo str_replace('form-control', 'form-select', $cssClasses); ?>" 
                id="<?php echo $field['id']; ?>"
                name="<?php echo $field['name']; ?>"
                aria-label="<?php echo htmlspecialchars($field['ariaLabel'] ?: $field['label']); ?>"
                <?php if ($field['required']): ?>required<?php endif; ?>
                <?php if ($field['disabled']): ?>disabled<?php endif; ?>
                <?php if ($field['multiple']): ?>multiple<?php endif; ?>
            >
                <?php if (!empty($field['placeholder'])): ?>
                <option value=""><?php echo htmlspecialchars($field['placeholder']); ?></option>
                <?php endif; ?>
                
                <?php foreach ($field['options'] as $value => $label): ?>
                <option value="<?php echo htmlspecialchars($value); ?>" 
                        <?php echo ($field['value'] == $value) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
                <?php endforeach; ?>
            </select>

        <?php else: ?>
            <!-- Regular Input Field -->
            <input 
                type="<?php echo $field['type']; ?>" 
                class="<?php echo $cssClasses; ?>" 
                id="<?php echo $field['id']; ?>"
                name="<?php echo $field['name']; ?>"
                placeholder="<?php echo htmlspecialchars($field['placeholder']); ?>"
                aria-label="<?php echo htmlspecialchars($field['ariaLabel'] ?: $field['label']); ?>"
                minlength="<?php echo $field['minlength']; ?>"
                maxlength="<?php echo $field['maxlength']; ?>"
                pattern="<?php echo $field['pattern']; ?>"
                autocomplete="<?php echo $field['autocomplete']; ?>"
                value="<?php echo htmlspecialchars($field['value']); ?>"
                <?php if ($field['required']): ?>required<?php endif; ?>
                <?php if ($field['readonly']): ?>readonly<?php endif; ?>
                <?php if ($field['disabled']): ?>disabled<?php endif; ?>
            >
        <?php endif; ?>

        <?php if (!empty($field['helpText'])): ?>
        <div class="form-text"><?php echo htmlspecialchars($field['helpText']); ?></div>
        <?php endif; ?>

        <?php if (!empty($field['errorText'])): ?>
        <div class="invalid-feedback d-block"><?php echo htmlspecialchars($field['errorText']); ?></div>
        <?php endif; ?>
    </div>
    <?php
         return ob_get_clean();
    }
}

// Render fields
if (!empty($config['fields'])) {
         if ($config['layout'] === '1-column') {
         // Single column layout
         foreach ($config['fields'] as $field) {
             echo renderFormField($field);
         }
     } else {
         // Multi-column layout
         echo '<div class="' . $config['rowClass'] . '">';
         foreach ($config['fields'] as $field) {
             echo '<div class="' . $colClass . '">';
             echo renderFormField($field);
             echo '</div>';
         }
         echo '</div>';
     }
}
?>

<?php
// Add password functionality if any password fields exist
$hasPasswordFields = false;
foreach ($config['fields'] as $field) {
    if ($field['type'] === 'password' && ($field['showToggle'] || $field['strengthMeter'])) {
        $hasPasswordFields = true;
        break;
    }
}
?>

<?php if ($hasPasswordFields): ?>
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

.password-toggle i {
    transition: transform 0.2s ease-in-out;
}

.password-toggle.active i {
    transform: scale(1.1);
}

.password-strength .progress-bar.weak { background-color: #dc3545; }
.password-strength .progress-bar.fair { background-color: #ffc107; }
.password-strength .progress-bar.good { background-color: #17a2b8; }
.password-strength .progress-bar.strong { background-color: #28a745; }

@media (max-width: 768px) {
    .password-container .form-control {
        font-size: 16px;
    }
}
</style>

<script>
(function() {
    'use strict';
    
    // Initialize all password fields
    function initPasswordFields() {
        <?php foreach ($config['fields'] as $field): ?>
        <?php if ($field['type'] === 'password' && ($field['showToggle'] || $field['strengthMeter'])): ?>
        const fieldId_<?php echo $field['id']; ?> = '<?php echo $field['id']; ?>';
        const passwordInput_<?php echo $field['id']; ?> = document.getElementById(fieldId_<?php echo $field['id']; ?>);
        const toggleBtn_<?php echo $field['id']; ?> = document.getElementById(fieldId_<?php echo $field['id']; ?> + '_toggle');
        const toggleIcon_<?php echo $field['id']; ?> = document.getElementById(fieldId_<?php echo $field['id']; ?> + '_icon');
        const strengthMeter_<?php echo $field['id']; ?> = document.getElementById(fieldId_<?php echo $field['id']; ?> + '_strength');
        const strengthBar_<?php echo $field['id']; ?> = document.getElementById(fieldId_<?php echo $field['id']; ?> + '_strength_bar');
        const strengthText_<?php echo $field['id']; ?> = document.getElementById(fieldId_<?php echo $field['id']; ?> + '_strength_text');
        
        if (passwordInput_<?php echo $field['id']; ?>) {
            <?php if ($field['showToggle']): ?>
            if (toggleBtn_<?php echo $field['id']; ?>) {
                toggleBtn_<?php echo $field['id']; ?>.addEventListener('click', function() {
                    const type = passwordInput_<?php echo $field['id']; ?>.type === 'password' ? 'text' : 'password';
                    passwordInput_<?php echo $field['id']; ?>.type = type;
                    
                    if (toggleIcon_<?php echo $field['id']; ?>) {
                        toggleIcon_<?php echo $field['id']; ?>.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
                    }
                    
                    toggleBtn_<?php echo $field['id']; ?>.classList.toggle('active', type === 'text');
                    toggleBtn_<?php echo $field['id']; ?>.setAttribute('aria-label', 
                        type === 'password' ? 'Show password' : 'Hide password'
                    );
                });
            }
            <?php endif; ?>
            
            <?php if ($field['strengthMeter']): ?>
            if (strengthMeter_<?php echo $field['id']; ?>) {
                passwordInput_<?php echo $field['id']; ?>.addEventListener('input', function() {
                    const password = passwordInput_<?php echo $field['id']; ?>.value;
                    
                    if (password.length === 0) {
                        strengthMeter_<?php echo $field['id']; ?>.style.display = 'none';
                        return;
                    }
                    
                    strengthMeter_<?php echo $field['id']; ?>.style.display = 'block';
                    
                    let strength = 0;
                    
                    if (password.length >= 8) strength += 25;
                    if (password.length >= 12) strength += 25;
                    if (/[a-z]/.test(password)) strength += 10;
                    if (/[A-Z]/.test(password)) strength += 10;
                    if (/[0-9]/.test(password)) strength += 10;
                    if (/[^A-Za-z0-9]/.test(password)) strength += 10;
                    if (/(.)\1{2,}/.test(password)) strength -= 10;
                    if (/123|abc|qwe/i.test(password)) strength -= 10;
                    
                    strength = Math.max(0, Math.min(100, strength));
                    strengthBar_<?php echo $field['id']; ?>.style.width = strength + '%';
                    
                    let strengthLevel = '';
                    let strengthClass = '';
                    let feedback = '';
                    
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
                    
                    strengthBar_<?php echo $field['id']; ?>.className = 'progress-bar ' + strengthClass;
                    strengthText_<?php echo $field['id']; ?>.textContent = strengthLevel + ' - ' + feedback;
                });
            }
            <?php endif; ?>
        }
        <?php endif; ?>
        <?php endforeach; ?>
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initPasswordFields);
    } else {
        initPasswordFields();
    }
    
})();
</script>
<?php endif; ?>
