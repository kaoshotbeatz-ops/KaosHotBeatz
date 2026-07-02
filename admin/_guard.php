<?php
// Admin gate — single password from config.php (ADMIN_PASS), session + CSRF + throttle.
require_once __DIR__ . '/../lib.php';
if (!defined('ADMIN_PASS')) define('ADMIN_PASS', 'change-me');

$authed = !empty($_SESSION['khb_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $_SESSION['admin_tries'] = ($_SESSION['admin_tries'] ?? 0);
    if ($_SESSION['admin_tries'] < 8 && hash_equals(ADMIN_PASS, $_POST['password'] ?? '')) {
        session_regenerate_id(true);
        $_SESSION['khb_admin'] = true;
        unset($_SESSION['admin_tries']);
        header('Location: /admin/'); exit;
    }
    $_SESSION['admin_tries'] = ($_SESSION['admin_tries'] ?? 0) + 1;
    $loginErr = 'Incorrect password.';
}
if (isset($_GET['logout'])) { unset($_SESSION['khb_admin']); header('Location: /admin/'); exit; }

function admin_login_page($err = '') {
    ?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin — KAOS Hot Beatz</title>
    <meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"></head>
    <body><section><div class="form-box"><h2>🎛️ Admin</h2>
    <?php if ($err) echo '<div class="notice err">'.h($err).'</div>'; ?>
    <form method="post"><label>Password</label><input type="password" name="password" autofocus>
    <input type="hidden" name="admin_login" value="1">
    <button class="btn block" style="margin-top:16px">Sign in</button></form></div></section></body></html><?php
    exit;
}
if (!$authed) admin_login_page($loginErr ?? '');
if (empty($_SESSION['acsrf'])) $_SESSION['acsrf'] = bin2hex(random_bytes(32));
function acsrf_field(){ return '<input type="hidden" name="acsrf" value="'.h($_SESSION['acsrf']).'">'; }
function acsrf_ok(){ return hash_equals($_SESSION['acsrf'] ?? '', $_POST['acsrf'] ?? ''); }
