-- Customer-approved BLUESQ super MFA recipient, 2026-10-19.
-- Only fills empty email on the exact active super account; never overwrites an existing recipient.
START TRANSACTION;
UPDATE nb_admin
SET email = 'nineonelabs@gmail.com', updated_at = NOW()
WHERE no = 1 AND uid = 'tmaster' AND sitekey = 'BLUESQ'
  AND role_code = 'super' AND active_status = 'Y'
  AND (email IS NULL OR TRIM(email) = '');
SELECT ROW_COUNT() AS changed_accounts;
SELECT no, uid, role_code, active_status,
       (email = 'nineonelabs@gmail.com') AS recipient_matches
FROM nb_admin
WHERE no = 1 AND uid = 'tmaster' AND sitekey = 'BLUESQ';
COMMIT;
