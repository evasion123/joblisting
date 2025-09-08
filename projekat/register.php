<?php
require_once __DIR__ . '/init.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $password2 === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        // Check duplicate email
        $stmt = get_pdo()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = get_pdo()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user'] = ['id' => get_pdo()->lastInsertId(), 'name' => $name, 'email' => $email];
            header('Location: index.php');
            exit;
        }
    }
}
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
    <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="card">
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
    </form>
  </main>
</body>
</html>
