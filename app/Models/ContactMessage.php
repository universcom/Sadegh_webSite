<?php
declare(strict_types=1);

namespace App\Models;

final class ContactMessage extends Model
{
    public const STATUSES = ['new', 'read', 'replied', 'archived'];

    public static function create(array $data): int
    {
        return self::db()->insert('contact_messages', $data);
    }

    /**
     * @return array{items:array,total:int,pages:int,page:int,counts:array<string,int>}
     */
    public static function listing(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['1 = 1'];
        $params = [];

        if (!empty($filters['status']) && in_array($filters['status'], self::STATUSES, true)) {
            $where[]          = 'status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(name LIKE :search OR email LIKE :search OR subject LIKE :search
                         OR company LIKE :search OR phone LIKE :search OR message LIKE :search)';
            $params['search'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], (string) $filters['search']) . '%';
        }

        $clause  = implode(' AND ', $where);
        $total   = self::db()->count('SELECT COUNT(*) FROM contact_messages WHERE ' . $clause, $params);
        $perPage = max(1, $perPage);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, $page), $pages);

        $items = self::db()->all(
            'SELECT * FROM contact_messages WHERE ' . $clause . '
             ORDER BY created_at DESC, id DESC
             LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage),
            $params
        );

        return [
            'items'  => $items,
            'total'  => $total,
            'pages'  => $pages,
            'page'   => $page,
            'counts' => self::countsByStatus(),
        ];
    }

    /** @return array<string,int> */
    public static function countsByStatus(): array
    {
        $counts = array_fill_keys(self::STATUSES, 0);
        $counts['all'] = 0;

        foreach (self::db()->all('SELECT status, COUNT(*) AS total FROM contact_messages GROUP BY status') as $row) {
            $counts[$row['status']] = (int) $row['total'];
            $counts['all']         += (int) $row['total'];
        }

        return $counts;
    }

    public static function find(int $id): ?array
    {
        return self::db()->first('SELECT * FROM contact_messages WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    /** Mark as read on first open, without clobbering a later status. */
    public static function markRead(int $id): void
    {
        self::db()->run(
            "UPDATE contact_messages
             SET status = 'read', read_at = COALESCE(read_at, NOW())
             WHERE id = :id AND status = 'new'",
            ['id' => $id]
        );
    }

    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $data = ['status' => $status];

        if ($status === 'replied') {
            $data['replied_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'new') {
            $data['read_at'] = null;
        }
        if (in_array($status, ['read', 'replied', 'archived'], true)) {
            $data['read_at'] = date('Y-m-d H:i:s');
        }

        return self::db()->update('contact_messages', $data, 'id = :id', ['id' => $id]) >= 0;
    }

    public static function delete(int $id): void
    {
        self::db()->delete('contact_messages', 'id = :id', ['id' => $id]);
    }

    public static function unreadCount(): int
    {
        return self::db()->count("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'");
    }

    public static function total(): int
    {
        return self::db()->count('SELECT COUNT(*) FROM contact_messages');
    }

    public static function recent(int $limit = 5): array
    {
        return self::db()->all(
            'SELECT id, name, email, subject, status, created_at
             FROM contact_messages ORDER BY created_at DESC LIMIT ' . max(1, $limit)
        );
    }

    /**
     * Rate-limit the public form: at most N submissions per IP per hour.
     */
    public static function recentFromIp(string $ip, int $minutes = 60): int
    {
        return self::db()->count(
            'SELECT COUNT(*) FROM contact_messages WHERE ip_address = :ip AND created_at > :since',
            ['ip' => $ip, 'since' => date('Y-m-d H:i:s', time() - ($minutes * 60))]
        );
    }

    /** Export the current filter selection as CSV (UTF-8 with BOM for Excel). */
    public static function toCsv(array $filters = []): string
    {
        $result = self::listing($filters, 1, 5000);
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, ['ID', 'Date', 'Name', 'Email', 'Phone', 'Company', 'Subject', 'Message', 'Status', 'Language']);

        foreach ($result['items'] as $row) {
            fputcsv($handle, [
                $row['id'],
                $row['created_at'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['company'],
                $row['subject'],
                // Keep the message on one CSV line.
                preg_replace('/\s+/u', ' ', (string) $row['message']),
                $row['status'],
                $row['lang'],
            ]);
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
