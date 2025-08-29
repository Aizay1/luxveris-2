<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/helpers.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if ($u && $p) {
        $stmt = db()->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$u]);
        $user = $stmt->fetch();
        if ($user && password_verify($p, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['flash'] = "Welcome back, " . $user['username'] . "!";
            header('Location: /admin.php');
            exit;
        }
    }
    $err = 'Invalid username or password.';
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Login — Luxveris</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/output.css">
</head>
<body class="min-h-screen grid place-items-center bg-gradient-to-br from-gray-50 to-gray-100">
  <form method="post" class="card w-full max-w-sm">
    <h1 class="text-2xl font-bold mb-4">Admin Login</h1>
    <?php if ($err): ?><div class="mb-3 p-3 rounded bg-red-100 border border-red-300 text-red-800"><?= h($err) ?></div><?php endif; ?>
    <label class="block mb-2 text-sm">Username</label>
    <input name="username" class="w-full border rounded-lg p-2 mb-4" required>

    <label class="block mb-2 text-sm">Password</label>
    <input type="password" name="password" class="w-full border rounded-lg p-2 mb-6" required>

    <button class="w-full rounded-xl bg-black text-white py-2 font-semibold hover:opacity-90">Login</button>
    <p class="mt-4 text-sm text-gray-500">Default admin: <b>admin</b> / <b>admin123</b> (change it in Admin → Users later)</p>
    <p class="mt-1 text-center"><a class="text-blue-600 hover:underline" href="/">← Back to site</a></p>
  </form>
</body>
</html>
