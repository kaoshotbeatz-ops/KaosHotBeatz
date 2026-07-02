<?php
require_once __DIR__ . '/partials.php';
$beats = array_filter(khb_load('beats'), fn($b) => empty($b['sold_exclusive']));
usort($beats, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
$featured = array_slice($beats, 0, 6);
khb_header('Original MPC Beats & Studio Sessions', '');
?>
<section class="hero">
  <div class="pad-grid"><?php for($i=0;$i<16;$i++) echo '<i></i>'; ?></div>
  <div class="wrap">
    <p class="kicker">MPC-Crafted · Sample Chopped · Hand-Played</p>
    <h1>Beats built on <span style="color:var(--amber)">real machines.</span></h1>
    <p class="lead">Original instrumentals sequenced on classic Akai MPCs and Yamaha keys. Lease it, own it exclusive, or book studio time and we build it together.</p>
    <div class="hero-actions">
      <a class="btn" href="/beats.php">Browse Beats</a>
      <a class="btn ghost" href="/book.php">Book a Session</a>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="section-head">
      <div><p class="ey">Latest Drops</p><h2>Fresh from the pads</h2></div>
      <a class="btn ghost sm" href="/beats.php">All beats →</a>
    </div>
    <?php if (!$featured): ?>
      <div class="card"><p class="muted">No beats posted yet — check back soon, or <a href="/admin/">add your first beat in the admin panel</a>.</p></div>
    <?php else: ?>
    <div class="beat-list">
      <?php $tiers = license_tiers(); foreach ($featured as $b):
        $preview = $b['preview'] ? '/assets/beats/' . h($b['preview']) : ''; ?>
      <div class="beat">
        <?php if ($preview): ?><button class="play" data-src="<?= $preview ?>" data-title="<?= h($b['title']) ?>">▶</button><?php else: ?><span class="play" style="opacity:.3">♪</span><?php endif; ?>
        <div class="meta"><div class="t"><?= h($b['title']) ?></div>
          <div class="s"><?= h($b['bpm']) ?> BPM · <?= h($b['key'] ?? '—') ?><?= !empty($b['genre']) ? ' · ' . h($b['genre']) : '' ?></div></div>
        <div class="tags"><?php foreach (array_slice(explode(',', $b['moods'] ?? ''),0,3) as $t){ $t=trim($t); if($t) echo '<span class="tag">'.h($t).'</span>'; } ?></div>
        <a class="btn sm" href="/beat.php?id=<?= h($b['id']) ?>"><?= money($tiers['mp3']['price']) ?>+</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section style="background:var(--panel)">
  <div class="wrap">
    <div class="section-head"><div><p class="ey">Services</p><h2>What you can get</h2></div></div>
    <div class="grid c3">
      <div class="card"><h3>🎧 License a Beat</h3><p class="muted">Instant download after checkout. MP3, WAV, trackout stems, or full exclusive ownership — pick the license that fits your project.</p><a href="/beats.php">Shop beats →</a></div>
      <div class="card"><h3>🎚️ Studio Sessions</h3><p class="muted">Book in-studio or remote time. Beat making, recording, mixing. Lock your slot with a deposit and we build the record together.</p><a href="/book.php">Book time →</a></div>
      <div class="card"><h3>💿 Custom Production</h3><p class="muted">Need something bespoke? Commission a beat from scratch tailored to your artist, tempo, and vibe.</p><a href="/contact.php">Request a quote →</a></div>
    </div>
  </div>
</section>

<section>
  <div class="wrap">
    <div class="grid c2" style="align-items:center">
      <div>
        <p class="ey">The Sound</p>
        <h2>Analog soul, digital precision</h2>
        <p class="muted">Every beat starts on hardware — the swing of an MPC, the warmth of a Yamaha. No presets, no shortcuts. A working collector's rig, tuned by ear, sequenced by hand.</p>
        <a class="btn ghost" href="/about.php">See the collection</a>
      </div>
      <div class="pad-grid" style="position:relative;right:0;top:0;opacity:1;justify-content:center"><?php for($i=0;$i<16;$i++) echo '<i></i>'; ?></div>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
