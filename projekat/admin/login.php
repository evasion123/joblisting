<?php
// admin/login.php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../lib/ensure_admins.php';
ensure_admins(get_pdo());
if (isset($_SESSION['admin'])) { header('Location: listings.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin Login</title>
  <link rel="stylesheet" href="../assets/styles.css"/>
  <style>.auth .card{max-width:520px;margin:0 auto}</style>
</head>
<body>
  <main class="container auth">
    <h1>Admin Login</h1>
    <form id="loginForm" class="card">
      <label>Email <input type="email" name="email" required></label>
      <label>Password <input type="password" name="password" required minlength="6"></label>
      <button class="btn primary" type="submit">Login</button>
      <p class="muted" style="margin-top:.5rem;">Default: <code>admin@example.com</code> / <code>change_me_123</code></p>
      <a href="/">Back to main page</a>
      <div id="msg" class="muted" style="margin-top:.5rem;"></div>
    </form>
  </main>
  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const res = await fetch('../api/admin/login.php', { method: 'POST', body: fd });
      const j = await res.json();
      const msg = document.getElementById('msg');
      if (j.ok) { msg.textContent = 'Welcome! Redirecting…'; location.href = 'listings.php'; }
      else { msg.textContent = j.error || 'Login failed'; }
    });
  </script>
</body>
</html>
