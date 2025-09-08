<?php
require_once __DIR__ . '/init.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = get_pdo()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email']];
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
  <title>Login</title>
  <link rel="stylesheet" href="assets/styles.css"/>
</head>
<body>
  <main class="container auth">
    <h1>Login</h1>
    <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="card">
      <label>Email
        <input type="email" name="email" required/>
      </label>
      <label>Password
        <input type="password" name="password" required minlength="6"/>
      </label>
      <button class="btn primary" type="submit">Login</button>
      <p class="muted">No account? <a href="register.php">Register</a></p>
    </form>
  </main>
</body>
</html>
