<?php
require_once __DIR__ . '/../partials.php';
require_member();
$member = current_member();
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok() && isset($_POST['save_bio'])) {
    $bio = trim(strip_tags($_POST['bio'] ?? ''));
    $members = khb_load('members');
    foreach ($members as &$m) if ($m['id'] === $member['id']) { $m['bio'] = substr($bio, 0, 600); $member['bio'] = $m['bio']; }
    unset($m);
    khb_save('members', $members);
    $saved = true;
}
$orders = array_filter(khb_load('orders'), fn($o) => $o['member'] === $member['id']);
usort($orders, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
$bookings = array_filter(khb_load('bookings'), fn($b) => strtolower($b['email']) === strtolower($member['email']));
usort($bookings, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
khb_header('My Account', '');
?>
<section>
  <div class="wrap" style="max-width:860px">
    <div class="section-head">
      <div><p class="ey">Signed in as <?= h($member['email']) ?></p><h2>Hey <?= h($member['name']) ?> 👋</h2></div>
      <a class="btn ghost sm" href="/member/logout.php">Sign out</a>
    </div>
    <?php if (!empty($_GET['purchased'])): ?><div class="notice ok">Purchase complete — your downloads are below. 🎉</div><?php endif; ?>
    <?php if ($saved): ?><div class="notice ok">Profile updated.</div><?php endif; ?>

    <h3>Your profile</h3>
    <form method="post" class="card" style="margin-bottom:24px">
      <?= csrf_field() ?><input type="hidden" name="save_bio" value="1">
      <label>Bio <span class="muted">(artist name, sound, socials — shown on your profile)</span></label>
      <textarea name="bio" rows="3" maxlength="600"><?= h($member['bio'] ?? '') ?></textarea>
      <button class="btn sm" style="margin-top:10px">Save bio</button>
    </form>

    <h3>Your beats & downloads</h3>
    <?php if (!$orders): ?>
      <div class="card"><p class="muted">No purchases yet. <a href="/beats.php">Browse beats →</a></p></div>
    <?php else: foreach ($orders as $o): ?>
      <div class="card" style="margin-bottom:14px">
        <div class="muted mono" style="font-size:.82rem"><?= date('M j, Y', $o['ts']) ?> · Order <?= h(substr($o['id'],0,8)) ?> · <?= money($o['total']) ?></div>
        <?php foreach ($o['items'] as $it): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-top:1px solid var(--line)">
            <div><strong><?= h($it['title']) ?></strong> <span class="tag"><?= h($it['tier_name']) ?></span></div>
            <a class="btn sm" href="/download.php?t=<?= h($it['download']) ?>">↓ Download</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; endif; ?>

    <h3 style="margin-top:34px">Your sessions</h3>
    <?php if (!$bookings): ?>
      <div class="card"><p class="muted">No sessions booked. <a href="/book.php">Book studio time →</a></p></div>
    <?php else: foreach ($bookings as $b): ?>
      <div class="card" style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center">
        <div><strong><?= h($b['service_name']) ?></strong><br><span class="muted mono"><?= h($b['date']) ?> at <?= h($b['time']) ?></span></div>
        <span class="tag"><?= h(str_replace('_',' ',$b['status'])) ?></span>
      </div>
    <?php endforeach; endif; ?>
  </div>
</section>
<?php khb_footer(); ?>
