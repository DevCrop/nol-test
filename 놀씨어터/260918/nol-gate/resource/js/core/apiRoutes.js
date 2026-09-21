const ADMIN_BASE = globalThis.NO_ADMIN_BASE ?? "";

export const API = {
  ACCOUNT: `${ADMIN_BASE}/Controller/AccountController.php`,
  AUDIT: `${ADMIN_BASE}/Controller/AuditController.php`,
  PRIVACY_ACCESS: `${ADMIN_BASE}/Controller/PrivacyAccessController.php`,
  BOARD: `${ADMIN_BASE}/Controller/BoardController.php`,
  SETTING: `${ADMIN_BASE}/Controller/SettingController.php`,
  SEO: `${ADMIN_BASE}/Controller/SeoController.php`,
  FAQ: `${ADMIN_BASE}/Controller/FaqController.php`,
  BANNER: `${ADMIN_BASE}/Controller/BannerController.php`,
  POPUP: `${ADMIN_BASE}/Controller/PopupController.php`,
};
