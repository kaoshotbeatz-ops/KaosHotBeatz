<?php
require_once __DIR__ . '/partials.php';
$beats = array_filter(khb_load('beats'), fn($b) => empty($b['sold_exclusive']));
usort($beats, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
khb_header('Listen', 'listen.php');
?>
<section class="hero brick" style="padding-bottom:34px">
  <div class="wrap">
    <p class="kicker"><?= h(ARTIST_GENRES) ?></p>
    <h1>The Catalog</h1>
    <p class="lead"><?= count($beats) ?> beats, hand-played on the MPC. Hit play — everything streams right here.</p>
  </div>
</section>
<section>
  <div class="wrap">
    <?php if (!$beats): ?>
      <div class="card"><p class="muted">No beats posted yet. <a href="/admin/">Add some in the admin panel</a>.</p></div>
    <?php else: ?>
    <div class="beat-list">
      <?php foreach ($beats as $b): $preview = $b['preview'] ? '/assets/beats/' . h($b['preview']) : ''; ?>
      <div class="beat">
        <?php if ($preview): ?><button class="play" data-src="<?= $preview ?>" data-title="<?= h($b['title']) ?>">▶</button><?php else: ?><span class="play" style="opacity:.3">♪</span><?php endif; ?>
        <div class="meta"><div class="t"><a href="/beat.php?id=<?= h($b['id']) ?>" style="color:var(--ink)"><?= h($b['title']) ?></a></div>
          <div class="s"><?= h($b['genre'] ?? '') ?><?= !empty($b['bpm']) ? ' · ' . h($b['bpm']) . ' BPM' : '' ?></div></div>
        <div class="tags"><?php foreach (array_slice(explode(',', $b['moods'] ?? ''),0,3) as $t){ $t=trim($t); if($t) echo '<span class="tag">'.h($t).'</span>'; } ?></div>
        <a class="btn sm" href="/beat.php?id=<?= h($b['id']) ?>">Get Beat</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div style="text-align:center;margin-top:40px">
      <a class="btn" href="/beats.php">Browse the full store →</a>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
