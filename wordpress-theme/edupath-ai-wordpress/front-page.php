<?php get_header(); $roles=edupath_ai_roles(); $agents=edupath_ai_agent_map(); ?>
<div id="edupath-root" data-version="<?php echo esc_attr(EDUPATH_AI_VERSION); ?>">
<section class="ep-login" data-ep-login>
  <div class="ep-login-card">
    <span class="ep-kicker">Pyrneo · SITA Hackathon</span>
    <img class="ep-brand-logo" src="https://pyrneo.com/wp-content/themes/pyrneo-ai-theme/assets/img/pyrneo-logo-wordmark.png" alt="Pyrneo">
    <h1>EduPath AI</h1><p>Learn today. Build tomorrow. One learner journey, continuously supported.</p>
    <label class="ep-field"><span>Select user type</span><select data-ep-login-role><?php foreach($roles as $r): ?><option><?php echo esc_html($r); ?></option><?php endforeach; ?></select></label>
    <button class="ep-btn" data-ep-enter>Enter secure workspace</button>
    <p style="font-size:.78rem">Hackathon demonstration. Synthetic learner data. Consequential decisions require authorised human review.</p>
  </div>
</section>
<div class="ep-shell ep-hidden" data-ep-shell>
  <aside class="ep-sidebar" data-ep-sidebar>
    <img class="ep-brand-logo" src="https://pyrneo.com/wp-content/themes/pyrneo-ai-theme/assets/img/pyrneo-logo-wordmark.png" alt="Pyrneo">
    <div class="ep-role-card"><label>User type</label><select data-ep-role><?php foreach($roles as $r): ?><option><?php echo esc_html($r); ?></option><?php endforeach; ?></select></div>
    <nav class="ep-nav" data-ep-nav aria-label="EduPath AI workspace"></nav>
    <div class="ep-sidebar-foot"><strong>Project Guardian inheritance</strong><br>RBAC-aware experience · approval gates · audit events · explainable recommendations</div>
  </aside>
  <main class="ep-main">
    <header class="ep-topbar"><div class="ep-title-wrap"><h1 data-ep-title>Dashboard</h1><small data-ep-portal>Learner portal</small></div><div class="ep-toolbar"><button class="ep-icon-btn ep-mobile-menu" data-ep-menu style="display:none" aria-label="Open menu">☰</button><button class="ep-icon-btn ep-desktop-only" data-ep-theme aria-label="Toggle theme">◐</button><button class="ep-icon-btn ep-desktop-only" data-ep-notifications aria-label="Notifications">🔔</button></div></header>
    <section class="ep-view" data-ep-view></section>
  </main>
</div>
<button class="ep-ayanda" data-ep-ayanda aria-label="Open Ayanda assistant"><span class="ep-ayanda-avatar"><img src="<?php echo esc_url(EDUPATH_AI_URI.'/assets/images/ayanda.svg'); ?>" alt="Ayanda"><i></i></span><b>Ayanda</b></button>
<section class="ep-chat ep-hidden" data-ep-chat aria-label="Ayanda AI assistant"><div class="ep-chat-head"><div><span class="ep-kicker">Voice + text</span><h3>Ayanda</h3></div><button class="ep-icon-btn" data-ep-chat-close aria-label="Close">×</button></div><div class="ep-chat-body" data-ep-chat-body><div class="ep-msg">Sawubona. I can open pages, explain information and prepare authorised actions. Say “Open Pathways”, “Find bursaries”, or “Show learners needing intervention”.</div></div><div class="ep-chat-actions"><button class="ep-icon-btn ep-mic" data-ep-mic title="Voice command">🎙</button><input data-ep-chat-input placeholder="Ask Ayanda or give a command"><button class="ep-btn" data-ep-send>Send</button></div></section>
<div class="ep-modal ep-hidden" data-ep-modal><div class="ep-modal-card"><div class="ep-modal-head"><h2 data-ep-modal-title>Details</h2><button class="ep-icon-btn" data-ep-modal-close>×</button></div><div data-ep-modal-body></div></div></div>
<div class="ep-toast" data-ep-toast></div>
<script type="application/json" id="edupath-agent-map"><?php echo wp_json_encode($agents); ?></script>
</div>
<?php get_footer(); ?>
