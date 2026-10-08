<?php
namespace Security;

/** Database reads only; no authentication, session or response side effects. */
final class PdoAccountRepository implements AccountRepository
{
    private $db;
    private $site;

    public function __construct(\PDO $db, string $site)
    {
        $this->db = $db;
        $this->site = $site;
    }

    public function findByUid(string $uid): array
    {
        $stmt = $this->db->prepare('SELECT * FROM nb_admin WHERE uid = :uid AND sitekey = :sitekey LIMIT 1');
        $stmt->execute(['uid' => $uid, 'sitekey' => $this->site]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    public function findByNo(int $no): array
    {
        $stmt = $this->db->prepare('SELECT * FROM nb_admin WHERE no = ? AND sitekey = ? LIMIT 1');
        $stmt->execute([$no, $this->site]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    public function all(): array
    {
        $stmt = $this->db->prepare('SELECT no, uid, uname, email, active_status, role_code, login_fail_count, login_locked_until, last_login_at, idle_locked_at, password_changed_at, password_must_change, created_at FROM nb_admin WHERE sitekey = ? ORDER BY no ASC');
        $stmt->execute([$this->site]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
