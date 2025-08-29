<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_login();
$pdo = db();

// Fetch latest news
$stmt = $pdo->query("SELECT n.*, u.username AS author FROM news n LEFT JOIN users u ON u.id = n.author_id ORDER BY n.created_at DESC");
$items = $stmt->fetchAll();
$user = current_user();
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
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="/" class="text-xl font-black">Luxveris</a>
      <span class="text-gray-300">/</span>
      <span class="text-gray-600">Admin</span>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm text-gray-600">Hello, <?= h($user['username']) ?></span>
      <a class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-100" href="/logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="max-w-6xl mx-auto px-4 py-8">
  <?= flash(); ?>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Manage News</h1>
    <a href="/news_form.php" class="rounded-xl bg-black text-white px-4 py-2 font-semibold hover:opacity-90">+ Add News</a>
  </div>

  <div class="overflow-x-auto bg-white rounded-2xl shadow">
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
        <tr><td colspan="5" class="p-6 text-center text-gray-500">No articles yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
