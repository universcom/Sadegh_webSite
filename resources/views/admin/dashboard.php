<?php
/**
 * Admin dashboard.
 *
 * @var array $stats
 * @var array $recentMessages
 * @var array $recentProducts
 * @var array $recentPages
 * @var array $needsReview
 */

use App\Core\Url;

$tiles = [
    ['label' => 'Published products', 'value' => $stats['products'],   'icon' => 'box',    'tone' => '',       'url' => Url::admin('products')],
    ['label' => 'Categories',         'value' => $stats['categories'], 'icon' => 'grid',   'tone' => '',       'url' => Url::admin('categories')],
    ['label' => 'R&D projects',       'value' => $stats['research'],   'icon' => 'flask',  'tone' => 'ok',     'url' => Url::admin('research')],
    ['label' => 'New messages',       'value' => $stats['unread'],     'icon' => 'mail',   'tone' => 'accent', 'url' => Url::admin('messages?status=new')],
    ['label' => 'Total enquiries',    'value' => $stats['messages'],   'icon' => 'inbox',  'tone' => '',       'url' => Url::admin('messages')],
    ['label' => 'Media files',        'value' => $stats['media'],      'icon' => 'image',  'tone' => '',       'url' => Url::admin('media')],
];

$tileIcons = [
    'box'   => '<path d="M12 2 3 7v10l9 5 9-5V7z"/><path d="m3 7 9 5 9-5"/><path d="M12 22V12"/>',
    'grid'  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'flask' => '<path d="M9 2v6L4.5 17A2 2 0 0 0 6.3 20h11.4a2 2 0 0 0 1.8-3L15 8V2"/><path d="M8 2h8"/>',
    'mail'  => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>',
    'inbox' => '<path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/>',
    'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.8"/><path d="m21 15-5-5L5 21"/>',
];

$statusBadge = static fn (string $status): string => match ($status) {
    'new'       => '<span class="badge red">New</span>',
    'read'      => '<span class="badge blue">Read</span>',
    'replied'   => '<span class="badge green">Replied</span>',
    'archived'  => '<span class="badge gray">Archived</span>',
    'published' => '<span class="badge green">Published</span>',
    'draft'     => '<span class="badge amber">Draft</span>',
    default     => '<span class="badge gray">' . e($status) . '</span>',
};
?>

<div class="stat-grid">
    <?php foreach ($tiles as $tile): ?>
        <a class="stat-tile" href="<?= e($tile['url']) ?>">
            <span class="stat-tile__icon <?= e($tile['tone']) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <?= $tileIcons[$tile['icon']] ?>
                </svg>
            </span>
            <span>
                <span class="stat-tile__value"><?= e((string) $tile['value']) ?></span>
                <span class="stat-tile__label"><?= e($tile['label']) ?></span>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($needsReview !== []): ?>
    <div class="alert warning">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>
            <path d="M12 9v4"/><path d="M12 17h.01"/>
        </svg>
        <span>
            <strong>Imported content awaiting review.</strong>
            These entries were extracted from the source materials but could not be fully verified —
            please confirm their details and clear the review flag:
            <?php foreach ($needsReview as $index => $item): ?>
                <a href="<?= e(Url::admin('products/' . $item['id'])) ?>"><?= e($item['name'] ?: $item['slug']) ?></a><?= $index < count($needsReview) - 1 ? ', ' : '' ?>
            <?php endforeach; ?>
        </span>
    </div>
<?php endif; ?>

<div class="panel">
    <div class="panel__head">
        <h2>Quick actions</h2>
    </div>
    <div class="panel__body">
        <div class="btn-row">
            <a class="btn primary" href="<?= e(Url::admin('products/create')) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                New product
            </a>
            <a class="btn ghost" href="<?= e(Url::admin('categories/create')) ?>">New category</a>
            <a class="btn ghost" href="<?= e(Url::admin('research/create')) ?>">New R&amp;D project</a>
            <a class="btn ghost" href="<?= e(Url::admin('media')) ?>">Upload media</a>
            <a class="btn ghost" href="<?= e(Url::admin('settings')) ?>">Site settings</a>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h2>Latest enquiries</h2>
        <span class="spacer"></span>
        <a class="btn ghost sm" href="<?= e(Url::admin('messages')) ?>">View all</a>
    </div>

    <?php if ($recentMessages === []): ?>
        <div class="empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
            </svg>
            <h3>No enquiries yet</h3>
            <p>Messages sent through the contact form will appear here.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>From</th><th>Subject</th><th>Received</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentMessages as $message): ?>
                        <tr class="<?= $message['status'] === 'new' ? 'unread' : '' ?>">
                            <td>
                                <div class="title"><?= e($message['name']) ?></div>
                                <div class="sub ltr"><?= e($message['email']) ?></div>
                            </td>
                            <td><?= e(excerpt((string) $message['subject'], 54)) ?></td>
                            <td class="num sub nowrap"><?= e(date('Y-m-d H:i', strtotime((string) $message['created_at']))) ?></td>
                            <td><?= $statusBadge((string) $message['status']) ?></td>
                            <td class="actions">
                                <a class="btn ghost sm" href="<?= e(Url::admin('messages/' . $message['id'])) ?>">Open</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div class="grid-2" style="align-items:start">
    <div class="panel">
        <div class="panel__head"><h2>Recently updated products</h2></div>
        <?php if ($recentProducts === []): ?>
            <div class="empty"><p>No products yet.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        <?php foreach ($recentProducts as $product): ?>
                            <tr>
                                <td>
                                    <a class="title" href="<?= e(Url::admin('products/' . $product['id'])) ?>">
                                        <?= e($product['name'] ?: $product['slug']) ?>
                                    </a>
                                </td>
                                <td><?= $statusBadge((string) $product['status']) ?></td>
                                <td class="num sub nowrap"><?= e(date('Y-m-d', strtotime((string) $product['updated_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>Recently updated pages</h2></div>
        <?php if ($recentPages === []): ?>
            <div class="empty"><p>No pages yet.</p></div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        <?php foreach ($recentPages as $page): ?>
                            <tr>
                                <td><span class="title"><?= e($page['title'] ?: $page['slug']) ?></span></td>
                                <td class="num sub nowrap"><?= e(date('Y-m-d', strtotime((string) $page['updated_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
