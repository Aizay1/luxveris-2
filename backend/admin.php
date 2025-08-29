<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_login();
$pdo = db();
$user = current_user();

// Determine which view to show
$action = $_GET['action'] ?? 'news';
$show_password_form = $action === 'change_password';

// Handle password change
$pw_message = '';
$pw_message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $pw_message = 'All fields are required.';
        $pw_message_type = 'error';
    } elseif ($new !== $confirm) {
        $pw_message = 'New passwords do not match.';
        $pw_message_type = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $db_user = $stmt->fetch();

        if ($db_user && password_verify($current, $db_user['password_hash'])) {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update->execute([$new_hash, $_SESSION['user_id']]);
            $pw_message = 'Password successfully updated!';
            $pw_message_type = 'success';
        } else {
            $pw_message = 'Current password is incorrect.';
            $pw_message_type = 'error';
        }
    }
}

// Fetch news only if news view
$items = [];
$categories = [];
$search = '';
$filter_category = '';
if (!$show_password_form) {
    $search = $_GET['search'] ?? '';
    $filter_category = $_GET['category'] ?? '';

    $sql = "SELECT n.*, u.username AS author FROM news n LEFT JOIN users u ON u.id = n.author_id WHERE 1=1";
    $params = [];

    if ($filter_category) {
        $sql .= " AND n.category = ?";
        $params[] = $filter_category;
    }

    if ($search) {
      $sql .= " AND (
          n.title LIKE ? OR
          n.content LIKE ? OR
          n.external_link LIKE ? OR
          n.contact_email LIKE ? OR
          n.contact_phone LIKE ?
      )";
      $searchTerm = "%$search%";
      $params = array_merge($params, [
          $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm
      ]);
  }

    $sql .= " ORDER BY n.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    $catsStmt = $pdo->query("SELECT DISTINCT category FROM news");
    $categories = $catsStmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin — Luxveris</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-gray-50">
<header class="border-b bg-white">
<div class="w-full px-6 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="/" class="text-xl font-black">Luxveris</a>
      <span class="text-gray-300">/</span>
      <span class="text-gray-600">Admin</span>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm text-gray-600">Hello, <?= h($user['username']) ?></span>
      <?php if (!$show_password_form): ?>
        <a class="px-3 py-1 rounded-lg border hover:bg-gray-100" href="/admin.php?action=change_password">Change Password</a>
      <?php endif; ?>
      <a class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-100" href="/logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="w-full px-6 py-8">

  <!-- Breadcrumbs -->
  <div class="mb-4 text-sm text-gray-500">
    <span>Admin</span>
    <?php if ($show_password_form): ?>
      &gt; <span>Change Password</span> 
      &nbsp;|&nbsp; <a href="/admin.php" class="text-blue-600 hover:underline">News Management</a>
    <?php else: ?>
      &gt; <span>News Management</span>
    <?php endif; ?>
  </div>

  <?php if ($show_password_form): ?>
    <div class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-bold mb-4">Change Password</h2>
        <?php if ($pw_message): ?>
            <?php $color = $pw_message_type === 'error' ? 'bg-red-100 border-red-300 text-red-800' : 'bg-green-100 border-green-300 text-green-800'; ?>
            <div class="mb-4 p-3 rounded border <?= $color ?>"><?= h($pw_message) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="hidden" name="change_password" value="1">
            <label class="block mb-2 text-sm">Current Password</label>
            <input type="password" name="current_password" class="w-full border rounded-lg p-2 mb-4" required>

            <label class="block mb-2 text-sm">New Password</label>
            <input type="password" name="new_password" class="w-full border rounded-lg p-2 mb-4" required>

            <label class="block mb-2 text-sm">Confirm New Password</label>
            <input type="password" name="confirm_password" class="w-full border rounded-lg p-2 mb-4" required>

            <button class="w-full rounded-xl bg-black text-white py-2 font-semibold hover:opacity-90">Update Password</button>
        </form>
    </div>
  <?php else: ?>
    <div class="mb-6 w-full">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Manage News</h1>
            <a href="/news_form.php" class="rounded-xl bg-black text-white px-4 py-2 font-semibold hover:opacity-90">+ Add News</a>
        </div>

        <!-- Search & Filter -->
        <form method="get" class="mb-4 flex gap-3 w-full">
            <input type="text" name="search" placeholder="Search by title, description, link, email, phone" value="<?= h($search) ?>" class="border rounded-lg p-2 flex-1">
            <select name="category" class="border rounded-lg p-2">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= h($c) ?>" <?= $c === $filter_category ? 'selected' : '' ?>><?= h($c) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="px-4 py-2 rounded-lg bg-black text-white">Go</button>
        </form>

        <div class="overflow-x-auto bg-white rounded-2xl shadow w-full">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="text-left p-3">Title</th>
                        <th class="text-left p-3">Category</th>
                        <th class="text-left p-3">Created</th>
                        <th class="text-left p-3">Author</th>
                        <th class="text-right p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $n): ?>
                    <tr class="border-t">
                        <td class="p-3"><?= h($n['title']) ?></td>
                        <td class="p-3"><?= h($n['category']) ?></td>
                        <td class="p-3"><?= h(date('Y-m-d H:i', strtotime($n['created_at']))) ?></td>
                        <td class="p-3"><?= h($n['author'] ?? 'Unknown') ?></td>
                        <td class="p-3 text-right">
                            <a class="px-3 py-1 rounded-lg border hover:bg-gray-50" href="/view.php?id=<?= (int)$n['id'] ?>">View</a>
                            <a class="px-3 py-1 rounded-lg border hover:bg-gray-50" href="/news_form.php?id=<?= (int)$n['id'] ?>">Edit</a>
                            <a class="px-3 py-1 rounded-lg border border-red-300 text-red-700 hover:bg-red-50" href="/delete.php?id=<?= (int)$n['id'] ?>" onclick="return confirm('Delete this article?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$items): ?>
                    <tr><td colspan="5" class="p-6 text-center text-gray-500">No articles found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
  <?php endif; ?>

</main>
</body>
</html>
