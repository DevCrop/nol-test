CREATE TABLE IF NOT EXISTS `nb_admin_privacy_access` (
  `no` int unsigned NOT NULL AUTO_INCREMENT,
  `sitekey` varchar(64) NOT NULL DEFAULT '',
  `actor_no` int NOT NULL DEFAULT 0,
  `actor_uid` varchar(50) NOT NULL DEFAULT '',
  `actor_ip` varchar(45) NOT NULL DEFAULT '',
  `action` varchar(16) NOT NULL,
  `entity` varchar(32) NOT NULL,
  `target_no` int NOT NULL DEFAULT 0,
  `subject_label` varchar(255) NOT NULL DEFAULT '',
  `task` varchar(255) NOT NULL DEFAULT '',
  `reason` varchar(500) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`no`),
  KEY `idx_privacy_site_created` (`sitekey`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='개인정보 열람/다운로드 접속기록. 엑셀 21';
