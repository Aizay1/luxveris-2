<?php
require_once __DIR__ . '/config.php';

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function current_user() {
    if (!empty($_SESSION['user_id'])) {
        return ['id' => $_SESSION['user_id'], 'username' => $_SESSION['username'] ?? ''];
    }
    return null;
}
