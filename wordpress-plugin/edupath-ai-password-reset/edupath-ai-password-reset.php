<?php
/**
 * Plugin Name: EduPath AI Password Reset
 * Description: Secure branded password-reset extension for EduPath AI. Uses WordPress core reset keys and preserves EduPath OTP/MFA on the next sign-in.
 * Version: 3.1.1
 * Author: Pyrneo
 * Text Domain: edupath-ai-password-reset
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Transitional compatibility plugin.
 * If EduPath AI Platform v3.1.1+ already provides the class, this plugin does
 * not register duplicate routes.
 */
if ( ! class_exists( 'EduPath_AI_Password_Reset' ) ) {
    require_once __DIR__ . '/includes/class-edupath-password-reset.php';
}
