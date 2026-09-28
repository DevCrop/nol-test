-- noltheater: read-only preflight; run without --force; stop at first error.
-- Precondition: missing_admin_table
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin')=1, 'SELECT 1', 'RELEASE_ABORT_missing_admin_table');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: wrong_project_or_empty_admin
SET @release_check = IF((SELECT COUNT(*) FROM nb_admin WHERE sitekey='NOLTHE')>0, 'SELECT 1', 'RELEASE_ABORT_wrong_project_or_empty_admin');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='no' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_sitekey_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='sitekey' AND COLUMN_TYPE='varchar(6)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_sitekey_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_uid_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='uid' AND COLUMN_TYPE='varchar(25)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_uid_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_upwd_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='upwd' AND COLUMN_TYPE='varchar(255)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_upwd_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_uname_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='uname' AND COLUMN_TYPE='varchar(25)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_uname_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_active_status_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='active_status' AND COLUMN_TYPE='enum(''N'',''Y'')' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_active_status_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_role_id_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='role_id' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_role_id_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_email_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='email' AND COLUMN_TYPE='varchar(100)' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_email_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_phone_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='phone' AND COLUMN_TYPE='varchar(20)' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_phone_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_created_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='created_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_created_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_updated_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='updated_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_updated_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_login_token_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='login_token')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='login_token' AND COLUMN_TYPE='varchar(64)' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_login_token_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_last_login_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='last_login_at')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='last_login_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_last_login_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_idle_locked_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='idle_locked_at')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='idle_locked_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_idle_locked_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_must_change_password_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='must_change_password')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='must_change_password' AND COLUMN_TYPE='tinyint(1)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_must_change_password_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_password_changed_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='password_changed_at')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='password_changed_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_password_changed_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='no' AND COLUMN_TYPE='int unsigned' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_sitekey_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='sitekey' AND COLUMN_TYPE='varchar(64)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_sitekey_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_actor_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='actor_no' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_actor_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_actor_uid_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='actor_uid' AND COLUMN_TYPE='varchar(50)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_actor_uid_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_actor_ip_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='actor_ip' AND COLUMN_TYPE='varchar(45)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_actor_ip_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_action_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='action' AND COLUMN_TYPE='varchar(16)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_action_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_entity_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='entity' AND COLUMN_TYPE='varchar(32)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_entity_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_target_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='target_no' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_target_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_target_label_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='target_label' AND COLUMN_TYPE='varchar(255)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_target_label_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_summary_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='summary' AND COLUMN_TYPE='varchar(255)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_summary_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_detail_json_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='detail_json' AND COLUMN_TYPE='text' AND IS_NULLABLE='YES')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_detail_json_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_audit_created_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_audit' AND COLUMN_NAME='created_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_audit_created_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='no' AND COLUMN_TYPE='int unsigned' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_sitekey_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='sitekey' AND COLUMN_TYPE='varchar(64)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_sitekey_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_actor_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='actor_no' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_actor_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_actor_uid_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='actor_uid' AND COLUMN_TYPE='varchar(50)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_actor_uid_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_actor_ip_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='actor_ip' AND COLUMN_TYPE='varchar(45)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_actor_ip_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_action_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='action' AND COLUMN_TYPE='varchar(16)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_action_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_entity_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='entity' AND COLUMN_TYPE='varchar(32)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_entity_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_target_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='target_no' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_target_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_subject_label_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='subject_label' AND COLUMN_TYPE='varchar(255)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_subject_label_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_task_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='task' AND COLUMN_TYPE='varchar(255)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_task_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_reason_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='reason' AND COLUMN_TYPE='varchar(500)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_reason_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_privacy_access_created_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_privacy_access' AND COLUMN_NAME='created_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_privacy_access_created_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_schema_migrations_id_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_schema_migrations')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_schema_migrations' AND COLUMN_NAME='id' AND COLUMN_TYPE='varchar(128)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_schema_migrations_id_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_schema_migrations_applied_at_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_schema_migrations')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_schema_migrations' AND COLUMN_NAME='applied_at' AND COLUMN_TYPE='datetime' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_schema_migrations_applied_at_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_acl_admin_no_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl' AND COLUMN_NAME='admin_no' AND COLUMN_TYPE='int' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_acl_admin_no_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_acl_menu_key_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl' AND COLUMN_NAME='menu_key' AND COLUMN_TYPE='varchar(32)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_acl_menu_key_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_acl_can_view_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl' AND COLUMN_NAME='can_view' AND COLUMN_TYPE='tinyint(1)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_acl_can_view_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_acl_can_create_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl' AND COLUMN_NAME='can_create' AND COLUMN_TYPE='tinyint(1)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_acl_can_create_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_acl_can_update_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl' AND COLUMN_NAME='can_update' AND COLUMN_TYPE='tinyint(1)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_acl_can_update_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;
-- Precondition: nb_admin_acl_can_delete_type
SET @release_check = IF((SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl')=0 OR (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin_acl' AND COLUMN_NAME='can_delete' AND COLUMN_TYPE='tinyint(1)' AND IS_NULLABLE='NO')=1, 'SELECT 1', 'RELEASE_ABORT_nb_admin_acl_can_delete_type');
PREPARE release_check FROM @release_check;
EXECUTE release_check;
DEALLOCATE PREPARE release_check;

