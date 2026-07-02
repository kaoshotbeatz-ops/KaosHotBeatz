<?php
require_once __DIR__ . '/partials.php';
$beats = array_filter(khb_load('beats'), fn($b) => empty($b['sold_exclusive']));
usort($beats, fn($a, $b) => ($b['ts'] ?? 0) <=> ($a['ts'] ?? 0));
$featured = array_slice($beats, 0, 5);
$music = array_slice(suno_tracks(), 0, 3);
$tiers = license_tiers();
khb_header('Original Beats & Studio Sessions', '');
?>
<section class="hero split brick">
  <?php
    $piece = '<span class="piece"><svg viewBox="0 0 1520 340" xmlns="http://www.w3.org/2000/svg">'
      . '<text x="42" y="252" font-family="Anton,Impact,sans-serif" font-size="230" fill="#000">KAOSHOTBEATZ</text>'
      . '<text x="26" y="234" font-family="Anton,Impact,sans-serif" font-size="230" fill="#e11d1d" stroke="#000" stroke-width="8" paint-order="stroke" style="paint-order:stroke">KAOSHOTBEATZ</text>'
      . '<polygon points="1360,110 1470,150 1360,190" fill="#e11d1d" stroke="#000" stroke-width="8"/>'
      . '<circle cx="22" cy="70" r="8" fill="#e11d1d"/><circle cx="70" cy="40" r="5" fill="#e11d1d"/>'
      . '<circle cx="1330" cy="300" r="7" fill="#e11d1d"/><circle cx="1290" cy="316" r="4" fill="#e11d1d"/>'
      . '</svg></span>';
  ?>
  <div class="graf-scene"><div class="graf-track"><?= $piece . $piece ?></div></div>
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
        <a class="soc" href="<?= h(BEATSTARS_URL) ?>" target="_blank" rel="noopener">⭐ BeatStars</a>
        <a class="soc" href="<?= h(SOUNDCLOUD_URL) ?>" target="_blank" rel="noopener">☁ SoundCloud</a>
        <a class="soc" href="<?= h(YOUTUBE_URL) ?>" target="_blank" rel="noopener">▶ YouTube</a>
        <a class="soc" href="<?= h(INSTAGRAM_URL) ?>" target="_blank" rel="noopener">📸 Instagram</a>
        <a class="soc" href="<?= h(SUNO_URL) ?>" target="_blank" rel="noopener">Suno</a>
        <span class="wave" aria-hidden="true"><?php for($i=0;$i<14;$i++) echo '<i></i>'; ?></span>
      </div>
    </div>
    <div class="mpc">
      <svg viewBox="0 0 600 380" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Akai MPC 2000XL sampler">
        <rect x="8" y="8" width="584" height="364" rx="18" fill="#1e1e22" stroke="#000" stroke-width="3"/>
        <rect x="8" y="8" width="584" height="22" rx="18" fill="#2a2a30"/>
        <rect class="scr" x="36" y="44" width="214" height="104" rx="6" stroke="#000" stroke-width="3"/>
        <text x="48" y="72" fill="#3ecf8e" font-family="monospace" font-size="16">KAOS HOT BEATZ</text>
        <line class="scrline" x1="48" y1="90" x2="234" y2="90"/>
        <line class="scrline" x1="48" y1="106" x2="200" y2="106"/>
        <text x="48" y="134" fill="#3ecf8e" font-family="monospace" font-size="12">BPM 90   BOOM BAP</text>
        <circle class="wheel" cx="504" cy="94" r="58"/>
        <circle cx="504" cy="94" r="40" fill="none" stroke="#000" stroke-width="2"/>
        <circle cx="504" cy="54" r="6" fill="#e11d1d"/>
        <text class="lbl2" x="504" y="168" text-anchor="middle">DATA WHEEL</text>
        <rect class="btn" x="36" y="166" width="36" height="18" rx="3"/>
        <rect class="btn" x="80" y="166" width="36" height="18" rx="3"/>
        <rect class="btn" x="124" y="166" width="36" height="18" rx="3"/>
        <rect class="btn" x="168" y="166" width="36" height="18" rx="3"/>
        <circle class="btn" cx="54" cy="212" r="13"/>
        <circle class="btn" cx="92" cy="212" r="13"/>
        <rect class="btn" x="118" y="201" width="122" height="20" rx="10"/>
        <rect x="40" y="252" width="200" height="10" rx="5" fill="#0c0c0e" stroke="#000" stroke-width="2"/>
        <rect x="118" y="246" width="16" height="22" rx="3" fill="#e11d1d" stroke="#000" stroke-width="2"/>
        <rect x="40" y="292" width="200" height="10" rx="5" fill="#0c0c0e" stroke="#000" stroke-width="2"/>
        <rect x="168" y="286" width="16" height="22" rx="3" fill="#c9c9cf" stroke="#000" stroke-width="2"/>
        <?php
          $cols=[305,356,407,458]; $rows=[164,215,266,317];
          $blink=['p1',null,null,'p2',null,'p3',null,null,null,null,'p4',null,null,null,null,null];
          $i=0; foreach($rows as $ry){ foreach($cols as $cx){ $cls='pad'.(!empty($blink[$i])?' '.$blink[$i]:''); echo '<rect class="'.$cls.'" x="'.$cx.'" y="'.$ry.'" width="44" height="44" rx="5"/>'; $i++; } }
        ?>
        <text class="lbl" x="36" y="352">MPC2000XL</text>
        <text class="lbl2" x="305" y="150">16 LEVELS</text>
      </svg>
    </div>
  </div>
