<?php

namespace Database;

class DB
{
    /** @var \PDO|null */
    private static ?\PDO $instance = null;

    /** 내부에서 쓸 PDO */
    private static function pdo(): \PDO {
        return static::getInstance();
    }

    public static function getInstance(): \PDO
    {
        if (self::$instance instanceof \PDO) {
            return self::$instance;
        }

        // 1) 설정 읽기 (환경변수 우선, 없으면 정의된 상수 사용, 그래도 없으면 기본값)
        $host    = defined('DB_HOST') ? DB_HOST : 'db';
        $port    = defined('DB_PORT') ? DB_PORT : 3306;
        $name    = defined('DB_NAME') ? DB_NAME : 'nineonelabs';
        $user    = defined('DB_USER') ? DB_USER : 'user';
        $pass    = defined('DB_PASS') ? DB_PASS : '';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        $socket  = defined('DB_UNIX_SOCKET') ? DB_UNIX_SOCKET : '';

        // 2) DSN 구성 (Unix Socket 우선)
        if ($socket) {
            $dsn = "mysql:unix_socket={$socket};dbname={$name};charset={$charset}";
        } else {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
        }

        // 3) 옵션
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,            // native prepared statements
            \PDO::ATTR_PERSISTENT         => false,            // 필요 시 true로
        ];

        try {
            self::$instance = new \PDO($dsn, $user, $pass, $options);

            // (선택) 세션 수준 타임존 지정이 필요하면 주석 해제
            // $tz = getenv('DB_TIMEZONE') ?: (\defined('DB_TIMEZONE') ? DB_TIMEZONE : null);
            // if ($tz) {
            //     self::$instance->exec("SET time_zone = " . self::$instance->quote($tz));
            // }

        } catch (\PDOException $e) {

            // 운영에서는 민감정보 노출 금지
            error_log('[DB] Connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed.', 0, $e);
        }

        return self::$instance;
    }


    // 리스트/상세 슬러그 규칙 통일
    public static function makeSlug(string $title): string {
        $s = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (class_exists('\Normalizer')) $s = \Normalizer::normalize($s, \Normalizer::FORM_KC);
        $s = preg_replace('/[\"\'\x{2018}\x{2019}\x{201C}\x{201D}`´]/u', '', $s);
        $s = preg_replace('/[\x{00A0}\s]+/u', ' ', $s);
        $s = preg_replace('/[^가-힣a-zA-Z0-9\.\- ]/u', '', $s);
        $s = preg_replace('/\s+/u', '-', trim($s));
        $s = preg_replace('/-+/', '-', $s);
        $s = trim($s, '-');
        return mb_strtolower($s, 'UTF-8');
    }

}
