<?php
/**
 * @var array $result
 * @var array $filters
 */

use App\Core\Csrf;
use App\Core\Url;

$items  = $result['items'];
$counts = $result['counts'];
$status = $filters['status'];

$tabs = [
    ''         => ['label' => 'All',      'count' => $counts['all']],
    'new'      => ['label' => 'New',      'count' => $counts['new']],
    'read'     => ['label' => 'Read',     'count' => $counts['read']],
    'replied'  => ['label' => 'Replied',  'count' => $counts['replied']],
    'archived' => ['label' => 'Archived', 'count' => $counts['archived']],
];

$query = static function (array $overrides) use ($filters): string {
    $params = array_filter([
        'status' => $overrides['status'] ?? $filters['status'],
        'q'      => $overrides['q']      ?? $filters['search'],
        'page'   => $overrides['page']   ?? null,
    ], static fn ($v) => $v !== '' && $v !== null);

    return Url::admin('messages') . ($params === [] ? '' : '?' . http_build_query($params));
};

$badge = static fn (string $s): string => match ($s) {
    'new'      => 'red',
    'read'     => 'blue',
    'replied'  => 'green',
    default    => 'gray',
};
?>
<div class="panel">
    <div class="panel__head">
        <h2>Contact enquiries</h2>
        <span class="spacer"></span>
        <a class="btn ghost" href="<?= e(str_replace('/messages', '/messages/export', $query([]))) ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/>
            </svg>
            Export CSV
        </a>
    </div>

    <div class="panel__body">
        <div class="tabs mb-4">
            <?php foreach ($tabs as $key => $tab): ?>
                <a class="tab" href="<?= e($query(['status' => $key, 'page' => null])) ?>"
                   <?= $status === $key ? 'aria-current="true"' : '' ?>>
                    <?= e($tab['label']) ?> <span class="count"><?= e((string) $tab['count']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form class="filters" method="get" action="<?= e(Url::admin('messages')) ?>">
            <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
            <div class="field grow">
                <label for="mq">Search</label>
                <input class="input" type="search" id="mq" name="q" value="<?= e($filters['search']) ?>"
                       placeholder="Name, e-mail, company, subject or message text">
            </div>
            <button class="btn ghost" type="submit">Search</button>
            <?php if ($filters['search'] !== ''): ?>
                <a class="btn ghost" href="<?= e($query(['q' => null])) ?>">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($items === []): ?>
        <div class="empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 6 10-6"/>
            </svg>
            <h3>No messages found</h3>
            <p>Enquiries sent through the contact form appear here.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>From</th><th>Subject</th><th>Received</th><th>Lang</th><th>Status</th><th class="right">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $message): ?>
                        <tr class="<?= $message['status'] === 'new' ? 'unread' : '' ?>">
                            <td>
                                <a class="title" href="<?= e(Url::admin('messages/' . $message['id'])) ?>"><?= e($message['name']) ?></a>
                                <div class="sub ltr"><?= e($message['email']) ?></div>
                                <?php if (!empty($message['company'])): ?>
                                    <div class="sub"><?= e($message['company']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= e(excerpt((string) $message['subject'], 60)) ?></td>
                            <td class="num sub nowrap"><?= e(date('Y-m-d H:i', strtotime((string) $message['created_at']))) ?></td>
                            <td class="sub"><?= e(strtoupper((string) $message['lang'])) ?></td>
                            <td><span class="badge <?= $badge((string) $message['status']) ?>"><?= e(ucfirst((string) $message['status'])) ?></span></td>
                            <td class="actions">
                                <div class="btn-row" style="justify-content:flex-end">
                                    <a class="btn ghost sm" href="<?= e(Url::admin('messages/' . $message['id'])) ?>">Open</a>
                                    <form class="inline-form" method="post"
                                          action="<?= e(Url::admin('messages/' . $message['id'] . '/status')) ?>">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="redirect" value="<?= e($query([])) ?>">
                                        <input type="hidden" name="status" value="archived">
                                        <button class="btn ghost sm" type="submit">Archive</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <nav class="pager" aria-label="Pagination">
                <?php for ($page = 1; $page <= $result['pages']; $page++): ?>
                    <a href="<?= e($query(['page' => $page > 1 ? $page : null])) ?>"
                       <?= $page === $result['page'] ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>
