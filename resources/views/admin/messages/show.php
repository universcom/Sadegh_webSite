<?php
/**
 * @var array      $message
 * @var array|null $product
 */

use App\Core\Csrf;
use App\Core\Url;

$badge = static fn (string $s): string => match ($s) {
    'new'     => 'red',
    'read'    => 'blue',
    'replied' => 'green',
    default   => 'gray',
};

$replySubject = 'Re: ' . $message['subject'];
$mailto = 'mailto:' . rawurlencode((string) $message['email'])
    . '?subject=' . rawurlencode($replySubject);
?>
<div class="panel">
    <div class="panel__head">
        <h2><?= e($message['subject']) ?></h2>
        <span class="badge <?= $badge((string) $message['status']) ?>"><?= e(ucfirst((string) $message['status'])) ?></span>
        <span class="spacer"></span>
        <a class="btn ghost sm" href="<?= e(Url::admin('messages')) ?>">← Back to messages</a>
    </div>

    <div class="panel__body">
        <div class="message-meta">
            <div class="meta-item">
                <div class="meta-item__label">From</div>
                <div class="meta-item__value"><?= e($message['name']) ?></div>
            </div>

            <div class="meta-item">
                <div class="meta-item__label">E-mail</div>
                <div class="meta-item__value">
                    <a class="ltr" href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a>
                    <button class="btn ghost sm" type="button" data-copy="<?= e($message['email']) ?>" title="Copy e-mail">Copy</button>
                </div>
            </div>

            <?php if (!empty($message['phone'])): ?>
                <div class="meta-item">
                    <div class="meta-item__label">Phone</div>
                    <div class="meta-item__value">
                        <a class="ltr" href="tel:<?= e(preg_replace('/[^0-9+]/', '', (string) $message['phone'])) ?>">
                            <?= e($message['phone']) ?>
                        </a>
                        <button class="btn ghost sm" type="button" data-copy="<?= e($message['phone']) ?>" title="Copy phone">Copy</button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($message['company'])): ?>
                <div class="meta-item">
                    <div class="meta-item__label">Company</div>
                    <div class="meta-item__value"><?= e($message['company']) ?></div>
                </div>
            <?php endif; ?>

            <div class="meta-item">
                <div class="meta-item__label">Received</div>
                <div class="meta-item__value num"><?= e(date('Y-m-d H:i', strtotime((string) $message['created_at']))) ?></div>
            </div>

            <div class="meta-item">
                <div class="meta-item__label">Site language</div>
                <div class="meta-item__value"><?= e(strtoupper((string) $message['lang'])) ?></div>
            </div>

            <?php if ($product !== null): ?>
                <div class="meta-item">
                    <div class="meta-item__label">Product enquiry</div>
                    <div class="meta-item__value">
                        <a href="<?= e(Url::admin('products/' . $product['id'])) ?>">
                            <?= e($product['name'] ?: $product['slug']) ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="meta-item">
                <div class="meta-item__label">Notification e-mail</div>
                <div class="meta-item__value">
                    <?= !empty($message['notified'])
                        ? '<span class="badge green">Delivered</span>'
                        : '<span class="badge gray">Not sent</span>' ?>
                </div>
            </div>
        </div>

        <h3 class="mb-3" style="font-size:.95rem">Message</h3>
        <?php /* Rendered escaped inside a pre-wrap block: the sender's text is never treated as HTML. */ ?>
        <div class="message-body" dir="auto"><?= e($message['message']) ?></div>

        <p class="small muted mt-4">
            Sent from <span class="ltr"><?= e($message['ip_address'] ?? '—') ?></span>
        </p>
    </div>

    <div class="panel__foot">
        <a class="btn primary" href="<?= e($mailto) ?>">Reply by e-mail</a>

        <?php foreach (['read' => 'Mark unread→read', 'replied' => 'Mark replied', 'archived' => 'Archive', 'new' => 'Mark as new'] as $status => $label): ?>
            <?php if ($status === $message['status']) { continue; } ?>
            <form class="inline-form" method="post" action="<?= e(Url::admin('messages/' . $message['id'] . '/status')) ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="status" value="<?= e($status) ?>">
                <button class="btn ghost" type="submit"><?= e($label) ?></button>
            </form>
        <?php endforeach; ?>

        <span style="margin-left:auto"></span>
        <form class="inline-form" method="post" action="<?= e(Url::admin('messages/' . $message['id'] . '/delete')) ?>"
              data-confirm="Delete this message permanently?">
            <?= Csrf::field() ?>
            <button class="btn danger" type="submit">Delete</button>
        </form>
    </div>
</div>
