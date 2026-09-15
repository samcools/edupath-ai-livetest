<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class EduPath_AI_Settings {
    public static function init() { add_action('admin_menu',array(__CLASS__,'menu')); add_action('admin_init',array(__CLASS__,'register')); }
    public static function menu() { add_theme_page('EduPath AI Agents','EduPath AI Agents','manage_options','edupath-ai-agents',array(__CLASS__,'page')); }
    public static function register() { register_setting('edupath_ai_agents','edupath_ai_microsoft_agents',array('sanitize_callback'=>array(__CLASS__,'sanitize'))); }
    public static function sanitize($input) { return array(
        'mode'=>sanitize_text_field($input['mode']??'demo'),
        'proxy_url'=>esc_url_raw($input['proxy_url']??''),
        'proxy_key'=>sanitize_text_field($input['proxy_key']??''),
        'speech_proxy_url'=>esc_url_raw($input['speech_proxy_url']??''),
        'speech_proxy_key'=>sanitize_text_field($input['speech_proxy_key']??''),
    ); }
    public static function page() {
        if (!current_user_can('manage_options')) return; $o=get_option('edupath_ai_microsoft_agents',array()); ?>
        <div class="wrap"><h1>EduPath AI — Microsoft Agent & Voice Configuration</h1><p>Configure organisation-controlled HTTPS proxies to Microsoft Foundry Agent Service, Copilot Studio / Microsoft 365 Agents SDK orchestration, and speech services. Secrets remain server-side.</p>
        <form method="post" action="options.php"><?php settings_fields('edupath_ai_agents'); ?>
        <table class="form-table"><tr><th>Mode</th><td><select name="edupath_ai_microsoft_agents[mode]"><option value="demo" <?php selected($o['mode']??'','demo'); ?>>Governed demo</option><option value="microsoft" <?php selected($o['mode']??'','microsoft'); ?>>Microsoft agent proxy</option></select></td></tr>
        <tr><th>Agent proxy URL</th><td><input class="regular-text" type="url" name="edupath_ai_microsoft_agents[proxy_url]" value="<?php echo esc_attr($o['proxy_url']??''); ?>"><p class="description">Server-side agent endpoint. Unauthenticated demo visitors are never forwarded to this endpoint.</p></td></tr>
        <tr><th>Agent proxy bearer key</th><td><input class="regular-text" type="password" name="edupath_ai_microsoft_agents[proxy_key]" value="<?php echo esc_attr($o['proxy_key']??''); ?>" autocomplete="new-password"></td></tr>
        <tr><th>Speech transcription proxy URL</th><td><input class="regular-text" type="url" name="edupath_ai_microsoft_agents[speech_proxy_url]" value="<?php echo esc_attr($o['speech_proxy_url']??''); ?>"><p class="description">Optional. Used when the browser has no native SpeechRecognition implementation.</p></td></tr>
        <tr><th>Speech proxy bearer key</th><td><input class="regular-text" type="password" name="edupath_ai_microsoft_agents[speech_proxy_key]" value="<?php echo esc_attr($o['speech_proxy_key']??''); ?>" autocomplete="new-password"></td></tr></table><?php submit_button(); ?></form></div><?php
    }
}
EduPath_AI_Settings::init();
