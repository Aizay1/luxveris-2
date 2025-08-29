<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/helpers.php';

$pdo = db();

// Optional category filter
$category = $_GET['category'] ?? '';
$params = [];
$sql = "SELECT n.*, u.username AS author FROM news n LEFT JOIN users u ON u.id = n.author_id";
if ($category && in_array($category, ['Educational','Political','Economical','Technological'])) {
    $sql .= " WHERE category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY created_at DESC LIMIT 50";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Luxveris — News</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/app.css">
<script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 text-gray-800">

<!-- Header -->
<header class="sticky top-0 z-40 backdrop-blur bg-white/80 border-b shadow-sm">
  <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
    <a href="/" class="text-3xl font-extrabold tracking-tight text-indigo-600 hover:text-indigo-800 transition">Luxveris</a>
    
    <!-- Nav -->
    <nav class="hidden md:flex gap-6 font-medium">
      <a class="hover:text-indigo-600 transition" href="/">All</a>
      <a class="hover:text-indigo-600 transition" href="/?category=Educational">Educational</a>
      <a class="hover:text-indigo-600 transition" href="/?category=Political">Political</a>
      <a class="hover:text-indigo-600 transition" href="/?category=Economical">Economical</a>
      <a class="hover:text-indigo-600 transition" href="/?category=Technological">Technological</a>
    </nav>
    
    <div class="flex items-center gap-3">
      <a class="px-4 py-2 rounded-xl border border-gray-300 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-700 transition" href="/login.php">Admin</a>
    </div>
  </div>
</header>

<!-- Main -->
<main class="max-w-7xl mx-auto px-4 py-10">
  <?= flash(); ?>
  
  <div class="grid md:grid-cols-3 gap-8">
    <?php foreach ($items as $n): 
      $badgeClass = [
        'Educational'=>'badge-edu','Political'=>'badge-pol',
        'Economical'=>'badge-eco','Technological'=>'badge-tech'
      ][$n['category']] ?? 'badge-edu';
    ?>
    <article class="card hover:shadow-2xl transition duration-300 transform hover:-translate-y-1 hover:scale-[1.02] bg-white rounded-2xl overflow-hidden border">
      <?php if (!empty($n['image_path'])): ?>
        <a href="/view.php?id=<?= (int)$n['id'] ?>">
          <img src="<?= h($n['image_path']) ?>" alt="" class="w-full h-48 object-cover">
        </a>
      <?php endif; ?>
      
      <div class="p-5">
        <div class="flex items-center justify-between mb-3">
          <span class="badge <?= $badgeClass ?>"><?= h($n['category']) ?></span>
          <time class="text-xs text-gray-400"><?= h(date('M j, Y', strtotime($n['created_at']))) ?></time>
        </div>
        <a href="/view.php?id=<?= (int)$n['id'] ?>" class="block">
          <h2 class="text-xl font-bold mb-2 line-clamp-2 hover:text-indigo-600 transition"><?= h($n['title']) ?></h2>
        </a>
        <div class="text-sm text-gray-500">By <?= h($n['author'] ?? 'Unknown') ?></div>
      </div>
    </article>
    <?php endforeach; ?>
    
    <?php if (!$items): ?>
      <div class="md:col-span-3 text-center text-gray-500 py-10">🚀 No news yet. Check back soon.</div>
    <?php endif; ?>
  </div>
</main>

<!-- Footer -->
<footer class="border-t bg-white mt-12">
  <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col md:flex-row items-center justify-between text-sm text-gray-500">
    <p>© <?= date('Y') ?> <span class="font-semibold">Luxveris</span>. All rights reserved.</p>
    <div class="flex gap-4 mt-3 md:mt-0">
      <a href="#" class="hover:text-indigo-600">Privacy</a>
      <a href="#" class="hover:text-indigo-600">Terms</a>
      <a href="#" class="hover:text-indigo-600">Contact</a>
    </div>
  </div>
</footer>

</body>
</html>
