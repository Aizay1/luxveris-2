<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_login();
$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = $id > 0;

$title = $category = $content = $external_link = $contact_email = $contact_phone = $image_path = '';

if ($editing) {
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $title = $row['title'];
        $category = $row['category'];
        $content = $row['content'];
        $external_link = $row['external_link'];
        $contact_email = $row['contact_email'];
        $contact_phone = $row['contact_phone'];
        $image_path = $row['image_path'];
    } else {
        $_SESSION['flash'] = "Article not found.";
        header('Location: /admin.php'); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $content = trim($_POST['content'] ?? '');
    $external_link = trim($_POST['external_link'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $f = $_FILES['image'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowed)) {
                $newName = 'uploads/' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], __DIR__ . '/' . $newName)) {
                    $image_path = '/' . $newName;
                }
            }
        }
    }

    if ($title && $category && $content) {
        if ($editing) {
            $sql = "UPDATE news SET title=?, category=?, content=?, external_link=?, contact_email=?, contact_phone=?, image_path=IF(? IS NULL OR ?='', image_path, ?) WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title,$category,$content,$external_link,$contact_email,$contact_phone,$image_path,$image_path,$image_path,$id]);
            $_SESSION['flash'] = "Article updated.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO news (title, category, content, image_path, external_link, contact_email, contact_phone, author_id) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$title,$category,$content,$image_path ?: null,$external_link ?: null,$contact_email ?: null,$contact_phone ?: null,$_SESSION['user_id']]);
            $_SESSION['flash'] = "Article created.";
        }
        header('Location: /admin.php'); exit;
    } else {
        $_SESSION['flash'] = "Please fill required fields.";
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?= $editing?'Edit':'Add' ?> News — Luxveris</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="/assets/app.css">
</head>
<body class="bg-gray-50">
<header class="border-b bg-white">
  <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <a href="/" class="text-xl font-black">Luxveris</a>
      <span class="text-gray-300">/</span>
      <a href="/admin.php" class="text-gray-600 hover:underline">Admin</a>
      <span class="text-gray-300">/</span>
      <span><?= $editing ? 'Edit' : 'Add' ?> News</span>
    </div>
    <a class="px-3 py-1 rounded-lg border border-gray-300 hover:bg-gray-100" href="/logout.php">Logout</a>
  </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-8">
  <?= flash(); ?>

  <form method="post" enctype="multipart/form-data" class="card">
    <div class="grid gap-4">
      <div>
        <label class="block mb-2 text-sm">Title *</label>
        <input name="title" class="w-full border rounded-lg p-2" value="<?= h($title) ?>" required>
      </div>

      <div>
        <label class="block mb-2 text-sm">Category *</label>
        <select name="category" class="w-full border rounded-lg p-2" required>
          <?php
          $cats = ['Educational','Political','Economical','Technological'];
          foreach ($cats as $c) {
            echo '<option '.selected($category?:$cats[0], $c).'>'.$c.'</option>';
          }
          ?>
        </select>
      </div>

      <div>
        <label class="block mb-2 text-sm">Content (supports paragraphs) *</label>
        <textarea name="content" rows="10" class="w-full border rounded-lg p-2" placeholder="Write your article..."><?= h($content) ?></textarea>
        <p class="text-xs text-gray-500 mt-1">Use blank lines to separate paragraphs.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-4">
        <div>
          <label class="block mb-2 text-sm">External Link</label>
          <input name="external_link" class="w-full border rounded-lg p-2" value="<?= h($external_link) ?>" placeholder="https://example.com">
        </div>
        <div>
          <label class="block mb-2 text-sm">Contact Email</label>
          <input type="email" name="contact_email" class="w-full border rounded-lg p-2" value="<?= h($contact_email) ?>" placeholder="editor@example.com">
        </div>
        <div>
          <label class="block mb-2 text-sm">Contact Phone</label>
          <input name="contact_phone" class="w-full border rounded-lg p-2" value="<?= h($contact_phone) ?>" placeholder="+251 9xx xxx xxx">
        </div>
      </div>

      <div>
        <label class="block mb-2 text-sm">Image (jpg, png, webp, gif)</label>
        <?php if ($image_path): ?>
          <img src="<?= h($image_path) ?>" class="w-full max-h-56 object-cover rounded-xl mb-2">
        <?php endif; ?>
        <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full border rounded-lg p-2 bg-white">
      </div>

      <div class="flex items-center justify-end gap-3 pt-2">
        <a href="/admin.php" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Cancel</a>
        <button class="px-5 py-2 rounded-xl bg-black text-white font-semibold hover:opacity-90"><?= $editing?'Save Changes':'Create' ?></button>
      </div>
    </div>
  </form>
</main>
</body>
</html>
