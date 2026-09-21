<?php

require_once dirname(__DIR__) . '/Model/AccountModel.php';
require_once __DIR__ . '/AdminClock.php';

class AccountIdleLock
{
    public const ALLOW = 'allow';
    public const CLEAR = 'clear';
    public const LOCK = 'lock';
    public const DENY = 'deny';

    public const MSG_LOCKED = '장기 미접속으로 잠긴 계정입니다. 최고 관리자에게 해제 요청하세요.';
    public const MSG_SELF = '본인 계정의 미접속 잠금은 해제할 수 없습니다.';

    /** @var AdminClock */
    private $clock;
    /** @var int */
    private $days;

    public function __construct(?int $days = null, ?callable $now = null)
    {
        $this->days = $days !== null
            ? $days
            : (defined('ACCOUNT_IDLE_DAYS') ? (int) ACCOUNT_IDLE_DAYS : 90);
        $this->clock = new AdminClock($now);
    }

    public function days(): int
    {
        return $this->days;
    }

    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }

    public function isLocked(array $row): bool
    {
        return trim((string) ($row['idle_locked_at'] ?? '')) !== '';
    }

    public function referenceAt(array $row): ?DateTimeImmutable
    {
        return $this->clock->firstPresent($row, ['last_login_at', 'created_at']);
    }

    public function isDue(array $row): bool
    {
        return $this->clock->reached($this->referenceAt($row), $this->days);
    }

    /**
     * 순수 판정. DB를 건드리지 않는다.
     * allow: 통과 / clear: 마지막 최고라 잠금을 풀고 통과 / lock: 지금 잠그고 거절 / deny: 이미 잠김
     */
    public function decide(array $row, int $otherLiveSupers): string
    {
        $isLastSuper = (int) ($row['role_id'] ?? 0) === 1 && $otherLiveSupers < 1;
        if ($isLastSuper) {
            return $this->isLocked($row) ? self::CLEAR : self::ALLOW;
        }
        if ($this->isLocked($row)) {
            return self::DENY;
        }
        if ($this->isDue($row)) {
            return self::LOCK;
        }
        return self::ALLOW;
    }

    public function guard(array $row): void
    {
        $id = (int) ($row['no'] ?? 0);
        $others = AccountModel::countLiveSupers($id);
        $decision = $this->decide($row, $others);
        $at = $this->clock->stamp();

        if ($decision === self::CLEAR) {
            AccountModel::unlockIdle($id, $at);
            return;
        }
        if ($decision === self::ALLOW) {
            return;
        }
        if ($decision === self::LOCK && $id > 0) {
            AccountModel::lockIdle($id, $at);
        }
        throw new RuntimeException(self::MSG_LOCKED);
    }

    public function unlock(int $id, int $actorId): void
    {
        if ($id < 1) {
            throw new RuntimeException('해제할 계정이 없습니다.');
        }
        if ($id === $actorId) {
            throw new RuntimeException(self::MSG_SELF);
        }
        AccountModel::unlockIdle($id, $this->clock->stamp());
    }

    public function touchLogin(int $id): void
    {
        if ($id < 1) {
            return;
        }
        AccountModel::stampLastLogin($id, $this->clock->stamp());
    }
}
