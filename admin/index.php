<?php
require __DIR__ . '/_guard.php';
$tab = $_GET['tab'] ?? 'dash';
$notice = '';

// ---------- Actions ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && acsrf_ok()) {
    $act = $_POST['action'] ?? '';

    if ($act === 'save_beat') {
        $beats = khb_load('beats');
        $id = $_POST['id'] ?: khb_uuid();
        $row = ['id' => $id, 'title' => trim($_POST['title']), 'bpm' => trim($_POST['bpm']),
            'key' => trim($_POST['key']), 'genre' => trim($_POST['genre']), 'moods' => trim($_POST['moods']),
            'desc' => trim($_POST['desc']), 'sold_exclusive' => false, 'ts' => time()];
        // Preserve existing files if editing
        foreach ($beats as $b) if ($b['id'] === $id) {
            $row['preview'] = $b['preview'] ?? ''; $row['file_mp3'] = $b['file_mp3'] ?? '';
            $row['file_wav'] = $b['file_wav'] ?? ''; $row['file_stems'] = $b['file_stems'] ?? '';
            $row['ts'] = $b['ts'] ?? time(); $row['sold_exclusive'] = $b['sold_exclusive'] ?? false;
        }
        $row += ['preview'=>'','file_mp3'=>'','file_wav'=>'','file_stems'=>''];
        // Handle uploads
        $up = function($field) use ($id) {
            if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] !== 0) return null;
            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $ok = ['mp3','wav','zip','m4a','aif','aiff'];
            if (!in_array($ext, $ok)) return null;
            $name = $id . '-' . $field . '.' . $ext;
            $dest = __DIR__ . '/../assets/beats/' . $name;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) return $name;
            return null;
        };
        foreach (['preview','file_mp3','file_wav','file_stems'] as $f) { $n = $up($f); if ($n) $row[$f] = $n; }
        $found = false;
        foreach ($beats as &$b) if ($b['id'] === $id) { $b = $row; $found = true; }
        unset($b);
        if (!$found) $beats[] = $row;
        khb_save('beats', $beats);
        $notice = 'Beat saved.'; $tab = 'beats';
    }
    if ($act === 'del_beat') {
        khb_save('beats', array_values(array_filter(khb_load('beats'), fn($b) => $b['id'] !== $_POST['id'])));
        $notice = 'Beat deleted.'; $tab = 'beats';
    }
    if ($act === 'booking_status') {
        $bk = khb_load('bookings');
        foreach ($bk as &$b) if ($b['id'] === $_POST['id']) $b['status'] = $_POST['status'];
        unset($b); khb_save('bookings', $bk); $notice = 'Booking updated.'; $tab = 'bookings';
    }
    if ($act === 'save_gear') {
        $gear = khb_load('gear');
        $g = ['id' => khb_uuid(), 'name' => trim($_POST['name']), 'note' => trim($_POST['note']), 'img' => ''];
        if (!empty($_FILES['img']['name']) && $_FILES['img']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp'])) {
                $name = $g['id'] . '.' . $ext;
                if (move_uploaded_file($_FILES['img']['tmp_name'], __DIR__ . '/../assets/img/' . $name)) $g['img'] = $name;
            }
        }
        $gear[] = $g; khb_save('gear', $gear); $notice = 'Gear added.'; $tab = 'gear';
    }
    if ($act === 'del_gear') {
        khb_save('gear', array_values(array_filter(khb_load('gear'), fn($g) => $g['id'] !== $_POST['id'])));
        $notice = 'Gear removed.'; $tab = 'gear';
    }
}

$beats = khb_load('beats'); $bookings = khb_load('bookings'); $orders = khb_load('orders');
$members = khb_load('members'); $inquiries = khb_load('inquiries'); $gear = khb_load('gear');
usort($bookings, fn($a,$b)=>($b['ts']??0)<=>($a['ts']??0));
usort($orders, fn($a,$b)=>($b['ts']??0)<=>($a['ts']??0));
$revenue = array_sum(array_column($orders, 'total'));
$editBeat = null;
if ($tab === 'beat_edit') foreach ($beats as $b) if ($b['id'] === ($_GET['id'] ?? '')) $editBeat = $b;

