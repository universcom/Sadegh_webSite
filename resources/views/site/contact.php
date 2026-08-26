<?php
/**
 * Contact page: company details plus the enquiry form.
 *
 * @var array  $page
 * @var array  $errors      field => message
 * @var array  $old         previously submitted values
 * @var string $subjectHint prefilled subject (product enquiry)
 */

use App\Core\Csrf;
use App\Core\Url;
use App\Core\View;
use App\Models\Setting;

$errors = $errors ?? [];
$old    = $old ?? [];

$value = static fn (string $key, string $default = ''): string => (string) ($old[$key] ?? $default);
$hasError = static fn (string $key): bool => isset($errors[$key]);

$phones = Setting::phones();
$emails = Setting::emails();
$mapUrl = Setting::get('map_url');
$mapEmbed = Setting::get('map_embed');
?>
<section class="page-hero">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumbs">
                <li><a href="<?= e(Url::home()) ?>"><?= _e('common.home') ?></a></li>
                <li><span aria-current="page"><?= _e('nav.contact') ?></span></li>
            </ol>
        </nav>
        <h1><?= e($page['title'] ?? __('contact.title')) ?></h1>
        <p class="page-hero__lead"><?= e($page['subtitle'] ?? __('contact.lead')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?= View::partial('partials.flash') ?>

        <div class="contact-grid mt-5">
            <?php /* ------------------------------------------- Company details */ ?>
            <div class="stack">
                <?php if (Setting::get('address') !== ''): ?>
                    <div class="info-card">
                        <span class="info-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                        </span>
                        <div>
                            <p class="info-card__label"><?= _e('contact.address') ?></p>
                            <p class="info-card__value"><?= e(Setting::get('address')) ?></p>
                            <?php if (Setting::get('postal_code') !== ''): ?>
                                <p class="info-card__value" style="margin-block-start: var(--space-2)">
                                    <span class="info-card__label" style="display:inline"><?= _e('contact.postal_code') ?>:</span>
                                    <span class="ltr-num"><?= e(num(Setting::get('postal_code'))) ?></span>
                                </p>
                            <?php endif; ?>
                            <?php if ($mapUrl !== ''): ?>
                                <p style="margin-block-start: var(--space-3)">
                                    <a class="btn btn--outline btn--sm" href="<?= e($mapUrl) ?>"
                                       target="_blank" rel="noopener noreferrer"><?= _e('contact.map') ?></a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($phones !== []): ?>
                    <div class="info-card">
                        <span class="info-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>
                            </svg>
                        </span>
                        <div>
                            <p class="info-card__label"><?= _e('contact.phone') ?></p>
                            <?php foreach ($phones as $phone): ?>
                                <p class="info-card__value">
                                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $phone)) ?>">
                                        <span class="ltr-num"><?= e(num($phone)) ?></span>
                                    </a>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($emails !== []): ?>
                    <div class="info-card">
                        <span class="info-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
                            </svg>
                        </span>
                        <div>
                            <p class="info-card__label"><?= _e('contact.email') ?></p>
                            <?php foreach ($emails as $email): ?>
                                <p class="info-card__value">
                                    <a href="mailto:<?= e($email) ?>"><span class="ltr-num"><?= e($email) ?></span></a>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (Setting::get('working_hours') !== ''): ?>
                    <div class="info-card">
                        <span class="info-card__icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                            </svg>
                        </span>
                        <div>
                            <p class="info-card__label"><?= _e('contact.hours') ?></p>
                            <p class="info-card__value"><?= nl2br(e(Setting::get('working_hours'))) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php /* --------------------------------------------------- Form */ ?>
            <div class="form-panel">
                <h2><?= _e('contact.form_title') ?></h2>
                <p class="text-muted mt-4"><?= _e('contact.form_lead') ?></p>

                <form class="form mt-6" method="post" action="<?= e(Url::contact()) ?>"
                      novalidate data-pending-form>
                    <?= Csrf::field() ?>

                    <?php /* Honeypot: bots fill it, humans never see it. */ ?>
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <input type="hidden" name="form_time" value="<?= e((string) time()) ?>">

                    <div class="form-row">
                        <div class="field<?= $hasError('name') ? ' field--error' : '' ?>">
                            <label class="field__label" for="name">
                                <?= _e('contact.name') ?> <span class="field__required" aria-hidden="true">*</span>
                            </label>
                            <input class="input" type="text" id="name" name="name" required maxlength="120"
                                   autocomplete="name" value="<?= e($value('name')) ?>"
                                   <?= $hasError('name') ? 'aria-invalid="true" aria-describedby="err-name"' : '' ?>>
                            <?php if ($hasError('name')): ?>
                                <p class="field__error" id="err-name"><?= e($errors['name']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field<?= $hasError('email') ? ' field--error' : '' ?>">
                            <label class="field__label" for="email">
                                <?= _e('contact.email_label') ?> <span class="field__required" aria-hidden="true">*</span>
                            </label>
                            <input class="input" type="email" id="email" name="email" required maxlength="190"
                                   autocomplete="email" dir="ltr" value="<?= e($value('email')) ?>"
                                   <?= $hasError('email') ? 'aria-invalid="true" aria-describedby="err-email"' : '' ?>>
                            <?php if ($hasError('email')): ?>
                                <p class="field__error" id="err-email"><?= e($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field<?= $hasError('phone') ? ' field--error' : '' ?>">
                            <label class="field__label" for="phone">
                                <?= _e('contact.phone_label') ?> <span class="field__required" aria-hidden="true">*</span>
                            </label>
                            <input class="input" type="tel" id="phone" name="phone" required maxlength="40"
                                   autocomplete="tel" dir="ltr" inputmode="tel" value="<?= e($value('phone')) ?>"
                                   <?= $hasError('phone') ? 'aria-invalid="true" aria-describedby="err-phone"' : '' ?>>
                            <?php if ($hasError('phone')): ?>
                                <p class="field__error" id="err-phone"><?= e($errors['phone']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="field<?= $hasError('company') ? ' field--error' : '' ?>">
                            <label class="field__label" for="company">
                                <?= _e('contact.company') ?>
                                <span class="field__hint">(<?= _e('common.optional') ?>)</span>
                            </label>
                            <input class="input" type="text" id="company" name="company" maxlength="190"
                                   autocomplete="organization" value="<?= e($value('company')) ?>">
                            <?php if ($hasError('company')): ?>
                                <p class="field__error"><?= e($errors['company']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="field<?= $hasError('subject') ? ' field--error' : '' ?>">
                        <label class="field__label" for="subject">
                            <?= _e('contact.subject') ?> <span class="field__required" aria-hidden="true">*</span>
                        </label>
                        <input class="input" type="text" id="subject" name="subject" required maxlength="190"
                               value="<?= e($value('subject', $subjectHint ?? '')) ?>"
                               <?= $hasError('subject') ? 'aria-invalid="true" aria-describedby="err-subject"' : '' ?>>
                        <?php if ($hasError('subject')): ?>
                            <p class="field__error" id="err-subject"><?= e($errors['subject']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="field<?= $hasError('message') ? ' field--error' : '' ?>">
                        <label class="field__label" for="message">
                            <?= _e('contact.message') ?> <span class="field__required" aria-hidden="true">*</span>
                        </label>
                        <textarea class="textarea" id="message" name="message" required
                                  minlength="10" maxlength="4000" rows="6"
                                  <?= $hasError('message') ? 'aria-invalid="true" aria-describedby="err-message"' : '' ?>><?= e($value('message')) ?></textarea>
                        <?php if ($hasError('message')): ?>
                            <p class="field__error" id="err-message"><?= e($errors['message']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($productId)): ?>
                        <input type="hidden" name="product_id" value="<?= e((string) $productId) ?>">
                    <?php endif; ?>

                    <button class="btn btn--accent btn--lg btn--block" type="submit"
                            data-label-pending="<?= _e('contact.sending') ?>">
                        <?= _e('contact.send') ?>
                    </button>
                </form>
            </div>
        </div>

        <?php if ($mapEmbed !== ''): ?>
            <div class="map-embed mt-8">
                <iframe src="<?= e($mapEmbed) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="<?= _e('contact.map') ?>" allowfullscreen></iframe>
            </div>
        <?php endif; ?>
    </div>
</section>
