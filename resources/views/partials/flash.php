<?php
/** Flash message bar. */

use App\Core\Session;

$flashes = Session::flashes();

if ($flashes === []) {
    return;
}

$icons = [
    'success' => '<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="M22 4 12 14.01l-3-3"/>',
    'error'   => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
    'info'    => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    'warning' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
];
?>
<?php foreach ($flashes as $flash): ?>
    <?php $type = in_array($flash['type'], ['success', 'error', 'info', 'warning'], true) ? $flash['type'] : 'info'; ?>
    <div class="alert alert--<?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <?= $icons[$type] ?>
        </svg>
        <span><?= e($flash['message']) ?></span>
    </div>
<?php endforeach; ?>
