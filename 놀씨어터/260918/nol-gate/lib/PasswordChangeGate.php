<?php

require_once dirname(__DIR__) . '/Model/AccountModel.php';
require_once __DIR__ . '/AuthSession.php';
require_once __DIR__ . '/PasswordExpiry.php';

class PasswordChangeGate
{
    public const REASON_RESET = 'reset';
    public const REASON_EXPIRED = 'expired';
    public const SESSION_REASON = 'pwd_force_reason';

    public const MSG = '관리자가 변경한 비밀번호입니다. 새 비밀번호로 바꾼 뒤 다시 로그인하세요.';
    public const MSG_EXPIRED = '비밀번호 사용 기간이 지났습니다. 새 비밀번호로 바꾼 뒤 다시 로그인하세요.';
    public const MSG_SAME = '현재 비밀번호와 다른 비밀번호로 변경하세요.';

    /** @var PasswordExpiry */
    private $expiry;

    public function __construct(?PasswordExpiry $expiry = null)
    {
        $this->expiry = $expiry ?? new PasswordExpiry();
    }

    public function mustChange(array $row): bool
    {
        $v = $row['must_change_password'] ?? 0;
        return $v === 1 || $v === '1' || $v === true;
    }

    /**
     * 순수 판정. DB를 건드리지 않는다.
     * @return array{force:bool, reason:?string}
     */
    public function evaluate(array $row, bool $onAllowedSurface): array
    {
        $reason = null;
        if ($this->mustChange($row)) {
            $reason = self::REASON_RESET;
        } elseif ($this->expiry->isDue($row)) {
            $reason = self::REASON_EXPIRED;
        }
        if ($reason === null || $onAllowedSurface) {
            return ['force' => false, 'reason' => $reason];
        }
        return ['force' => true, 'reason' => $reason];
    }

    public function isAllowedSurface(): bool
    {
        if (AuthSession::isAuthSurface()) {
            return true;
        }
        $script = AuthSession::scriptName();
        if (preg_match('#/pages/setting/pwd\.php$#', $script)) {
            return true;
        }
        if (
            strpos($script, '/pages/setting/ajax/setting.process.php') !== false
            && strtolower((string) ($_POST['mode'] ?? '')) === 'pwd.change'
        ) {
            return true;
        }
        return false;
    }

    public function message(?string $reason): string
    {
        return $reason === self::REASON_EXPIRED ? self::MSG_EXPIRED : self::MSG;
    }

    public function assert(): void
    {
        $no = (int) ($_SESSION['no_adm_login_no'] ?? 0);
        if ($no < 1) {
            return;
        }
        $state = $this->evaluate(AccountModel::passwordGateRow($no), $this->isAllowedSurface());
        $this->syncSession($state['reason']);
        if (!$state['force']) {
            return;
        }
        $this->bounce($state['reason']);
    }

    public function landingAfterLogin(): string
    {
        $pages = (string) ($GLOBALS['NO_ADMIN_PAGES_BASE'] ?? '');
        $no = (int) ($_SESSION['no_adm_login_no'] ?? 0);
        $row = $no > 0 ? AccountModel::passwordGateRow($no) : [];
        $state = $this->evaluate($row, false);
        $this->syncSession($state['reason']);
        if ($state['reason'] !== null) {
            return $pages . '/setting/pwd.php';
        }
        return $pages . '/board/board.list.php';
    }

    public function markResetByAdmin(int $targetId, int $actorId): void
    {
        if ($targetId < 1 || $targetId === $actorId) {
            return;
        }
        AccountModel::markPasswordReset($targetId);
    }

    public function complete(int $id): void
    {
        AccountModel::finishPasswordChange($id, $this->expiry->now()->format('Y-m-d H:i:s'));
        unset($_SESSION[self::SESSION_REASON]);
    }

    private function syncSession(?string $reason): void
    {
        if ($reason === null) {
            unset($_SESSION[self::SESSION_REASON]);
            return;
        }
        $_SESSION[self::SESSION_REASON] = $reason;
    }

    private function bounce(?string $reason): void
    {
        $pages = (string) ($GLOBALS['NO_ADMIN_PAGES_BASE'] ?? '');
        $msg = $this->message($reason);
        if (AuthSession::wantsJson()) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
            }
            echo json_encode(['success' => false, 'message' => $msg]);
            exit;
        }
        if (!headers_sent()) {
            header('Location: ' . $pages . '/setting/pwd.php');
        }
        exit;
    }
}
