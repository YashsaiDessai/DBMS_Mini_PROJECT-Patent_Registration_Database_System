// assets/js/status-backend.js
document.addEventListener('DOMContentLoaded', () => {
  const statusForm = document.getElementById('statusForm');
  const statusList = document.getElementById('statusList');
  const patentSelect = document.getElementById('statusPatentSelect');

  const ADD_URL = '/patent_registry/backend/add_status.php';
  const LIST_URL = '/patent_registry/backend/list_status.php';
  const LIST_PATENTS_URL = '/patent_registry/backend/list_patents.php';

  function esc(s){ if(!s) return ''; return String(s).replace(/[&<>"'\/]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c])); }

  async function loadPatentOptions() {
    if (!patentSelect) return;
    patentSelect.innerHTML = '<option>Loading patents…</option>';
    try {
      const res = await fetch(LIST_PATENTS_URL);
      const data = await res.json();
      patentSelect.innerHTML = '<option value="">Select a patent</option>';
      (data || []).forEach(p=>{
        const opt = document.createElement('option');
        opt.value = p.id;
opt.textContent = `${p.title} — ${p.application_number || p.id}`;

        patentSelect.appendChild(opt);
      });
    } catch (err) {
      console.error('Failed to load patents', err);
      patentSelect.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  async function loadStatus(patentId=null) {
    if (!statusList) return;
    statusList.innerHTML = '<div class="p-3">Loading…</div>';
    try {
      const url = patentId ? `${LIST_URL}?patent_id=${encodeURIComponent(patentId)}` : LIST_URL;
      const res = await fetch(url);
      if (!res.ok) throw new Error('Failed to load status: ' + res.status);
      const data = await res.json();
      statusList.innerHTML = '';
      if (!Array.isArray(data) || data.length === 0) {
        statusList.innerHTML = '<div class="p-3 text-muted">No status updates yet.</div>';
        return;
      }
      data.forEach(s => {
        const div = document.createElement('div');
        div.className = 'list-group-item';
        div.innerHTML = `
          <div class="d-flex justify-content-between">
            <div>
              <strong>${esc(s.status_text)}</strong>
              ${s.status_type ? `<br><small class="text-muted">${esc(s.status_type)}</small>` : ''}
              <br><small class="text-muted">Patent: ${esc(s.patent_id)}</small>
            </div>
            <div class="text-right">
              <small class="text-muted">${new Date(s.created_at).toLocaleString()}</small>
            </div>
          </div>
        `;
        statusList.appendChild(div);
      });
    } catch (err) {
      console.error('Error loading status', err);
      statusList.innerHTML = '<div class="p-3 text-danger">Failed to load status updates.</div>';
    }
  }

  if (statusForm) {
    statusForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(statusForm);
      try {
        const res = await fetch(ADD_URL, { method: 'POST', body: fd });
        const json = await res.json();
        if (res.ok && json.success) {
          statusForm.reset();
          const pid = patentSelect?.value || null;
          await loadStatus(pid);
          alert('Status added ✔');
        } else {
          console.error('Add status error', json);
          alert('Error: ' + (json.error || 'Unable to add status'));
        }
      } catch (err) {
        console.error('Network error adding status', err);
        alert('Network error — check console');
      }
    });

    // refresh list when patent changes
    if (patentSelect) {
      patentSelect.addEventListener('change', () => loadStatus(patentSelect.value || null));
    }
  }

  // init
  loadPatentOptions();
  loadStatus();
});
