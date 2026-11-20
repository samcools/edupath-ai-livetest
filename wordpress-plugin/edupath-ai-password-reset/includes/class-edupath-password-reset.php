<?php
/**
 * EduPath AI password reset backend.
 *
 * Authentication/business logic belongs in the platform plugin. Presentation is
 * supplied by the EduPath theme module.
 *
 * @package EduPath_AI_Platform
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
        add_filter( 'password_reset_expiration', array( __CLASS__, 'reset_expiration' ) );
    }

    public static function reset_expiration( $seconds ) {
        return self::EXPIRY_SECONDS;
    }

    public static function register_routes() {
        register_rest_route( self::REST_NAMESPACE, self::REQUEST_ROUTE, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'request_reset' ),
            'permission_callback' => '__return_true',
            'args' => array(
                'email' => array(
                    'required' => true,
                    'sanitize_callback' => 'sanitize_email',
                    'validate_callback' => static function( $value ) { return is_email( $value ); },
                ),
            ),
        ) );

        register_rest_route( self::REST_NAMESPACE, self::CONFIRM_ROUTE, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( __CLASS__, 'confirm_reset' ),
            'permission_callback' => '__return_true',
        ) );
    }

    private static function client_ip() {
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
        return substr( $ip, 0, 64 );
    }

    private static function rate_key( $kind, $value ) {
        return 'edupath_pwreset_' . $kind . '_' . hash( 'sha256', strtolower( (string) $value ) );
    }

    private static function rate_limited( $email ) {
        $blocked = false;
        foreach ( array(
            self::rate_key( 'email', $email ),
            self::rate_key( 'ip', self::client_ip() ),
        ) as $key ) {
            $count = (int) get_transient( $key );
            if ( $count >= self::RATE_LIMIT ) {
                $blocked = true;
                continue;
            }
            set_transient( $key, $count + 1, self::RATE_WINDOW );
        }
        return $blocked;
    }

    private static function response() {
        return new WP_REST_Response( array(
            'ok' => true,
            'message' => 'If an EduPath AI account exists for that email address, a password reset link has been sent. Please check your inbox and spam folder.',
        ), 200 );
    }

    private static function audit( $event, $context = array() ) {
        $safe = array_merge( array(
            'event' => sanitize_key( $event ),
            'timestamp' => current_time( 'mysql', true ),
            'ip_hash' => hash( 'sha256', self::client_ip() ),
        ), is_array( $context ) ? $context : array() );

        do_action( 'edupath_audit_event', $event, $safe );
        do_action( 'edupath_audit_log', $event, $safe );
    }

    public static function request_reset( WP_REST_Request $request ) {
        $email = strtolower( sanitize_email( (string) $request->get_param( 'email' ) ) );

        if ( ! is_email( $email ) ) {
            return self::response();
        }

        if ( self::rate_limited( $email ) ) {
            self::audit( 'password_reset_rate_limited', array( 'email_hash' => hash( 'sha256', $email ) ) );
            return self::response();
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user instanceof WP_User ) {
            usleep( 150000 );
            self::audit( 'password_reset_requested_unknown_account', array( 'email_hash' => hash( 'sha256', $email ) ) );
            return self::response();
        }

        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            self::audit( 'password_reset_key_generation_failed', array( 'user_id' => (int) $user->ID ) );
            return self::response();
        }

        $reset_url = add_query_arg( array(
            'edupath_reset' => '1',
            'login' => $user->user_login,
            'key' => $key,
        ), home_url( '/' ) );

        $subject = 'Reset your EduPath AI password';
        $message  = "EduPath AI\n\n";
        $message .= "We received a request to reset the password for your EduPath AI account.\n\n";
        $message .= "Reset your password using this secure link:\n" . esc_url_raw( $reset_url ) . "\n\n";
        $message .= "This link expires in 30 minutes and can only be used once.\n";
        $message .= "If you did not request this reset, ignore this email. Your password will not change.\n\n";
        $message .= "After resetting your password, sign in with your new password and the normal six-digit email OTP.\n\n";
        $message .= "EduPath AI by Pyrneo\nOne Learner. One Learning Journey. Equal Opportunity.";

        $sent = wp_mail(
            $user->user_email,
            $subject,
            $message,
            array( 'From: EduPath AI <admin@pyrneo.com>', 'Content-Type: text/plain; charset=UTF-8' )
        );

        self::audit( $sent ? 'password_reset_requested' : 'password_reset_email_failed', array(
            'user_id' => (int) $user->ID,
            'email_hash' => hash( 'sha256', $email ),
        ) );

        return self::response();
    }

    private static function validate_password( $password ) {
        $errors = array();
        if ( strlen( $password ) < 12 ) { $errors[] = 'Use at least 12 characters.'; }
        if ( ! preg_match( '/[a-z]/', $password ) ) { $errors[] = 'Include a lowercase letter.'; }
        if ( ! preg_match( '/[A-Z]/', $password ) ) { $errors[] = 'Include an uppercase letter.'; }
        if ( ! preg_match( '/[0-9]/', $password ) ) { $errors[] = 'Include a number.'; }
        if ( ! preg_match( '/[^A-Za-z0-9]/', $password ) ) { $errors[] = 'Include a symbol.'; }
        return $errors;
    }

    public static function confirm_reset( WP_REST_Request $request ) {
        $login = sanitize_user( (string) $request->get_param( 'login' ), true );
        $key = sanitize_text_field( (string) $request->get_param( 'key' ) );
        $password = (string) $request->get_param( 'password' );
        $confirm = (string) $request->get_param( 'confirm_password' );

        if ( '' === $login || '' === $key ) {
            return new WP_Error( 'edupath_reset_invalid_link', 'This password reset link is invalid or incomplete. Request a new reset link.', array( 'status' => 400 ) );
        }
        if ( $password !== $confirm ) {
            return new WP_Error( 'edupath_reset_password_mismatch', 'The passwords do not match.', array( 'status' => 422 ) );
        }

        $policy_errors = self::validate_password( $password );
        if ( ! empty( $policy_errors ) ) {
            return new WP_Error( 'edupath_reset_weak_password', implode( ' ', $policy_errors ), array( 'status' => 422 ) );
        }

        $user = check_password_reset_key( $key, $login );
        if ( is_wp_error( $user ) ) {
            self::audit( 'password_reset_invalid_or_expired_key', array( 'login_hash' => hash( 'sha256', strtolower( $login ) ) ) );
            return new WP_Error( 'edupath_reset_expired', 'This password reset link is invalid or has expired. Request a new reset link.', array( 'status' => 410 ) );
        }

        reset_password( $user, $password );
        self::audit( 'password_reset_completed', array( 'user_id' => (int) $user->ID ) );

        return new WP_REST_Response( array(
            'ok' => true,
            'message' => 'Your password has been reset successfully. Sign in with your new password; EduPath AI will still ask for your six-digit email OTP.',
            'loginUrl' => home_url( '/' ),
        ), 200 );
    }
}
EduPath_AI_Password_Reset::boot();
}
