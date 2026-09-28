<?php

class Migrator
{
    private PDO $pdo;
    private string $dir;

    public function __construct(PDO $pdo, string $dir)
    {
        $this->pdo = $pdo;
        $this->dir = rtrim($dir, '/\\');
    }

    public function status(): array
    {
        $this->ensureLog();
        $applied = $this->applied();
        $rows = [];
        foreach ($this->files('up') as $id => $path) {
            $rows[] = [
                'id' => $id,
                'file' => basename($path),
                'applied' => isset($applied[$id]),
                'at' => $applied[$id] ?? null,
            ];
        }
        return $rows;
    }

    public function up(): array
    {
        $this->ensureLog();
        $applied = $this->applied();
        $ran = [];
        foreach ($this->files('up') as $id => $path) {
            if (isset($applied[$id])) {
                continue;
            }
            $this->runFile($path);
            $stmt = $this->pdo->prepare('INSERT INTO nb_schema_migrations (id, applied_at) VALUES (:id, NOW())');
            $stmt->execute([':id' => $id]);
            $ran[] = $id;
        }
        return $ran;
    }

    public function down(?string $targetId = null): array
    {
        $this->ensureLog();
        $applied = $this->applied();
        $ids = array_reverse(array_keys($applied));
        if ($ids === []) {
            return [];
        }
        if ($targetId === null) {
            $ids = [$ids[0]];
        } else {
            $cut = array_search($targetId, $ids, true);
            if ($cut === false) {
                throw new RuntimeException('적용된 마이그레이션이 아닙니다: ' . $targetId);
            }
            $ids = array_slice($ids, 0, $cut + 1);
        }

        $ran = [];
        foreach ($ids as $id) {
            $down = $this->dir . DIRECTORY_SEPARATOR . $id . '.down.sql';
            if (!is_file($down)) {
                throw new RuntimeException('롤백 파일이 없습니다: ' . basename($down));
            }
            $this->runFile($down);
            $del = $this->pdo->prepare('DELETE FROM nb_schema_migrations WHERE id = :id');
            $del->execute([':id' => $id]);
            $ran[] = $id;
        }
        return $ran;
    }

    public static function make(string $dir, string $name): array
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);
        $slug = trim((string) $slug, '_');
        if ($slug === '') {
            throw new RuntimeException('마이그레이션 이름을 넣으세요.');
        }
        $id = date('YmdHis') . '_' . $slug;
        $up = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $id . '.up.sql';
        $down = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $id . '.down.sql';
        if (is_file($up) || is_file($down)) {
            throw new RuntimeException('이미 있는 파일입니다: ' . $id);
        }
        file_put_contents($up, "-- up {$id}\n");
        file_put_contents($down, "-- down {$id}\n");
        return [$up, $down];
    }

    private function ensureLog(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS nb_schema_migrations (
                id varchar(128) NOT NULL,
                applied_at datetime NOT NULL,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /** @return array<string, string> */
    private function applied(): array
    {
        $rows = $this->pdo->query('SELECT id, applied_at FROM nb_schema_migrations ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row['id']] = (string) $row['applied_at'];
        }
        return $out;
    }

    /** @return array<string, string> */
    private function files(string $kind): array
    {
        $pattern = $this->dir . DIRECTORY_SEPARATOR . '*.' . $kind . '.sql';
        $paths = glob($pattern) ?: [];
        sort($paths, SORT_STRING);
        $out = [];
        foreach ($paths as $path) {
            $base = basename($path);
            if (!preg_match('/^(\d{14}_[a-z0-9_]+)\.(up|down)\.sql$/', $base, $m)) {
                continue;
            }
            $out[$m[1]] = $path;
        }
        return $out;
    }

    private function runFile(string $path): void
    {
        $sql = (string) file_get_contents($path);
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
        $parts = preg_split('/;\s*$/m', $sql) ?: [];
        foreach ($parts as $part) {
            $stmt = trim($part);
            if ($stmt === '' || strpos($stmt, '--') === 0 && strlen($stmt) < 3) {
                continue;
            }
            $lines = [];
            foreach (preg_split('/\R/', $stmt) ?: [] as $line) {
                if (preg_match('/^\s*--/', $line)) {
                    continue;
                }
                $lines[] = $line;
            }
            $stmt = trim(implode("\n", $lines));
            if ($stmt === '') {
                continue;
            }
            $this->pdo->exec($stmt);
        }
    }
}
