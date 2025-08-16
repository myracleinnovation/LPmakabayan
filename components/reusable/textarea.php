<?php
/**
 * Reusable Textarea Component
 * 
 * Usage:
 * <?php 
 *   $textareaConfig = [
 *     'id' => 'message',
 *     'name' => 'message',
 *     'label' => 'Your Message',
 *     'placeholder' => 'Type here...',
 *     'value' => '',
 *     'class' => 'form-control shadow-none',
 *     'rows' => 4,
 *     'required' => true,
 *     'helpText' => 'Max 500 characters.'
 *   ];
 *   include 'components/reusable/textarea.php'; 
 * ?>
 */

$defaultConfig = [
    'id' => 'textarea_' . uniqid(),
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => '',
    'class' => 'form-control shadow-none',
    'rows' => 3,
    'required' => false,
    'readonly' => false,
    'disabled' => false,
    'helpText' => ''
];

$config = isset($textareaConfig) ? array_merge($defaultConfig, $textareaConfig) : $defaultConfig;
?>

<div class="mb-3">
    <?php if (!empty($config['label'])): ?>
        <label for="<?= $config['id']; ?>" class="form-label">
            <?= htmlspecialchars($config['label']); ?>
        </label>
    <?php endif; ?>

    <textarea
        id="<?= $config['id']; ?>"
        name="<?= htmlspecialchars($config['name']); ?>"
        class="<?= $config['class']; ?>"
        rows="<?= $config['rows']; ?>"
        placeholder="<?= htmlspecialchars($config['placeholder']); ?>"
        <?php if ($config['required']) echo 'required'; ?>
        <?php if ($config['readonly']) echo 'readonly'; ?>
        <?php if ($config['disabled']) echo 'disabled'; ?>
    ><?= htmlspecialchars($config['value']); ?></textarea>

    <?php if (!empty($config['helpText'])): ?>
        <div class="form-text"><?= htmlspecialchars($config['helpText']); ?></div>
    <?php endif; ?>
</div>