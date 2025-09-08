async function getCategories() {
  const r = await fetch("../api/company/categories.php");
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "categories error");
  return j.categories;
}

async function myJobs() {
  const r = await fetch("../api/company/my_jobs.php");
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "jobs error");
  return j.jobs;
}

async function createJob(payload) {
  const r = await fetch("../api/company/create_job.php", {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify(payload),
  });
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "create error");
  return j;
}

async function getApplicants(jobId) {
  const r = await fetch(
    "../api/company/applicants.php?job_id=" + encodeURIComponent(jobId)
  );
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "applicants error");
  return j.applicants;
}

async function contact(jobId, userId, subject, message) {
  const r = await fetch("../api/company/contact.php", {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({ job_id: jobId, user_id: userId, subject, message }),
  });
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "contact error");
  return j;
}

function el(tag, attrs = {}, children = []) {
  const d = document.createElement(tag);
  for (const [k, v] of Object.entries(attrs)) {
    if (k === "class") d.className = v;
    else if (k === "html") d.innerHTML = v;
    else d.setAttribute(k, v);
  }
  children.forEach((c) => d.appendChild(c));
  return d;
}

function escapeHtml(s) {
  return String(s ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
        c
      ])
  );
}

async function render() {
  const sel = document.querySelector("select[name=category_id]");
  sel.innerHTML = '<option value="">Loading...</option>';
  try {
    const cats = await getCategories();
    sel.innerHTML =
      '<option value="">Select category</option>' +
      cats
        .map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`)
        .join("");
  } catch (e) {
    sel.innerHTML = '<option value="">(failed)</option>';
  }

  const wrap = document.getElementById("my-jobs");
  wrap.innerHTML = "";
  try {
    const jobs = await myJobs();
    if (!jobs.length) {
      wrap.innerHTML =
        '<p class="muted">No jobs yet — create your first one above.</p>';
    } else {
      jobs.forEach((j) => wrap.appendChild(jobCard(j)));
    }
  } catch (e) {
    wrap.innerHTML = `<div class="alert error">${escapeHtml(e.message)}</div>`;
  }
}

function jobCard(j) {
  const root = el("div", { class: "card job" });
  root.innerHTML = `
    <div class="title">${escapeHtml(j.title)}</div>
    <div class="meta">
      <span class="tag">${escapeHtml(j.category || "General")}</span>
      <span class="tag">${escapeHtml(j.city)}</span>
      <span class="tag">${j.is_active ? "Active" : "Inactive"}</span>
      <span class="tag">${j.applicants_count} applicants</span>
    </div>
    <div class="desc">${escapeHtml(j.description)}</div>
    <div class="row" style="gap:.5rem; margin-top:.5rem;">
      <button class="btn" data-action="view" data-id="${
        j.id
      }">View Applicants</button>
    </div>
    <div class="applicants" id="apps-${j.id}" style="margin-top:.75rem;"></div>
  `;
  root
    .querySelector("[data-action=view]")
    .addEventListener("click", async () => {
      const box = root.querySelector("#apps-" + j.id);
      if (box.dataset.loaded === "1") {
        box.classList.toggle("hidden");
        return;
      }
      box.innerHTML = '<p class="muted">Loading applicants...</p>';
      try {
        const apps = await getApplicants(j.id);
        if (!apps.length) {
          box.innerHTML = '<p class="muted">No applicants yet.</p>';
        } else {
          const frag = document.createDocumentFragment();
          apps.forEach((a) => frag.appendChild(applicantRow(j.id, a)));
          box.innerHTML = "";
          box.appendChild(frag);
        }
        box.dataset.loaded = "1";
      } catch (e) {
        box.innerHTML = `<div class="alert error">${escapeHtml(
          e.message
        )}</div>`;
      }
    });
  return root;
}

function applicantRow(jobId, a) {
  const row = el("div", { class: "card", style: "margin:.5rem 0;" });
  row.innerHTML = `
    <div class="row between center" style="gap:.5rem; flex-wrap:wrap;">
      <div>
        <div><strong>${escapeHtml(a.name)}</strong> &lt;${escapeHtml(
    a.email
  )}&gt;</div>
        <div class="muted">Applied: ${escapeHtml(a.created_at)}</div>
      </div>
      <button class="btn primary" data-action="contact">Contact</button>
    </div>
  `;
  row.querySelector("[data-action=contact]").addEventListener("click", () => {
    const subject = prompt("Subject:");
    if (subject === null) return;
    const message = prompt("Message:");
    if (message === null) return;
    contact(jobId, a.user_id, subject, message)
      .then(() => {
        alert("Message queued (or logged to /emails).");
      })
      .catch((e) => alert(e.message));
  });
  return row;
}

document.getElementById("job-form").addEventListener("submit", async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  const payload = {
    title: fd.get("title").trim(),
    city: fd.get("city").trim(),
    category_id: fd.get("category_id"),
    description: fd.get("description").trim(),
    is_active: fd.get("is_active") ? 1 : 0,
  };
  const status = document.getElementById("create-status");
  status.textContent = "Publishing...";
  try {
    await createJob(payload);
    status.textContent = "Created ✓";
    e.target.reset();
    render();
  } catch (e2) {
    status.textContent = e2.message;
  }
});

render();
