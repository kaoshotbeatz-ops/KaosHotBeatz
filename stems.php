<?php
require_once __DIR__ . '/partials.php';
$id = $_GET['id'] ?? '';
$beat = null;
foreach (khb_load('beats') as $b) if ($b['id'] === $id) { $beat = $b; break; }
if (!$beat) { http_response_code(404); exit('Beat not found.'); }

$STEM_ORDER = [
  'drums'         => ['DRUMS', '#ffd152'],
  'percussion'    => ['PERCUSSION', '#ffb84d'],
  'bass'          => ['BASS', '#3ecf8e'],
  'guitar'        => ['GUITAR', '#8be04e'],
  'keyboard'      => ['KEYS', '#7fb0ff'],
  'synth'         => ['SYNTH', '#e11d1d'],
  'vocals'        => ['LEAD VOCAL', '#ff6fa8'],
  'backingvocals' => ['BACKING VOX', '#ff9a4d'],
  'fx'            => ['FX / OTHER', '#c9c9cf'],
];
$dir = __DIR__ . '/assets/stems/' . basename($id);
$available = [];
foreach ($STEM_ORDER as $key => $meta) {
    if (file_exists($dir . '/' . $key . '.mp3')) $available[$key] = $meta;
}
if (!$available) {
    khb_header('Stems not available', '');
    echo '<section><div class="wrap" style="max-width:640px">';
    echo '<h2>No stems for "' . h($beat['title']) . '" yet</h2>';
    echo '<p class="muted">This beat hasn\'t been split into stems. <a href="/beat.php?id=' . h($beat['id']) . '">← Back to the beat</a></p>';
    echo '</div></section>';
    khb_footer(); exit;
}
khb_header('Stems — ' . $beat['title'], '');
?>
<style>
.stems-wrap{max-width:900px;margin:0 auto}
.stems-deck{background:linear-gradient(180deg,#1c1c1f,#141416);border:3px solid #000;border-radius:16px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,.6)}
.stems-top{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.stems-time{font-family:var(--mono);color:var(--gold2);font-size:.85rem;margin-left:auto}
.stems-strips{display:flex;flex-direction:column;gap:10px}
.stems-strip{display:grid;grid-template-columns:120px 1fr 70px;align-items:center;gap:12px;background:#0e0e10;border:2px solid #000;border-radius:8px;padding:10px 14px}
.stems-strip .nm{font-family:var(--disp);font-size:.9rem;letter-spacing:.02em}
.stems-strip .fader-row{display:flex;align-items:center;gap:10px}
.stems-strip input[type=range]{flex:1}
.stems-strip .meter{height:8px;background:#0c0c0e;border:1px solid #000;border-radius:4px;overflow:hidden;margin-top:6px}
.stems-strip .meter i{display:block;height:100%;width:0%;background:linear-gradient(90deg,#3ecf8e,#ffd152 70%,#e11d1d 92%)}
.stems-strip .mute{width:32px;height:26px;border-radius:5px;border:2px solid #000;background:#26262b;color:var(--muted);font-family:var(--mono);font-weight:800;font-size:.7rem;cursor:pointer}
.stems-strip .mute.on{background:#e11d1d;color:#fff}
.stems-strip .solo{width:32px;height:26px;border-radius:5px;border:2px solid #000;background:#26262b;color:var(--muted);font-family:var(--mono);font-weight:800;font-size:.7rem;cursor:pointer}
.stems-strip .solo.on{background:var(--gold);color:#000}
.stems-strip .btns{display:flex;gap:6px}
.stems-progress{height:10px;background:#0c0c0e;border:2px solid #000;border-radius:6px;margin-top:18px;overflow:hidden;cursor:pointer}
.stems-progress i{display:block;height:100%;width:0;background:var(--gold)}
</style>

<section>
  <div class="wrap stems-wrap">
    <p class="ey">Inside the Beat</p>
    <h2><?= h($beat['title']) ?> — Stems</h2>
    <p class="muted">Every instrument, its own fader. Mute, solo, and hear exactly what's making the record — split straight from the original session.</p>

    <div class="stems-deck">
      <div class="stems-top">
        <button class="btn sm" id="stemsPlay">▶ PLAY</button>
        <button class="btn ghost sm" id="stemsStop">■ STOP</button>
        <span class="stems-time"><span id="stemsCur">0:00</span> / <span id="stemsDur">0:00</span></span>
      </div>
      <div class="stems-strips">
        <?php foreach ($available as $key => $meta): ?>
        <div class="stems-strip" data-stem="<?= h($key) ?>">
          <div class="nm" style="color:<?= h($meta[1]) ?>"><?= h($meta[0]) ?></div>
          <div>
            <div class="fader-row"><input type="range" class="stem-fader" data-stem="<?= h($key) ?>" min="0" max="1.4" step="0.01" value="1"></div>
            <div class="meter"><i class="stem-meter" data-stem="<?= h($key) ?>"></i></div>
          </div>
          <div class="btns">
            <button class="mute" data-stem="<?= h($key) ?>">M</button>
            <button class="solo" data-stem="<?= h($key) ?>">S</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="stems-progress" id="stemsProg"><i id="stemsProgFill"></i></div>
      <p class="muted" style="margin-top:10px;font-size:.78rem">Tap the progress bar to jump anywhere — every stem stays in sync.</p>
    </div>
    <p style="margin-top:18px"><a href="/beat.php?id=<?= h($beat['id']) ?>" class="muted">← Back to the beat</a></p>
  </div>
</section>

<script>
(function(){
var STEMS=<?= json_encode(array_keys($available)) ?>;
var BASE='/assets/stems/<?= h(basename($id)) ?>/';
var AC=null, master=null, gains={}, analysers={}, buffers={}, sources={}, muted={}, soloed=null;
var playing=false, startAt=0, offset=0, dur=0, rafId=null;

function ctx(){ if(!AC){ AC=new (window.AudioContext||window.webkitAudioContext)(); master=AC.createGain(); master.gain.value=1; master.connect(AC.destination); } if(AC.state==='suspended') AC.resume(); return AC; }

function loadAll(cb){
  ctx();
  var done=0;
  STEMS.forEach(function(s){
    fetch(BASE+s+'.mp3').then(function(r){return r.arrayBuffer();})
      .then(function(ab){return AC.decodeAudioData(ab);})
      .then(function(buf){ buffers[s]=buf; dur=Math.max(dur,buf.duration); done++; if(done>=STEMS.length) cb(); })
      .catch(function(){ done++; if(done>=STEMS.length) cb(); });
  });
}

function setupMixer(){
  STEMS.forEach(function(s){
    var g=AC.createGain(); g.gain.value=1; g.connect(master);
    var an=AC.createAnalyser(); an.fftSize=256; g.connect(an);
    gains[s]=g; analysers[s]=an; muted[s]=false;
  });
}

function applyMix(){
  var anySolo = !!soloed;
  STEMS.forEach(function(s){
    var audible = anySolo ? (s===soloed) : !muted[s];
    var fader=document.querySelector('.stem-fader[data-stem="'+s+'"]');
    gains[s].gain.value = audible ? (+fader.value) : 0;
  });
}

function stopSources(){ Object.keys(sources).forEach(function(s){ try{sources[s].stop();}catch(e){} }); sources={}; }

function startAt0(fromOffset){
  stopSources();
  var t=ctx().currentTime;
  startAt=t; offset=fromOffset||0;
  STEMS.forEach(function(s){
    var src=AC.createBufferSource(); src.buffer=buffers[s]; src.connect(gains[s]);
    src.start(t, offset);
    sources[s]=src;
  });
}

function fmt(sec){ sec=Math.max(0,sec); var m=Math.floor(sec/60), ss=Math.floor(sec%60); return m+':'+(ss<10?'0':'')+ss; }

function tick(){
  if(!playing) return;
  var elapsed=offset+(ctx().currentTime-startAt);
  if(elapsed>=dur){ stopSources(); playing=false; document.getElementById('stemsPlay').textContent='▶ PLAY'; elapsed=dur; }
  document.getElementById('stemsCur').textContent=fmt(elapsed);
  document.getElementById('stemsProgFill').style.width=(dur?((elapsed/dur)*100):0)+'%';
  STEMS.forEach(function(s){
    var an=analysers[s]; if(!an) return;
    var buf=new Uint8Array(an.fftSize); an.getByteTimeDomainData(buf);
    var sum=0; for(var i=0;i<buf.length;i++){ var v=(buf[i]-128)/128; sum+=v*v; }
    var lvl=Math.sqrt(sum/buf.length);
    var el=document.querySelector('.stem-meter[data-stem="'+s+'"]'); if(el) el.style.width=Math.min(100,lvl*260)+'%';
  });
  rafId=requestAnimationFrame(tick);
}

document.getElementById('stemsPlay').addEventListener('click',function(){
  ctx();
  if(!Object.keys(buffers).length){ this.textContent='loading…'; loadAll(function(){ setupMixer(); document.getElementById('stemsDur').textContent=fmt(dur); go(); }); return; }
  go();
  function go(){
    if(playing){ // pause
      offset=offset+(ctx().currentTime-startAt); stopSources(); playing=false;
      document.getElementById('stemsPlay').textContent='▶ PLAY'; return;
    }
    applyMix(); startAt0(offset); playing=true;
    document.getElementById('stemsPlay').textContent='❚❚ PAUSE'; tick();
  }
});
document.getElementById('stemsStop').addEventListener('click',function(){
  stopSources(); playing=false; offset=0;
  document.getElementById('stemsPlay').textContent='▶ PLAY';
  document.getElementById('stemsCur').textContent='0:00';
  document.getElementById('stemsProgFill').style.width='0%';
});
document.getElementById('stemsProg').addEventListener('click',function(e){
  if(!dur) return;
  var r=this.getBoundingClientRect(); var frac=Math.max(0,Math.min(1,(e.clientX-r.left)/r.width));
  offset=frac*dur;
  document.getElementById('stemsProgFill').style.width=(frac*100)+'%';
  document.getElementById('stemsCur').textContent=fmt(offset);
  if(playing){ startAt0(offset); }
});
[].slice.call(document.querySelectorAll('.stem-fader')).forEach(function(f){ f.addEventListener('input',applyMix); });
[].slice.call(document.querySelectorAll('.mute')).forEach(function(btn){
  btn.addEventListener('click',function(){ var s=btn.getAttribute('data-stem'); muted[s]=btn.classList.toggle('on'); applyMix(); });
});
[].slice.call(document.querySelectorAll('.solo')).forEach(function(btn){
  btn.addEventListener('click',function(){
    var s=btn.getAttribute('data-stem');
    var wasOn=btn.classList.contains('on');
    [].slice.call(document.querySelectorAll('.solo')).forEach(function(b){ b.classList.remove('on'); });
    soloed = wasOn ? null : s;
    if(!wasOn) btn.classList.add('on');
    applyMix();
  });
});
})();
</script>
<?php khb_footer(); ?>
