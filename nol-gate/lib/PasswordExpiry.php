<?php

require_once __DIR__ . '/AdminClock.php';

class PasswordExpiry
{
    public const ALLOW = 'allow';
    public const DUE = 'due';

    /** @var AdminClock */
    private $clock;
    /** @var int */
    private $days;

    public function __construct(?int $days = null, ?callable $now = null)
    {
        $this->days = $days !== null
            ? $days
            : (defined('PASSWORD_MAX_DAYS') ? (int) PASSWORD_MAX_DAYS : 90);
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

    public function referenceAt(array $row): ?DateTimeImmutable
    {
        return $this->clock->firstPresent($row, ['password_changed_at', 'created_at']);
    }

    public function isDue(array $row): bool
    {
        return $this->clock->reached($this->referenceAt($row), $this->days);
    }

    public function decide(array $row): string
    {
        return $this->isDue($row) ? self::DUE : self::ALLOW;
    }
}
