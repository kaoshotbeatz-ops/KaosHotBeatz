<?php
require_once __DIR__ . '/partials.php';
$music = suno_tracks();
$playlists = suno_playlists();
khb_header('Listen', 'listen.php');
?>
<section class="hero" style="padding-bottom:36px">
  <div class="wrap">
    <p class="kicker"><?= h(ARTIST_GENRES) ?></p>
    <h1>The Catalog</h1>
    <p class="lead"><?= h(STAT_SONGS) ?> records · <?= h(STAT_PLAYS) ?> plays. Soul-sampled boom bap from Long Island.</p>
    <a class="suno-cta" href="<?= h(SUNO_URL) ?>" target="_blank" rel="noopener">🎵 Follow on Suno ↗</a>
  </div>
</section>
<section>
  <div class="wrap">
    <div class="section-head"><div><p class="ey">Playlists</p><h2>Curated on Suno</h2></div></div>
    <div class="grid c3" style="margin-bottom:44px">
      <?php foreach ($playlists as $p): ?>
      <a class="card" href="https://suno.com/playlist/<?= h($p['id']) ?>" target="_blank" rel="noopener" style="display:block">
        <h3 style="margin:0">▶ <?= h($p['name']) ?></h3>
        <p class="muted" style="margin:.3em 0 0"><?= (int)$p['count'] ?> tracks · Suno ↗</p>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="section-head"><div><p class="ey">Tracks</p><h2>Featured records</h2></div></div>
    <?php if (!$music): ?>
      <div class="card"><p class="muted">Tracks land here once added in the admin panel (Music tab). For now, stream everything on <a href="<?= h(SUNO_URL) ?>" target="_blank" rel="noopener">Suno ↗</a>.</p></div>
    <?php else: ?>
      <div class="suno-grid">
        <?php foreach ($music as $m): $sid = suno_id($m['suno']); ?>
          <div class="suno-embed">
            <?php if (!empty($m['title'])): ?><p style="font-weight:700;margin:0 0 8px"><?= h($m['title']) ?><?= !empty($m['tags']) ? ' <span class="tag">'.h($m['tags']).'</span>' : '' ?></p><?php endif; ?>
            <iframe src="https://suno.com/embed/<?= h($sid) ?>" loading="lazy" allow="autoplay" title="<?= h($m['title'] ?? 'Track') ?>"></iframe>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div style="text-align:center;margin-top:40px">
      <a class="btn" href="/beats.php">Shop the beats these came from →</a>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
