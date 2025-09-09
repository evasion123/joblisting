<?php
// profile.php — User profile & password change
require_once __DIR__ . '/init.php';
if (!isset($_SESSION['user'])) { header('Location: /login.php'); exit; }

$pdo = get_pdo();
$userId = (int)$_SESSION['user']['id'];

// Fetch latest user data for display
$st = $pdo->prepare('SELECT id, name, email, password_hash, created_at FROM users WHERE id = ? LIMIT 1');
$st->execute([$userId]);
$user = $st->fetch();
if (!$user) { $_SESSION = []; session_destroy(); header('Location: /login.php'); exit; }

$basicMsg = $passMsg = '';
$basicErr = $passErr = '';

// Handle Basic Info (name + email) update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'basic') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $current_pw = $_POST['current_password'] ?? '';

  if ($name === '' || $email === '' || $current_pw === '') {
    $basicErr = 'Please fill out all fields.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $basicErr = 'Invalid email format.';
  } elseif (!password_verify($current_pw, $user['password_hash'])) {
    $basicErr = 'Current password is incorrect.';
  } else {
    // Ensure email is unique (not used by another user)
    $chk = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $chk->execute([$email, $userId]);
    if ($chk->fetch()) {
      $basicErr = 'That email is already in use.';
    } else {
      $upd = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
      $upd->execute([$name, $email, $userId]);
      // Update session to reflect new values
      $_SESSION['user']['name'] = $name;
      $_SESSION['user']['email'] = $email;
      $basicMsg = 'Profile updated ✓';
      // Refresh $user for display
      $st->execute([$userId]);
      $user = $st->fetch();
    }
  }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
  $current_pw = $_POST['current_password'] ?? '';
  $new_pw = $_POST['new_password'] ?? '';
  $new_pw2 = $_POST['new_password2'] ?? '';

  if ($current_pw === '' || $new_pw === '' || $new_pw2 === '') {
    $passErr = 'Please fill out all fields.';
  } elseif (!password_verify($current_pw, $user['password_hash'])) {
    $passErr = 'Current password is incorrect.';
  } elseif (strlen($new_pw) < 6) {
    $passErr = 'New password must be at least 6 characters.';
  } elseif ($new_pw !== $new_pw2) {
    $passErr = 'New passwords do not match.';
  } else {
    $hash = password_hash($new_pw, PASSWORD_DEFAULT);
    $upd = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
    $upd->execute([$hash, $userId]);
    $passMsg = 'Password updated ✓';
    // No need to re-fetch; password hash not displayed.
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Your Profile</title>
  <link rel="stylesheet" href="assets/styles.css"/>
  <style>
    .profile-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:1rem; }
    .alert.success { background:#142a14; color:#d7ffd7; border:1px solid #2a5a2a; }
  </style>
</head>
<body>
<header class="site-header">
  <div class="container row between center">
    <a class="brand" href="index.php">💼 Job Listings</a>
    <nav>
      <span class="hello">Hi, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
      <a class="btn" href="index.php">Listings</a>
      <a class="btn" href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1>Your Profile</h1>
  <p class="muted">Update your basic information and password.</p>

  <div class="profile-grid">
    <!-- Basic Info -->
    <section class="card">
      <h2 style="margin-top:0;">Basic information</h2>
      <?php if ($basicErr): ?><div class="alert error"><?php echo htmlspecialchars($basicErr); ?></div><?php endif; ?>
      <?php if ($basicMsg): ?><div class="alert success"><?php echo htmlspecialchars($basicMsg); ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="form" value="basic">
        <label>Name
          <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </label>
        <label>Email
          <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </label>
        <label>Current password (required to save changes)
          <input type="password" name="current_password" required minlength="6">
        </label>
        <button class="btn primary" type="submit">Save changes</button>
      </form>
    </section>

    <!-- Password -->
    <section class="card">
      <h2 style="margin-top:0;">Change password</h2>
      <?php if ($passErr): ?><div class="alert error"><?php echo htmlspecialchars($passErr); ?></div><?php endif; ?>
      <?php if ($passMsg): ?><div class="alert success"><?php echo htmlspecialchars($passMsg); ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="form" value="password">
        <label>Current password
          <input type="password" name="current_password" required minlength="6">
        </label>
        <label>New password
          <input type="password" name="new_password" required minlength="6" autocomplete="new-password">
        </label>
        <label>Confirm new password
          <input type="password" name="new_password2" required minlength="6" autocomplete="new-password">
        </label>
        <button class="btn primary" type="submit">Update password</button>
      </form>
    </section>
  </div>

  <div class="card" style="margin-top:1rem;">
    <h3 style="margin-top:0;">Account details</h3>
    <p class="muted">Member since: <?php echo htmlspecialchars($user['created_at']); ?></p>
  </div>
</main>
</body>
</html>
