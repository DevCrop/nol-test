import { AccountController } from "./Controller/AccountController.js";
import { AuditController } from "./Controller/AuditController.js";
import { PrivacyAccessController } from "./Controller/PrivacyAccessController.js";
import { PrivacyCopyLock } from "./utils/PrivacyCopyLock.js";
import { InquiryDownloadReason } from "./utils/InquiryDownloadReason.js";
import { SettingController } from "./Controller/SettingController.js";
import { SeoController } from "./Controller/SeoController.js";
import { FaqController } from "./Controller/FaqController.js";
import { BannerController } from "./Controller/BannerController.js";
import { PopupController } from "./Controller/PopupController.js";
import { attachRadioToggle } from "./utils/initRadioToggle.js";
import { SessionIdleTimer } from "./utils/SessionIdleTimer.js";

document.addEventListener("DOMContentLoaded", () => {
  const sessionTimer = document.querySelector("[data-session-idle]");
  if (sessionTimer) new SessionIdleTimer(sessionTimer).init();

  const page = document.body.dataset.page;

  switch (page) {
    case "account":
      const accountController = new AccountController();
      accountController.init();
      break;

    case "audit":
      const auditController = new AuditController();
      auditController.init();
      break;

    case "privacy-access":
      const privacyAccessController = new PrivacyAccessController();
      privacyAccessController.init();
      break;

    case "inquiry":
      new PrivacyCopyLock(document.body).init();
      break;

    case "inquiry-view":
      new PrivacyCopyLock(document.body).init();
      new InquiryDownloadReason(document.body).init();
      break;

    case "setting":
      const settingController = new SettingController();
      settingController.init();
      break;

    case "seo":
      const seoController = new SeoController();
      seoController.init();
      break;

    case "faq":
      const faqController = new FaqController();
      faqController.init();
      break;

    case "banner":
      const bannerController = new BannerController();
      bannerController.init();
      break;

    case "popup":
      const popupController = new PopupController();
      popupController.init();
      break;

    default:
      break;
  }
});
