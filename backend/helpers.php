<?php
function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function selected($a, $b) { return $a === $b ? 'selected' : ''; }

function flash() {
    if (!empty($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return '<div class="my-4 p-3 rounded bg-green-100 border border-green-300 text-green-800">'.$msg.'</div>';
    }
    return '';
}
