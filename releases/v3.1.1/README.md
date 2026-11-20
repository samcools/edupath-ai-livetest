# EduPath AI v3.1.1

EduPath AI v3.1.1 is a security-focused authentication update built as an overlay on the immutable v3.1.0 source snapshot.

## Added

- Branded **Forgot password?** option on the EduPath login experience.
- Secure email-based password reset using WordPress core reset keys.
- 30-minute reset-link expiry.
- One-time reset links.
- Generic reset-request response to reduce account-enumeration risk.
- Request throttling by email hash and source IP.
- Strong-password validation:
  - minimum 12 characters;
  - uppercase and lowercase letters;
  - number;
  - symbol.
- Invalid and expired-link handling.
- Reset-request, rate-limit, delivery-failure and successful-reset audit events without logging passwords, OTPs or reset keys.
- Reset does **not** create a logged-in session. The normal EduPath email + password + six-digit OTP flow remains mandatory after a reset.
- Branded reset email from `EduPath AI <admin@pyrneo.com>`.

## Packaging

The v3.1.0 source snapshot remains unchanged. CI restores v3.1.0, applies `scripts/apply-v3.1.1-password-reset.sh`, validates PHP/JavaScript and packages v3.1.1 installable ZIPs.
