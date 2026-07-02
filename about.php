<?php
require_once __DIR__ . '/partials.php';
$gear = khb_load('gear'); // optional, admin-managed
khb_header('The Collection', 'about.php');
?>
<section class="hero" style="padding-bottom:40px">
  <div class="wrap">
    <p class="kicker">The Rig</p>
    <h1>A working collector's studio.</h1>
    <p class="lead">MPCs and Yamahas aren't display pieces here — they're the instruments. Every beat carries the swing, grit, and character of the machine it was made on.</p>
  </div>
</section>
<section>
  <div class="wrap">
    <div class="grid c2" style="align-items:center;margin-bottom:40px">
      <div>
        <h2>Why hardware</h2>
        <p class="muted">Software can copy the sound but not the feel. The timing of an MPC's swing, the response of weighted Yamaha keys, the way you commit to a take on real gear — that's the difference between a beat and a record. Nothing here is off a preset pack.</p>
      </div>
      <div class="pad-grid" style="position:relative;right:0;top:0;opacity:1;justify-content:center"><?php for($i=0;$i<16;$i++) echo '<i></i>'; ?></div>
    </div>

    <div class="section-head"><div><p class="ey">The Machines</p><h2>MPC & Yamaha collection</h2></div></div>
    <?php if ($gear): ?>
      <div class="grid c3">
        <?php foreach ($gear as $g): ?>
        <div class="card">
          <?php if(!empty($g['img'])): ?><img src="/assets/img/<?= h($g['img']) ?>" alt="<?= h($g['name']) ?>" style="width:100%;border-radius:8px;margin-bottom:12px"><?php endif; ?>
          <h3><?= h($g['name']) ?></h3><p class="muted"><?= h($g['note'] ?? '') ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="grid c3">
        <div class="card"><h3>Akai MPC</h3><p class="muted">The heart of the swing. Sample chopping and sequencing done the classic way.</p></div>
        <div class="card"><h3>Yamaha Keys</h3><p class="muted">Weighted keys and iconic Yamaha tone for chords, leads, and live playing.</p></div>
        <div class="card"><h3>+ more</h3><p class="muted">Outboard gear, synths, and drum machines rounding out the sound. Add your collection in the admin panel.</p></div>
      </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:48px">
      <a class="btn" href="/beats.php">Hear the beats</a>
      <a class="btn ghost" href="/book.php">Book studio time</a>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
