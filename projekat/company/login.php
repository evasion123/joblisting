<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../lib/ensure_companies.php';
ensure_companies_table(get_pdo());

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  if ($email === '' || $password === '') {
    $error = 'Please enter your email and password.';
  } else {
    $st = get_pdo()->prepare('SELECT id, name, email, password_hash FROM companies WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $c = $st->fetch();
    if ($c && password_verify($password, $c['password_hash'])) {
      $_SESSION['company'] = ['id' => $c['id'], 'name' => $c['name'], 'email' => $c['email']];
      header('Location: index.php');
      exit;
    } else {
      $error = 'Invalid email or password.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Company Login</title>
  <link rel="stylesheet" href="../assets/styles.css"/>
</head>
<body>
  <main class="container auth">
    <h1>Company Login</h1>
    <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="card">
      <label>Email
        <input type="email" name="email" required/>
      </label>
      <label>Password
        <input type="password" name="password" required minlength="6"/>
      </label>
      <button class="btn primary" type="submit">Login</button>
      <p class="muted">New company? <a href="register.php">Register</a></p>
    </form>
  </main>
</body>
</html>
