ALTER TABLE `nb_admin`
  ADD COLUMN `login_token` varchar(64) DEFAULT NULL COMMENT '단일 세션 토큰. 나중 로그인만 유효' AFTER `updated_at`;
