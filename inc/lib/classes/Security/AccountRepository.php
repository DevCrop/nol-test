<?php
namespace Security;

interface AccountRepository
{
    public function findByUid(string $uid): array;
    public function findByNo(int $no): array;
    public function all(): array;
}