function tab_link($t,$cur,$label){ $on=$t===$cur?'style="color:var(--amber);border-color:var(--amber)"':''; echo '<a class="btn ghost sm" '.$on.' href="?tab='.$t.'">'.$label.'</a>'; }
?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin — KAOS Hot Beatz</title>
<meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="/assets/style.css"></head><body>
<header class="site-head"><div class="wrap"><a class="brand" href="/admin/"><span class="brand-kaos">KAOS</span> ADMIN</a>
<nav class="site-nav"><a href="/" target="_blank">View site ↗</a><a href="?logout=1">Sign out</a></nav></div></header>
<main><section><div class="wrap">
<?php if ($notice): ?><div class="notice ok"><?= h($notice) ?></div><?php endif; ?>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:26px">
  <?php tab_link('dash',$tab,'Dashboard'); tab_link('beats',$tab,'Beats ('.count($beats).')');
  tab_link('bookings',$tab,'Bookings ('.count($bookings).')'); tab_link('orders',$tab,'Sales ('.count($orders).')');
  tab_link('members',$tab,'Members ('.count($members).')'); tab_link('inquiries',$tab,'Inbox ('.count($inquiries).')');
  tab_link('gear',$tab,'Gear'); ?>
</div>

<?php if ($tab === 'dash'): ?>
  <h2>Dashboard</h2>
  <div class="grid c3">
    <div class="card price-card"><div class="amt"><?= money($revenue) ?></div><p class="muted">Total beat sales</p></div>
    <div class="card price-card"><div class="amt"><?= count(array_filter($bookings,fn($b)=>$b['status']==='confirmed')) ?></div><p class="muted">Confirmed sessions</p></div>
    <div class="card price-card"><div class="amt"><?= count($members) ?></div><p class="muted">Members</p></div>
  </div>
  <p class="muted" style="margin-top:20px">PayPal mode: <strong><?= h(PAYPAL_ENV) ?></strong> · Payments <?= PAYPAL_CLIENT?'<span style="color:var(--green)">configured</span>':'<span style="color:#ff8a72">not set — add credentials to config.php</span>' ?></p>

<?php elseif ($tab === 'beats' || $tab === 'beat_edit'): ?>
  <div class="grid c2" style="align-items:start">
    <div>
      <h2><?= $editBeat ? 'Edit beat' : 'Add a beat' ?></h2>
      <form method="post" enctype="multipart/form-data" class="card">
        <?= acsrf_field() ?><input type="hidden" name="action" value="save_beat">
        <input type="hidden" name="id" value="<?= h($editBeat['id'] ?? '') ?>">
        <label>Title</label><input name="title" value="<?= h($editBeat['title'] ?? '') ?>" required>
        <div class="grid c2">
          <div><label>BPM</label><input name="bpm" value="<?= h($editBeat['bpm'] ?? '') ?>"></div>
          <div><label>Key</label><input name="key" value="<?= h($editBeat['key'] ?? '') ?>" placeholder="e.g. C min"></div>
        </div>
        <label>Genre</label><input name="genre" value="<?= h($editBeat['genre'] ?? '') ?>" placeholder="Hip-Hop, Trap, Soul…">
        <label>Moods / tags (comma separated)</label><input name="moods" value="<?= h($editBeat['moods'] ?? '') ?>" placeholder="dark, hard, melodic">
        <label>Description</label><textarea name="desc" rows="2"><?= h($editBeat['desc'] ?? '') ?></textarea>
        <label>Preview clip (MP3, tagged/short)</label><input type="file" name="preview" accept=".mp3,.m4a">
        <label>MP3 file (lease)</label><input type="file" name="file_mp3" accept=".mp3">
        <label>WAV file (lease)</label><input type="file" name="file_wav" accept=".wav,.aif,.aiff">
        <label>Stems (ZIP — trackout & exclusive)</label><input type="file" name="file_stems" accept=".zip">
        <?php if ($editBeat): ?><p class="muted" style="font-size:.8rem">Leave a file empty to keep the current one. Files: <?= h(implode(', ', array_filter([$editBeat['preview'],$editBeat['file_mp3'],$editBeat['file_wav'],$editBeat['file_stems']]))) ?: 'none uploaded' ?></p><?php endif; ?>
        <button class="btn block" style="margin-top:16px">Save beat</button>
      </form>
    </div>
    <div>
      <h2>Catalog</h2>
      <?php if (!$beats): ?><p class="muted">No beats yet.</p><?php endif; ?>
      <?php foreach ($beats as $b): ?>
      <div class="card" style="margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;gap:10px">
        <div><strong><?= h($b['title']) ?></strong> <?= !empty($b['sold_exclusive'])?'<span class="tag">SOLD EXCL</span>':'' ?><br>
          <span class="muted mono" style="font-size:.8rem"><?= h($b['bpm']) ?> BPM · <?= h($b['genre'] ?? '') ?></span></div>
        <div style="display:flex;gap:6px">
          <a class="btn ghost sm" href="?tab=beat_edit&id=<?= h($b['id']) ?>">Edit</a>
          <form method="post" onsubmit="return confirm('Delete this beat?')"><?= acsrf_field() ?><input type="hidden" name="action" value="del_beat"><input type="hidden" name="id" value="<?= h($b['id']) ?>"><button class="btn ghost sm">✕</button></form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

