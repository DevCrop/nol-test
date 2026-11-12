-- BlueSquare only. Additive and rerunnable; no account data or implicit grants.
-- Apply after the base security, temporary-password and authority-period schema.
SET @menu_permissions_exists=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='menu_permissions');
SET @menu_permissions_ddl=IF(@menu_permissions_exists=0,'ALTER TABLE nb_admin ADD COLUMN menu_permissions TEXT NULL','SELECT 1');
PREPARE menu_permissions_statement FROM @menu_permissions_ddl;
EXECUTE menu_permissions_statement;
DEALLOCATE PREPARE menu_permissions_statement;
-- NULL means no menu grants for regular accounts. Super administrators retain
-- full access. Configure existing regular accounts explicitly before deployment.
