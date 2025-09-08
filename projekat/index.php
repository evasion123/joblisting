<?php require_once __DIR__ . '/init.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Job Listings</title>
  <link rel="stylesheet" href="assets/styles.css"/>
</head>
<body>
  <header class="site-header">
    <div class="container row between center">
      <a class="brand" href="index.php">💼 Job Listings</a>
      <nav>
        <?php if (isset($_SESSION['user'])): ?>
          <span class="hello">Hi, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
          <a class="btn" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="btn" href="company/login.php">Company Login</a>
          <a class="btn primary" href="company/register.php">Company Register</a>
          <a class="btn" href="login.php">Login</a>
          <a class="btn primary" href="register.php">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container">
    <h1>Open Positions</h1>
    <p class="muted">Browse current openings. You must be logged in to apply.</p>

    <div id="jobs" class="grid"></div>
  </main>

  <footer class="site-footer">
    <div class="container">placeholder footer</div>
  </footer>

  <script>
    window.IS_LOGGED_IN = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;
  </script>
  <script src="assets/main.js"></script>
</body>
</html>
