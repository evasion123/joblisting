<?php
// admin/entities.php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../lib/ensure_companies.php'; // creates companies table if missing
ensure_companies_table(get_pdo());

if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin · Users & Companies</title>
  <link rel="stylesheet" href="../assets/styles.css"/>
  <style>
    table{width:100%;border-collapse:collapse}
    th,td{border-bottom:1px solid var(--border);padding:.6rem;vertical-align:top}
    th{text-align:left;color:var(--muted);font-weight:600}
    .tabs{display:flex;gap:.5rem;margin:.75rem 0}
    .tab{padding:.4rem .7rem;border:1px solid var(--border);border-radius:10px;cursor:pointer}
    .tab.active{background:#17202b}
    .hidden{display:none}
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.6)}
    .modal.open{display:flex}
    .modal .inner{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1rem;width:min(720px,92%)}
    .btn.danger{background:#2a1414;border-color:#5a2a2a;color:#ffd7d7}
  </style>
</head>
<body>
<header class="site-header">
  <div class="container row between center">
    <a class="brand" href="listings.php">🛠 Admin</a>
    <nav>
      <a class="btn" href="listings.php">Listings</a>
      <a class="btn" href="entities.php">Users & Companies</a>
      <a class="btn" href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Users & Companies</h1>

  <div class="tabs">
    <button class="tab active" data-target="usersView">Users</button>
    <button class="tab" data-target="companiesView">Companies</button>
    <button class="btn" id="refreshBtn" style="margin-left:auto;">Refresh</button>
  </div>

  <!-- USERS -->
  <section id="usersView" class="card">
    <div class="row" style="gap:.5rem;align-items:center;margin-bottom:.5rem;">
      <button class="btn primary" id="newUserBtn">+ New User</button>
    </div>
    <table>
      <thead>
      <tr>
        <th style="width:60px;">ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Joined</th>
        <th>Applications</th>
        <th style="width:160px;">Actions</th>
      </tr>
      </thead>
      <tbody id="usersRows"></tbody>
    </table>
  </section>

  <!-- COMPANIES -->
  <section id="companiesView" class="card hidden">
    <div class="row" style="gap:.5rem;align-items:center;margin-bottom:.5rem;">
      <button class="btn primary" id="newCompanyBtn">+ New Company</button>
    </div>
    <table>
      <thead>
      <tr>
        <th style="width:60px;">ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Website</th>
        <th>Address</th>
        <th>Joined</th>
        <th>Jobs</th>
        <th style="width:160px;">Actions</th>
      </tr>
      </thead>
      <tbody id="companiesRows"></tbody>
    </table>
  </section>
</main>

<!-- User Modal -->
<div class="modal" id="userModal" role="dialog" aria-modal="true">
  <div class="inner">
    <h2 id="userModalTitle" style="margin-top:0;">Edit User</h2>
    <form id="userForm" class="row" style="flex-wrap:wrap;gap:.75rem;">
      <input type="hidden" name="id">
      <label style="flex:1 1 100%;">Name
        <input type="text" name="name" required>
      </label>
      <label style="flex:1 1 100%;">Email
        <input type="email" name="email" required>
      </label>
      <label style="flex:1 1 100%;">New Password (leave blank to keep)
        <input type="password" name="new_password" minlength="6">
      </label>
      <div style="margin-left:auto;">
        <button class="btn" type="button" id="userCancel">Cancel</button>
        <button class="btn primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Company Modal -->
<div class="modal" id="companyModal" role="dialog" aria-modal="true">
  <div class="inner">
    <h2 id="companyModalTitle" style="margin-top:0;">Edit Company</h2>
    <form id="companyForm" class="row" style="flex-wrap:wrap;gap:.75rem;">
      <input type="hidden" name="id">
      <label style="flex:1 1 100%;">Company Name
        <input type="text" name="name" required>
      </label>
      <label style="flex:1 1 100%;">Email
        <input type="email" name="email" required>
      </label>
      <label style="flex:1 1 48%;">Website
        <input type="text" name="website" placeholder="https://example.com">
      </label>
      <label style="flex:1 1 48%;">Address
        <input type="text" name="address" placeholder="Street, City">
      </label>
      <label style="flex:1 1 100%;">About
        <textarea name="about" rows="4"></textarea>
      </label>
      <div style="margin-left:auto;">
        <button class="btn" type="button" id="companyCancel">Cancel</button>
        <button class="btn primary" type="submit">Save</button>
      </div>
      <p class="muted" style="margin-top:.5rem;">Renaming a company will update <code>jobs.company_name</code> to keep ownership in sync.</p>
    </form>
  </div>
</div>

<script src="../assets/admin_entities.js"></script>
</body>
</html>
