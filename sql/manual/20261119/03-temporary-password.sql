-- Incremental schema only. Back up DB first. Execute BEFORE deploying temporary-password code.
-- Does not restore dumps, change existing passwords, or mark existing accounts temporary.
-- Equivalent to migration 20261021090000; repeat execution is safe.
SET @temporary_column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='temporary_password_expires_at');
SET @temporary_ddl = IF(@temporary_column_exists=0, 'ALTER TABLE nb_admin ADD COLUMN temporary_password_expires_at DATETIME NULL DEFAULT NULL', 'SELECT 1');
PREPARE temporary_statement FROM @temporary_ddl;
EXECUTE temporary_statement;
DEALLOCATE PREPARE temporary_statement;
