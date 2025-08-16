<?php
/**
 * Reusable Select (Dropdown) Component
 * 
 * Usage:
 * <?php 
 *   $selectConfig = [
 *     'id' => 'gender',
 *     'name' => 'gender',
 *     'label' => 'Gender',
 *     'options' => [
 *         '' => 'Choose...',
 *         'male' => 'Male',
 *         'female' => 'Female',
 *         'other' => 'Other'
 *     ],
 *     'value' => 'female',
 *     'class' => 'form-select shadow-none',
 *     'required' => true,
 *     'helpText' => 'Select your gender.'
 *   ];
 *   include 'components/reusable/select.php'; 
 * ?>
 */

$defaultConfig = [
    'id' => 'select_' . uniqid(),
    'name' => '',
    'label' => '',
    'options' => [],
    'value' => '',
    'class' => 'form-select shadow-none',
    'required' => false,
    'disabled' => false,
    'helpText' => ''
];

$config = isset($selectConfig) ? array_merge($defaultConfig, $selectConfig) : $defaultConfig;
?>

<div class="mb-3">
    <?php if (!empty($config['label'])): ?>
        <label for="<?= $config['id']; ?>" class="form-label">
            <?= htmlspecialchars($config['label']); ?>
        </label>
    <?php endif; ?>

    <select
        id="<?= $config['id']; ?>"
        name="<?= htmlspecialchars($config['name']); ?>"
        class="<?= $config['class']; ?>"
        <?php if ($config['required']) echo 'required'; ?>
        <?php if ($config['disabled']) echo 'disabled'; ?>
    >
        <?php foreach ($config['options'] as $optValue => $optLabel): ?>
            <option value="<?= htmlspecialchars($optValue); ?>"
                <?= ($optValue == $config['value']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($optLabel); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (!empty($config['helpText'])): ?>
        <div class="form-text"><?= htmlspecialchars($config['helpText']); ?></div>
    <?php endif; ?>
</div>