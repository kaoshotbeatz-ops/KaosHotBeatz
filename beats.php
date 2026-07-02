<?php
require_once __DIR__ . '/partials.php';
$beats = array_filter(khb_load('beats'), fn($b) => empty($b['sold_exclusive']));
$q = trim($_GET['q'] ?? ''); $genre = trim($_GET['genre'] ?? '');
$genres = [];
foreach ($beats as $b) if (!empty($b['genre'])) $genres[$b['genre']] = true;
if ($q) $beats = array_filter($beats, fn($b) => stripos($b['title'].' '.($b['moods']??'').' '.($b['genre']??''), $q) !== false);
if ($genre) $beats = array_filter($beats, fn($b) => ($b['genre'] ?? '') === $genre);
usort($beats, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
$tiers = license_tiers();
khb_header('Beats', 'beats.php');
?>
<section>
  <div class="wrap">
    <div class="section-head"><div><p class="ey">Beat Store</p><h2>Browse the catalog</h2></div></div>
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px">
      <input name="q" value="<?= h($q) ?>" placeholder="Search title, mood, genre…" style="flex:1;min-width:220px">
      <select name="genre" style="max-width:200px"><option value="">All genres</option>
        <?php foreach (array_keys($genres) as $g) echo '<option'.($g===$genre?' selected':'').'>'.h($g).'</option>'; ?>
      </select>
      <button class="btn">Filter</button>
    </form>
    <?php if (!$beats): ?>
      <div class="card"><p class="muted">No beats match. <a href="/beats.php">Clear filters</a>.</p></div>
    <?php else: ?>
    <div class="beat-list">
      <?php foreach ($beats as $b): $preview = $b['preview'] ? '/assets/beats/' . h($b['preview']) : ''; ?>
      <div class="beat">
        <?php if ($preview): ?><button class="play" data-src="<?= $preview ?>" data-title="<?= h($b['title']) ?>">▶</button><?php else: ?><span class="play" style="opacity:.3">♪</span><?php endif; ?>
        <div class="meta"><div class="t"><a href="/beat.php?id=<?= h($b['id']) ?>" style="color:var(--ink)"><?= h($b['title']) ?></a></div>
          <div class="s"><?= h($b['bpm']) ?> BPM · <?= h($b['key'] ?? '—') ?><?= !empty($b['genre']) ? ' · '.h($b['genre']) : '' ?></div></div>
        <div class="tags"><?php foreach (array_slice(explode(',', $b['moods'] ?? ''),0,3) as $t){ $t=trim($t); if($t) echo '<span class="tag">'.h($t).'</span>'; } ?></div>
        <a class="btn sm" href="/beat.php?id=<?= h($b['id']) ?>">From <?= money($tiers['mp3']['price']) ?></a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php khb_footer(); ?>
