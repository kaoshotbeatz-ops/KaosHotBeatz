<?php
require_once __DIR__ . '/partials.php';
$id = $_GET['id'] ?? '';
$beat = null; foreach (khb_load('beats') as $b) if ($b['id'] === $id) { $beat = $b; break; }

// Add-to-cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $beat && csrf_ok()) {
    $tier = $_POST['tier'] ?? '';
    if (isset(license_tiers()[$tier])) { cart_add($beat['id'], $tier); header('Location: /cart.php'); exit; }
}
if (!$beat || !empty($beat['sold_exclusive'])) {
    khb_header('Beat not found','beats.php');
    echo '<section><div class="wrap"><h2>Beat not available</h2><p class="muted">This beat may have sold exclusively or been removed. <a href="/beats.php">Browse other beats →</a></p></div></section>';
    khb_footer(); exit;
}
$tiers = license_tiers();
$preview = $beat['preview'] ? '/assets/beats/' . h($beat['preview']) : '';
khb_header($beat['title'], 'beats.php');
?>
<section>
  <div class="wrap">
    <a href="/beats.php" class="muted">← All beats</a>
    <div class="grid c2" style="margin-top:20px;align-items:start">
      <div class="card">
        <p class="ey mono" style="color:var(--amber)"><?= h($beat['bpm']) ?> BPM · <?= h($beat['key'] ?? '—') ?></p>
        <h1 style="font-size:2.2rem"><?= h($beat['title']) ?></h1>
        <div class="tags" style="margin:10px 0 18px">
          <?php if(!empty($beat['genre'])) echo '<span class="tag">'.h($beat['genre']).'</span>'; ?>
          <?php foreach (explode(',', $beat['moods'] ?? '') as $t){ $t=trim($t); if($t) echo '<span class="tag">'.h($t).'</span>'; } ?>
        </div>
        <?php if ($preview): ?>
          <button class="play btn ghost" data-src="<?= $preview ?>" data-title="<?= h($beat['title']) ?>">▶ Preview</button>
        <?php endif; ?>
        <?php if (!empty($beat['desc'])): ?><p class="muted" style="margin-top:18px"><?= nl2br(h($beat['desc'])) ?></p><?php endif; ?>
      </div>
      <div>
        <h3>Choose a license</h3>
        <?php foreach ($tiers as $key => $t): ?>
        <form method="post" class="card" style="margin-bottom:12px;display:flex;align-items:center;gap:16px">
          <?= csrf_field() ?>
          <input type="hidden" name="tier" value="<?= $key ?>">
          <div style="flex:1"><strong><?= h($t['name']) ?></strong><br><span class="muted" style="font-size:.86rem"><?= h($t['desc']) ?></span></div>
          <button class="btn sm">Add</button>
        </form>
        <?php endforeach; ?>
        <p class="muted" style="font-size:.82rem">Instant download after checkout. See full <a href="/licensing.php">licensing terms</a>.</p>
        <p style="margin-top:6px"><a class="suno-cta" href="<?= h(BEATSTARS_URL) ?>" target="_blank" rel="noopener" style="font-size:.85rem;padding:8px 14px">⭐ Prefer BeatStars? Buy there ↗</a></p>
      </div>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
