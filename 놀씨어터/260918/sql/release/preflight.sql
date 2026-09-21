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
