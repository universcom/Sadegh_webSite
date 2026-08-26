<?php
/**
 * Pagination that preserves the current query string.
 *
 * @var int $page
 * @var int $pages
 */

use App\Core\Url;

if (($pages ?? 1) < 2) {
    return;
}

$link = static function (int $target) use ($request): string {
    return Url::withQuery(['page' => $target > 1 ? $target : null], $request);
};

// Window of pages around the current one, with ellipses either side.
$window  = [];
$window[] = 1;
for ($i = $page - 1; $i <= $page + 1; $i++) {
    if ($i > 1 && $i < $pages) {
        $window[] = $i;
    }
}
if ($pages > 1) {
    $window[] = $pages;
}
$window = array_values(array_unique($window));
sort($window);
?>
<nav class="pagination" aria-label="<?= _e('common.page') ?>">
    <a class="pagination__link" href="<?= e($link(max(1, $page - 1))) ?>"
       <?= $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>
       aria-label="<?= _e('common.previous') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
             stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true">
            <path d="m15 18-6-6 6-6"/>
        </svg>
    </a>

    <?php $previous = 0; ?>
    <?php foreach ($window as $number): ?>
        <?php if ($previous !== 0 && $number - $previous > 1): ?>
            <span class="pagination__link" aria-hidden="true" style="border:0">…</span>
        <?php endif; ?>
        <a class="pagination__link" href="<?= e($link($number)) ?>"
           <?= $number === $page ? 'aria-current="page"' : '' ?>><?= e(num($number)) ?></a>
        <?php $previous = $number; ?>
    <?php endforeach; ?>

    <a class="pagination__link" href="<?= e($link(min($pages, $page + 1))) ?>"
       <?= $page >= $pages ? 'aria-disabled="true" tabindex="-1"' : '' ?>
       aria-label="<?= _e('common.next') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
             stroke-linecap="round" stroke-linejoin="round" width="16" height="16" aria-hidden="true">
            <path d="m9 18 6-6-6-6"/>
        </svg>
    </a>
</nav>
