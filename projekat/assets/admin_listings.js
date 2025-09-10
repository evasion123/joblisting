// Place under /assets/admin_listings.js
async function api(path, opts = {}) {
  const res = await fetch("../api/admin/" + path, {
    headers: { Accept: "application/json" },
    ...opts,
  });
  const j = await res.json();
  if (!j.ok) throw new Error(j.error || "Request failed");
  return j;
}

const rowsEl = document.getElementById("rows");
const filterCategory = document.getElementById("filterCategory");
const searchBox = document.getElementById("searchBox");

const manageCatsBtn = document.getElementById("manageCatsBtn");
const catsModal = document.getElementById("catsModal");
const catsList = document.getElementById("catsList");
const catForm = document.getElementById("catForm");
const catCloseBtn = document.getElementById("catCloseBtn");

function openCatsModal() {
  catsModal.classList.add("open");
}
function closeCatsModal() {
  catsModal.classList.remove("open");
  catForm.reset();
}

manageCatsBtn?.addEventListener("click", async () => {
  openCatsModal();
  await renderCategoriesUI(); // load fresh
});
catCloseBtn?.addEventListener("click", closeCatsModal);

function escapeHtml(s) {
  return String(s ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
        c
      ])
  );
}

catForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const name = new FormData(catForm).get("name").trim();
  if (!name) return;
  try {
    await createCategory(name);
    catForm.reset();
    await renderCategoriesUI();
    await loadCategories(); // refresh selects (filter + modal)
  } catch (e) {
    alert(e.message);
  }
});

async function renderCategoriesUI() {
  const j = await api("categories.php"); // already exists
  const cats = j.categories || [];
  catsList.innerHTML = "";
  if (!cats.length) {
    catsList.innerHTML = '<p class="muted">No categories yet.</p>';
    return;
  }
  const frag = document.createDocumentFragment();
  cats.forEach((c) => {
    const card = document.createElement("div");
    card.className = "card";
    card.innerHTML = `
      <div class="row between center">
        <div><strong>${escapeHtml(c.name)}</strong><br><span class="muted">#${
      c.id
    }</span></div>
        <button class="btn danger" data-del="${c.id}">Delete</button>
      </div>
    `;
    card.querySelector("[data-del]").addEventListener("click", async () => {
      if (!confirm(`Delete category "${c.name}"?`)) return;
      try {
        await deleteCategory(c.id);
        await renderCategoriesUI(); // refresh modal list
        await loadCategories(); // refresh dropdowns
        await loadJobs(); // in case filter or job rows depend on it
      } catch (e) {
        alert(e.message);
      }
    });
    frag.appendChild(card);
  });
  catsList.appendChild(frag);
}

async function createCategory(name) {
  const j = await api("category_create.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name }),
  });
  return j.category; // {id, name}
}

async function deleteCategory(category_id) {
  return api("category_delete.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ category_id }),
  });
}

async function loadCategories() {
  const j = await api("categories.php");
  filterCategory.innerHTML =
    `<option value="0">All</option>` +
    j.categories
      .map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`)
      .join("");
  // modal select
  document.querySelector("#editForm select[name=category_id]").innerHTML =
    j.categories
      .map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`)
      .join("");
}

async function loadJobs() {
  const qs = new URLSearchParams();
  const cid = filterCategory.value;
  const q = searchBox.value.trim();
  if (cid && cid !== "0") qs.set("category_id", cid);
  if (q) qs.set("q", q);

  const j = await api("jobs.php" + (qs.toString() ? "?" + qs.toString() : ""));
  rowsEl.innerHTML = "";
  if (!j.jobs.length) {
    rowsEl.innerHTML =
      '<tr><td colspan="7" class="muted">No listings.</td></tr>';
    return;
  }
  const frag = document.createDocumentFragment();
  j.jobs.forEach((job) => frag.appendChild(row(job)));
  rowsEl.appendChild(frag);
}

function row(job) {
  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td>${job.id}</td>
    <td>${escapeHtml(job.title)}</td>
    <td>${escapeHtml(job.company_name || "")}</td>
    <td>${escapeHtml(job.city)}</td>
    <td>${escapeHtml(job.category || "")}</td>
    <td>${job.is_active ? "Yes" : "No"}</td>
    <td>
      <button class="btn" data-action="edit">Edit</button>
      <button class="btn danger" data-action="delete">Delete</button>
    </td>
  `;
  tr.querySelector("[data-action=edit]").addEventListener("click", () =>
    openEdit(job.id)
  );
  tr.querySelector("[data-action=delete]").addEventListener(
    "click",
    async () => {
      if (!confirm("Delete this listing? This cannot be undone.")) return;
      try {
        await api("job_delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ job_id: job.id }),
        });
        tr.remove();
      } catch (e) {
        alert(e.message);
      }
    }
  );
  return tr;
}

const editModal = document.getElementById("editModal");
const cancelBtn = document.getElementById("cancelBtn");
const newBtn = document.getElementById("newBtn");
const editForm = document.getElementById("editForm");
const modalTitle = document.getElementById("modalTitle");

function openModal() {
  editModal.classList.add("open");
}
function closeModal() {
  editModal.classList.remove("open");
  editForm.reset();
  editForm.querySelector("[name=id]").value = "";
}

cancelBtn.addEventListener("click", closeModal);
newBtn.addEventListener("click", () => {
  modalTitle.textContent = "New Listing";
  openModal();
});

async function openEdit(id) {
  try {
    const j = await api("job_get.php?id=" + encodeURIComponent(id));
    modalTitle.textContent = "Edit Listing #" + id;
    const f = editForm;
    f.querySelector("[name=id]").value = j.job.id;
    f.querySelector("[name=title]").value = j.job.title;
    f.querySelector("[name=company_name]").value = j.job.company_name || "";
    f.querySelector("[name=city]").value = j.job.city;
    f.querySelector("[name=category_id]").value = j.job.category_id || "";
    f.querySelector("[name=description]").value = j.job.description || "";
    f.querySelector("[name=is_active]").checked = Number(j.job.is_active) === 1;
    openModal();
  } catch (e) {
    alert(e.message);
  }
}

editForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const fd = new FormData(editForm);
  const payload = {
    id: fd.get("id") ? Number(fd.get("id")) : 0,
    title: fd.get("title").trim(),
    company_name: fd.get("company_name").trim(),
    category_id: Number(fd.get("category_id")),
    city: fd.get("city").trim(),
    description: fd.get("description").trim(),
    is_active: editForm.querySelector("[name=is_active]").checked ? 1 : 0,
  };
  try {
    await api("job_save.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    closeModal();
    await loadJobs();
  } catch (e) {
    alert(e.message);
  }
});

document.getElementById("refreshBtn").addEventListener("click", loadJobs);
filterCategory.addEventListener("change", loadJobs);
searchBox.addEventListener("input", (ev) => {
  if (ev.target.value.length === 0 || ev.target.value.length > 2) loadJobs();
});

(async function init() {
  await loadCategories();
  await loadJobs();
})();
