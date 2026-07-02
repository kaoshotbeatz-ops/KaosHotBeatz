<?php
require_once __DIR__ . '/partials.php';
$err = ''; $sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $name = trim(strip_tags($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = trim(strip_tags($_POST['subject'] ?? ''));
    $message = trim(strip_tags($_POST['message'] ?? ''));
    if (!$name || !$email || !$message) { $err = 'Please add your name, a valid email, and a message.'; }
    else {
        $inq = khb_load('inquiries');
        $inq[] = ['id' => khb_uuid(), 'ts' => time(), 'name' => $name, 'email' => $email,
                  'subject' => $subject ?: 'General', 'message' => $message, 'read' => false];
        khb_save('inquiries', $inq);
        @mail(SITE_EMAIL, 'KHB inquiry: ' . ($subject ?: 'General'),
            "From: $name <$email>\n\n$message", "From: no-reply@kaoshotbeatz.com\r\nReply-To: $email");
        $sent = true;
    }
}
khb_header('Contact', 'contact.php');
?>
<section>
  <div class="wrap" style="max-width:620px">
    <p class="ey">Get at me</p>
    <h2>Contact & Custom Work</h2>
    <p class="muted">Custom production, collabs, exclusives, press — drop a line. For studio time, use <a href="/book.php">the booking page</a>.</p>
    <?php if ($sent): ?><div class="notice ok">Message sent — I'll get back to you soon. 🎧</div><?php endif; ?>
    <?php if ($err): ?><div class="notice err"><?= h($err) ?></div><?php endif; ?>
    <form method="post" class="card">
      <?= csrf_field() ?>
      <div class="grid c2">
        <div><label>Name</label><input name="name" required></div>
        <div><label>Email</label><input type="email" name="email" required></div>
      </div>
      <label>Subject</label>
      <select name="subject"><option>Custom beat</option><option>Exclusive purchase</option><option>Collab</option><option>Mixing/Mastering</option><option>Press / Booking</option><option>Other</option></select>
      <label>Message</label><textarea name="message" rows="5" required></textarea>
      <button class="btn block" style="margin-top:16px">Send message</button>
    </form>
  </div>
</section>
<?php khb_footer(); ?>
