<?php
/**
 * EduPath AI password-reset presentation.
 *
 * Theme responsibility only: branded link, modal, client-side interaction.
 * Authentication/token/email logic remains in the platform plugin.
 *
 * @package EduPath_AI_Theme
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'edupath_ai_password_reset_ui_boot' ) ) {
function edupath_ai_password_reset_ui_boot() {
    add_action( 'wp_footer', 'edupath_ai_password_reset_ui_render', 99 );
}
add_action( 'after_setup_theme', 'edupath_ai_password_reset_ui_boot', 20 );

function edupath_ai_password_reset_ui_render() {
    if ( is_admin() ) { return; }

    $request_url = esc_url_raw( rest_url( 'edupath/v1/auth/password-reset/request' ) );
    $confirm_url = esc_url_raw( rest_url( 'edupath/v1/auth/password-reset/confirm' ) );
    ?>
    <style id="edupath-password-reset-styles">
      .ep-reset-link{display:inline-flex;margin-top:.55rem;border:0;background:transparent;color:#5950d7;font:inherit;font-weight:700;cursor:pointer;text-decoration:underline;text-underline-offset:3px}
      .ep-reset-overlay{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;padding:24px;background:rgba(15,23,42,.68);backdrop-filter:blur(4px)}
      .ep-reset-overlay.is-open{display:flex}
      .ep-reset-card{width:min(520px,100%);max-height:92vh;overflow:auto;background:#fff;color:#26344b;border:1px solid #dbe6f4;border-radius:22px;box-shadow:0 24px 70px rgba(15,23,42,.28);padding:28px}
      .ep-reset-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px}
      .ep-reset-kicker{display:inline-block;margin-bottom:6px;color:#5950d7;font-size:.78rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
      .ep-reset-card h2{margin:0;font-size:1.65rem;line-height:1.2}.ep-reset-card p{line-height:1.55;color:#5f6f86}
      .ep-reset-close{border:0;background:#eef3fb;color:#26344b;border-radius:999px;width:38px;height:38px;font-size:24px;cursor:pointer}
      .ep-reset-field{display:block;margin:16px 0}.ep-reset-field span{display:block;margin-bottom:7px;font-size:.9rem;font-weight:700}
      .ep-reset-field input{box-sizing:border-box;width:100%;padding:12px 14px;border:1px solid #cfdbea;border-radius:12px;background:#fff;color:#26344b;font:inherit}
      .ep-reset-field input:focus{outline:3px solid rgba(83,185,236,.22);border-color:#53b9ec}
      .ep-reset-button{width:100%;border:0;border-radius:12px;padding:13px 16px;background:linear-gradient(135deg,#5950d7,#53b9ec);color:#fff;font:inherit;font-weight:800;cursor:pointer}
      .ep-reset-button[disabled]{opacity:.62;cursor:wait}
      .ep-reset-message{display:none;margin:14px 0;padding:12px 14px;border-radius:12px;background:#f3f7fd;color:#26344b;line-height:1.45}
      .ep-reset-message.is-visible{display:block}.ep-reset-message.is-error{background:#fff1f2;color:#9f1239}
      .ep-reset-policy{margin:8px 0 16px;padding-left:20px;color:#5f6f86;font-size:.88rem}
      @media(max-width:600px){.ep-reset-overlay{padding:12px}.ep-reset-card{padding:22px;border-radius:18px}}
      body.dark-mode .ep-reset-card,.dark .ep-reset-card{background:#161d2b;color:#eef5ff;border-color:#334155}
      body.dark-mode .ep-reset-card p,body.dark-mode .ep-reset-policy,.dark .ep-reset-card p,.dark .ep-reset-policy{color:#b9c5d6}
    </style>

    <div class="ep-reset-overlay" data-ep-reset-overlay aria-hidden="true">
      <section class="ep-reset-card" role="dialog" aria-modal="true" aria-labelledby="ep-reset-title">
        <div class="ep-reset-head">
          <div><span class="ep-reset-kicker">EduPath AI secure access</span><h2 id="ep-reset-title" data-ep-reset-title>Reset your password</h2></div>
          <button type="button" class="ep-reset-close" data-ep-reset-close aria-label="Close password reset">×</button>
        </div>

        <div data-ep-reset-request-panel>
          <p>Enter the email address linked to your EduPath AI account. If the account exists, we will email you a secure one-time reset link.</p>
          <form data-ep-reset-request-form novalidate>
            <label class="ep-reset-field"><span>Email address</span><input type="email" name="email" autocomplete="email" required></label>
            <div class="ep-reset-message" data-ep-reset-request-message role="status" aria-live="polite"></div>
            <button type="submit" class="ep-reset-button">Send password reset link</button>
          </form>
        </div>

        <div data-ep-reset-confirm-panel hidden>
          <p>Create a new password. The reset link expires after 30 minutes and can only be used once.</p>
          <ul class="ep-reset-policy"><li>At least 12 characters</li><li>Uppercase and lowercase letters</li><li>At least one number</li><li>At least one symbol</li></ul>
          <form data-ep-reset-confirm-form novalidate>
            <label class="ep-reset-field"><span>New password</span><input type="password" name="password" autocomplete="new-password" minlength="12" required></label>
            <label class="ep-reset-field"><span>Confirm new password</span><input type="password" name="confirm_password" autocomplete="new-password" minlength="12" required></label>
            <div class="ep-reset-message" data-ep-reset-confirm-message role="status" aria-live="polite"></div>
            <button type="submit" class="ep-reset-button">Reset password</button>
          </form>
        </div>
      </section>
    </div>

    <script id="edupath-password-reset-ui-script">
    (function(){
      'use strict';
      var requestUrl=<?php echo wp_json_encode( $request_url ); ?>, confirmUrl=<?php echo wp_json_encode( $confirm_url ); ?>;
      var overlay=document.querySelector('[data-ep-reset-overlay]'); if(!overlay)return;
      var reqPanel=overlay.querySelector('[data-ep-reset-request-panel]'), conPanel=overlay.querySelector('[data-ep-reset-confirm-panel]');
      var reqForm=overlay.querySelector('[data-ep-reset-request-form]'), conForm=overlay.querySelector('[data-ep-reset-confirm-form]');
      var reqMsg=overlay.querySelector('[data-ep-reset-request-message]'), conMsg=overlay.querySelector('[data-ep-reset-confirm-message]');
      var title=overlay.querySelector('[data-ep-reset-title]'), lastFocus=null;

      function msg(node,text,error){node.textContent=text||'';node.classList.toggle('is-visible',!!text);node.classList.toggle('is-error',!!error);}
      function open(mode){lastFocus=document.activeElement;overlay.classList.add('is-open');overlay.setAttribute('aria-hidden','false');var c=mode==='confirm';reqPanel.hidden=c;conPanel.hidden=!c;title.textContent=c?'Choose a new password':'Reset your password';setTimeout(function(){var i=overlay.querySelector(c?'input[name="password"]':'input[name="email"]');if(i)i.focus();},0);}
      function close(){overlay.classList.remove('is-open');overlay.setAttribute('aria-hidden','true');if(lastFocus&&lastFocus.focus)lastFocus.focus();}
      async function post(url,data){var r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},credentials:'same-origin',body:JSON.stringify(data)}),p={};try{p=await r.json();}catch(e){}if(!r.ok)throw new Error(p&&p.message?p.message:'We could not complete the request. Please try again.');return p;}

      function addLink(){
        if(document.querySelector('[data-ep-forgot-password]'))return;
        var password=document.querySelector('input[type="password"]'); if(!password)return;
        var link=document.createElement('button');link.type='button';link.className='ep-reset-link';link.setAttribute('data-ep-forgot-password','1');link.textContent='Forgot password?';link.addEventListener('click',function(){open('request');});
        var field=password.closest('label,.field,.ep-field,.form-group,.login-field'); if(field)field.insertAdjacentElement('afterend',link);else password.insertAdjacentElement('afterend',link);
      }

      reqForm.addEventListener('submit',async function(e){e.preventDefault();var b=reqForm.querySelector('button[type="submit"]'),email=reqForm.elements.email.value.trim();msg(reqMsg,'',false);if(!email){msg(reqMsg,'Enter your email address.',true);return;}b.disabled=true;try{var p=await post(requestUrl,{email:email});msg(reqMsg,p.message||'If an account exists, a reset link has been sent.',false);}catch(err){msg(reqMsg,err.message,true);}finally{b.disabled=false;}});
      conForm.addEventListener('submit',async function(e){e.preventDefault();var b=conForm.querySelector('button[type="submit"]'),q=new URLSearchParams(location.search);msg(conMsg,'',false);b.disabled=true;try{var p=await post(confirmUrl,{login:q.get('login')||'',key:q.get('key')||'',password:conForm.elements.password.value,confirm_password:conForm.elements.confirm_password.value});msg(conMsg,p.message||'Password reset successfully.',false);conForm.reset();history.replaceState({},document.title,location.pathname+location.hash);b.textContent='Password reset';b.disabled=true;}catch(err){msg(conMsg,err.message,true);b.disabled=false;}});
      overlay.querySelector('[data-ep-reset-close]').addEventListener('click',close);overlay.addEventListener('click',function(e){if(e.target===overlay)close();});document.addEventListener('keydown',function(e){if(e.key==='Escape'&&overlay.classList.contains('is-open'))close();});

      var q=new URLSearchParams(location.search);if(q.get('edupath_reset')==='1'&&q.get('key')&&q.get('login'))open('confirm');
      addLink();new MutationObserver(addLink).observe(document.documentElement,{childList:true,subtree:true});
    }());
    </script>
    <?php
}
}
