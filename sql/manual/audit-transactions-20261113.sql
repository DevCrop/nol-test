-- BlueSquare audit atomicity prerequisite. Incremental engine changes only.
-- Back up the CURRENT DB and pause administrator writes before running ALTER.
-- DDL may lock/rebuild tables and commits implicitly. Do not import an old dump.
-- Run on dbusrbluesq0211. All final flags must equal 1 before uploading the patch.
-- Preserve legacy zero dates during engine rebuild without changing global/app mode.
SET @audit_saved_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = TRIM(BOTH ',' FROM REPLACE(REPLACE(REPLACE(REPLACE(CONCAT(',', @audit_saved_sql_mode, ','), ',STRICT_TRANS_TABLES,', ','), ',STRICT_ALL_TABLES,', ','), ',NO_ZERO_DATE,', ','), ',NO_ZERO_IN_DATE,', ','));
SET @audit_database_ok = (DATABASE()='dbusrbluesq0211');
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_banner');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_banner ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_board ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_category');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_board_category ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_comment');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_board_comment ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_lev_manage');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_board_lev_manage ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_manage');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_board_manage ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_data');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_data ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_opera');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_opera ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_popup');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_popup ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_request');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_request ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_request_manage');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_request_manage ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_siteinfo');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_siteinfo ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_works');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_works ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET @audit_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit');
SET @audit_ddl = IF(@audit_database_ok AND @audit_engine IS NOT NULL AND @audit_engine <> 'InnoDB', 'ALTER TABLE nb_admin_audit ENGINE=InnoDB', 'SELECT 1');
PREPARE audit_engine_statement FROM @audit_ddl;
EXECUTE audit_engine_statement;
DEALLOCATE PREPARE audit_engine_statement;
SET SESSION sql_mode = @audit_saved_sql_mode;
SELECT @audit_database_ok AS database_correct,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_banner'),0) AS nb_banner_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board'),0) AS nb_board_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_category'),0) AS nb_board_category_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_comment'),0) AS nb_board_comment_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_lev_manage'),0) AS nb_board_lev_manage_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_board_manage'),0) AS nb_board_manage_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_data'),0) AS nb_data_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_opera'),0) AS nb_opera_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_popup'),0) AS nb_popup_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_request'),0) AS nb_request_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_request_manage'),0) AS nb_request_manage_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_siteinfo'),0) AS nb_siteinfo_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_works'),0) AS nb_works_ready,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit'),0) AS nb_admin_audit_ready;
