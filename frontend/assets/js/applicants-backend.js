
document.addEventListener('DOMContentLoaded', function(){
  const appListEl = document.getElementById('appList');
  const appForm = document.getElementById('appForm');

  async function loadApplicants() {
    try{
      const res = await fetch('/patent_registry/backend/list_applicants.php');
      if(!res.ok) throw new Error('Network response was not ok');
      const data = await res.json();
      if(!appListEl) return;
      appListEl.innerHTML = '';
      data.forEach(a => {
        const li = document.createElement('li');
        li.className = 'list-group-item';
        li.innerHTML = `<div class="d-flex justify-content-between align-items-start">
                          <div>
                             <strong>${escapeHtml(a.name)} </strong><br>
                              <small classn ="text-muted">${a.email || ''} . ${a.organization || ''} </small>"
                          </div>
                          <small class ="text-muted">${new Date(a.created_at).toLocaleDateString()}</small>
                        </div>`;
          appListEl.appendChild(li);
      });
    }catch (err) {
      console.error('Error loading applicants:', err);
    }
  }

  function escapeHtml(s) {
    if (!s) return '';
    return s.replace(/[&<>"'\/]/g, function (c){ 
      return {'&': '&amp;', '<':'&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#x47;'}[c];
     });
  }

  if(appForm){
    appForm.addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(appForm);
      try{
        const res = await fetch('/patent_registry/backend/add_applicant.php', {
          method: 'POST',
          body: fd
        });
        const json = await res.json();
        if(res.ok && json.success){
          appForm.reset();
          await loadApplicants();
          alert('Applicant saved successfully');
        }else{
          alert('Error: ' + (json.message || 'Unable to save'));
        }
        }catch(err){
          console.error(err);
          alert('Network error - see console');
        }
    });
}
  loadApplicants();
});