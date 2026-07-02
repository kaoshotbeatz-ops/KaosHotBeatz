<?php
require_once __DIR__ . '/partials.php';
$beats = array_filter(khb_load('beats'), fn($b) => empty($b['sold_exclusive']));
usort($beats, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
$featured = array_slice($beats, 0, 5);
$music = array_slice(suno_tracks(), 0, 3);
$tiers = license_tiers();
khb_header('Original Beats & Studio Sessions', '');
?>
<section class="hero split">
  <div class="wrap">
    <div>
      <p class="kicker"><?= h(ARTIST_TAGLINE) ?></p>
      <h1>KAOS<span class="out">Hot Beatz</span></h1>
      <p class="lead">Soul-sampled, MPC-swung boom bap out of Long Island. <?= h(STAT_PLAYS) ?> plays and counting. Stream the catalog, lease a beat, or book studio time and we cook one from scratch.</p>
      <div class="hero-actions">
        <a class="btn" href="/listen.php">▶ Listen</a>
        <a class="btn ghost" href="/beats.php">Shop Beats</a>
        <a class="btn ghost" href="/book.php">Book a Session</a>
      </div>
      <div class="socials">
        <a href="<?= h(BEATSTARS_URL) ?>" target="_blank" rel="noopener">⭐ BeatStars</a>
        <a href="<?= h(SUNO_URL) ?>" target="_blank" rel="noopener">🎵 Suno</a>
        <a href="<?= h(INSTAGRAM_URL) ?>" target="_blank" rel="noopener">📸 Instagram</a>
        <span class="wave" aria-hidden="true"><?php for($i=0;$i<14;$i++) echo '<i></i>'; ?></span>
      </div>
    </div>
    <div class="tt">
      <div class="platter"><span class="lbl">KAOS<br>HOT<br>BEATZ</span></div>
      <div class="arm"></div>
      <div class="fader"></div>
      <span class="badge">SL-1200 MK2</span>
    </div>
  </div>
</section>

<div class="stats"><div class="wrap">
  <div class="stat"><div class="num"><?= h(STAT_PLAYS) ?></div><div class="lbl">Plays</div></div>
  <div class="stat"><div class="num"><?= h(STAT_SONGS) ?></div><div class="lbl">Records</div></div>
  <div class="stat"><div class="num">LI · NY</div><div class="lbl">Based</div></div>
  <div class="stat"><div class="num">MPC</div><div class="lbl">Hand-played</div></div>
</div></div>

<div class="marquee"><div class="track">
  <?php $g = str_repeat('<span><em>◆</em> Soul <em>◆</em> Hip-Hop <em>◆</em> Boom Bap <em>◆</em> Raw <em>◆</em> Gospel </span>', 2); echo $g; ?>
</div></div>

<?php if ($music): ?>
<section>
  <div class="wrap">
    <div class="section-head"><div><p class="ey">Straight from Suno</p><h2>Now playing</h2></div>
      <a class="suno-cta" href="<?= h(SUNO_URL) ?>" target="_blank" rel="noopener">Full catalog on Suno ↗</a></div>
    <div class="suno-grid">
      <?php foreach ($music as $m): $sid = suno_id($m['suno']); ?>
        <div class="suno-embed"><iframe src="https://suno.com/embed/<?= h($sid) ?>" loading="lazy" allow="autoplay" title="<?= h($m['title'] ?? 'Track') ?>"></iframe></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php else: ?>
<section>
  <div class="wrap"><div class="section-head"><div><p class="ey">Straight from Suno</p><h2>Now playing</h2></div></div>
    <div class="card"><p class="muted">Feature your Suno tracks here — add their links in the admin panel (Music tab). Meanwhile, <a href="<?= h(SUNO_URL) ?>" target="_blank" rel="noopener">catch the full catalog on Suno ↗</a></p></div>
  </div>
</section>
<?php endif; ?>

<section style="background:var(--panel)">
  <div class="wrap">
    <div class="section-head"><div><p class="ey">Beat Store</p><h2>Latest drops</h2></div>
      <a class="btn ghost sm" href="/beats.php">All beats →</a></div>
    <?php if (!$featured): ?>
      <div class="card"><p class="muted">No beats posted yet — <a href="/admin/">add your first in the admin panel</a>.</p></div>
    <?php else: ?>
    <div class="beat-list">
      <?php foreach ($featured as $b): $preview = $b['preview'] ? '/assets/beats/' . h($b['preview']) : ''; ?>
      <div class="beat">
        <?php if ($preview): ?><button class="play" data-src="<?= $preview ?>" data-title="<?= h($b['title']) ?>">▶</button><?php else: ?><span class="play" style="opacity:.3">♪</span><?php endif; ?>
        <div class="meta"><div class="t"><?= h($b['title']) ?></div>
          <div class="s"><?= h($b['bpm']) ?> BPM · <?= h($b['key'] ?? '—') ?><?= !empty($b['genre']) ? ' · '.h($b['genre']) : '' ?></div></div>
        <div class="tags"><?php foreach (array_slice(explode(',', $b['moods'] ?? ''),0,3) as $t){ $t=trim($t); if($t) echo '<span class="tag">'.h($t).'</span>'; } ?></div>
        <a class="btn sm" href="/beat.php?id=<?= h($b['id']) ?>"><?= money($tiers['mp3']['price']) ?>+</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head"><div><p class="ey">Work with me</p><h2>What you can get</h2></div></div>
    <div class="grid c3">
      <div class="card"><h3>🎧 License a Beat</h3><p class="muted">MP3, WAV, trackout stems, or full exclusive ownership. Instant download after checkout.</p><a href="/beats.php">Shop beats →</a></div>
      <div class="card"><h3>🎚️ Studio Sessions</h3><p class="muted">In-studio or remote. Beat making, recording, mixing. Lock your slot with a deposit.</p><a href="/book.php">Book time →</a></div>
      <div class="card"><h3>💿 Custom Production</h3><p class="muted">Commission a record from scratch — built around your artist, tempo, and vibe.</p><a href="/contact.php">Request a quote →</a></div>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
