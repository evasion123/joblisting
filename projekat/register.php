<?php
require_once __DIR__ . '/init.php';
if (isset($_SESSION['user'])) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Register</title>
  <link rel="stylesheet" href="assets/styles.css"/>
</head>
<body>
  <main class="container auth">
    <h1>Create Account</h1>
    <form id="registerForm" class="card">
      <label>Name
        <input type="text" name="name" required/>
      </label>
      <label>Email
        <input type="email" name="email" required/>
      </label>
      <label>Password
        <input type="password" name="password" required minlength="6"/>
      </label>
      <label>Confirm Password
        <input type="password" name="password2" required minlength="6"/>
      </label>
      <button class="btn primary" type="submit">Register</button>
      <p class="muted">Already have an account? <a href="login.php">Login</a></p>
      <div id="msg" class="muted" style="margin-top:.5rem;"></div>
    </form>
  </main>
  <script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const res = await fetch('api/register.php', { method: 'POST', body: fd });
      const j = await res.json();
      const msg = document.getElementById('msg');
      if (j.ok) { msg.textContent = 'Registration successful! Redirecting…'; location.href = 'index.php'; }
      else { msg.textContent = j.error || 'Registration failed'; }
    });
  </script>
</body>
</html>
