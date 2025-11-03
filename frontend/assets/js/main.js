
document.addEventListener('DOMContentLoaded', ()=>{
  if(!sessionStorage.getItem('patent_demo')) sessionStorage.setItem('patent_demo', JSON.stringify({applicants:[], patents:[], docs:[], fees:[], status:[]}));
  const store = ()=> JSON.parse(sessionStorage.getItem('patent_demo'));
  const save = s => sessionStorage.setItem('patent_demo', JSON.stringify(s));
  // applicants
  const appForm = document.getElementById('appForm');
  if(appForm){
    appForm.addEventListener('submit', (e)=>{
      e.preventDefault();
      const fd = new FormData(appForm);
      const obj = Object.fromEntries(fd.entries());
      obj.id = Date.now();
      const s = store(); s.applicants.unshift(obj); save(s);
      appForm.reset(); renderApplicants(); alert('Applicant saved in browser session');
    });
  }
  function renderApplicants(){
    const el = document.getElementById('appList'); if(!el) return;
    const s = store(); el.innerHTML='';
    s.applicants.forEach(a=>{ const li=document.createElement('li'); li.className='list-group-item'; li.innerHTML=`<strong>${a.name}</strong><br><small class='muted'>${a.email||''} • ${a.organization||''}</small>`; el.appendChild(li); });
  }
  renderApplicants();
  // patents
  const patForm = document.getElementById('patForm');
  if(patForm){
    patForm.addEventListener('submit', (e)=>{
      e.preventDefault();
      const fd = new FormData(patForm);
      const obj = Object.fromEntries(fd.entries()); obj.id=Date.now(); obj.status='Filed';
      const s=store(); s.patents.unshift(obj); save(s); patForm.reset(); renderPatents(); alert('Patent recorded (session)');
    });
  }
  function renderPatents(){ const el=document.getElementById('patList'); if(!el) return; const s=store(); el.innerHTML=''; s.patents.forEach(p=>{ const li=document.createElement('li'); li.className='list-group-item'; li.innerHTML=`<strong>${p.title}</strong><br><small class='muted'>${p.application_number} • ${p.status}</small>`; el.appendChild(li);}); }
  renderPatents();
  // documents
  const docForm = document.getElementById('docForm');
  if(docForm){
    docForm.addEventListener('submit',(e)=>{ e.preventDefault(); const fd=new FormData(docForm); const patent_id=fd.get('patent_id'); const file = docForm.querySelector('input[type=file]').files[0]; if(!file){alert('choose a file'); return;} const s=store(); s.docs.unshift({id:Date.now(), patent_id, filename:file.name}); save(s); docForm.reset(); renderDocs(); alert('Document recorded (session)'); });
  }
  function renderDocs(){ const el=document.getElementById('docList'); if(!el) return; const s=store(); el.innerHTML=''; s.docs.forEach(d=>{ const p=document.createElement('div'); p.className='mb-2'; p.innerHTML = `<strong>${d.filename}</strong> <small class='muted'>for patent ${d.patent_id}</small>`; el.appendChild(p); })}
  renderDocs();
  // fees
  const feeForm = document.getElementById('feeForm');
  if(feeForm){
    feeForm.addEventListener('submit',(e)=>{ e.preventDefault(); const fd=new FormData(feeForm); const obj=Object.fromEntries(fd.entries()); obj.id=Date.now(); const s=store(); s.fees.unshift(obj); save(s); feeForm.reset(); renderFees(); alert('Fee recorded (session)'); });
  }
  function renderFees(){ const el=document.getElementById('feeList'); if(!el) return; const s=store(); el.innerHTML=''; s.fees.forEach(f=>{ const p=document.createElement('div'); p.className='mb-2'; p.innerHTML = `<strong>${f.fee_type}</strong> <small class='muted'>${f.amount} • patent ${f.patent_id}</small>`; el.appendChild(p); }); }
  renderFees();
  // status
  function renderStatus(){ const el=document.getElementById('statusList'); if(!el) return; const s=store(); el.innerHTML=''; s.status.forEach(st=>{ const p=document.createElement('div'); p.className='mb-2'; p.innerHTML = `<strong>Patent ${st.patent_id}</strong> - ${st.current_status} <br><small class='muted'>${st.notes||''}</small>`; el.appendChild(p); }); }
  renderStatus();
});
