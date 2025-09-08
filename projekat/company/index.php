<?php
require_once __DIR__ . '/../init.php';
if (!isset($_SESSION['company'])) { header('Location: /projekat/company/login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Company Dashboard</title>
  <link rel="stylesheet" href="../assets/styles.css"/>
</head>
<body>
  <header class="site-header">
    <div class="container row between center">
      <a class="brand" href="../index.php">💼 Job Listings</a>
      <nav>
        <span class="hello">Company: <?php echo htmlspecialchars($_SESSION['company']['name']); ?></span>
        <a class="btn" href="../logout.php">Logout</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <h1>Company Dashboard</h1>

    <section class="card" style="margin-bottom:1rem;">
      <h2 style="margin-top:0;">Create a Job</h2>
      <form id="job-form" class="row" style="flex-wrap:wrap; gap:.75rem;">
        <input type="text" name="title" placeholder="Title" required style="flex:1 1 240px;">
        <input type="text" name="city" placeholder="City" required style="flex:1 1 200px;">
        <select name="category_id" required style="flex:1 1 200px;"></select>
        <textarea name="description" placeholder="Description" required style="flex:1 1 100%; min-height:120px;"></textarea>
        <label style="display:flex;align-items:center;gap:.5rem;">
          <input type="checkbox" name="is_active" checked> Active
        </label>
        <button class="btn primary" type="submit">Publish Job</button>
      </form>
      <div id="create-status" class="muted" style="margin-top:.5rem;"></div>
    </section>

    <section class="card">
      <h2 style="margin-top:0;">My Jobs</h2>
      <div id="my-jobs" class="grid"></div>
    </section>
  </main>

  <script src="../assets/company.js"></script>
</body>
</html>
