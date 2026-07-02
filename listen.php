<?php
require_once __DIR__ . '/partials.php';
$music = khb_load('music');
usort($music, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
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