<?php elseif ($tab === 'bookings'): ?>
  <h2>Bookings</h2>
  <?php if (!$bookings): ?><p class="muted">No bookings yet.</p><?php endif; ?>
  <?php foreach ($bookings as $b): ?>
  <div class="card" style="margin-bottom:12px">
    <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px">
      <div><strong><?= h($b['service_name']) ?></strong> — <?= h($b['date']) ?> at <?= h($b['time']) ?><br>
        <span class="muted"><?= h($b['name']) ?> · <?= h($b['email']) ?> · <?= h($b['phone']) ?></span>
        <?php if(!empty($b['notes'])): ?><br><span class="muted" style="font-size:.86rem">📝 <?= h($b['notes']) ?></span><?php endif; ?></div>
      <form method="post" style="display:flex;gap:6px;align-items:center">
        <?= acsrf_field() ?><input type="hidden" name="action" value="booking_status"><input type="hidden" name="id" value="<?= h($b['id']) ?>">
        <select name="status" onchange="this.form.submit()">
          <?php foreach (['pending_deposit','confirmed','completed','cancelled'] as $s) echo '<option'.($s===$b['status']?' selected':'').'>'.$s.'</option>'; ?>
        </select>
      </form>
    </div>
  </div>
  <?php endforeach; ?>

<?php elseif ($tab === 'orders'): ?>
  <h2>Beat Sales — <?= money($revenue) ?></h2>
  <?php if (!$orders): ?><p class="muted">No sales yet.</p><?php endif; ?>
  <?php foreach ($orders as $o): ?>
  <div class="card" style="margin-bottom:10px">
    <div class="muted mono" style="font-size:.82rem"><?= date('M j, Y g:ia', $o['ts']) ?> · <?= h($o['member_email']) ?> · <?= money($o['total']) ?> · PayPal <?= h($o['paypal_order'] ?? '') ?></div>
    <?php foreach ($o['items'] as $it) echo '<div>'.h($it['title']).' — <span class="tag">'.h($it['tier_name']).'</span> '.money($it['price']).'</div>'; ?>
  </div>
  <?php endforeach; ?>

<?php elseif ($tab === 'members'): ?>
  <h2>Members</h2>
  <?php if (!$members): ?><p class="muted">No members yet.</p><?php endif; ?>
  <div class="beat-list"><?php foreach ($members as $m): ?>
    <div class="beat" style="grid-template-columns:1fr auto"><div class="meta"><div class="t"><?= h($m['name']) ?></div><div class="s"><?= h($m['email']) ?></div></div>
    <span class="muted mono" style="font-size:.8rem"><?= date('M j, Y', $m['ts']) ?></span></div>
  <?php endforeach; ?></div>

<?php elseif ($tab === 'inquiries'): ?>
  <h2>Inbox</h2>
  <?php if (!$inquiries): ?><p class="muted">No messages.</p><?php endif; ?>
  <?php foreach (array_reverse($inquiries) as $i): ?>
  <div class="card" style="margin-bottom:10px">
    <div class="muted mono" style="font-size:.82rem"><?= date('M j, Y g:ia', $i['ts']) ?> · <?= h($i['subject']) ?></div>
    <strong><?= h($i['name']) ?></strong> — <a href="mailto:<?= h($i['email']) ?>"><?= h($i['email']) ?></a>
    <p><?= nl2br(h($i['message'])) ?></p>
  </div>
  <?php endforeach; ?>

<?php elseif ($tab === 'gear'): ?>
  <div class="grid c2" style="align-items:start">
    <div><h2>Add gear</h2>
      <form method="post" enctype="multipart/form-data" class="card"><?= acsrf_field() ?><input type="hidden" name="action" value="save_gear">
        <label>Name</label><input name="name" placeholder="Akai MPC3000" required>
        <label>Note</label><input name="note" placeholder="The one everything runs through.">
        <label>Photo</label><input type="file" name="img" accept=".jpg,.jpeg,.png,.webp">
        <button class="btn block" style="margin-top:14px">Add to collection</button>
      </form>
    </div>
    <div><h2>Collection</h2>
      <?php foreach ($gear as $g): ?>
      <div class="card" style="margin-bottom:10px;display:flex;justify-content:space-between;align-items:center">
        <div><strong><?= h($g['name']) ?></strong><br><span class="muted"><?= h($g['note']) ?></span></div>
        <form method="post"><?= acsrf_field() ?><input type="hidden" name="action" value="del_gear"><input type="hidden" name="id" value="<?= h($g['id']) ?>"><button class="btn ghost sm">✕</button></form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
</div></section></main></body></html>
