<?php
/**
 * Public site layout.
 *
 * @var string $content   rendered page body
 * @var string $locale
 * @var string $direction
 * @var array  $locales
 */

use App\Core\Lang;
use App\Core\Url;
use App\Core\View;
use App\Models\Setting;

$siteName    = Setting::get('site_name', 'Rahyaft Sanat');
$title       = $metaTitle ?? $siteName;
// Append the brand only when the title does not already carry it, so an
// SEO title written as "Brand | …" is not doubled up.
$fullTitle   = ($title === $siteName || str_contains($title, $siteName))
    ? $title
    : $title . ' | ' . $siteName;
$description = (string) ($metaDescription ?? Setting::get('seo_description', Setting::get('site_tagline', '')));
$canonical   = $canonical ?? Url::to(ltrim($request->path(), '/'));
$ogImage     = $ogImage ?? Url::asset('img/logo-mark.png');
$assetVersion = $assetVersion ?? '1';

$organisation = array_filter([
    '@context'    => 'https://schema.org',
    '@type'       => 'Organization',
    'name'        => $siteName,
    'url'         => Url::base(),
    'logo'        => Url::asset('img/logo-mark.png'),
    'description' => excerpt($description, 300) ?: null,
    'address'     => Setting::get('address') !== '' ? array_filter([
        '@type'           => 'PostalAddress',
        'streetAddress'   => Setting::get('address'),
        'addressLocality' => Setting::get('city'),
        'postalCode'      => Setting::get('postal_code'),
        'addressCountry'  => 'IR',
    ]) : null,
    'contactPoint' => Setting::phones() !== [] ? array_filter([
        '@type'       => 'ContactPoint',
        'contactType' => 'sales',
        'telephone'   => Setting::phones()[0],
        'email'       => Setting::emails()[0] ?? null,
        'areaServed'  => 'IR',
    ]) : null,
    'sameAs' => array_values(Setting::socialLinks()) ?: null,
]);
?>
<!DOCTYPE html>
<html lang="<?= e(Lang::htmlLang()) ?>" dir="<?= e($direction) ?>" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($fullTitle) ?></title>
    <?php if ($description !== ''): ?>
        <meta name="description" content="<?= e(excerpt($description, 300)) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= e($metaRobots ?? 'index, follow') ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <?php /* One hreflang per language, plus x-default for the site default. */ ?>
    <?php foreach ($locales as $code): ?>
        <link rel="alternate" hreflang="<?= e(Lang::htmlLang($code)) ?>" href="<?= e(Url::switchLocale($code, $request)) ?>">
    <?php endforeach; ?>
    <link rel="alternate" hreflang="x-default" href="<?= e(Url::switchLocale(Lang::default(), $request)) ?>">

    <meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e(excerpt($description, 200)) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e($ogImage) ?>">
    <meta property="og:locale" content="<?= e(str_replace('-', '_', Lang::htmlLang())) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($title) ?>">
    <meta name="twitter:description" content="<?= e(excerpt($description, 200)) ?>">
    <meta name="twitter:image" content="<?= e($ogImage) ?>">

    <meta name="theme-color" content="#353a96">
    <link rel="icon" href="<?= e(asset('img/favicon-32.png')) ?>" sizes="32x32">
    <link rel="icon" href="<?= e(asset('img/favicon-192.png')) ?>" sizes="192x192">
    <link rel="apple-touch-icon" href="<?= e(asset('img/favicon-180.png')) ?>">

    <?php /* Preload the faces used above the fold so the hero never flashes. */ ?>
    <link rel="preload" href="<?= e(asset('fonts/Vazirmatn-Regular.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= e(asset('fonts/Vazirmatn-Bold.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/site.css') . '?v=' . e($assetVersion)) ?>">

    <script type="application/ld+json"><?= json_encode($organisation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <?= View::section('schema') ?>
    <?= View::section('head') ?>
</head>
<body>
    <a class="skip-link" href="#main"><?= _e('nav.skip') ?></a>

    <?= View::partial('partials.header') ?>

    <main id="main">
        <?= $content ?>
    </main>

    <?= View::partial('partials.footer') ?>

    <script src="<?= e(asset('js/site.js') . '?v=' . e($assetVersion)) ?>" defer></script>
    <?= View::section('scripts') ?>
</body>
</html>