-- Additive changes. DDL auto-commits; backup before applying.
SET @release_ddl = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='login_token')=0, 'ALTER TABLE `nb_admin` ADD COLUMN `login_token` varchar(64) DEFAULT NULL COMMENT ''단일 세?\n? 토큰. 나중 로그인만 유효''', 'SELECT 1');
PREPARE release_ddl FROM @release_ddl;
EXECUTE release_ddl;
DEALLOCATE PREPARE release_ddl;
SET @release_ddl = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='last_login_at')=0, 'ALTER TABLE `nb_admin` ADD COLUMN `last_login_at` datetime DEFAULT NULL COMMENT ''관리자 로그인 완료 시각''', 'SELECT 1');
PREPARE release_ddl FROM @release_ddl;
EXECUTE release_ddl;
DEALLOCATE PREPARE release_ddl;
SET @release_ddl = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='idle_locked_at')=0, 'ALTER TABLE `nb_admin` ADD COLUMN `idle_locked_at` datetime DEFAULT NULL COMMENT ''장기 미접속 자동 잠금 시각. 수동 비활성과 분리''', 'SELECT 1');
PREPARE release_ddl FROM @release_ddl;
EXECUTE release_ddl;
DEALLOCATE PREPARE release_ddl;
SET @release_ddl = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='must_change_password')=0, 'ALTER TABLE `nb_admin` ADD COLUMN `must_change_password` tinyint(1) NOT NULL DEFAULT ''0'' COMMENT ''관리자가 비번을 대신 넣은 뒤 본인 변경 강제''', 'SELECT 1');
PREPARE release_ddl FROM @release_ddl;
EXECUTE release_ddl;
DEALLOCATE PREPARE release_ddl;
SET @release_ddl = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='nb_admin' AND COLUMN_NAME='password_changed_at')=0, 'ALTER TABLE `nb_admin` ADD COLUMN `password_changed_at` datetime DEFAULT NULL COMMENT ''마지막 본인 비밀번호 변경. 90일 주기 기준''', 'SELECT 1');
PREPARE release_ddl FROM @release_ddl;
EXECUTE release_ddl;
DEALLOCATE PREPARE release_ddl;
CREATE TABLE IF NOT EXISTS `nb_admin_audit` (
  `no` int unsigned NOT NULL AUTO_INCREMENT,
  `sitekey` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `actor_no` int NOT NULL DEFAULT '0',
  `actor_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `actor_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_no` int NOT NULL DEFAULT '0',
  `target_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `detail_json` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`no`),
  KEY `idx_audit_site_created` (`sitekey`,`created_at`),
  KEY `idx_audit_entity` (`sitekey`,`entity`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관리자 생성/수정/삭제 이력. 엑?\n? 26';
CREATE TABLE IF NOT EXISTS `nb_admin_privacy_access` (
  `no` int unsigned NOT NULL AUTO_INCREMENT,
  `sitekey` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `actor_no` int NOT NULL DEFAULT '0',
  `actor_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `actor_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_no` int NOT NULL DEFAULT '0',
  `subject_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `task` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`no`),
  KEY `idx_privacy_site_created` (`sitekey`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='개인정보 열람/다운로드 접속기록. 엑?\n? 21';
CREATE TABLE IF NOT EXISTS `nb_schema_migrations` (
  `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `nb_admin_acl` (
  `admin_no` int NOT NULL,
  `menu_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT '0',
  `can_create` tinyint(1) NOT NULL DEFAULT '0',
  `can_update` tinyint(1) NOT NULL DEFAULT '0',
  `can_delete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`admin_no`,`menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='일반 관리자 메뉴 CRUD. 최고관리자는 행 없이 전체 허용';
UPDATE nb_admin SET last_login_at=NOW() WHERE sitekey='NOLTHE' AND last_login_at IS NULL;
UPDATE nb_admin SET password_changed_at=NOW() WHERE sitekey='NOLTHE' AND password_changed_at IS NULL;
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918161200_create_nb_admin_acl',NOW());
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918180800_admin_login_token',NOW());
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918183000_admin_idle_lock',NOW());
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918184500_admin_must_change_password',NOW());
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918185500_admin_password_changed_at',NOW());
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918191000_admin_audit',NOW());
INSERT IGNORE INTO nb_schema_migrations(id,applied_at) VALUES('20260918193000_admin_privacy_access',NOW());
