// auth-ui.js — tiny auth helper only (login button / logout)
(function(){
  'use strict';

  async function fetchJson(url){
    try {
      const r = await fetch(url, { credentials: 'same-origin' });
      return await r.json();
    } catch(e){
      return { logged_in:false };
    }
  }

  function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"'\/]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c])); }

  async function updateAuthArea(){
    const info = await fetchJson('/patent_registry/backend/auth_check.php');
    const navContainer = document.querySelector('.navbar .container');
    if (!navContainer) return;
    // remove old
    const prev = document.getElementById('authArea'); if (prev) prev.remove();
    const wrapper = document.createElement('div');
    wrapper.id = 'authArea';
    wrapper.className = 'ms-auto d-flex align-items-center';
    if (info.logged_in) {
      wrapper.innerHTML = `<span class="me-3" style="color:#fff">Hi, ${escapeHtml(info.name)}</span>
        <button id="logoutBtn" class="btn btn-sm btn-outline-light">Logout</button>`;
      navContainer.appendChild(wrapper);
      document.getElementById('logoutBtn').addEventListener('click', async (e)=>{
        e.preventDefault();
        await fetch('/patent_registry/backend/logout.php', { method: 'GET', credentials: 'same-origin' });
        location.reload();
      });
    } else {
      wrapper.innerHTML = `<a class="btn btn-sm btn-light" href="/patent_registry/auth.html">Login</a>`;
      navContainer.appendChild(wrapper);
    }
  }

  // init on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateAuthArea);
  } else {
    updateAuthArea();
  }

  // expose a tiny helper if needed by modules
  window.AuthUI = { update: updateAuthArea };

})();
