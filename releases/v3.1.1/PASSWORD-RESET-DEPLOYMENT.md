# EduPath AI Password Reset Deployment

## Version
3.1.1

## Installable packages
- `EduPath_AI_Password_Reset_Plugin_v3.1.1.zip`
- `EduPath_AI_Password_Reset_Theme_v3.1.1.zip`

## Installation order
1. Keep the existing **EduPath AI Platform** plugin installed and active.
2. Upload and activate **EduPath AI Password Reset** under Plugins.
3. Keep the parent theme folder `edupath-ai-theme` installed.
4. Upload and activate **EduPath AI Password Reset** child theme under Appearance → Themes.
5. Verify SMTP/WordPress mail delivery.
6. Test the complete flow:
   - open the EduPath login page;
   - click **Forgot password?**;
   - enter a registered email address;
   - receive the branded reset email;
   - open the one-time link;
   - set a strong new password;
   - sign in with the new password;
   - complete the normal six-digit email OTP.

## Security behaviour
- 30-minute reset-link expiry;
- one-time WordPress core reset key;
- generic request response to reduce account enumeration;
- email/IP rate limiting;
- minimum 12-character password with upper/lowercase, number and symbol;
- reset does not create an authenticated session;
- existing EduPath OTP/MFA remains mandatory;
- no passwords, OTPs or reset tokens are written to audit context.

## Upgrade path
The companion plugin/child theme are a safe deployment bridge because the canonical v3.1 source snapshot in the repository is incomplete. The v3.1.1 overlay is also committed to the canonical theme/plugin build path. When the missing v3.1 source snapshot parts are restored and the full platform/theme are rebuilt, the companion components can be retired.
