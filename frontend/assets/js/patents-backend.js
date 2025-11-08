document.addEventListener('DOMContentLoaded', () =>{
    const patForm =document.getElementById('patForm');
    const patList = document.getElementById('patList');

    const LIST_URL = '/patent_registry/backend/list_patents.php';
  const CREATE_URL = '/patent_registry/backend/create_patent.php';

  // Escape to avoid XSS when injecting text
  function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"'\/]/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#47;' }[c];
    });
  }

  async function loadPatents() {
    if (!patList) return;
    patList.innerHTML = '<li class="list-group-item">Loading…</li>';
    try {
      const res = await fetch(LIST_URL);
      if (!res.ok) throw new Error('Failed to fetch patents: ' + res.status);
      const data = await res.json();
      // data should be array
      patList.innerHTML = '';
      if (!Array.isArray(data) || data.length === 0) {
        patList.innerHTML = '<li class="list-group-item text-muted">No patents yet.</li>';
        return;
      }
      data.forEach(p => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.innerHTML = `
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <strong>${escapeHtml(p.title)}</strong><br>
              <small class="text-muted">App No: ${escapeHtml(p.application_number || '-')}</small>
            </div>
            <div class="text-end">
              <small class="text-muted">${p.filing_date ? escapeHtml(p.filing_date) : '-'}</small>
              <div><small class="text-muted">${new Date(p.created_at || '').toLocaleString() || ''}</small></div>
            </div>
          </div>
        `;
        patList.appendChild(li);
      });
    } catch (err) {
      console.error('Error loading patents:', err);
      patList.innerHTML = '<li class="list-group-item text-danger">Failed to load patents.</li>';
    }
  }

  if (patForm) {
    patForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(patForm);
      try {
        const res = await fetch(CREATE_URL, { method: 'POST', body: fd });
        // The backend should always return JSON
        const json = await res.json();
        if (res.ok && json.success) {
          patForm.reset();
          await loadPatents();
          // Friendly feedback
          const btn = patForm.querySelector('button[type="submit"]');
          if (btn) {
            btn.disabled = true;
            btn.textContent = 'Saved ✓';
            setTimeout(()=>{ btn.disabled = false; btn.textContent = 'Submit (simulate)'; }, 900);
          }
        } else {
          console.error('Save failed:', json);
          alert('Error saving patent: ' + (json.error || json.message || 'Unknown error'));
        }
      } catch (err) {
        console.error('Network / JSON parse error:', err);
        alert('Network error — check console');
      }
    });
  }

  // initial load
  loadPatents();
});