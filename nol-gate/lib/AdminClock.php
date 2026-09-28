<?php

class AdminClock
{
    /** @var callable():DateTimeImmutable */
    private $clock;

    public function __construct(?callable $clock = null)
    {
        $this->clock = $clock ?? static function (): DateTimeImmutable {
            return new DateTimeImmutable('now');
        };
    }

    public function now(): DateTimeImmutable
    {
        return ($this->clock)();
    }

    public function stamp(): string
    {
        return $this->now()->format('Y-m-d H:i:s');
    }

    public static function parse(string $raw): ?DateTimeImmutable
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $raw);
        if ($dt instanceof DateTimeImmutable) {
            return $dt;
        }
        try {
            return new DateTimeImmutable($raw);
        } catch (Exception $e) {
            return null;
        }
    }

    /** @param list<string> $keys */
    public function firstPresent(array $row, array $keys): ?DateTimeImmutable
    {
        foreach ($keys as $key) {
            $dt = self::parse((string) ($row[$key] ?? ''));
            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
        }
        return null;
    }

    public function reached(?DateTimeImmutable $from, int $days): bool
    {
        if ($days < 1 || $from === null) {
            return false;
        }
        return $this->now() >= $from->modify('+' . $days . ' days');
    }
}
