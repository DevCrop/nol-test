-- Apply after the existing NOL security SQL. MySQL DDL rebuilds nb_request once.
SET @rental_engine = (SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'nb_request');
SET @rental_ddl = IF(@rental_engine = 'InnoDB', 'SELECT 1', 'ALTER TABLE nb_request ENGINE=InnoDB');
PREPARE rental_migration FROM @rental_ddl;
EXECUTE rental_migration;
DEALLOCATE PREPARE rental_migration;
SELECT (COUNT(*) = 2) AS rental_privacy_transaction_ready
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('nb_request', 'nb_admin_privacy_access') AND ENGINE = 'InnoDB';
