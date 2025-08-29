<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

require_login();
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = db()->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash'] = "Deleted article #{$id}.";
}
header('Location: /admin.php');
exit;
