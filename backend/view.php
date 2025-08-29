<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/helpers.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT n.*, u.username AS author FROM news n LEFT JOIN users u ON u.id = n.author_id WHERE n.id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) {
    http_response_code(404);
}
function render_paragraphs($text) {
    $parts = preg_split("/\n\s*\n/", trim($text));
    $out = '';
    foreach ($parts as $p) {
        $out .= '<p class="mb-4 leading-7">'.nl2br(h(trim($p))).'</p>';
    }
    return $out;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= $article? h($article['title']) : 'Not Found' ?> — Luxveris</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-gray-50">
<header class="sticky top-0 z-40 backdrop-blur bg-white/80 border-b">
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <a href="/" class="text-2xl font-black tracking-tight">Luxveris</a>
    <a class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-100" href="/">Back</a>
  </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-8">
  <?php if (!$article): ?>
    <div class="card">Article not found.</div>
  <?php else: ?>
    <article class="card">
      <div class="flex items-center justify-between mb-3">
        <span class="badge <?=
          ['Educational'=>'badge-edu','Political'=>'badge-pol','Economical'=>'badge-eco','Technological'=>'badge-tech'][$article['category']] ?? 'badge-edu'
        ?>"><?= h($article['category']) ?></span>
        <time class="text-sm text-gray-500"><?= h(date('M j, Y', strtotime($article['created_at']))) ?></time>
      </div>
      <h1 class="text-3xl font-extrabold mb-4"><?= h($article['title']) ?></h1>
      <?php if ($article['image_path']): ?>
        <img src="<?= h($article['image_path']) ?>" class="w-full rounded-xl mb-6">
      <?php endif; ?>
      <div class="prose max-w-none">
        <?= render_paragraphs($article['content']) ?>
      </div>

      <div class="mt-6 grid md:grid-cols-3 gap-4 text-sm">
        <?php if ($article['external_link']): ?>
          <a class="rounded-xl border px-3 py-2 hover:bg-gray-50" href="<?= h($article['external_link']) ?>" target="_blank" rel="noopener">External Link →</a>
        <?php endif; ?>
        <?php if ($article['contact_email']): ?>
          <a class="rounded-xl border px-3 py-2 hover:bg-gray-50" href="mailto:<?= h($article['contact_email']) ?>">Email: <?= h($article['contact_email']) ?></a>
        <?php endif; ?>
        <?php if ($article['contact_phone']): ?>
          <a class="rounded-xl border px-3 py-2 hover:bg-gray-50" href="tel:<?= h($article['contact_phone']) ?>">Phone: <?= h($article['contact_phone']) ?></a>
        <?php endif; ?>
      </div>

      <div class="mt-6 text-sm text-gray-500">By <?= h($article['author'] ?? 'Unknown') ?></div>
    </article>
  <?php endif; ?>
</main>
</body>
</html>
