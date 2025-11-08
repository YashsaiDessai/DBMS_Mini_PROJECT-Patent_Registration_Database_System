// assets/js/documents-backend.js
document.addEventListener('DOMContentLoaded', () => {
  const docForm = document.getElementById('docForm');
  const docList = document.getElementById('docList');
  const patentSelectForDocs = document.getElementById('docPatentSelect'); // optional select for filtering

  const UPLOAD_URL = '/patent_registry/backend/upload_document.php';
  const LIST_URL   = '/patent_registry/backend/list_documents.php';
  const LIST_PATENTS_URL = '/patent_registry/backend/list_patents.php';

  function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"'\/]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c])); }

  async function loadPatentOptions() {
    if (!document.getElementById('docPatentSelect')) return;
    const sel = document.getElementById('docPatentSelect');
    sel.innerHTML = '<option>Loading patents…</option>';
    try {
      const res = await fetch(LIST_PATENTS_URL);
      const data = await res.json();
      sel.innerHTML = '<option value="">All patents</option>';
      (data || []).forEach(p=>{
        const opt = document.createElement('option');
        opt.value = p.application_number || p.id || '';
        opt.textContent = `${p.title} — ${p.application_number || p.id}`;
        sel.appendChild(opt);
      });
    } catch (err) {
      console.error('Failed to load patents for docs', err);
      sel.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  async function loadDocs(patentId=null) {
    if (!docList) return;
    docList.innerHTML = '<div class="p-3">Loading…</div>';
    try {
      const url = patentId ? `${LIST_URL}?patent_id=${encodeURIComponent(patentId)}` : LIST_URL;
      const res = await fetch(url);
      if (!res.ok) throw new Error('Fetch error ' + res.status);
      const data = await res.json();
      docList.innerHTML = '';
      if (!Array.isArray(data) || data.length === 0) {
        docList.innerHTML = '<div class="p-3 text-muted">No documents uploaded.</div>';
        return;
      }
      data.forEach(d=>{
        const div = document.createElement('div');
        div.className = 'list-group-item';
        div.innerHTML = `
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <strong>${escapeHtml(d.filename)}</strong><br>
              <small class="text-muted">Patent: ${escapeHtml(d.patent_id)}</small>
            </div>
            <div>
              <a href="${escapeHtml('/'+d.filepath)}" target="_blank" class="btn btn-sm btn-outline-primary">Open</a>
              <small class="text-muted d-block">${new Date(d.created_at).toLocaleString()}</small>
            </div>
          </div>
        `;
        docList.appendChild(div);
      });
    } catch (err) {
      console.error('Error loading documents', err);
      docList.innerHTML = '<div class="p-3 text-danger">Failed to load documents.</div>';
    }
  }

  if (docForm) {
    docForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(docForm);
      try {
        const res = await fetch(UPLOAD_URL, { method: 'POST', body: fd });
        const json = await res.json();
        if (res.ok && json.success) {
          docForm.reset();
          const pid = document.getElementById('docPatentSelect')?.value || null;
          await loadDocs(pid);
          alert('Uploaded ✔');
        } else {
          console.error('Upload failed:', json);
          alert('Error: ' + (json.error || 'Upload failed'));
        }
      } catch (err) {
        console.error('Network/upload error', err);
        alert('Network error — see console');
      }
    });
  }

  if (patentSelectForDocs) {
    patentSelectForDocs.addEventListener('change', () => loadDocs(patentSelectForDocs.value || null));
  }

  // init
  loadPatentOptions();
  loadDocs();
});
