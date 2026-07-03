<?php
require_once __DIR__ . '/../partials.php';
if (current_member()) { header('Location: /member/account.php'); exit; }
$next = $_GET['next'] ?? ($_POST['next'] ?? '/member/account.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $name = trim(strip_tags($_POST['name'] ?? ''));
    $email = strtolower(filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL));
    $pass = $_POST['password'] ?? '';
    $members = khb_load('members');
    if (!$name || !$email) $err = 'Enter your name and a valid email.';
    elseif (strlen($pass) < 8) $err = 'Password must be at least 8 characters.';
    elseif (array_filter($members, fn($m) => $m['email'] === $email)) $err = 'An account with that email already exists.';
    else {
        $id = khb_uuid();
        $members[] = ['id' => $id, 'name' => $name, 'email' => $email,
            'pass' => password_hash($pass, PASSWORD_DEFAULT), 'ts' => time()];
        khb_save('members', $members);
        session_regenerate_id(true);
        $_SESSION['member_id'] = $id;
        header('Location: ' . (str_starts_with($next, '/') ? $next : '/member/account.php')); exit;
    }
}
khb_header('Create Account', '');
?>
<section><div class="form-box">
  <h2>Create your account</h2>
  <p class="muted">Save your purchases, re-download files anytime, and check out faster.</p>
  <?php if ($err): ?><div class="notice err"><?= h($err) ?></div><?php endif; ?>
  <form method="post">
    <?= csrf_field() ?><input type="hidden" name="next" value="<?= h($next) ?>">
    <label>Name / artist name</label><input name="name" required>
    <label>Email</label><input type="email" name="email" required>
    <label>Password</label><input type="password" name="password" minlength="8" required>
    <button class="btn block" style="margin-top:18px">Create account</button>
    <p class="muted" style="font-size:.78rem;margin-top:10px">By creating an account, you agree to our <a href="/terms.php">Terms &amp; Conditions</a> and <a href="/privacy.php">Privacy Policy</a>.</p>
  </form>
  <p class="muted" style="margin-top:16px">Already have one? <a href="/member/login.php?next=<?= h(urlencode($next)) ?>">Sign in</a></p>
</div></section>
<?php khb_footer(); ?>
