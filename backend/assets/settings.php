<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/helpers.php';

// Only logged-in admins
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$err = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $err = 'All fields are required.';
    } elseif ($new !== $confirm) {
        $err = 'New passwords do not match.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($current, $user['password_hash'])) {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->execute([$new_hash, $_SESSION['user_id']]);
            $success = 'Password successfully updated!';
        } else {
            $err = 'Current password is incorrect.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Settings — Luxveris</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/app.css">
</head>
<body class="min-h-screen bg-gray-50">
<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-xl shadow-lg">
    <h1 class="text-xl font-bold mb-4">Change Password</h1>
    <?php if ($err): ?>
        <div class="mb-3 p-3 rounded bg-red-100 text-red-800"><?= h($err) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="mb-3 p-3 rounded bg-green-100 text-green-800"><?= h($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <label class="block mb-2 text-sm">Current Password</label>
        <input type="password" name="current_password" class="w-full border rounded-lg p-2 mb-4" required>

        <label class="block mb-2 text-sm">New Password</label>
        <input type="password" name="new_password" class="w-full border rounded-lg p-2 mb-4" required>

        <label class="block mb-2 text-sm">Confirm New Password</label>
        <input type="password" name="confirm_password" class="w-full border rounded-lg p-2 mb-6" required>

        <button class="w-full rounded-xl bg-black text-white py-2 font-semibold hover:opacity-90">Update Password</button>
    </form>
    <p class="mt-4 text-sm text-gray-500"><a href="/admin.php" class="text-blue-600 hover:underline">← Back to Admin</a></p>
</div>
</body>
</html>
