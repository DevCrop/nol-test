-- Incremental change only; preserve original dumps and all account values.
-- Apply after a fresh backup, during a maintenance window (ALTER can lock tables).
-- Safe to run repeatedly. Do not automatically revert to a nontransactional engine.
SET @admin_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin');
SET @admin_engine_ddl = IF(@admin_engine='InnoDB', 'SELECT 1', 'ALTER TABLE nb_admin ENGINE=InnoDB');
PREPARE admin_engine_statement FROM @admin_engine_ddl;
EXECUTE admin_engine_statement;
DEALLOCATE PREPARE admin_engine_statement;
