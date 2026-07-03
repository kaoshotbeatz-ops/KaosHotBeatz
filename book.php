<?php
require_once __DIR__ . '/partials.php';

$services = [
    'beatmaking' => 'Beat Making Session (2 hrs)',
    'recording'  => 'Recording Session (2 hrs)',
    'mixing'     => 'Mix & Master (per song)',
    'custom'     => 'Custom Production Consult',
];
$SLOTS = ['12:00', '14:00', '16:00', '18:00', '20:00']; // open times each day
$DURATION = 120;      // minutes
$CLOSED_DOW = [0];    // 0 = Sunday closed
$TZ = new DateTimeZone('America/New_York');

// Which date+time are already taken
$booked = [];
foreach (khb_load('bookings') as $bk) {
    if (($bk['status'] ?? '') !== 'cancelled') $booked[] = $bk['date'] . ' ' . $bk['time'];
}

// Accepted upload types for the photo + ID verification
$UPLOAD_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
$UPLOAD_MAX = 8 * 1024 * 1024; // 8MB
function khb_save_verification_upload($field, $dir, $basename, $types, $maxSize) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return null;
    $f = $_FILES[$field];
    if ($f['size'] > $maxSize) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!isset($types[$mime])) return false;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $path = $dir . '/' . $basename . '.' . $types[$mime];
    if (!move_uploaded_file($f['tmp_name'], $path)) return false;
    return basename($path);
}

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $name  = trim(strip_tags($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $svc   = $_POST['service'] ?? '';
    $date  = $_POST['date'] ?? ''; $time = $_POST['time'] ?? '';
    $notes = trim(strip_tags($_POST['notes'] ?? ''));

    $valid = $name && $email && isset($services[$svc])
        && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && in_array($time, $SLOTS, true);
    $dt = $valid ? DateTime::createFromFormat('Y-m-d H:i', "$date $time", $TZ) : null;
    if ($valid && (!$dt || $dt < new DateTime('now', $TZ))) $valid = false;

    $hasPhoto = !empty($_FILES['photo']['name']);
    $hasId    = !empty($_FILES['gov_id']['name']);

    if (!$valid)                              $err = 'Pick an open slot and add your name + a valid email.';
    elseif (in_array("$date $time", $booked)) $err = 'That slot was just taken — pick another.';
    elseif (!$hasPhoto || !$hasId)            $err = 'A photo and a government ID are required to confirm a session.';
    else {
        $id = khb_uuid();
        $uploadDir = __DIR__ . '/data/uploads/ids/' . $id;
        $photoFile = khb_save_verification_upload('photo', $uploadDir, 'photo', $UPLOAD_TYPES, $UPLOAD_MAX);
        $idFile    = khb_save_verification_upload('gov_id', $uploadDir, 'id', $UPLOAD_TYPES, $UPLOAD_MAX);
        if (!$photoFile || !$idFile) { $err = 'Photo/ID upload failed — use a JPG, PNG, WEBP, or PDF under 8MB.'; }
        else {
        $bookings = khb_load('bookings');
        $bookings[] = ['id' => $id, 'name' => $name, 'email' => $email, 'phone' => $phone,
            'service' => $svc, 'service_name' => $services[$svc], 'date' => $date, 'time' => $time,
            'duration' => $DURATION, 'notes' => $notes, 'status' => 'confirmed', 'ts' => time(),
            'verify_photo' => $photoFile, 'verify_id' => $idFile];
        khb_save('bookings', $bookings);
        $when = $dt->format('l, M j Y \a\t g:i A');
        @mail(SITE_EMAIL, 'New session booked — ' . $when,
            "$name booked {$services[$svc]}\n$when\nEmail: $email  Phone: $phone\nNotes: $notes",
            'From: no-reply@kaoshotbeatz.com');
        @mail($email, 'Your KAOS Hot Beatz session is booked',
            "You're booked: {$services[$svc]} on $when.\nAdd it to your calendar: https://kaoshotbeatz.com/book-ics.php?id=$id",
            'From: ' . SITE_EMAIL);
        header('Location: /book.php?confirmed=' . $id); exit;
        }
    }
}

// Confirmation view
$cid = $_GET['confirmed'] ?? ''; $cbk = null;
if ($cid) foreach (khb_load('bookings') as $bk) if ($bk['id'] === $cid) $cbk = $bk;

khb_header('Book a Session', 'book.php');

if ($cbk) {
    $dt  = DateTime::createFromFormat('Y-m-d H:i', $cbk['date'] . ' ' . $cbk['time'], $TZ);
    $end = (clone $dt)->modify('+' . ($cbk['duration'] ?? 120) . ' minutes');
    $uS  = (clone $dt)->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
    $uE  = (clone $end)->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
    $ttl = rawurlencode('KAOS Hot Beatz — ' . $cbk['service_name']);
    $det = rawurlencode('Studio session booked via kaoshotbeatz.com');
    $gcal = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=$ttl&dates=$uS/$uE&details=$det";
    $out  = 'https://outlook.live.com/calendar/0/deeplink/compose?subject=' . $ttl
          . '&startdt=' . $dt->format('Y-m-d\TH:i:s') . '&enddt=' . $end->format('Y-m-d\TH:i:s') . '&path=/calendar/action/compose';
    ?>
    <section><div class="wrap" style="max-width:640px">
      <div class="notice ok">🎉 Booked! You're locked in.</div>
      <div class="card" style="text-align:center">
        <p class="ey">Confirmed</p>
        <h2 style="margin:.2em 0"><?= h($cbk['service_name']) ?></h2>
        <p class="mono" style="font-size:1.2rem;color:var(--gold)"><?= h($dt->format('l, M j Y')) ?><br><?= h($dt->format('g:i A')) ?> – <?= h($end->format('g:i A')) ?></p>
        <p class="muted">Add it to your phone / calendar — works on Apple, Google & Outlook:</p>
        <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:14px">
          <a class="btn" href="/book-ics.php?id=<?= h($cbk['id']) ?>">📅 Apple / iCal</a>
          <a class="btn ghost" href="<?= h($gcal) ?>" target="_blank" rel="noopener">Google Calendar</a>
          <a class="btn ghost" href="<?= h($out) ?>" target="_blank" rel="noopener">Outlook</a>
        </div>
        <p class="muted" style="margin-top:16px;font-size:.85rem">A confirmation was emailed to <?= h($cbk['email']) ?>.</p>
        <a href="/book.php" class="muted">← Book another</a>
      </div>
    </div></section>
    <?php
    khb_footer(); exit;
}

// dataset for the calendar UI
$data = [
    'slots'   => $SLOTS,
    'booked'  => $booked,
    'closed'  => $CLOSED_DOW,
    'today'   => (new DateTime('now', $TZ))->format('Y-m-d'),
    'nowTime' => (new DateTime('now', $TZ))->format('H:i'),
];
?>
<section>
  <div class="wrap" style="max-width:820px">
    <p class="ey">Studio Time</p>
    <h2>Book a Session</h2>
    <p class="muted">Pick an open day and time — grey slots are already booked. It drops straight onto your phone's calendar after.</p>
    <?php if ($err): ?><div class="notice err"><?= h($err) ?></div><?php endif; ?>

    <div class="cal-wrap">
      <div class="cal-head">
        <button type="button" id="prevM" class="btn ghost sm">‹</button>
        <strong id="monLbl" class="mono"></strong>
        <button type="button" id="nextM" class="btn ghost sm">›</button>
      </div>
      <div class="cal-dow"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div>
      <div class="cal-grid" id="calGrid"></div>
    </div>

    <div id="slotBox" class="card" style="display:none;margin-top:18px">
      <h3 id="slotDay" style="margin-top:0"></h3>
      <div class="slots" id="slotRow"></div>
    </div>

    <form method="post" id="bookForm" class="card" style="display:none;margin-top:18px" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="date" id="fDate"><input type="hidden" name="time" id="fTime">
      <p class="ey">Booking: <span id="pick" class="mono" style="color:var(--gold)"></span></p>
      <div class="grid c2">
        <div><label>Name</label><input name="name" required></div>
        <div><label>Email</label><input type="email" name="email" required></div>
      </div>
      <div class="grid c2">
        <div><label>Phone</label><input name="phone"></div>
        <div><label>Service</label><select name="service" required><?php foreach ($services as $k => $v) echo '<option value="'.$k.'">'.h($v).'</option>'; ?></select></div>
      </div>
      <label>Notes (artist, reference tracks, what you're working on)</label>
      <textarea name="notes" rows="3"></textarea>
      <div class="grid c2" style="margin-top:14px">
        <div><label>Photo of yourself</label><input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></div>
        <div><label>Government ID</label><input type="file" name="gov_id" accept="image/jpeg,image/png,image/webp,application/pdf" required></div>
      </div>
      <p class="muted" style="font-size:.78rem;margin-top:6px">Required to confirm any in-person session. Used only to verify your identity at check-in and is never shared publicly. JPG, PNG, WEBP, or PDF, up to 8MB each.</p>
      <button class="btn block" style="margin-top:16px">Confirm booking →</button>
    </form>
  </div>
</section>

<script>
var CAL = <?= json_encode($data) ?>;
var bookedSet = new Set(CAL.booked);
var view = new Date(CAL.today + 'T00:00:00');
view.setDate(1);
var MON = ['January','February','March','April','May','June','July','August','September','October','November','December'];
function pad(n){return (n<10?'0':'')+n;}
function ymd(d){return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());}
var maxDate = new Date(CAL.today + 'T00:00:00'); maxDate.setDate(maxDate.getDate()+60);

function renderMonth(){
  document.getElementById('monLbl').textContent = MON[view.getMonth()]+' '+view.getFullYear();
  var grid = document.getElementById('calGrid'); grid.innerHTML='';
  var first = new Date(view); var startDow = first.getDay();
  for(var i=0;i<startDow;i++){ var e=document.createElement('div'); e.className='cal-cell empty'; grid.appendChild(e); }
  var dim = new Date(view.getFullYear(), view.getMonth()+1, 0).getDate();
  for(var d=1; d<=dim; d++){
    var cell=document.createElement('div'); cell.className='cal-cell';
    var dateObj=new Date(view.getFullYear(),view.getMonth(),d);
    var ds=ymd(dateObj);
    cell.textContent=d;
    var past = ds < CAL.today;
    var closed = CAL.closed.indexOf(dateObj.getDay())>=0;
    var tooFar = dateObj>maxDate;
    // any open slot left?
    var open=false;
    if(!past && !closed && !tooFar){
      for(var s=0;s<CAL.slots.length;s++){
        var key=ds+' '+CAL.slots[s];
        var slotPast = (ds===CAL.today && CAL.slots[s] <= CAL.nowTime);
        if(!bookedSet.has(key) && !slotPast){ open=true; break; }
      }
    }
    if(past||closed||tooFar||!open){ cell.classList.add('off'); }
    else { cell.classList.add('open'); cell.setAttribute('data-date',ds);
      cell.onclick=function(){ selectDay(this.getAttribute('data-date')); }; }
    grid.appendChild(cell);
  }
}
function selectDay(ds){
  document.querySelectorAll('.cal-cell.sel').forEach(function(c){c.classList.remove('sel');});
  document.querySelectorAll('.cal-cell.open').forEach(function(c){ if(c.getAttribute('data-date')===ds) c.classList.add('sel'); });
  var box=document.getElementById('slotBox'); box.style.display='block';
  var dObj=new Date(ds+'T00:00:00');
  document.getElementById('slotDay').textContent = dObj.toLocaleDateString(undefined,{weekday:'long',month:'long',day:'numeric'});
  var row=document.getElementById('slotRow'); row.innerHTML='';
  CAL.slots.forEach(function(t){
    var key=ds+' '+t; var taken=bookedSet.has(key);
    var slotPast=(ds===CAL.today && t<=CAL.nowTime);
    var b=document.createElement('div'); b.className='slot'+((taken||slotPast)?' taken':'');
    var h=parseInt(t), m=t.split(':')[1], ampm=h>=12?'PM':'AM', hr=((h+11)%12)+1;
    b.innerHTML='<strong>'+hr+':'+m+' '+ampm+'</strong>'+((taken||slotPast)?'<br><small>booked</small>':'');
    if(!taken && !slotPast){ b.onclick=function(){ pickSlot(ds,t,this); }; }
    row.appendChild(b);
  });
  document.getElementById('bookForm').style.display='none';
}
function pickSlot(ds,t,el){
  document.querySelectorAll('.slot.sel').forEach(function(s){s.classList.remove('sel');});
  el.classList.add('sel');
  document.getElementById('fDate').value=ds; document.getElementById('fTime').value=t;
  var dObj=new Date(ds+'T00:00:00'); var h=parseInt(t),m=t.split(':')[1],ampm=h>=12?'PM':'AM',hr=((h+11)%12)+1;
  document.getElementById('pick').textContent=dObj.toLocaleDateString(undefined,{weekday:'short',month:'short',day:'numeric'})+' · '+hr+':'+m+' '+ampm;
  var f=document.getElementById('bookForm'); f.style.display='block'; f.scrollIntoView({behavior:'smooth',block:'center'});
}
document.getElementById('prevM').onclick=function(){ view.setMonth(view.getMonth()-1); if(view.getMonth()<new Date(CAL.today).getMonth()&&view.getFullYear()<=new Date(CAL.today).getFullYear()){} renderMonth(); };
document.getElementById('nextM').onclick=function(){ view.setMonth(view.getMonth()+1); renderMonth(); };
renderMonth();
</script>
<?php khb_footer(); ?>
