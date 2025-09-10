<?php
// Place under /admin/listings.php
require_once __DIR__ . '/../init.php';
if (!isset($_SESSION['admin'])) { header('Location: /projekat/admin/login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin · Listings</title>
  <link rel="stylesheet" href="../assets/styles.css"/>
  <style>
    table{width:100%;border-collapse:collapse}
    th,td{border-bottom:1px solid var(--border);padding:.6rem;vertical-align:top}
    th{text-align:left;color:var(--muted);font-weight:600}
    .toolbar{display:flex;gap:.5rem;align-items:center;margin:.75rem 0}
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
  <h1>Listings</h1>

  <div class="toolbar">
    <button class="btn primary" id="newBtn">+ New Listing</button>
    <button class="btn" id="manageCatsBtn">Manage Categories</button>

    <label class="muted">Category
      <select id="filterCategory" style="margin-left:.4rem;"></select>
    </label>
    <input id="searchBox" type="text" placeholder="Search title/company/city…" style="max-width:280px;">
    <button class="btn" id="refreshBtn">Refresh</button>
  </div>

  <div class="card table-responsive" id="tableWrap">
    <table>
      <thead>
        <tr>
          <th style="width:40px;">ID</th>
          <th>Title</th>
          <th>Company</th>
          <th>City</th>
          <th>Category</th>
          <th>Active</th>
          <th style="width:160px;">Actions</th>
        </tr>
      </thead>
      <tbody id="rows"></tbody>
    </table>
  </div>
</main>

<!-- Edit/Create modal -->
<div class="modal" id="editModal" role="dialog" aria-modal="true">
  <div class="inner">
    <h2 id="modalTitle" style="margin-top:0;">Edit Listing</h2>
    <form id="editForm" class="row" style="flex-wrap:wrap; gap:.75rem;">
      <input type="hidden" name="id">
      <label style="flex:1 1 100%;">Title
        <input type="text" name="title" required>
      </label>
      <label style="flex:1 1 100%;">Company name
        <input type="text" name="company_name" required>
      </label>
      <label style="flex:1 1 40%;">City
        <input type="text" name="city" required>
      </label>
      <label style="flex:1 1 40%;">Category
        <select name="category_id" required></select>
      </label>
      <label style="flex:1 1 100%;">Description
        <textarea name="description" required style="min-height:140px;"></textarea>
      </label>
      <label style="display:flex;align-items:center;gap:.5rem;">
        <input type="checkbox" name="is_active"> Active
      </label>
      <div style="margin-left:auto;">
        <button class="btn" type="button" id="cancelBtn">Cancel</button>
        <button class="btn primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Categories modal -->
<div class="modal" id="catsModal" role="dialog" aria-modal="true">
  <div class="inner">
    <h2 style="margin-top:0;">Manage Categories</h2>

    <form id="catForm" class="row" style="gap:.6rem; flex-wrap:wrap; margin-bottom:.75rem;">
      <label style="flex:1 1 280px;">New category name
        <input type="text" name="name" placeholder="e.g. Engineering" required maxlength="120">
      </label>
      <button class="btn primary" type="submit">Add</button>
      <button class="btn" type="button" id="catCloseBtn" style="margin-left:auto;">Close</button>
    </form>

    <div class="card">
      <div id="catsList" class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));"></div>
      <p class="muted" style="margin-top:.5rem;">
        Note: You can’t delete a category that is used by any job listing.
      </p>
    </div>
  </div>
</div>


<script src="../assets/admin_listings.js"></script>
</body>
</html>
