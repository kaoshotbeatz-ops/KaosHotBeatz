<?php
require_once __DIR__ . '/partials.php';
$services = [
    'beatmaking' => 'Beat Making Session (2 hrs)',
    'recording'  => 'Recording Session (2 hrs)',
    'mixing'     => 'Mix & Master (per song)',
    'custom'     => 'Custom Production Consult',
];
$msg = ''; $err = '';
// Build the next 14 days of slots (skip Sundays), 3 time blocks each.
$blocks = ['12:00', '15:00', '18:00'];
$booked = [];
foreach (khb_load('bookings') as $bk) if (($bk['status'] ?? '') !== 'cancelled') $booked[$bk['date'] . ' ' . $bk['time']] = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $name  = trim(strip_tags($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $svc   = $_POST['service'] ?? '';
    $date  = $_POST['date'] ?? ''; $time = $_POST['time'] ?? '';
    $notes = trim(strip_tags($_POST['notes'] ?? ''));
    if (!$name || !$email || !isset($services[$svc]) || !$date || !$time) {
        $err = 'Please fill in your name, a valid email, service, and pick a slot.';
    } elseif (isset($booked[$date . ' ' . $time])) {
        $err = 'That slot was just taken — please pick another.';
    } else {
        $bookings = khb_load('bookings');
        $id = khb_uuid();
        $bookings[] = ['id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone,
            'service' => $svc, 'service_name' => $services[$svc], 'date' => $date, 'time' => $time,
            'notes' => $notes, 'deposit' => DEPOSIT_AMOUNT, 'status' => 'pending_deposit', 'ts' => time()];
        khb_save('bookings', $bookings);
        $_SESSION['pending_booking'] = $id;
        header('Location: /book.php?deposit=' . $id); exit;
    }
}
$depositId = $_GET['deposit'] ?? '';
$depositBooking = null;
if ($depositId) foreach (khb_load('bookings') as $bk) if ($bk['id'] === $depositId) $depositBooking = $bk;

khb_header('Book a Session', 'book.php');
?>
<section>
  <div class="wrap" style="max-width:760px">
    <p class="ey">Studio Time</p>
    <h2>Book a Session</h2>

    <?php if ($depositBooking && ($depositBooking['status'] === 'pending_deposit')): ?>
      <div class="card">
        <h3>Lock your slot with a deposit</h3>
        <p class="muted"><?= h($depositBooking['service_name']) ?> · <?= h($depositBooking['date']) ?> at <?= h($depositBooking['time']) ?></p>
        <p>A <strong><?= money(DEPOSIT_AMOUNT) ?></strong> deposit confirms your booking. The balance is settled at the session.</p>
        <?php if (!PAYPAL_CLIENT): ?>
          <div class="notice err">Deposits not configured yet — add PayPal credentials to <code>config.php</code>. Your request is saved; we'll follow up by email.</div>
        <?php else: ?>
          <div id="dep-btn"></div><div id="dep-msg"></div>
          <script src="https://www.paypal.com/sdk/js?client-id=<?= h(PAYPAL_CLIENT) ?>&currency=USD"></script>
          <script>
          paypal.Buttons({
            createOrder:function(){return fetch('/deposit-pay.php?action=create&id=<?= h($depositBooking['id']) ?>',{method:'POST',headers:{'X-CSRF-Token':'<?= h(csrf_token()) ?>'}}).then(r=>r.json()).then(d=>d.id);},
            onApprove:function(data){return fetch('/deposit-pay.php?action=capture&id=<?= h($depositBooking['id']) ?>',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':'<?= h(csrf_token()) ?>'},body:JSON.stringify({orderID:data.orderID})}).then(r=>r.json()).then(function(res){if(res.ok){window.location='/book.php?confirmed=1';}else{document.getElementById('dep-msg').innerHTML='<div class="notice err">'+(res.error||'Deposit failed.')+'</div>';}});}
          }).render('#dep-btn');
          </script>
        <?php endif; ?>
        <p style="margin-top:14px"><a class="muted" href="/book.php">← Pick a different slot</a></p>
      </div>
    <?php else: ?>
      <?php if (!empty($_GET['confirmed'])): ?><div class="notice ok">🎉 Booking confirmed and deposit received! Check your email for details.</div><?php endif; ?>
      <?php if ($err): ?><div class="notice err"><?= h($err) ?></div><?php endif; ?>
      <p class="muted">Pick a service and an open slot. A <?= money(DEPOSIT_AMOUNT) ?> deposit locks your time — balance due at the session.</p>
      <form method="post" class="card" id="bookform">
        <?= csrf_field() ?>
        <div class="grid c2">
          <div><label>Name</label><input name="name" required></div>
          <div><label>Email</label><input type="email" name="email" required></div>
        </div>
        <div class="grid c2">
          <div><label>Phone</label><input name="phone"></div>
          <div><label>Service</label><select name="service" required><?php foreach ($services as $k => $v) echo '<option value="'.$k.'">'.h($v).'</option>'; ?></select></div>
        </div>
        <label>Pick a slot</label>
        <input type="hidden" name="date" id="f-date"><input type="hidden" name="time" id="f-time">
        <div class="slots" id="slots">
          <?php
          $tz = new DateTime('now');
          for ($day = 1; $day <= 14; $day++) {
              $dt = (clone $tz)->modify("+$day day");
              if ($dt->format('w') === '0') continue; // skip Sundays
              $ds = $dt->format('Y-m-d'); $label = $dt->format('D M j');
              foreach ($blocks as $t) {
                  $key = $ds . ' ' . $t;
                  $taken = isset($booked[$key]);
                  echo '<div class="slot'.($taken?' taken':'').'"'.($taken?'':' data-date="'.$ds.'" data-time="'.$t.'"').'>'
                     . '<div>'.h($label).'</div><small>'.h($t).'</small></div>';
              }
          }
          ?>
        </div>
        <label>Notes (artist, reference tracks, what you're working on)</label>
        <textarea name="notes" rows="3"></textarea>
        <button class="btn block" style="margin-top:18px" id="book-submit" disabled>Continue to deposit</button>
      </form>
      <script>
      document.getElementById('slots').addEventListener('click',function(e){
        var s=e.target.closest('.slot'); if(!s||s.classList.contains('taken'))return;
        document.querySelectorAll('.slot.sel').forEach(x=>x.classList.remove('sel'));
        s.classList.add('sel');
        document.getElementById('f-date').value=s.dataset.date;
        document.getElementById('f-time').value=s.dataset.time;
        document.getElementById('book-submit').disabled=false;
      });
      </script>
    <?php endif; ?>
  </div>
</section>
<?php khb_footer(); ?>
