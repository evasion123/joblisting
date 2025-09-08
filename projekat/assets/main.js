async function fetchJobs() {
  const res = await fetch('api/listings.php', { headers: { 'Accept': 'application/json' } });
  const data = await res.json();
  if (!data.ok) throw new Error(data.error || 'Failed to load jobs');
  return data.jobs;
}

function jobCard(job) {
  const div = document.createElement('div');
  div.className = 'card job';
  div.innerHTML = `
    <div class="title">${escapeHtml(job.title)}</div>
    <div class="meta">
      <span class="tag">${escapeHtml(job.category || 'General')}</span>
      <span class="tag">${escapeHtml(job.city)}</span>
      <span class="tag">${escapeHtml(job.company_name)}</span>
    </div>
    <div class="desc">${escapeHtml(job.description)}</div>
    <button class="btn primary block apply-btn" data-id="${job.id}">Apply</button>
  `;
  const btn = div.querySelector('.apply-btn');
  if (!window.IS_LOGGED_IN) {
    btn.setAttribute('disabled', 'disabled');
    btn.title = 'Please login to apply';
  } else {
    btn.addEventListener('click', () => apply(job.id, btn));
  }
  return div;
}

async function apply(jobId, button) {
  button.disabled = true;
  try {
    const res = await fetch('api/apply.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ job_id: jobId })
    });
    const data = await res.json();
    if (!res.ok || !data.ok) throw new Error(data.error || 'Apply failed');
    button.textContent = 'Applied ✓';
  } catch (e) {
    alert(e.message);
    button.disabled = false;
  }
}

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));
}

(async () => {
  const container = document.getElementById('jobs');
  try {
    const jobs = await fetchJobs();
    if (!jobs.length) {
      container.innerHTML = '<p class="muted">No jobs yet.</p>';
      return;
    }
    const frag = document.createDocumentFragment();
    jobs.forEach(j => frag.appendChild(jobCard(j)));
    container.appendChild(frag);
  } catch (e) {
    container.innerHTML = `<div class="alert error">${escapeHtml(e.message)}</div>`;
  }
})();
