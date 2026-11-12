-- BlueSquare incremental engine change only. NO dump import / DELETE / TRUNCATE.
-- Back up current DB first. Pause rental writes during ALTER (DDL may lock/rebuild).
-- Verify the selected DB and all result flags = 1. Repeated runs are safe.
SET @rental_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_request');
SET @rental_database_ok = (DATABASE()='dbusrbluesq0211');
SET @rental_engine_ddl = IF(@rental_database_ok AND @rental_engine IS NOT NULL AND @rental_engine <> 'InnoDB', 'ALTER TABLE nb_request ENGINE=InnoDB', 'SELECT 1');
PREPARE rental_engine_statement FROM @rental_engine_ddl;
EXECUTE rental_engine_statement;
DEALLOCATE PREPARE rental_engine_statement;
SELECT @rental_database_ok AS database_correct,
       COALESCE((SELECT ENGINE='InnoDB' FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_request'),0) AS rental_transactions_ready;
