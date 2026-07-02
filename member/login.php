<?php
require_once __DIR__ . '/../partials.php';
if (current_member()) { header('Location: /member/account.php'); exit; }
$next = $_GET['next'] ?? ($_POST['next'] ?? '/member/account.php');
$err = '';
// Simple per-session throttle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $_SESSION['login_tries'] = ($_SESSION['login_tries'] ?? 0);
    if ($_SESSION['login_tries'] >= 8) { $err = 'Too many attempts — wait a few minutes.'; }
    else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $pass = $_POST['password'] ?? '';
        $member = null;
        foreach (khb_load('members') as $m) if ($m['email'] === $email) { $member = $m; break; }
        if ($member && password_verify($pass, $member['pass'])) {
            session_regenerate_id(true);
            $_SESSION['member_id'] = $member['id'];
            unset($_SESSION['login_tries']);
            header('Location: ' . (str_starts_with($next, '/') ? $next : '/member/account.php')); exit;
        }
        $_SESSION['login_tries']++;
        $err = 'Incorrect email or password.';
    }
}
khb_header('Sign In', '');
?>
<section><div class="form-box">
  <h2>Sign in</h2>
  <?php if ($err): ?><div class="notice err"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= h($next) ?>">
    <label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" required>
    <button class="btn block" style="margin-top:18px">Sign in</button>
  </form>
  <p class="muted" style="margin-top:16px">New here? <a href="/member/register.php?next=<?= h(urlencode($next)) ?>">Create an account</a></p>
</div></section>
<?php khb_footer(); ?>
