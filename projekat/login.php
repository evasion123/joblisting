<?php
require_once __DIR__ . '/init.php';
if (isset($_SESSION['user'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Login</title>
  <link rel="stylesheet" href="assets/styles.css"/>
</head>
<body>
  <main class="container auth">
    <h1>Login</h1>
    <form id="loginForm" class="card">
      <label>Email <input type="email" name="email" required></label>
      <label>Password <input type="password" name="password" required minlength="6"></label>
      <button class="btn primary" type="submit">Login</button>
      <p class="muted">No account? <a href="register.php">Register</a></p>
      <div id="msg" class="muted" style="margin-top:.5rem;"></div>
    </form>
  </main>
  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const res = await fetch('api/login.php', { method: 'POST', body: fd });
      const j = await res.json();
      const msg = document.getElementById('msg');
      if (j.ok) { msg.textContent = 'Welcome! Redirecting…'; location.href = 'index.php'; }
      else { msg.textContent = j.error || 'Login failed'; }
    });
  </script>
</body>
</html>
