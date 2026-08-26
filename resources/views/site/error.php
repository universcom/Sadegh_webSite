<?php
/**
 * Designed error page, used for 404 and 500.
 *
 * @var int    $status
 * @var string $errorTitle
 * @var string $errorBody
 */

use App\Core\Url;
?>
<section class="section section--loose">
    <div class="container">
        <div class="empty-state" style="border-style: solid; background: var(--white)">
            <p class="eyebrow" style="justify-content:center; display:inline-flex">
                <span class="ltr-num"><?= e(num((string) ($status ?? 404))) ?></span>
            </p>
            <h1 style="font-size: var(--step-4); margin-block: var(--space-3)"><?= e($errorTitle ?? '') ?></h1>
            <p style="max-width: 46ch; margin-inline: auto"><?= e($errorBody ?? '') ?></p>

            <div style="display:flex; gap: var(--space-3); justify-content:center; flex-wrap:wrap; margin-block-start: var(--space-6)">
                <a class="btn btn--primary" href="<?= e(Url::home()) ?>"><?= _e('error.404.cta') ?></a>
                <a class="btn btn--outline" href="<?= e(Url::products()) ?>"><?= _e('nav.products') ?></a>
                <a class="btn btn--outline" href="<?= e(Url::contact()) ?>"><?= _e('nav.contact') ?></a>
            </div>
        </div>
    </div>
</section>
