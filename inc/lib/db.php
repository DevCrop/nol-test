<?php

if (!function_exists('blue_env')) require_once __DIR__ . '/security.bootstrap.php';
define('DB_HOST', (string) blue_env('DB_HOST', ''));
define('DB_NAME', (string) blue_env('DB_NAME', ''));
define('DB_USER', (string) blue_env('DB_USER', ''));
define('DB_PASS', (string) blue_env('DB_PASS', ''));
define('DB_PORT', (int) blue_env('DB_PORT', 3306));
define('DB_CHAR', (string) blue_env('DB_CHARSET', 'utf8mb4'));

class DB {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance !== null) {
            return self::$instance;
        }

        try {
			$dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;
			$conn = new PDO($dsn, DB_USER, DB_PASS);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            require_once __DIR__ . '/classes/Security/AuditedStatement.php';
            require_once __DIR__ . '/classes/Security/AuditLogger.php';
            $conn->setAttribute(PDO::ATTR_STATEMENT_CLASS, [\Security\AuditedStatement::class, [$conn]]);
			$conn->exec("SET NAMES utf8mb4");
            self::$instance = $conn;
        } catch (PDOException $e) {
            error_log("DB connection failed");
            return null; // 오류 발생 시 null 반환
        }

        return self::$instance;
    }

    public static function query($sql, $params = []) {
        $pdo = self::getInstance();
        if ($pdo === null) {
            return false; // DB 연결 실패 시 false 반환
        }

        try {
            $stmt = $pdo->prepare($sql);

            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $stmt->bindValue(is_int($key) ? $key + 1 : $key, $value);
                }
            }

            $stmt->execute();

            // SQL 명령어 판별 (첫 단어를 추출하여 대문자로 변환)
            $queryType = strtoupper(trim(strtok($sql, " ")));

            if ($queryType === 'SELECT') {
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? null; // 데이터 없으면 null 반환
            } elseif (in_array($queryType, ['INSERT', 'UPDATE', 'DELETE'], true)) {
                return $stmt->rowCount() > 0; // 변경된 행이 있다면 true, 없으면 false
            }

            return false; // 지원되지 않는 SQL 유형
        } catch (PDOException $e) {
            error_log("Database query failed");
            return false;
        }
    }
}
