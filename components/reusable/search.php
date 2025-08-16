<?php
    /**
     * Concise Reusable Search Component
     * 
     * Usage:
     * <?php 
     *   $searchConfig = [
     *     'id' => 'mySearch',
     *     'placeholder' => 'Search items...',
     *     'dataTarget' => 'tableId',
     *     'class' => 'form-control',
     *     'minLength' => 2,
     *     'delay' => 300,
     *     'showClear' => true,
     *     'size' => ''
     *   ];
     *   include 'components/reusable/search.php'; 
     * ?>
     */

    $defaultConfig = [
        'id' => 'search_' . uniqid(),
        'placeholder' => 'Search...',
        'class' => 'form-control shadow-none',
        'dataTarget' => '',
        'minLength' => 2,
        'delay' => 300,
        'showClear' => true
    ];

    $config = isset($searchConfig) ? array_merge($defaultConfig, $searchConfig) : $defaultConfig;
?>

<div class="position-relative">
    <input type="text" 
        id="<?= $config['id']; ?>"
        class="<?= $config['class']; ?>"
        placeholder="<?= htmlspecialchars($config['placeholder']); ?>"
        data-target="<?= $config['dataTarget']; ?>"
        data-min-length="<?= $config['minLength']; ?>"
        data-delay="<?= $config['delay']; ?>"
        autocomplete="off">

    <?php if ($config['showClear']): ?>
    <button type="button" id="<?= $config['id']; ?>_clear" 
        class="btn-close position-absolute top-50 end-0 translate-middle-y d-none"
        aria-label="Clear"></button>
    <?php endif; ?>
</div>

<script>
    (function() {
        const input = document.getElementById('<?= $config['id']; ?>');
        const clearBtn = document.getElementById('<?= $config['id']; ?>_clear');
        let timeout;

        if (!input) return;

        input.addEventListener('input', function(e) {
            const val = e.target.value.trim();
            if (clearBtn) clearBtn.classList.toggle('d-none', !val);

            clearTimeout(timeout);
            const minLen = parseInt(input.dataset.minLength) || 2;
            if (val.length < minLen && val.length > 0) return;

            timeout = setTimeout(() => {
                const event = new CustomEvent('searchPerformed', {
                    detail: { id: input.id, value: val, target: input.dataset.target }
                });
                document.dispatchEvent(event);
            }, parseInt(input.dataset.delay) || 300);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                clearBtn.classList.add('d-none');
                const event = new CustomEvent('searchCleared', { detail: { id: input.id }});
                document.dispatchEvent(event);
            });
        }
    })();
</script>