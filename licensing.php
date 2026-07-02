<?php
require_once __DIR__ . '/partials.php';
$tiers = license_tiers();
khb_header('Licensing', '');
?>
<section>
  <div class="wrap">
    <p class="ey">Licensing</p>
    <h2>Pick the license that fits</h2>
    <p class="muted" style="max-width:60ch">Every beat is available under four license types. All non-exclusive leases are delivered instantly after checkout. Exclusive purchases transfer full ownership and remove the beat from the store.</p>
    <div class="grid c2" style="margin-top:30px">
      <?php $feat = ['excl']; foreach ($tiers as $k => $t): ?>
      <div class="card price-card<?= in_array($k,$feat)?' feat':'' ?>">
        <h3><?= h($t['name']) ?></h3>
        <div class="amt"><?= money($t['price']) ?></div>
        <p class="muted"><?= h($t['desc']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="card" style="margin-top:30px">
      <h3>The fine print</h3>
      <ul class="muted">
        <li>Leases are non-exclusive: the same beat may be licensed to other artists until sold exclusively.</li>
        <li>Producer credit ("Prod. by KAOS Hot Beatz") is required on all released work.</li>
        <li>Exclusive sales are final and transfer full rights; the beat is pulled from the store immediately.</li>
        <li>Beats may not be resold, redistributed, or registered to a content-ID/library as your own without an exclusive license.</li>
      </ul>
      <a class="btn" href="/beats.php">Browse beats →</a>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
