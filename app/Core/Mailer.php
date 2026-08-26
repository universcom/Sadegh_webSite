<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Outgoing mail via SMTP (PHPMailer, vendored) with a PHP mail() fallback.
 *
 * Sending is always best-effort: callers persist their data first and treat a
 * false return as "notify the operator later", never as a request failure.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $htmlBody, string $replyTo = '', string $replyToName = ''): bool
    {
        $config = Config::get('mail');

        if (trim($to) === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            if (($config['mailer'] ?? 'mail') === 'smtp' && ($config['host'] ?? '') !== '') {
                return self::sendSmtp($to, $subject, $htmlBody, $replyTo, $replyToName, $config);
            }

            return self::sendNative($to, $subject, $htmlBody, $replyTo, $config);
        } catch (\Throwable $e) {
            // Logger redacts credentials before anything reaches disk.
            Logger::error('Mail delivery failed', $e, ['to' => self::maskEmail($to)]);

            return false;
        }
    }

    private static function sendSmtp(string $to, string $subject, string $html, string $replyTo, string $replyToName, array $config): bool
    {
        require_once Config::get('app.base_path') . '/app/Vendor/PHPMailer/Exception.php';
        require_once Config::get('app.base_path') . '/app/Vendor/PHPMailer/PHPMailer.php';
        require_once Config::get('app.base_path') . '/app/Vendor/PHPMailer/SMTP.php';

        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = (string) $config['host'];
        $mail->Port       = (int) ($config['port'] ?: 587);
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;
        $mail->Encoding   = PHPMailer::ENCODING_BASE64;
        $mail->Timeout    = 15;
        $mail->SMTPDebug  = 0;

        $username = (string) ($config['username'] ?? '');
        if ($username !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = (string) ($config['password'] ?? '');
        }

        $encryption = strtolower((string) ($config['encryption'] ?? ''));
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom((string) $config['from_address'], (string) $config['from_name']);
        $mail->addAddress($to);

        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo, $replyToName);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html;
        $mail->AltBody = self::toPlainText($html);

        try {
            return $mail->send();
        } catch (MailException $e) {
            Logger::error('SMTP send failed', $e, ['to' => self::maskEmail($to)]);

            return false;
        }
    }

    private static function sendNative(string $to, string $subject, string $html, string $replyTo, array $config): bool
    {
        if (!function_exists('mail')) {
            return false;
        }

        $from = (string) $config['from_address'];
        $name = (string) $config['from_name'];

        // Header injection guard: no CR/LF may reach a header value.
        $clean = static fn (string $v): string => (string) preg_replace('/[\r\n]+/', ' ', $v);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . self::encodeHeader($clean($name)) . ' <' . $clean($from) . '>',
        ];

        if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $clean($replyTo);
        }

        return @mail(
            $clean($to),
            self::encodeHeader($clean($subject)),
            $html,
            implode("\r\n", $headers)
        );
    }

    /** RFC 2047 encoding so Persian/Arabic subjects survive transport. */
    private static function encodeHeader(string $value): string
    {
        return preg_match('/[^\x20-\x7E]/', $value)
            ? '=?UTF-8?B?' . base64_encode($value) . '?='
            : $value;
    }

    private static function toPlainText(string $html): string
    {
        $text = preg_replace('#<br\s*/?>#i', "\n", $html) ?? $html;
        $text = preg_replace('#</(p|div|tr|h[1-6])>#i', "\n", $text) ?? $text;

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));
    }

    /** Keep log lines free of full addresses. */
    private static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        return mb_substr($local, 0, 2) . '***@' . $domain;
    }
}
