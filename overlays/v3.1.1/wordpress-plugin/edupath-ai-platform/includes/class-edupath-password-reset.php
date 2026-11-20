<?php
/**
 * EduPath AI branded password reset flow.
 *
 * Security properties:
 * - public request endpoint returns a generic response to prevent account enumeration;
 * - WordPress core reset keys are used and expire after 30 minutes;
 * - reset keys are one-time because reset_password() invalidates the credential;
 * - request rate limiting is applied by email hash and source IP;
 * - reset does not create an authenticated EduPath session, so the normal email OTP
 *   is still required on the next sign-in;
 * - no password, reset key, OTP or secret is written to audit context.
 *
 * @package EduPath_AI_Platform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'EduPath_AI_Password_Reset' ) ) {
    final class EduPath_AI_Password_Reset {
        const REST_NAMESPACE = 'edupath/v1';
        const REQUEST_ROUTE  = '/auth/password-reset/request';
        const CONFIRM_ROUTE  = '/auth/password-reset/confirm';
        const EXPIRY_SECONDS = 1800;
        const RATE_WINDOW    = 900;
        const RATE_LIMIT     = 3;

        public static function boot() {
            add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
            add_action( 'wp_footer', array( __CLASS__, 'render_ui' ), 99 );
            add_filter( 'password_reset_expiration', array( __CLASS__, 'reset_expiration' ) );
        }

        public static function reset_expiration( $seconds ) {
            return self::EXPIRY_SECONDS;
        }

        public static function register_routes() {
            register_rest_route(
                self::REST_NAMESPACE,
                self::REQUEST_ROUTE,
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( __CLASS__, 'request_reset' ),
                    'permission_callback' => '__return_true',
                    'args'                => array(
                        'email' => array(
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_email',
                            'validate_callback' => static function( $value ) {
                                return is_email( $value );
                            },
                        ),
                    ),
                )
            );

            register_rest_route(
                self::REST_NAMESPACE,
                self::CONFIRM_ROUTE,
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( __CLASS__, 'confirm_reset' ),
                    'permission_callback' => '__return_true',
                )
            );
        }

        private static function client_ip() {
            $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
            return substr( $ip, 0, 64 );
        }

        private static function rate_key( $kind, $value ) {
            return 'edupath_pwreset_' . $kind . '_' . hash( 'sha256', strtolower( (string) $value ) );
        }

        private static function rate_limited( $email ) {
            $keys = array(
                self::rate_key( 'email', $email ),
                self::rate_key( 'ip', self::client_ip() ),
            );

            $blocked = false;
            foreach ( $keys as $key ) {
                $count = (int) get_transient( $key );
                if ( $count >= self::RATE_LIMIT ) {
                    $blocked = true;
                    continue;
                }
                set_transient( $key, $count + 1, self::RATE_WINDOW );
            }

            return $blocked;
        }

        private static function generic_request_response() {
            return new WP_REST_Response(
                array(
                    'ok'      => true,
                    'message' => 'If an EduPath AI account exists for that email address, a password reset link has been sent. Please check your inbox and spam folder.',
                ),
                200
            );
        }

        private static function audit( $event, $context = array() ) {
            $safe = array_merge(
                array(
                    'event'      => sanitize_key( $event ),
                    'timestamp'  => current_time( 'mysql', true ),
                    'ip_hash'    => hash( 'sha256', self::client_ip() ),
                ),
                is_array( $context ) ? $context : array()
            );

            /**
             * Existing EduPath audit implementations can subscribe to either action.
             * No reset key, password or OTP is included.
             */
            do_action( 'edupath_audit_event', $event, $safe );
            do_action( 'edupath_audit_log', $event, $safe );
        }

        public static function request_reset( WP_REST_Request $request ) {
            $email = strtolower( sanitize_email( (string) $request->get_param( 'email' ) ) );

            if ( ! is_email( $email ) ) {
                return self::generic_request_response();
            }

            if ( self::rate_limited( $email ) ) {
                self::audit(
                    'password_reset_rate_limited',
                    array( 'email_hash' => hash( 'sha256', $email ) )
                );
                return self::generic_request_response();
            }

            $user = get_user_by( 'email', $email );

            // Deliberately return the same response when the account does not exist.
            if ( ! $user instanceof WP_User ) {
                usleep( 150000 );
                self::audit(
                    'password_reset_requested_unknown_account',
                    array( 'email_hash' => hash( 'sha256', $email ) )
                );
                return self::generic_request_response();
            }

            $key = get_password_reset_key( $user );
            if ( is_wp_error( $key ) ) {
                self::audit( 'password_reset_key_generation_failed', array( 'user_id' => (int) $user->ID ) );
                return self::generic_request_response();
            }

            $reset_url = add_query_arg(
                array(
                    'edupath_reset' => '1',
                    'login'         => $user->user_login,
                    'key'           => $key,
                ),
                home_url( '/' )
            );

            $subject = 'Reset your EduPath AI password';
            $message = "EduPath AI\n\n";
            $message .= "We received a request to reset the password for your EduPath AI account.\n\n";
            $message .= "Reset your password using this secure link:\n" . esc_url_raw( $reset_url ) . "\n\n";
            $message .= "This link expires in 30 minutes and can only be used once.\n";
            $message .= "If you did not request a password reset, you can ignore this email. Your password will not change.\n\n";
            $message .= "After resetting your password, EduPath AI will still require the normal six-digit email OTP when you sign in.\n\n";
            $message .= "EduPath AI by Pyrneo\n";
            $message .= "One Learner. One Learning Journey. Equal Opportunity.";

            $headers = array(
                'From: EduPath AI <admin@pyrneo.com>',
                'Content-Type: text/plain; charset=UTF-8',
            );

            $sent = wp_mail( $user->user_email, $subject, $message, $headers );

            self::audit(
                $sent ? 'password_reset_requested' : 'password_reset_email_failed',
                array(
                    'user_id'    => (int) $user->ID,
                    'email_hash' => hash( 'sha256', $email ),
                )
            );

            return self::generic_request_response();
        }

        private static function validate_password( $password ) {
            $errors = array();

            if ( strlen( $password ) < 12 ) {
                $errors[] = 'Use at least 12 characters.';
            }
            if ( ! preg_match( '/[a-z]/', $password ) ) {
                $errors[] = 'Include a lowercase letter.';
            }
            if ( ! preg_match( '/[A-Z]/', $password ) ) {
                $errors[] = 'Include an uppercase letter.';
            }
            if ( ! preg_match( '/[0-9]/', $password ) ) {
                $errors[] = 'Include a number.';
            }
            if ( ! preg_match( '/[^A-Za-z0-9]/', $password ) ) {
                $errors[] = 'Include a symbol.';
            }

            return $errors;
        }

        public static function confirm_reset( WP_REST_Request $request ) {
            $login    = sanitize_user( (string) $request->get_param( 'login' ), true );
            $key      = sanitize_text_field( (string) $request->get_param( 'key' ) );
            $password = (string) $request->get_param( 'password' );
            $confirm  = (string) $request->get_param( 'confirm_password' );

            if ( '' === $login || '' === $key ) {
                return new WP_Error(
                    'edupath_reset_invalid_link',
                    'This password reset link is invalid or incomplete. Request a new reset link.',
                    array( 'status' => 400 )
                );
            }

            if ( $password !== $confirm ) {
                return new WP_Error(
                    'edupath_reset_password_mismatch',
                    'The passwords do not match.',
                    array( 'status' => 422 )
                );
            }

            $policy_errors = self::validate_password( $password );
            if ( ! empty( $policy_errors ) ) {
                return new WP_Error(
                    'edupath_reset_weak_password',
                    implode( ' ', $policy_errors ),
                    array( 'status' => 422 )
                );
            }

            $user = check_password_reset_key( $key, $login );
            if ( is_wp_error( $user ) ) {
                self::audit(
                    'password_reset_invalid_or_expired_key',
                    array( 'login_hash' => hash( 'sha256', strtolower( $login ) ) )
                );

                return new WP_Error(
                    'edupath_reset_expired',
                    'This password reset link is invalid or has expired. Request a new reset link.',
                    array( 'status' => 410 )
                );
            }

            reset_password( $user, $password );

            self::audit(
                'password_reset_completed',
                array( 'user_id' => (int) $user->ID )
            );

            return new WP_REST_Response(
                array(
                    'ok'      => true,
                    'message' => 'Your password has been reset successfully. You can now sign in with your new password. EduPath AI will still ask for your six-digit email OTP.',
                    'loginUrl'=> home_url( '/' ),
                ),
                200
            );
        }

        public static function render_ui() {
            if ( is_admin() ) {
                return;
            }

            $request_url = esc_url_raw( rest_url( self::REST_NAMESPACE . self::REQUEST_ROUTE ) );
            $confirm_url = esc_url_raw( rest_url( self::REST_NAMESPACE . self::CONFIRM_ROUTE ) );
            ?>
            <style id="edupath-password-reset-styles">
                .ep-reset-link{display:inline-flex;align-items:center;justify-content:center;margin-top:.55rem;padding:0;border:0;background:transparent;color:#5950d7;font:inherit;font-weight:700;cursor:pointer;text-decoration:underline;text-underline-offset:3px}
                .ep-reset-overlay{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.68);backdrop-filter:blur(4px)}
                .ep-reset-overlay.is-open{display:flex}
                .ep-reset-card{width:min(520px,100%);max-height:92vh;overflow:auto;background:#fff;color:#26344b;border:1px solid #dbe6f4;border-radius:22px;box-shadow:0 24px 70px rgba(15,23,42,.28);padding:28px}
                .ep-reset-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px}
                .ep-reset-kicker{display:inline-block;margin-bottom:6px;color:#5950d7;font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
                .ep-reset-card h2{margin:0;font-size:1.65rem;line-height:1.2}
                .ep-reset-card p{line-height:1.55;color:#5f6f86}
                .ep-reset-close{border:0;background:#eef3fb;color:#26344b;border-radius:999px;width:38px;height:38px;font-size:24px;cursor:pointer}
                .ep-reset-field{display:block;margin:16px 0}
                .ep-reset-field span{display:block;margin-bottom:7px;font-size:.9rem;font-weight:700}
                .ep-reset-field input{box-sizing:border-box;width:100%;padding:12px 14px;border:1px solid #cfdbea;border-radius:12px;background:#fff;color:#26344b;font:inherit}
                .ep-reset-field input:focus{outline:3px solid rgba(83,185,236,.22);border-color:#53b9ec}
                .ep-reset-button{width:100%;border:0;border-radius:12px;padding:13px 16px;background:linear-gradient(135deg,#5950d7,#53b9ec);color:#fff;font:inherit;font-weight:800;cursor:pointer}
                .ep-reset-button[disabled]{opacity:.62;cursor:wait}
                .ep-reset-message{display:none;margin:14px 0;padding:12px 14px;border-radius:12px;background:#f3f7fd;color:#26344b;line-height:1.45}
                .ep-reset-message.is-visible{display:block}
                .ep-reset-message.is-error{background:#fff1f2;color:#9f1239}
                .ep-reset-policy{margin:8px 0 16px;padding-left:20px;color:#5f6f86;font-size:.88rem}
                .ep-reset-back{display:inline-block;margin-top:14px;color:#5950d7;font-weight:700}
                @media (prefers-color-scheme:dark){
                    .ep-reset-card{background:#161d2b;color:#eef5ff;border-color:#334155}
                    .ep-reset-card p,.ep-reset-policy{color:#b9c5d6}
                    .ep-reset-field input{background:#0f172a;color:#eef5ff;border-color:#475569}
                    .ep-reset-close{background:#263449;color:#eef5ff}
                    .ep-reset-message{background:#243247;color:#eef5ff}
                    .ep-reset-message.is-error{background:#4a1f2c;color:#fecdd3}
                }
            </style>

            <div class="ep-reset-overlay" data-ep-reset-overlay aria-hidden="true">
                <section class="ep-reset-card" role="dialog" aria-modal="true" aria-labelledby="ep-reset-title">
                    <div class="ep-reset-head">
                        <div>
                            <span class="ep-reset-kicker">EduPath AI secure access</span>
                            <h2 id="ep-reset-title" data-ep-reset-title>Reset your password</h2>
                        </div>
                        <button type="button" class="ep-reset-close" data-ep-reset-close aria-label="Close password reset">×</button>
                    </div>

                    <div data-ep-reset-request-panel>
                        <p>Enter the email address linked to your EduPath AI account. If the account exists, we will email you a secure one-time reset link.</p>
                        <form data-ep-reset-request-form novalidate>
                            <label class="ep-reset-field">
                                <span>Email address</span>
                                <input type="email" name="email" autocomplete="email" required>
                            </label>
                            <div class="ep-reset-message" data-ep-reset-request-message role="status" aria-live="polite"></div>
                            <button type="submit" class="ep-reset-button">Send password reset link</button>
                        </form>
                    </div>

                    <div data-ep-reset-confirm-panel hidden>
                        <p>Create a new password. The reset link expires after 30 minutes and can be used only once.</p>
                        <ul class="ep-reset-policy">
                            <li>At least 12 characters</li>
                            <li>Uppercase and lowercase letters</li>
                            <li>At least one number</li>
                            <li>At least one symbol</li>
                        </ul>
                        <form data-ep-reset-confirm-form novalidate>
                            <label class="ep-reset-field">
                                <span>New password</span>
                                <input type="password" name="password" autocomplete="new-password" minlength="12" required>
                            </label>
                            <label class="ep-reset-field">
                                <span>Confirm new password</span>
                                <input type="password" name="confirm_password" autocomplete="new-password" minlength="12" required>
                            </label>
                            <div class="ep-reset-message" data-ep-reset-confirm-message role="status" aria-live="polite"></div>
                            <button type="submit" class="ep-reset-button">Reset password</button>
                        </form>
                    </div>
                </section>
            </div>

            <script id="edupath-password-reset-script">
            (function(){
                'use strict';

                var requestUrl = <?php echo wp_json_encode( $request_url ); ?>;
                var confirmUrl = <?php echo wp_json_encode( $confirm_url ); ?>;
                var overlay = document.querySelector('[data-ep-reset-overlay]');
                if (!overlay) return;

                var requestPanel = overlay.querySelector('[data-ep-reset-request-panel]');
                var confirmPanel = overlay.querySelector('[data-ep-reset-confirm-panel]');
                var requestForm = overlay.querySelector('[data-ep-reset-request-form]');
                var confirmForm = overlay.querySelector('[data-ep-reset-confirm-form]');
                var requestMessage = overlay.querySelector('[data-ep-reset-request-message]');
                var confirmMessage = overlay.querySelector('[data-ep-reset-confirm-message]');
                var title = overlay.querySelector('[data-ep-reset-title]');
                var closeButton = overlay.querySelector('[data-ep-reset-close]');
                var lastFocus = null;

                function showMessage(node, message, isError) {
                    node.textContent = message || '';
                    node.classList.toggle('is-visible', !!message);
                    node.classList.toggle('is-error', !!isError);
                }

                function openReset(mode) {
                    lastFocus = document.activeElement;
                    overlay.classList.add('is-open');
                    overlay.setAttribute('aria-hidden','false');
                    var confirmMode = mode === 'confirm';
                    requestPanel.hidden = confirmMode;
                    confirmPanel.hidden = !confirmMode;
                    title.textContent = confirmMode ? 'Choose a new password' : 'Reset your password';
                    setTimeout(function(){
                        var input = overlay.querySelector(confirmMode ? 'input[name="password"]' : 'input[name="email"]');
                        if (input) input.focus();
                    }, 0);
                }

                function closeReset() {
                    overlay.classList.remove('is-open');
                    overlay.setAttribute('aria-hidden','true');
                    if (lastFocus && lastFocus.focus) lastFocus.focus();
                }

                function addForgotLink() {
                    if (document.querySelector('[data-ep-forgot-password]')) return;
                    var password = document.querySelector('input[type="password"]');
                    if (!password) return;

                    var link = document.createElement('button');
                    link.type = 'button';
                    link.className = 'ep-reset-link';
                    link.setAttribute('data-ep-forgot-password','1');
                    link.textContent = 'Forgot password?';
                    link.addEventListener('click', function(){ openReset('request'); });

                    var field = password.closest('label, .field, .ep-field, .form-group, .login-field');
                    if (field && field.parentNode) {
                        field.insertAdjacentElement('afterend', link);
                    } else {
                        password.insertAdjacentElement('afterend', link);
                    }
                }

                async function postJson(url, data) {
                    var response = await fetch(url, {
                        method: 'POST',
                        headers: {'Content-Type':'application/json'},
                        credentials: 'same-origin',
                        body: JSON.stringify(data)
                    });

                    var payload = {};
                    try { payload = await response.json(); } catch(e) {}

                    if (!response.ok) {
                        var message = payload && payload.message ? payload.message : 'We could not complete the request. Please try again.';
                        throw new Error(message);
                    }
                    return payload;
                }

                requestForm.addEventListener('submit', async function(event){
                    event.preventDefault();
                    showMessage(requestMessage, '', false);
                    var button = requestForm.querySelector('button[type="submit"]');
                    var email = requestForm.elements.email.value.trim();
                    if (!email) {
                        showMessage(requestMessage, 'Enter your email address.', true);
                        return;
                    }

                    button.disabled = true;
                    try {
                        var payload = await postJson(requestUrl, {email: email});
                        showMessage(requestMessage, payload.message || 'If an account exists, a reset link has been sent.', false);
                    } catch(error) {
                        showMessage(requestMessage, error.message, true);
                    } finally {
                        button.disabled = false;
                    }
                });

                confirmForm.addEventListener('submit', async function(event){
                    event.preventDefault();
                    showMessage(confirmMessage, '', false);
                    var button = confirmForm.querySelector('button[type="submit"]');
                    var params = new URLSearchParams(window.location.search);
                    var password = confirmForm.elements.password.value;
                    var confirmPassword = confirmForm.elements.confirm_password.value;

                    button.disabled = true;
                    try {
                        var payload = await postJson(confirmUrl, {
                            login: params.get('login') || '',
                            key: params.get('key') || '',
                            password: password,
                            confirm_password: confirmPassword
                        });
                        showMessage(confirmMessage, payload.message || 'Password reset successfully.', false);
                        confirmForm.reset();
                        window.history.replaceState({}, document.title, window.location.pathname + window.location.hash);
                        button.textContent = 'Password reset';
                        button.disabled = true;
                    } catch(error) {
                        showMessage(confirmMessage, error.message, true);
                        button.disabled = false;
                    }
                });

                closeButton.addEventListener('click', closeReset);
                overlay.addEventListener('click', function(event){
                    if (event.target === overlay) closeReset();
                });
                document.addEventListener('keydown', function(event){
                    if (event.key === 'Escape' && overlay.classList.contains('is-open')) closeReset();
                });

                var params = new URLSearchParams(window.location.search);
                if (params.get('edupath_reset') === '1' && params.get('key') && params.get('login')) {
                    openReset('confirm');
                }

                addForgotLink();
                var observer = new MutationObserver(addForgotLink);
                observer.observe(document.documentElement, {childList:true, subtree:true});
            }());
            </script>
            <?php
        }
    }

    EduPath_AI_Password_Reset::boot();
}
