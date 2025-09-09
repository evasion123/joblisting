async function fetchCategories() {
  const r = await fetch("api/categories.php", {
    headers: { Accept: "application/json" },
  });
  const j = await r.json();
  if (!j.ok) throw new Error(j.error || "Failed to load categories");
  return j.categories;
}

async function fetchJobs(params = {}) {
  const qs = new URLSearchParams();
  if (params.category_id && Number(params.category_id) > 0)
    qs.set("category_id", params.category_id);
  const url = "api/listings.php" + (qs.toString() ? "?" + qs.toString() : "");
  const res = await fetch(url, { headers: { Accept: "application/json" } });
  const data = await res.json();
  if (!data.ok) throw new Error(data.error || "Failed to load jobs");
  return data.jobs;
}

function jobCard(job) {
  const div = document.createElement("div");
  div.className = "card job";
  div.innerHTML = `
    <div class="title">${escapeHtml(job.title)}</div>
    <div class="meta">
      <span class="tag">${escapeHtml(job.category || "General")}</span>
      <span class="tag">${escapeHtml(job.city)}</span>
      <span class="tag">${escapeHtml(job.company_name)}</span>
    </div>
    <div class="desc">${escapeHtml(job.description)}</div>
    <button class="btn primary block apply-btn" data-id="${
      job.id
    }">Apply</button>
  `;

  const btn = div.querySelector(".apply-btn");

  // Not logged in -> disabled
  if (!window.IS_LOGGED_IN) {
    btn.setAttribute("disabled", "disabled");
    btn.title = "Please login to apply";
    return div;
  }

  // Logged in: if already applied, mark & disable
  if (Number(job.has_applied) === 1) {
    btn.textContent = "Applied ✓";
    btn.classList.add("applied");
    btn.setAttribute("disabled", "disabled");
  } else {
    btn.addEventListener("click", () => apply(job.id, btn));
  }

  return div;
}

async function apply(jobId, button) {
  button.disabled = true;
  try {
    const res = await fetch("api/apply.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({ job_id: jobId }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) throw new Error(data.error || "Apply failed");

    // reflect applied state immediately
    button.textContent = "Applied ✓";
    button.classList.add("applied");
    button.disabled = true;
  } catch (e) {
    alert(e.message);
    button.disabled = false;
  }
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

async function init() {
  const jobsContainer = document.getElementById("jobs");
  const select = document.getElementById("category-filter");

  // Populate categories
  try {
    const cats = await fetchCategories();
    select.innerHTML =
      `<option value="0">All categories</option>` +
      cats
        .map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`)
        .join("");
  } catch (e) {
    select.innerHTML = `<option value="0">(failed to load)</option>`;
  }

  // Load and render jobs (with optional category filter)
  async function renderJobs() {
    jobsContainer.innerHTML = '<p class="muted">Loading...</p>';
    try {
      const jobs = await fetchJobs({ category_id: select.value });
      if (!jobs.length) {
        jobsContainer.innerHTML = '<p class="muted">No jobs found.</p>';
        return;
      }
      const frag = document.createDocumentFragment();
      jobs.forEach((j) => frag.appendChild(jobCard(j)));
      jobsContainer.innerHTML = "";
      jobsContainer.appendChild(frag);
    } catch (e) {
      jobsContainer.innerHTML = `<div class="alert error">${escapeHtml(
        e.message
      )}</div>`;
    }
  }

  select.addEventListener("change", renderJobs);
  await renderJobs();
}

init();
