<?php
/**
 * Reusable Input Component
 * 
 * Usage:
 * <?php 
 *   $inputConfig = [
 *     'id' => 'username',
 *     'name' => 'username',
 *     'type' => 'text',
 *     'label' => 'Username',
 *     'placeholder' => 'Enter username',
 *     'value' => '',
 *     'class' => 'form-control shadow-none',
 *     'required' => true
 *   ];
 *   include 'components/reusable/input.php'; 
 * ?>
 */

$defaultConfig = [
    'id' => 'input_' . uniqid(),
    'name' => '',
    'type' => 'text',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'class' => 'form-control shadow-none',
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'helpText' => ''
];

$config = isset($inputConfig) ? array_merge($defaultConfig, $inputConfig) : $defaultConfig;
?>

<div class="mb-3">
    <?php if (!empty($config['label'])): ?>
        <label for="<?= $config['id']; ?>" class="form-label">
            <?= htmlspecialchars($config['label']); ?>
        </label>
    <?php endif; ?>

    <input 
        type="<?= htmlspecialchars($config['type']); ?>"
        id="<?= $config['id']; ?>"
        name="<?= htmlspecialchars($config['name']); ?>"
        class="<?= $config['class']; ?>"
        placeholder="<?= htmlspecialchars($config['placeholder']); ?>"
        value="<?= htmlspecialchars($config['value']); ?>"
        <?php if ($config['required']) echo 'required'; ?>
        <?php if ($config['readonly']) echo 'readonly'; ?>
        <?php if ($config['disabled']) echo 'disabled'; ?>
    >

    <?php if (!empty($config['helpText'])): ?>
        <div class="form-text"><?= htmlspecialchars($config['helpText']); ?></div>
    <?php endif; ?>
</div>