</section>

<span class="drip"><svg viewBox="0 0 1200 34" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><path fill="#e11d1d" d="M0 0h1200v10c-40 0-40 18-80 18s-40-12-80-12-40 20-80 20-40-16-80-16-40 10-80 10-40-18-80-18-40 16-80 16-40-14-80-14-40 20-80 20-40-18-80-18-40 12-80 12-40-16-80-16-40 14-80 14-40-10-80-10V0z"/><circle cx="150" cy="30" r="3" fill="#e11d1d"/><circle cx="620" cy="33" r="2.5" fill="#e11d1d"/><circle cx="980" cy="31" r="3" fill="#e11d1d"/></svg></span>

<div class="stats"><div class="wrap">
  <div class="stat"><div class="num"><?= h(STAT_PLAYS) ?></div><div class="lbl">Plays</div></div>
  <div class="stat"><div class="num"><?= h(STAT_SONGS) ?></div><div class="lbl">Records</div></div>
  <div class="stat"><div class="num">LI · NY</div><div class="lbl">Based</div></div>
  <div class="stat"><div class="num">MPC</div><div class="lbl">Hand-played</div></div>
</div></div>

<div class="marquee"><div class="track">
  <?php $g = str_repeat('<span><em>◆</em> Soul <em>◆</em> Hip-Hop <em>◆</em> Boom Bap <em>◆</em> Raw <em>◆</em> Gospel </span>', 2); echo $g; ?>
</div></div>

<section class="gfx-strip">
  <div class="wrap">
    <p class="ey">The Culture</p>
    <h2>Street · Soul · Hip-Hop</h2>
    <div class="gfx-row">
      <div class="gfx-icon">
        <svg viewBox="0 0 64 64"><rect class="st" x="4" y="10" width="56" height="44" rx="4"/><circle class="st" cx="27" cy="32" r="16"/><circle class="fl" cx="27" cy="32" r="4"/><line class="st" x1="53" y1="14" x2="38" y2="26"/><rect class="st" x="43" y="45" width="13" height="4" rx="2"/></svg>
        <div class="cap">Turntables</div>
      </div>
      <div class="gfx-icon">
        <svg viewBox="0 0 64 64"><rect class="st" x="8" y="8" width="48" height="48" rx="4"/><rect class="fl" x="16" y="18" width="8" height="8" rx="1"/><rect class="fl" x="28" y="18" width="8" height="8" rx="1"/><rect class="fl" x="40" y="18" width="8" height="8" rx="1"/><rect class="fl" x="16" y="30" width="8" height="8" rx="1"/><rect class="fl" x="28" y="30" width="8" height="8" rx="1"/><rect class="fl" x="40" y="30" width="8" height="8" rx="1"/><rect class="fl" x="16" y="42" width="8" height="8" rx="1"/><rect class="fl" x="28" y="42" width="8" height="8" rx="1"/><rect class="fl" x="40" y="42" width="8" height="8" rx="1"/></svg>
        <div class="cap">MPC 2000XL</div>
      </div>
      <div class="gfx-icon">
        <svg viewBox="0 0 64 64"><path class="st" d="M18 18a16 9 0 0 1 28 0"/><rect class="st" x="5" y="18" width="54" height="34" rx="4"/><circle class="st" cx="20" cy="36" r="8"/><circle class="st" cx="44" cy="36" r="8"/><rect class="fl" x="29" y="23" width="6" height="4" rx="1"/></svg>
        <div class="cap">Boombox</div>
      </div>
      <div class="gfx-icon">
        <svg viewBox="0 0 64 64"><rect class="st" x="22" y="18" width="20" height="38" rx="4"/><rect class="st" x="26" y="10" width="12" height="8" rx="2"/><rect class="fl" x="30" y="4" width="4" height="6" rx="1"/><circle class="fl" cx="50" cy="8" r="2"/><circle class="fl" cx="55" cy="14" r="1.6"/><circle class="fl" cx="49" cy="16" r="1.4"/></svg>
        <div class="cap">Spray Cans</div>
      </div>
      <div class="gfx-icon">
        <svg viewBox="0 0 64 64"><rect class="st" x="6" y="16" width="52" height="32" rx="4"/><rect class="st" x="14" y="22" width="36" height="11" rx="2"/><circle class="fl" cx="24" cy="27" r="3"/><circle class="fl" cx="40" cy="27" r="3"/><line class="st" x1="18" y1="42" x2="46" y2="42"/></svg>
        <div class="cap">Cassette</div>
      </div>
      <div class="gfx-icon">
        <svg viewBox="0 0 64 64"><rect class="st" x="24" y="6" width="16" height="30" rx="8"/><line class="st" x1="25" y1="16" x2="39" y2="16"/><line class="st" x1="25" y1="24" x2="39" y2="24"/><path class="st" d="M18 30a14 14 0 0 0 28 0"/><line class="st" x1="32" y1="44" x2="32" y2="54"/><line class="st" x1="22" y1="54" x2="42" y2="54"/></svg>
        <div class="cap">The Mic</div>
      </div>
    </div>
  </div>
</section>

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
        <a class="btn sm" href="/beat.php?id=<?= h($b['id']) ?>">Get Beat</a>
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
