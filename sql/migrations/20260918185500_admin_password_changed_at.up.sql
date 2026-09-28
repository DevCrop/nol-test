ALTER TABLE `nb_admin`
  ADD COLUMN `password_changed_at` datetime DEFAULT NULL COMMENT '마지막 본인 비밀번호 변경. 90일 주기 기준' AFTER `must_change_password`;

UPDATE `nb_admin`
SET `password_changed_at` = NOW()
WHERE `password_changed_at` IS NULL;
