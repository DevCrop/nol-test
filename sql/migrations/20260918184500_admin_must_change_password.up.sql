ALTER TABLE `nb_admin`
  ADD COLUMN `must_change_password` tinyint(1) NOT NULL DEFAULT 0 COMMENT '관리자가 비번을 대신 넣은 뒤 본인 변경 강제' AFTER `idle_locked_at`;
