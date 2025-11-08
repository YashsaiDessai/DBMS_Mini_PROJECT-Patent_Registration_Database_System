// assets/js/examiners-backend.js
document.addEventListener('DOMContentLoaded', () => {
  const PATENTS_URL = '/patent_registry/backend/list_patents.php';
  const EXAMINERS_URL = '/patent_registry/backend/list_examiners.php';
  const ADD_EXAMINER_URL = '/patent_registry/backend/add_examiner.php';
  const ADD_REVIEW_URL = '/patent_registry/backend/add_review.php';
  const LIST_REVIEWS_URL = '/patent_registry/backend/list_reviews.php';

  const patentListEl = document.getElementById('patentList');
  const patentDetailsEl = document.getElementById('patentDetails');
  const patentTitleEl = document.getElementById('patentTitle');
  const patentMetaEl = document.getElementById('patentMeta');
  const patentFilesEl = document.getElementById('patentFiles');
  const reviewsEl = document.getElementById('reviews');

  const reviewerSelect = document.getElementById('examinerSelect');
  const patentSelectInForm = document.getElementById('reviewPatentSelect');

  let patentsCache = [];
  let currentPatent = null;

  function esc(s){ if(!s) return ''; return String(s).replace(/[&<>"'\/]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#47;'}[c])); }

  async function loadPatents() {
    patentListEl.innerHTML = '<div class="p-2 small-muted">Loading…</div>';
    try {
      const res = await fetch(PATENTS_URL);
      const data = await res.json();
      patentsCache = Array.isArray(data) ? data : [];
      if (!patentsCache.length) {
        patentListEl.innerHTML = '<div class="p-2 small-muted">No applications found.</div>';
        patentSelectInForm.innerHTML = '<option value="">No patents</option>';
        return;
      }
      patentListEl.innerHTML = '';
      patentSelectInForm.innerHTML = '<option value="">Select a patent</option>';
      patentsCache.forEach(p => {
        const item = document.createElement('a');
        item.className = 'list-group-item list-group-item-action patent-card';
        item.href = '#';
        item.innerHTML = `<strong>${esc(p.title)}</strong><br><small class="text-muted">${esc(p.application_number)} • ${esc(p.filing_date)}</small>`;
        item.addEventListener('click', (e) => { e.preventDefault(); selectPatent(p); });
        patentListEl.appendChild(item);

        // add to review form select
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = `${p.title} — ${p.application_number}`;
        patentSelectInForm.appendChild(opt);
      });
    } catch (err) {
      console.error('loadPatents', err);
      patentListEl.innerHTML = '<div class="p-2 text-danger">Failed to load applications.</div>';
    }
  }

  async function loadExaminers() {
    reviewerSelect.innerHTML = '<option>Loading...</option>';
    try {
      const res = await fetch(EXAMINERS_URL);
      const data = await res.json();
      reviewerSelect.innerHTML = '<option value="">Select examiner</option>';
      if (Array.isArray(data) && data.length) {
        data.forEach(ex => {
          const o = document.createElement('option'); o.value = ex.id; o.textContent = `${ex.name}${ex.organization? ' — '+ex.organization : ''}`; reviewerSelect.appendChild(o);
        });
      } else {
        reviewerSelect.innerHTML = '<option value="">No examiners</option>';
      }
    } catch (err) {
      console.error('loadExaminers', err);
      reviewerSelect.innerHTML = '<option value="">Failed to load</option>';
    }
  }

  function selectPatent(p) {
    currentPatent = p;
    patentTitleEl.textContent = p.title;
    patentMetaEl.innerHTML = `Application # <strong>${esc(p.application_number)}</strong> • Filed: ${esc(p.filing_date)}`;
    // show attached documents link (if exists)
    patentFilesEl.innerHTML = `<div class="mt-3"><a href="documents.html?patent_id=${encodeURIComponent(p.application_number)}" class="btn btn-sm btn-outline-primary">Open Documents</a></div>`;
    // set form select
    patentSelectInForm.value = p.id;
    loadReviews(p.id);
  }

  async function loadReviews(patentId=null) {
    reviewsEl.innerHTML = '<div class="p-2 small-muted">Loading reviews…</div>';
    try {
      const url = patentId ? `${LIST_REVIEWS_URL}?patent_id=${encodeURIComponent(patentId)}` : LIST_REVIEWS_URL;
      const res = await fetch(url);
      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        reviewsEl.innerHTML = '<div class="p-2 small-muted">No reviews yet for this application.</div>'; return;
      }
      reviewsEl.innerHTML = '';
      data.forEach(r => {
        const div = document.createElement('div');
        div.className = 'list-group-item';
        div.innerHTML = `
          <div class="d-flex justify-content-between">
            <div><strong>${esc(r.decision)}</strong> — <small>${esc(r.examiner_name || r.examiner_id)}</small><br><div class="small-muted">${esc(r.comments || '')}</div></div>
            <div class="text-end"><small class="small-muted">${new Date(r.created_at).toLocaleString()}</small></div>
          </div>
        `;
        reviewsEl.appendChild(div);
      });
    } catch (err) {
      console.error('loadReviews', err);
      reviewsEl.innerHTML = '<div class="p-2 text-danger">Failed to load reviews.</div>';
    }
  }

  // Add examiner form submit
  const examinerForm = document.getElementById('examinerForm');
  if (examinerForm) {
    examinerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(examinerForm);
      try {
        const res = await fetch(ADD_EXAMINER_URL, { method:'POST', body: fd });
        const j = await res.json();
        if (res.ok && j.success) {
          examinerForm.reset();
          await loadExaminers();
          alert('Examiner added');
        } else {
          alert('Error adding examiner: '+ (j.error || 'Server error'));
        }
      } catch (err) {
        console.error('addExaminer', err); alert('Network error — see console');
      }
    });
  }

  // Review form submit
  const reviewForm = document.getElementById('reviewForm');
  if (reviewForm) {
    reviewForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(reviewForm);
      try {
        const res = await fetch(ADD_REVIEW_URL, { method:'POST', body:fd });
        const j = await res.json();
        if (res.ok && j.success) {
          reviewForm.reset();
          if (patentSelectInForm.value) {
            await loadReviews(patentSelectInForm.value);
          } else {
            await loadReviews();
          }
          alert('Review saved');
        } else {
          console.error('save review', j);
          alert('Error: ' + (j.error || 'Unable to save'));
        }
      } catch (err) {
        console.error('addReview', err);
        alert('Network error — check console');
      }
    });
  }

  // initial loads
  loadPatents();
  loadExaminers();
});
