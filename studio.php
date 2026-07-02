<?php
require_once __DIR__ . '/partials.php';
khb_header('The Lab — Drum Machine', 'studio.php');
$PADS = [
  ['808 KICK','1'],['KICK','2'],['SNARE','3'],['CLAP','4'],
  ['RIM','Q'],['CH HAT','W'],['OH HAT','E'],['HAT','R'],
  ['PERC','A'],['PERC 2','S'],['CLAP 2','D'],['HAT 2','F'],
  ['808','Z'],['808 M','X'],['KICK 2','C'],['CRASH','V'],
];
$MELODY = [
  ['CREAM','1'],['CREAM 2','2'],['DADDY','3'],['HECKLER','4'],
  ['PIANO LP','Q'],['SYNTH LP','W'],['FONK','E'],['JEEGO','R'],
  ['AY BUD','A'],['KNEES','S'],['SATURN','D'],['RHODES','F'],
  ['CHORD','Z'],['STAB','X'],['BELLS','C'],['DUSTER','V'],
];
?>
<style>
.lab{max-width:760px;margin:0 auto}
.machine{position:relative;background:linear-gradient(180deg,#26262b,#161619);border:3px solid #000;border-radius:16px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,.6)}
.lab-top{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.lab-top .btn.sm{min-width:74px}
.lab-top .live{margin-left:auto;font-family:var(--mono);color:var(--muted);font-size:.8rem}
.bpm{display:flex;align-items:center;gap:8px;font-family:var(--mono);color:var(--muted)}
.bpm input{width:120px}
.rec.on{background:#e11d1d!important;color:#fff!important;animation:pulse .8s infinite}
@keyframes pulse{50%{opacity:.55}}
.pads{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;touch-action:manipulation}
.pad{aspect-ratio:1;border:2px solid #000;border-radius:10px;background:linear-gradient(180deg,#3a3a41,#2a2a30);
  box-shadow:inset 0 2px 0 rgba(255,255,255,.06),0 4px 0 #000;cursor:pointer;user-select:none;-webkit-user-select:none;
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;transition:transform .04s}
.pad .nm{font-family:var(--disp);font-size:.9rem;color:#cfcbc2;letter-spacing:.02em}
.pad .k{font-family:var(--mono);font-size:.66rem;color:#7a7770}
.pad.hit{background:linear-gradient(180deg,var(--gold2),var(--gold));box-shadow:0 0 22px rgba(225,29,29,.7);transform:translateY(2px)}
.pad.hit .nm,.pad.hit .k{color:#000}
.progress{height:8px;background:#0c0c0e;border:2px solid #000;border-radius:6px;margin-top:16px;overflow:hidden}
.progress i{display:block;height:100%;width:0;background:var(--gold)}
#unlock{position:absolute;inset:0;z-index:10;background:rgba(10,10,11,.92);border-radius:14px;display:flex;flex-direction:column;
  align-items:center;justify-content:center;cursor:pointer;text-align:center;color:#fff}
#unlock .p{width:110px;height:110px;border-radius:50%;border:4px solid var(--gold);display:grid;place-items:center;font-size:3rem;color:var(--gold)}
#unlock .t{margin-top:20px;font-family:var(--disp);font-size:1.4rem;text-transform:uppercase}
#unlock.hide{display:none}
</style>

<section>
  <div class="wrap lab">
    <p class="ey">The Lab</p>
    <h2>MPC Drum Machine</h2>
    <p class="muted">Real KAOS drum kit. Tap the pads. Hit <strong>REC</strong>, finger-drum a loop, then <strong>PLAY</strong> — you made a beat. Works on iPad &amp; phone (turn the ringer up / unmute).</p>

    <div class="machine xl-skin">
      <div id="unlock"><div class="p">▶</div><div class="t" id="unlockTxt">Tap to load the kit</div></div>
      <audio id="silentUnlock" loop playsinline style="display:none" src="data:audio/wav;base64,UklGRiQAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQAAAAA="></audio>
      <div class="xl-lcd">
        <div class="xl-lcd-scan"></div>
        <div class="xl-lcd-row1">
          <span class="xl-brand">KAOS<em>XL</em></span>
          <span class="xl-dot"></span>
        </div>
        <div class="xl-lcd-big"><span id="lcdBpm">090</span><small>BPM</small><span id="lcdBar">1:1</span></div>
        <div class="xl-lcd-status" id="lcdStatus">TAP TO LOAD KIT</div>
      </div>
      <div class="lab-top">
        <button class="btn sm rec" id="rec">● REC</button>
        <button class="btn ghost sm" id="play">▶ PLAY</button>
        <button class="btn ghost sm" id="stop">■ STOP</button>
        <button class="btn ghost sm" id="clear">CLEAR</button>
        <span class="bpm">BPM <input type="range" id="bpm" min="60" max="150" value="90"><b id="bpmv" class="mono">90</b></span>
        <span class="live" id="status">load the kit ↑</span>
      </div>
      <div class="bank-row">
        <button class="btn sm bank on" id="bankDrums" data-bank="0">🥁 DRUMS</button>
        <button class="btn ghost sm bank" id="bankMelody" data-bank="1">🎹 SOUL MELODY</button>
        <span class="muted" style="font-size:.8rem;margin-left:auto">mix both into one loop — hits keep their own bank</span>
      </div>
      <div class="pads" id="pads">
        <?php foreach ($PADS as $i => $p): ?>
        <div class="pad" data-i="<?= $i ?>"><span class="nm" data-drum="<?= h($p[0]) ?>" data-mel="<?= h($MELODY[$i][0]) ?>"><?= h($p[0]) ?></span><span class="k"><?= h($p[1]) ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="progress"><i id="prog"></i></div>
    </div>
    <p class="muted" style="text-align:center;margin-top:14px;font-size:.85rem">Keys: 1 2 3 4 · Q W E R · A S D F · Z X C V</p>
  </div>
</section>

<script>
(function(){
var AC=null, master=null, ready=false, curBank=0;
var buffers=[new Array(16), new Array(16)]; // [0]=drums, [1]=soul melody
var KITS=[{dir:'drums',done:0,total:16},{dir:'melody',done:0,total:16}];
function ctx(){ if(!AC){ AC=new (window.AudioContext||window.webkitAudioContext)(); master=AC.createGain(); master.gain.value=1.0; master.connect(AC.destination); } if(AC.state==='suspended') AC.resume(); return AC; }
function setStatus(s){ document.getElementById('status').textContent=s; var l=document.getElementById('lcdStatus'); if(l) l.textContent=s.toUpperCase(); }

// ---- fallback synth (used only if a sample fails to load) ----
var _nb=null; function noise(){ if(_nb) return _nb; var c=ctx(),b=c.createBuffer(1,c.sampleRate,c.sampleRate),d=b.getChannelData(0); for(var i=0;i<d.length;i++) d[i]=Math.random()*2-1; _nb=b; return b; }
function synth(i,t){ var c=ctx(); if(i===2||i===3||i===5||i===6||i===7||i===10||i===11){ var s=c.createBufferSource(); s.buffer=noise(); var g=c.createGain(),f=c.createBiquadFilter(); f.type='highpass'; f.frequency.value=(i===2||i===3||i===10)?1200:7000; var dur=(i===6)?0.3:0.05; g.gain.setValueAtTime(0.5,t); g.gain.exponentialRampToValueAtTime(0.001,t+dur); s.connect(f).connect(g).connect(master); s.start(t); s.stop(t+dur+0.05); } else { var o=c.createOscillator(),g2=c.createGain(); o.type='sine'; o.frequency.setValueAtTime(120,t); o.frequency.exponentialRampToValueAtTime(45,t+0.4); g2.gain.setValueAtTime(1,t); g2.gain.exponentialRampToValueAtTime(0.001,t+0.5); o.connect(g2).connect(master); o.start(t); o.stop(t+0.55); } }

function playSample(bank,i,when){ var b=buffers[bank][i]; if(!b){ if(bank===0) synth(i,when||ctx().currentTime); return; } var s=ctx().createBufferSource(); s.buffer=b; var g=ctx().createGain(); g.gain.value=0.95; s.connect(g).connect(master); s.start(when||ctx().currentTime); }
var padEls=[].slice.call(document.querySelectorAll('.pad'));
function flash(i){ var el=padEls[i]; if(!el) return; el.classList.add('hit'); setTimeout(function(){el.classList.remove('hit');},90); }
function trigger(bank,i,when){ playSample(bank,i,when); flash(i); }

// ---- load both kits (fetch/decode starts immediately — doesn't need a gesture) ----
var unlockEl=document.getElementById('unlock');
var kitLoading=false;
function loadKits(){
  if(kitLoading) return; kitLoading=true;
  setStatus('loading kits…');
  KITS.forEach(function(kit,bank){
    for(var i=0;i<kit.total;i++){ (function(bank,i){
      fetch('/assets/'+kit.dir+'/'+(bank===0?'pad':'m')+i+'.wav').then(function(r){ return r.arrayBuffer(); })
        .then(function(ab){ return ctx().decodeAudioData(ab); })
        .then(function(buf){ buffers[bank][i]=buf; kit.done++; checkReady(); })
        .catch(function(){ kit.done++; checkReady(); });
    })(bank,i); }
  });
}
function checkReady(){ var total=KITS.reduce(function(s,k){return s+k.total;},0); var done=KITS.reduce(function(s,k){return s+k.done;},0);
  if(done>=total){ ready=true; setStatus('kits loaded — bang it'); } }
loadKits(); // fetch+decode right away — no need to wait for the tap on any device

// ---- unlock: required on iOS to actually hear anything (mute-switch + gesture rules) ----
var silentEl=document.getElementById('silentUnlock');
function unlock(){
  var c=ctx();
  // 1) resume the Web Audio context (required after any user gesture)
  var s=c.createBufferSource(); s.buffer=c.createBuffer(1,1,22050); s.connect(c.destination); s.start(0);
  // 2) play a real <audio> element — this switches iOS's audio session to "playback" category,
  //    which makes sound audible even if the phone's silent/mute switch is flipped on.
  //    (Web Audio alone respects the mute switch on iPhone; a played <audio> tag does not.)
  if(silentEl){ silentEl.volume=0.01; var p=silentEl.play(); if(p&&p.catch) p.catch(function(){}); }
  unlockEl.classList.add('hide');
  if(!ready) setStatus('loading kit…'); else setStatus('kit loaded — bang it');
}
unlockEl.addEventListener('click',unlock);
unlockEl.addEventListener('touchstart',function(e){ e.preventDefault(); unlock(); },{passive:false});

// ---- record / loop ----
var bpm=90, recording=false, events=[], recStart=0, playing=false, loopTimers=[], progRAF=null, counting=false;
var BARS=4, BEATS_PER_BAR=4, COUNTIN_BARS=2;
function beatDur(){ return 60/bpm; }
function loopDur(){ return beatDur()*BEATS_PER_BAR*BARS; }
function hit(i){ var now=ctx().currentTime; trigger(curBank,i,now); if(recording){ events.push({i:i,bank:curBank,t:(now-recStart)%loopDur()}); } }
padEls.forEach(function(el){ var i=+el.getAttribute('data-i');
  el.addEventListener('mousedown',function(e){ e.preventDefault(); hit(i); });
  el.addEventListener('touchstart',function(e){ e.preventDefault(); hit(i); },{passive:false});
});
var KEYS={'1':0,'2':1,'3':2,'4':3,'q':4,'w':5,'e':6,'r':7,'a':8,'s':9,'d':10,'f':11,'z':12,'x':13,'c':14,'v':15};
document.addEventListener('keydown',function(e){ if(e.repeat) return; var k=e.key.toLowerCase(); if(k in KEYS){ if(unlockEl.classList.contains('hide')) hit(KEYS[k]); }});

// ---- bank switch (drums / soul melody) ----
var bankBtns=[document.getElementById('bankDrums'), document.getElementById('bankMelody')];
function setBank(b){ curBank=b; bankBtns.forEach(function(btn,i){ btn.classList.toggle('on', i===b); });
  padEls.forEach(function(el){ var nm=el.querySelector('.nm'); nm.textContent = b===0 ? nm.getAttribute('data-drum') : nm.getAttribute('data-mel'); });
  setStatus(b===0 ? 'drums bank' : 'soul melody bank');
}
bankBtns[0].addEventListener('click',function(){ setBank(0); });
bankBtns[1].addEventListener('click',function(){ setBank(1); });

// ---- metronome click (synth — no sample needed) ----
function metroClick(t,accent){ var c=ctx(),o=c.createOscillator(),g=c.createGain(); o.type='square'; o.frequency.value=accent?1600:1100; g.gain.setValueAtTime(0.5,t); g.gain.exponentialRampToValueAtTime(0.001,t+0.05); o.connect(g).connect(master); o.start(t); o.stop(t+0.06); }
function countIn(cb){
  counting=true; var bd=beatDur(), totalBeats=BEATS_PER_BAR*COUNTIN_BARS, base=ctx().currentTime;
  for(var b=0;b<totalBeats;b++){ (function(b){ loopTimers.push(setTimeout(function(){
    metroClick(ctx().currentTime, b%BEATS_PER_BAR===0);
    setStatus('count-in… '+(Math.floor(b/BEATS_PER_BAR)+1)+' : '+(b%BEATS_PER_BAR+1));
  }, b*bd*1000)); })(b); }
  loopTimers.push(setTimeout(function(){ counting=false; cb(); }, totalBeats*bd*1000));
}

var start0=0;
function startPlay(){ if(playing) return; playing=true; var start=ctx().currentTime; start0=start; var d=loopDur();
  function schedule(){ events.forEach(function(ev){ loopTimers.push(setTimeout(function(){ trigger(ev.bank||0,ev.i); }, ev.t*1000)); }); loopTimers.push(setTimeout(schedule, d*1000)); }
  if(!recording) recStart=start; schedule();
  function tick(){ if(!playing) return; var elapsed=(ctx().currentTime-start0)%d; var el=elapsed/d; document.getElementById('prog').style.width=(el*100)+'%';
    var beatPos=Math.floor(elapsed/beatDur()); var bar=Math.floor(beatPos/BEATS_PER_BAR)+1, beat=(beatPos%BEATS_PER_BAR)+1;
    var lb=document.getElementById('lcdBar'); if(lb) lb.textContent=bar+':'+beat;
    progRAF=requestAnimationFrame(tick); } tick();
}
function stopPlay(){ playing=false; counting=false; loopTimers.forEach(clearTimeout); loopTimers=[]; if(progRAF) cancelAnimationFrame(progRAF); document.getElementById('prog').style.width='0'; }
document.getElementById('rec').addEventListener('click',function(){
  if(counting) return;
  if(recording){ recording=false; this.classList.remove('on'); setStatus('recorded '+events.length+' hits'); return; }
  this.classList.add('on');
  if(!playing) events=[];
  countIn(function(){ recording=true; recStart=ctx().currentTime; setStatus('recording — 4 bars, drum it!'); startPlay(); });
});
document.getElementById('play').addEventListener('click',function(){
  if(counting) return;
  if(!events.length){ setStatus('record something first'); return;}
  stopPlay(); countIn(function(){ startPlay(); setStatus('looping your 4-bar beat'); });
});
document.getElementById('stop').addEventListener('click',function(){ stopPlay(); recording=false; document.getElementById('rec').classList.remove('on'); setStatus('stopped'); });
document.getElementById('clear').addEventListener('click',function(){ stopPlay(); events=[]; recording=false; document.getElementById('rec').classList.remove('on'); setStatus('cleared'); });
document.getElementById('bpm').addEventListener('input',function(){ bpm=+this.value; document.getElementById('bpmv').textContent=bpm; var lb=document.getElementById('lcdBpm'); if(lb) lb.textContent=(bpm<100?'0':'')+bpm; });
document.getElementById('lcdBpm').textContent='090';
})();
</script>
<?php khb_footer(); ?>
