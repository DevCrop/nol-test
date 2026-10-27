-- Additive schema only. Existing accounts keep unrestricted NULL boundaries.
-- Run twice on a copy first, after the base security and temporary-password SQL.
SET @policy_from_exists=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='authority_valid_from');
SET @policy_ddl=IF(@policy_from_exists=0,'ALTER TABLE nb_admin ADD COLUMN authority_valid_from DATETIME NULL DEFAULT NULL','SELECT 1');
PREPARE policy_statement FROM @policy_ddl;
EXECUTE policy_statement;
DEALLOCATE PREPARE policy_statement;
SET @policy_until_exists=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='authority_valid_until');
SET @policy_ddl=IF(@policy_until_exists=0,'ALTER TABLE nb_admin ADD COLUMN authority_valid_until DATETIME NULL DEFAULT NULL','SELECT 1');
PREPARE policy_statement FROM @policy_ddl;
EXECUTE policy_statement;
DEALLOCATE PREPARE policy_statement;
