-- Manual, opt-in account data correction. Not part of schema/release migration.
-- Set @admin_no, @admin_uid and @mfa_email in the same SQL session before execution.
-- Example identity: no=1, uid=tmaster. Use the customer's approved recipient email.
-- Only fills an empty email; never overwrites an existing recipient or another account.
START TRANSACTION;
UPDATE nb_admin
SET email = @mfa_email, updated_at = NOW()
WHERE no = @admin_no AND uid = @admin_uid AND sitekey = 'BLUESQ'
  AND (email IS NULL OR TRIM(email) = '')
  AND @mfa_email IS NOT NULL AND CHAR_LENGTH(@mfa_email) BETWEEN 3 AND 254
  AND @mfa_email LIKE '%_@_%._%';
SELECT ROW_COUNT() AS updated_accounts;
SELECT no, uid, (email = @mfa_email) AS email_matches
FROM nb_admin WHERE no = @admin_no AND uid = @admin_uid AND sitekey = 'BLUESQ';
COMMIT;
