<?php

require_once dirname(__DIR__) . '/Model/AuditModel.php';
require_once __DIR__ . '/AdminClock.php';

class AuditLogger
{
    public const ACTIONS = ['create', 'update', 'delete'];

    public const ENTITIES = [
        'account' => '계정',
        'board' => '게시글',
        'banner' => '배너',
        'popup' => '팝업',
        'faq' => 'FAQ',
        'works' => "WHAT'S ON",
        'privacy' => '개인정보처리방침',
        'seo' => 'SEO',
        'setting' => '사이트정보',
        'inquiry' => '대관신청',
    ];

    /**
     * 성공한 쓰기에만 호출. 실패해도 본 작업은 막지 않는다.
     *
     * @param array<string, mixed> $detail
     */
    public static function record(string $action, string $entity, $targetNo, string $label, array $detail = []): void
    {
        try {
            if (!in_array($action, self::ACTIONS, true) || !isset(self::ENTITIES[$entity])) {
                return;
            }
            global $NO_SITE_UNIQUE_KEY;
            $label = self::clip($label, 255);
            $summary = self::actionLabel($action) . ' · ' . self::entityLabel($entity);
            if ($label !== '') {
                $summary = self::clip($summary . ' · ' . $label, 255);
            }
            AuditModel::insert([
                'sitekey' => (string) $NO_SITE_UNIQUE_KEY,
                'actor_no' => (int) ($_SESSION['no_adm_login_no'] ?? 0),
                'actor_uid' => (string) ($_SESSION['no_adm_login_uid'] ?? ''),
                'actor_ip' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                'action' => $action,
                'entity' => $entity,
                'target_no' => (int) $targetNo,
                'target_label' => $label,
                'summary' => $summary,
                'detail_json' => self::encodeDetail($detail),
                'created_at' => (new AdminClock())->stamp(),
            ]);
        } catch (Throwable $e) {
            error_log('[audit] ' . $e->getMessage());
        }
    }

    public static function lastId(): int
    {
        try {
            return (int) DB::getInstance()->lastInsertId();
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function ifOk($ok, string $action, string $entity, $targetNo, string $label, array $detail = []): void
    {
        if ($ok) {
            self::record($action, $entity, $targetNo, $label, $detail);
        }
    }

    public static function actionLabel(string $action): string
    {
        $map = ['create' => '생성', 'update' => '수정', 'delete' => '삭제'];
        return $map[$action] ?? $action;
    }

    public static function entityLabel(string $entity): string
    {
        return self::ENTITIES[$entity] ?? $entity;
    }

    /** @param array<string, mixed> $detail */
    private static function encodeDetail(array $detail): ?string
    {
        if ($detail === []) {
            return null;
        }
        $clean = self::scrub($detail);
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (!is_string($json) || strlen($json) > 8000) {
            return null;
        }
        return $json;
    }

    /** @param mixed $value */
    private static function scrub($value)
    {
        $deny = ['upwd', 'upwd_confirm', 'pwd', 'pwd_old', 'pwd_new', 'pwd_new_confirm', 'password', 'login_token', 'footer_ssn'];
        if (!is_array($value)) {
            return $value;
        }
        $out = [];
        foreach ($value as $k => $v) {
            if (in_array((string) $k, $deny, true)) {
                continue;
            }
            $out[$k] = is_array($v) ? self::scrub($v) : $v;
        }
        return $out;
    }

    private static function clip(string $s, int $max): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($s, 0, $max);
        }
        return substr($s, 0, $max);
    }
}
