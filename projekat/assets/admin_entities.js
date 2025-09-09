// assets/admin_entities.js (no search boxes)

async function api(path, opts = {}) {
  const res = await fetch("../api/admin/" + path, {
    headers: { Accept: "application/json" },
    ...opts,
  });
  const j = await res.json();
  if (!j.ok) throw new Error(j.error || "Request failed");
  return j;
}
const esc = (s) =>
  String(s ?? "").replace(
    /[&<>"']/g,
    (c) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
        c
      ])
  );

// Tabs
document.querySelectorAll(".tab").forEach((btn) => {
  btn.addEventListener("click", () => {
    document
      .querySelectorAll(".tab")
      .forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
    const t = btn.dataset.target;
    document
      .getElementById("usersView")
      .classList.toggle("hidden", t !== "usersView");
    document
      .getElementById("companiesView")
      .classList.toggle("hidden", t !== "companiesView");
  });
});
document.getElementById("refreshBtn").addEventListener("click", () => {
  loadUsers();
  loadCompanies();
});

// ---------- USERS ----------
const usersRows = document.getElementById("usersRows");
const userModal = document.getElementById("userModal");
const userForm = document.getElementById("userForm");
const userCancel = document.getElementById("userCancel");
const newUserBtn = document.getElementById("newUserBtn");
const userModalTitle = document.getElementById("userModalTitle");

function openUserModal() {
  userModal.classList.add("open");
}
function closeUserModal() {
  userModal.classList.remove("open");
  userForm.reset();
  userForm.querySelector("[name=id]").value = "";
}

userCancel.addEventListener("click", closeUserModal);
newUserBtn.addEventListener("click", () => {
  userModalTitle.textContent = "New User";
  openUserModal();
});

async function loadUsers() {
  const j = await api("users.php");
  usersRows.innerHTML = "";
  if (!j.users.length) {
    usersRows.innerHTML =
      '<tr><td colspan="6" class="muted">No users.</td></tr>';
    return;
  }
  const frag = document.createDocumentFragment();
  j.users.forEach((u) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${u.id}</td>
      <td>${esc(u.name)}</td>
      <td>${esc(u.email)}</td>
      <td>${esc(u.created_at)}</td>
      <td>${u.applications_count}</td>
      <td>
        <button class="btn" data-edit>Edit</button>
        <button class="btn danger" data-del>Delete</button>
      </td>`;
    tr.querySelector("[data-edit]").addEventListener("click", () =>
      openUserEdit(u.id)
    );
    tr.querySelector("[data-del]").addEventListener("click", async () => {
      if (!confirm("Delete this user? Applications will also be removed."))
        return;
      try {
        await api("user_delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ user_id: u.id }),
        });
        tr.remove();
      } catch (e) {
        alert(e.message);
      }
    });
    frag.appendChild(tr);
  });
  usersRows.appendChild(frag);
}

async function openUserEdit(id) {
  try {
    const j = await api("user_get.php?id=" + encodeURIComponent(id));
    userModalTitle.textContent = "Edit User #" + j.user.id;
    const f = userForm;
    f.querySelector("[name=id]").value = j.user.id;
    f.querySelector("[name=name]").value = j.user.name || "";
    f.querySelector("[name=email]").value = j.user.email || "";
    f.querySelector("[name=new_password]").value = "";
    openUserModal();
  } catch (e) {
    alert(e.message);
  }
}

userForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const fd = new FormData(userForm);
  const payload = {
    id: fd.get("id") ? Number(fd.get("id")) : 0,
    name: fd.get("name").trim(),
    email: fd.get("email").trim(),
    new_password: fd.get("new_password"),
  };
  try {
    await api("user_save.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    closeUserModal();
    await loadUsers();
  } catch (e) {
    alert(e.message);
  }
});

// ---------- COMPANIES ----------
const companiesRows = document.getElementById("companiesRows");
const companyModal = document.getElementById("companyModal");
const companyForm = document.getElementById("companyForm");
const companyCancel = document.getElementById("companyCancel");
const newCompanyBtn = document.getElementById("newCompanyBtn");
const companyModalTitle = document.getElementById("companyModalTitle");

function openCompanyModal() {
  companyModal.classList.add("open");
}
function closeCompanyModal() {
  companyModal.classList.remove("open");
  companyForm.reset();
  companyForm.querySelector("[name=id]").value = "";
}

companyCancel.addEventListener("click", closeCompanyModal);
newCompanyBtn.addEventListener("click", () => {
  companyModalTitle.textContent = "New Company";
  openCompanyModal();
});

async function loadCompanies() {
  const j = await api("companies.php");
  companiesRows.innerHTML = "";
  if (!j.companies.length) {
    companiesRows.innerHTML =
      '<tr><td colspan="8" class="muted">No companies.</td></tr>';
    return;
  }
  const frag = document.createDocumentFragment();
  j.companies.forEach((c) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${c.id}</td>
      <td>${esc(c.name)}</td>
      <td>${esc(c.email)}</td>
      <td>${esc(c.website || "")}</td>
      <td>${esc(c.address || "")}</td>
      <td>${esc(c.created_at)}</td>
      <td>${c.jobs_count}</td>
      <td>
        <button class="btn" data-edit>Edit</button>
        <button class="btn danger" data-del>Delete</button>
      </td>`;
    tr.querySelector("[data-edit]").addEventListener("click", () =>
      openCompanyEdit(c.id)
    );
    tr.querySelector("[data-del]").addEventListener("click", async () => {
      if (
        !confirm(
          "Delete this company? (Jobs using its name will remain unless you cascade.)"
        )
      )
        return;
      try {
        await api("company_delete.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ company_id: c.id }),
        });
        tr.remove();
      } catch (e) {
        alert(e.message);
      }
    });
    frag.appendChild(tr);
  });
  companiesRows.appendChild(frag);
}

async function openCompanyEdit(id) {
  try {
    const j = await api("company_get.php?id=" + encodeURIComponent(id));
    companyModalTitle.textContent = "Edit Company #" + j.company.id;
    const f = companyForm;
    f.querySelector("[name=id]").value = j.company.id;
    f.querySelector("[name=name]").value = j.company.name || "";
    f.querySelector("[name=email]").value = j.company.email || "";
    f.querySelector("[name=website]").value = j.company.website || "";
    f.querySelector("[name=address]").value = j.company.address || "";
    f.querySelector("[name=about]").value = j.company.about || "";
    openCompanyModal();
  } catch (e) {
    alert(e.message);
  }
}

companyForm.addEventListener("submit", async (e) => {
  e.preventDefault();
  const fd = new FormData(companyForm);
  const payload = {
    id: fd.get("id") ? Number(fd.get("id")) : 0,
    name: fd.get("name").trim(),
    email: fd.get("email").trim(),
    website: fd.get("website").trim(),
    address: fd.get("address").trim(),
    about: fd.get("about").trim(),
  };
  try {
    await api("company_save.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });
    closeCompanyModal();
    await loadCompanies();
  } catch (e) {
    alert(e.message);
  }
});

// init
(async function init() {
  await loadUsers();
  await loadCompanies();
})();
