<?php

/**
 * Reusable Buttons Component
 *
 * Usage:
 * <?php
 *   $buttonConfig = [
 *     'id' => 'saveBtn',
 *     'label' => 'Save',
 *     'type' => 'primary', // primary, secondary, outline-primary, outline-secondary
 *     'size' => 'lg', // sm, lg, or empty
 *     'onclick' => 'alert("Saved!")'
 *   ];
 *   include 'components/reusable/button.php';
 * ?>
 */

$defaultConfig = [
    'id' => 'btn_' . uniqid(),
    'label' => 'Button',
    'type' => 'primary',
    'size' => '',
    'onclick' => ''
];

$config = isset($buttonConfig) ? array_merge($defaultConfig, $buttonConfig) : $defaultConfig;

// Build classes
$btnClass = 'btn ';
switch ($config['type']) {
    case 'secondary':
        $btnClass .= 'btn-secondary';
        break;
    case 'outline-primary':
        $btnClass .= 'btn-outline-primary';
        break;
    case 'outline-secondary':
        $btnClass .= 'btn-outline-secondary';
        break;
    default:
        $btnClass .= 'btn-primary';
}

if (!empty($config['size'])) {
    $btnClass .= ' btn-' . $config['size'];
}
?>

<button
    id="<?= $config['id']; ?>"
    class="<?= $btnClass; ?>"
    type="button"
    <?php if (!empty($config['onclick'])): ?>
    onclick="<?= $config['onclick']; ?>"
    <?php endif; ?>>
    <?= htmlspecialchars($config['label']); ?>
</button>