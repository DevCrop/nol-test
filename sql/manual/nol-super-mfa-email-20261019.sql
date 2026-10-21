-- NOL ONLY / operator-approved MFA recipient, 2026-10-19.
-- Execute AFTER the approved changes.sql, during maintenance with a fresh DB backup.
-- Only fills the empty email of the verified active super administrator.
-- MyISAM table: transaction rollback is NOT guaranteed. Stop on the first error.
-- Existing different non-empty email is NOT overwritten; investigate if final match is 0.
UPDATE nb_admin
SET email = 'nineonelabs@gmail.com'
WHERE no = 1
  AND uid = 'tmaster'
  AND sitekey = 'NOLTHE'
  AND role_id = 1
  AND active_status = 'Y'
  AND (email IS NULL OR TRIM(email) = '');
SELECT ROW_COUNT() AS changed_accounts;
-- First run: changed_accounts=1. Repeat: 0 is normal if recipient_matches=1.
SELECT no, uid, role_id, active_status,
       (email = 'nineonelabs@gmail.com') AS recipient_matches
FROM nb_admin
WHERE no = 1 AND uid = 'tmaster' AND sitekey = 'NOLTHE';
