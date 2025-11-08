// assets/js/fees-backend.js
document.addEventListener('DOMContentLoaded', () => {
  const feeForm = document.getElementById('feeForm');
  const feeList = document.getElementById('feeList');

  const CREATE_URL = '/patent_registry/backend/record_fee.php';
  const LIST_URL   = '/patent_registry/backend/list_fees.php';

  function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"'\/]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c]));
  }

  async function loadFees(patentId = null) {
    if (!feeList) return;
    feeList.innerHTML = '<div class="p-3">Loading…</div>';
    try {
      const url = patentId ? `${LIST_URL}?patent_id=${encodeURIComponent(patentId)}` : LIST_URL;
      const res = await fetch(url);
      if (!res.ok) throw new Error('Failed to load fees: ' + res.status);
      const data = await res.json();
      feeList.innerHTML = '';
      if (!Array.isArray(data) || data.length === 0) {
        feeList.innerHTML = '<div class="p-3 text-muted">No fees recorded.</div>';
        return;
      }
      data.forEach(f => {
        const div = document.createElement('div');
        div.className = 'list-group-item';
        div.innerHTML = `
          <div class="d-flex justify-content-between">
            <div>
              <strong>${escapeHtml(f.fee_type)}</strong><br>
              <small class="text-muted">Patent: ${escapeHtml(f.patent_id)}</small>
            </div>
            <div class="text-right">
              <div>₹ ${Number(f.amount).toFixed(2)}</div>
              <small class="text-muted">${new Date(f.created_at).toLocaleString()}</small>
            </div>
          </div>
        `;
        feeList.appendChild(div);
      });
    } catch (err) {
      console.error('Error loading fees:', err);
      feeList.innerHTML = '<div class="p-3 text-danger">Failed to load fees.</div>';
    }
  }

  if (feeForm) {
    feeForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(feeForm);
      try {
        const res = await fetch(CREATE_URL, { method: 'POST', body: fd });
        const json = await res.json();
        if (res.ok && json.success) {
          feeForm.reset();
          // if the form had a patent_id field, refresh for that patent only
          const pid = feeForm.querySelector('[name="patent_id"]')?.value || null;
          await loadFees(pid);
          alert('Fee recorded ✔');
        } else {
          console.error('Save fee failed:', json);
          alert('Error: ' + (json.error || 'Unable to save fee'));
        }
      } catch (err) {
        console.error('Network error saving fee:', err);
        alert('Network error — see console');
      }
    });
  }

  // initial load: show all fees
  loadFees();
});
