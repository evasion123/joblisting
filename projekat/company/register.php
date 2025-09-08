<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../lib/ensure_companies.php';
ensure_companies_table(get_pdo());

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';
  $website = trim($_POST['website'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $about = trim($_POST['about'] ?? '');

  if ($name === '' || $email === '' || $password === '' || $password2 === '') {
    $error = 'Required fields: name, email, password.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Invalid email format.';
  } elseif ($password !== $password2) {
    $error = 'Passwords do not match.';
  } else {
    $pdo = get_pdo();
    $st = $pdo->prepare('SELECT id FROM companies WHERE email = ? OR name = ? LIMIT 1');
    $st->execute([$email, $name]);
    if ($st->fetch()) {
      $error = 'Company email or name already registered.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $ins = $pdo->prepare('INSERT INTO companies (name,email,password_hash,website,address,about) VALUES (?,?,?,?,?,?)');
      $ins->execute([$name,$email,$hash,$website,$address,$about]);
      $_SESSION['company'] = ['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email];
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
  <title>Company Registration</title>
  <link rel="stylesheet" href="../assets/styles.css"/>
</head>
<body>
  <main class="container auth">
    <h1>Register Company</h1>
    <?php if ($error): ?><div class="alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="card">
      <label>Company Name
        <input type="text" name="name" required/>
      </label>
      <label>Email
        <input type="email" name="email" required/>
      </label>
      <label>Website
        <input type="text" name="website" placeholder="https://example.com"/>
      </label>
      <label>Address
        <input type="text" name="address" placeholder="Street, City"/>
      </label>
      <label>About
        <textarea name="about" placeholder="Short description"></textarea>
      </label>
      <label>Password
        <input type="password" name="password" required minlength="6"/>
      </label>
      <label>Confirm Password
        <input type="password" name="password2" required minlength="6"/>
      </label>
      <button class="btn primary" type="submit">Create Company</button>
      <p class="muted">Already registered? <a href="login.php">Login</a></p>
    </form>
  </main>
</body>
</html>
