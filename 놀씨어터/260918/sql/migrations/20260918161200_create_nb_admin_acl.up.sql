CREATE TABLE IF NOT EXISTS `nb_admin_acl` (
  `admin_no` int NOT NULL,
  `menu_key` varchar(32) NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_update` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`admin_no`, `menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='일반 관리자 메뉴 CRUD. 최고관리자는 행 없이 전체 허용';
