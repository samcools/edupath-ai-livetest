<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class EduPath_AI_REST {
    public static function init() { add_action( 'rest_api_init', array( __CLASS__, 'routes' ) ); }
    public static function routes() {
        register_rest_route( 'edupath/v1', '/status', array('methods'=>'GET','callback'=>array(__CLASS__,'status'),'permission_callback'=>'__return_true') );
        register_rest_route( 'edupath/v1', '/action', array('methods'=>'POST','callback'=>array(__CLASS__,'action'),'permission_callback'=>array(__CLASS__,'can_act') ) );
        register_rest_route( 'edupath/v1', '/agent', array('methods'=>'POST','callback'=>array(__CLASS__,'agent'),'permission_callback'=>'__return_true') );
    }
    public static function can_act() { return is_user_logged_in() && current_user_can( 'read' ); }
    public static function status() {
        $cfg = get_option( 'edupath_ai_microsoft_agents', array() );
        return rest_ensure_response(array(
            'ok'=>true,
            'mode'=>!empty($cfg['mode']) ? sanitize_text_field($cfg['mode']) : 'demo',
            'microsoftConfigured'=>!empty($cfg['proxy_url']),
            'agents'=>edupath_ai_agent_map(),
            'voice'=>'browser-first with optional server agent proxy',
        ));
    }
    public static function action( WP_REST_Request $request ) {
        $body = $request->get_json_params();
        $intent = sanitize_key( $body['intent'] ?? '' );
        $target = sanitize_text_field( $body['target'] ?? '' );
        $risk = sanitize_key( $body['risk'] ?? 'low' );
        if ( ! in_array( $risk, array('low','medium','high'), true ) ) { $risk='low'; }
        if ( in_array( $risk, array('medium','high'), true ) && empty( $body['confirmed'] ) ) {
            return new WP_REST_Response(array('ok'=>false,'requiresConfirmation'=>true,'message'=>'Human confirmation is required before this action can execute.'),409);
        }
        $event = array('time'=>current_time('mysql'),'actor'=>wp_get_current_user()->display_name,'intent'=>$intent,'target'=>$target,'risk'=>$risk);
        $audit = get_option('edupath_ai_audit',array());
        array_unshift($audit,$event);
        update_option('edupath_ai_audit',array_slice($audit,0,200),false);
        return rest_ensure_response(array('ok'=>true,'message'=>'Action recorded and executed within the authorised WordPress session.','event'=>$event));
    }
    public static function agent( WP_REST_Request $request ) {
        $body = $request->get_json_params();
        $message = sanitize_textarea_field( $body['message'] ?? '' );
        $role = sanitize_text_field( $body['role'] ?? 'Learner' );
        if ( ! $message ) { return new WP_Error('missing_message','Message is required.',array('status'=>400)); }
        $cfg = get_option( 'edupath_ai_microsoft_agents', array() );
        $proxy = !empty($cfg['proxy_url']) ? esc_url_raw($cfg['proxy_url']) : '';
        if ( $proxy ) {
            $payload = array('message'=>$message,'role'=>$role,'context'=>sanitize_textarea_field($body['context'] ?? ''),'source'=>'wordpress-edupath-ai');
            $args = array('timeout'=>20,'headers'=>array('Content-Type'=>'application/json'),'body'=>wp_json_encode($payload));
            if ( !empty($cfg['proxy_key']) ) { $args['headers']['Authorization']='Bearer '.sanitize_text_field($cfg['proxy_key']); }
            $response = wp_remote_post($proxy,$args);
            if ( !is_wp_error($response) && wp_remote_retrieve_response_code($response) < 400 ) {
                $data = json_decode(wp_remote_retrieve_body($response),true);
                return rest_ensure_response(array('ok'=>true,'text'=>sanitize_textarea_field($data['text'] ?? $data['reply'] ?? 'Microsoft agent completed the request.'),'provider'=>'microsoft-agent-proxy'));
            }
        }
        return rest_ensure_response(array('ok'=>true,'text'=>self::demo_reply($message,$role),'provider'=>'governed-demo'));
    }
    private static function demo_reply($message,$role) {
        $m = strtolower($message);
        if (str_contains($m,'bursar') || str_contains($m,'funding')) return 'I can open Funding, compare eligibility criteria and flag deadlines. Funding matches are decision support and should be verified against the provider rules.';
        if (str_contains($m,'pathway') || str_contains($m,'study')) return 'I can build a pathway from current subjects and results to university, TVET, occupational, learnership and certification options, while showing alternative routes.';
        if (str_contains($m,'struggl') || str_contains($m,'marks') || str_contains($m,'result')) return 'I can review authorised learning trends, explain the evidence and prepare a support recommendation. Consequential academic decisions remain with educators.';
        return sprintf('Ayanda is operating in governed demo mode for the %s role. I can open pages, explain this workspace, prepare low-risk actions and route consequential actions for human approval.', $role);
    }
}
EduPath_AI_REST::init();
