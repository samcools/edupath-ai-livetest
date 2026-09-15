<?php
/**
 * EduPath AI Guardian theme bootstrap.
 *
 * @package EduPath_AI
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'EDUPATH_AI_VERSION', '1.0.0' );
define( 'EDUPATH_AI_DIR', get_template_directory() );
define( 'EDUPATH_AI_URI', get_template_directory_uri() );

require_once EDUPATH_AI_DIR . '/inc/class-edupath-rest.php';
require_once EDUPATH_AI_DIR . '/inc/class-edupath-settings.php';

function edupath_ai_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
    add_theme_support( 'custom-logo' );
    register_nav_menus( array( 'primary' => __( 'EduPath Primary', 'edupath-ai' ) ) );
}
add_action( 'after_setup_theme', 'edupath_ai_setup' );

function edupath_ai_assets() {
    wp_enqueue_style( 'edupath-ai', get_stylesheet_uri(), array(), EDUPATH_AI_VERSION );
    wp_enqueue_script( 'edupath-ai-app', EDUPATH_AI_URI . '/assets/js/edupath-app.js', array(), EDUPATH_AI_VERSION, true );
    wp_enqueue_script( 'edupath-ai-voice', EDUPATH_AI_URI . '/assets/js/edupath-voice.js', array( 'edupath-ai-app' ), EDUPATH_AI_VERSION, true );

    $settings = get_option( 'edupath_ai_microsoft_agents', array() );
    wp_localize_script( 'edupath-ai-app', 'EduPathConfig', array(
        'restUrl'     => esc_url_raw( rest_url( 'edupath/v1/' ) ),
        'nonce'       => wp_create_nonce( 'wp_rest' ),
        'loggedIn'    => is_user_logged_in(),
        'user'        => is_user_logged_in() ? wp_get_current_user()->display_name : 'Demo User',
        'themeUrl'    => EDUPATH_AI_URI,
        'agentMode'   => ! empty( $settings['mode'] ) ? sanitize_text_field( $settings['mode'] ) : 'demo',
        'agentProxy'  => ! empty( $settings['proxy_url'] ) ? esc_url_raw( $settings['proxy_url'] ) : '',
        'speechProxy' => ! empty( $settings['speech_proxy_url'] ) ? esc_url_raw( $settings['speech_proxy_url'] ) : '',
    ) );
}
add_action( 'wp_enqueue_scripts', 'edupath_ai_assets' );

function edupath_ai_body_class( $classes ) {
    $classes[] = 'edupath-ai';
    return $classes;
}
add_filter( 'body_class', 'edupath_ai_body_class' );

function edupath_ai_roles() {
    return array(
        'Learner', 'Parent / Guardian', 'Teacher', 'Counsellor', 'School Administrator',
        'Principal', 'Education Department', 'Academic Administrator', 'University / TVET',
        'Employer', 'Platform Administrator'
    );
}

function edupath_ai_agent_map() {
    return array(
        array('id'=>'learning-guide','name'=>'Learning Guide','microsoft'=>'Microsoft Foundry Prompt Agent + Copilot Studio','guardian'=>'Learner-success recommendations; content retrieval; approval-gated consequential guidance','autonomy'=>'Recommend'),
        array('id'=>'assessment-coach','name'=>'Assessment Coach','microsoft'=>'Microsoft Foundry Prompt/Hosted Agent + Copilot Studio agent flow','guardian'=>'Assessment preparation, moderation evidence, teacher approval before publication','autonomy'=>'Prepare'),
        array('id'=>'pathway-guide','name'=>'Pathway Guide','microsoft'=>'Microsoft Foundry Hosted Agent + toolboxes/custom functions','guardian'=>'Multi-step pathway reasoning over programmes, requirements and learner profile','autonomy'=>'Recommend'),
        array('id'=>'support-navigator','name'=>'Support Navigator','microsoft'=>'Copilot Studio Agent + human-in-the-loop agent flow','guardian'=>'Early-intervention case preparation, human review and escalation','autonomy'=>'Prepare'),
        array('id'=>'work-readiness','name'=>'Work Readiness Coach','microsoft'=>'Microsoft 365 Agents SDK + Foundry Prompt Agent','guardian'=>'Cross-channel coaching through web/Teams with stateful conversations','autonomy'=>'Recommend'),
        array('id'=>'family-educator','name'=>'Family & Educator Guide','microsoft'=>'Copilot Studio multichannel agent + Microsoft 365 Agents SDK','guardian'=>'Role-aware family/educator guidance with restricted data exposure','autonomy'=>'Recommend'),
        array('id'=>'analytics-guide','name'=>'Analytics Guide','microsoft'=>'Microsoft Foundry Hosted Agent + governed analytics tools','guardian'=>'Natural-language analytics over authorised aggregates with traceable evidence','autonomy'=>'Prepare'),
        array('id'=>'notification-guide','name'=>'Notification Guide','microsoft'=>'Copilot Studio agent flow + Microsoft 365/Power Platform connectors','guardian'=>'Deadline/alert workflow with prioritisation, approval gates and audit events','autonomy'=>'Execute low-risk'),
    );
}
