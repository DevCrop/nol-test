ALTER TABLE `nb_admin`
  ADD COLUMN `last_login_at` datetime DEFAULT NULL COMMENT '관리자 로그인 완료 시각' AFTER `login_token`,
  ADD COLUMN `idle_locked_at` datetime DEFAULT NULL COMMENT '장기 미접속 자동 잠금 시각. 수동 비활성과 분리' AFTER `last_login_at`;

UPDATE `nb_admin`
SET `last_login_at` = NOW()
WHERE `last_login_at` IS NULL;
