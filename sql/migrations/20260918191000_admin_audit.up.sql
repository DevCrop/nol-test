CREATE TABLE IF NOT EXISTS `nb_admin_audit` (
  `no` int unsigned NOT NULL AUTO_INCREMENT,
  `sitekey` varchar(64) NOT NULL DEFAULT '',
  `actor_no` int NOT NULL DEFAULT 0,
  `actor_uid` varchar(50) NOT NULL DEFAULT '',
  `actor_ip` varchar(45) NOT NULL DEFAULT '',
  `action` varchar(16) NOT NULL,
  `entity` varchar(32) NOT NULL,
  `target_no` int NOT NULL DEFAULT 0,
  `target_label` varchar(255) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `detail_json` text,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`no`),
  KEY `idx_audit_site_created` (`sitekey`, `created_at`),
  KEY `idx_audit_entity` (`sitekey`, `entity`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='관리자 생성/수정/삭제 이력. 엑셀 26';
