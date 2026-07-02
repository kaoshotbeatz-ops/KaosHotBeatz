<?php
require_once __DIR__ . '/partials.php';
khb_header('The Lab — Drum Machine', 'studio.php');
$PADS = [
  ['808 KICK','1'],['KICK','2'],['SNARE','3'],['CLAP','4'],
  ['RIM','Q'],['CH HAT','W'],['OH HAT','E'],['RIDE','R'],
  ['TOM LO','A'],['TOM HI','S'],['PERC','D'],['SHAKER','F'],
  ['808 C','Z'],['808 F','X'],['COWBELL','C'],['CRASH','V'],
];
?>
<style>
.lab{max-width:760px;margin:0 auto}
.machine{background:linear-gradient(180deg,#26262b,#161619);border:3px solid #000;border-radius:16px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,.6)}
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
</style>

<section>
  <div class="wrap lab">
    <p class="ey">The Lab</p>
    <h2>MPC Drum Machine</h2>
    <p class="muted">Tap the pads to play a real hip-hop kit. Hit <strong>REC</strong>, finger-drum a loop, then <strong>PLAY</strong> — you just made a beat. Works on iPad &amp; phone (turn your ringer up).</p>

    <div class="machine">
      <div class="lab-top">
        <button class="btn sm rec" id="rec">● REC</button>
        <button class="btn ghost sm" id="play">▶ PLAY</button>
        <button class="btn ghost sm" id="stop">■ STOP</button>
        <button class="btn ghost sm" id="clear">CLEAR</button>
        <span class="bpm">BPM <input type="range" id="bpm" min="60" max="150" value="90"><b id="bpmv" class="mono">90</b></span>
        <span class="live" id="status">tap a pad to start</span>
      </div>
      <div class="pads" id="pads">
        <?php foreach ($PADS as $i => $p): ?>
        <div class="pad" data-i="<?= $i ?>"><span class="nm"><?= h($p[0]) ?></span><span class="k"><?= h($p[1]) ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="progress"><i id="prog"></i></div>
    </div>
    <p class="muted" style="text-align:center;margin-top:14px;font-size:.85rem">Keys: 1 2 3 4 · Q W E R · A S D F · Z X C V</p>
  </div>
</section>

<script>
(function(){
var AC=null; function ctx(){ if(!AC){ AC=new (window.AudioContext||window.webkitAudioContext)(); } if(AC.state==='suspended') AC.resume(); return AC; }
var master=null;
function bus(){ if(!master){ master=ctx().createGain(); master.gain.value=0.9; master.connect(ctx().destination);} return master; }
// white noise buffer
var _nb=null; function noise(){ if(_nb) return _nb; var c=ctx(),b=c.createBuffer(1,c.sampleRate*1,c.sampleRate),d=b.getChannelData(0); for(var i=0;i<d.length;i++) d[i]=Math.random()*2-1; _nb=b; return b; }
function noiseSrc(){ var s=ctx().createBufferSource(); s.buffer=noise(); return s; }
function gEnv(g,t,a,d,pk){ g.gain.setValueAtTime(0.0001,t); g.gain.linearRampToValueAtTime(pk,t+a); g.gain.exponentialRampToValueAtTime(0.0001,t+a+d); }
function tone(t,f0,f1,dur,pk,type){ var c=ctx(),o=c.createOscillator(),g=c.createGain(); o.type=type||'sine'; o.frequency.setValueAtTime(f0,t); if(f1) o.frequency.exponentialRampToValueAtTime(f1,t+dur); gEnv(g,t,0.001,dur,pk||1); o.connect(g).connect(bus()); o.start(t); o.stop(t+dur+0.05); }
function nz(t,dur,pk,hp,lp){ var c=ctx(),s=noiseSrc(),g=c.createGain(),f=c.createBiquadFilter(); f.type='highpass'; f.frequency.value=hp||1000; var lpf=null; if(lp){lpf=c.createBiquadFilter();lpf.type='lowpass';lpf.frequency.value=lp;} gEnv(g,t,0.001,dur,pk||0.6); s.connect(f); if(lpf){f.connect(lpf).connect(g);}else{f.connect(g);} g.connect(bus()); s.start(t); s.stop(t+dur+0.05); }

var KIT=[
 function(t){ tone(t,120,42,0.6,1,'sine'); },              // 808 kick
 function(t){ tone(t,180,50,0.22,1,'sine'); },             // kick
 function(t){ tone(t,190,120,0.16,0.5,'triangle'); nz(t,0.18,0.7,1200); }, // snare
 function(t){ [0,0.012,0.024].forEach(function(o){nz(t+o,0.09,0.5,1500,4000);}); }, // clap
 function(t){ tone(t,1700,900,0.05,0.5,'triangle'); },     // rim
 function(t){ nz(t,0.04,0.5,7000); },                      // closed hat
 function(t){ nz(t,0.3,0.4,7000); },                       // open hat
 function(t){ nz(t,0.5,0.3,3000,9000); },                  // ride
 function(t){ tone(t,110,70,0.35,0.9,'sine'); },           // tom lo
 function(t){ tone(t,200,120,0.3,0.9,'sine'); },           // tom hi
 function(t){ tone(t,420,300,0.12,0.6,'square'); },        // perc
 function(t){ nz(t,0.06,0.35,9000); },                     // shaker
 function(t){ tone(t,65,65,0.6,1,'sine'); },               // 808 C
 function(t){ tone(t,87,87,0.6,1,'sine'); },               // 808 F
 function(t){ tone(t,560,560,0.16,0.5,'square'); },        // cowbell
 function(t){ nz(t,0.9,0.5,2500); }                        // crash
];

var padEls=[].slice.call(document.querySelectorAll('.pad'));
function flash(i){ var el=padEls[i]; if(!el) return; el.classList.add('hit'); setTimeout(function(){el.classList.remove('hit');},90); }
function trigger(i,when){ if(!KIT[i]) return; KIT[i](when||ctx().currentTime); flash(i); }

// ---- record / loop ----
var bpm=90, recording=false, events=[], recStart=0, playing=false, loopTimers=[], progRAF=null;
function loopDur(){ return (60/bpm)*8; } // 2 bars
function setStatus(s){ document.getElementById('status').textContent=s; }

function hit(i){ // user pressed a pad
  var now=ctx().currentTime;
  trigger(i, now);
  if(recording){ events.push({i:i, t:(now-recStart)%loopDur()}); }
}
padEls.forEach(function(el){
  var i=+el.getAttribute('data-i');
  el.addEventListener('mousedown',function(e){ e.preventDefault(); hit(i); });
  el.addEventListener('touchstart',function(e){ e.preventDefault(); hit(i); }, {passive:false});
});
var KEYS={'1':0,'2':1,'3':2,'4':3,'q':4,'w':5,'e':6,'r':7,'a':8,'s':9,'d':10,'f':11,'z':12,'x':13,'c':14,'v':15};
document.addEventListener('keydown',function(e){ if(e.repeat) return; var k=e.key.toLowerCase(); if(k in KEYS){ hit(KEYS[k]); }});

document.getElementById('rec').addEventListener('click',function(){
  recording=!recording; this.classList.toggle('on',recording);
  if(recording){ if(!playing){ events=[]; } recStart=ctx().currentTime; setStatus('recording — drum it!'); startPlay(); }
  else setStatus('recorded '+events.length+' hits');
});
function startPlay(){
  if(playing) return; playing=true;
  var start=ctx().currentTime; var d=loopDur();
  function schedule(loopBase){
    events.forEach(function(ev){ loopTimers.push(setTimeout(function(){ trigger(ev.i); }, ev.t*1000)); });
    loopTimers.push(setTimeout(function(){ schedule(); }, d*1000));
  }
  if(!recording) recStart=start;
  schedule();
  // progress bar
  function tick(){ if(!playing) return; var el=((ctx().currentTime-start)%d)/d; document.getElementById('prog').style.width=(el*100)+'%'; progRAF=requestAnimationFrame(tick); }
  tick();
}
function stopPlay(){ playing=false; loopTimers.forEach(clearTimeout); loopTimers=[]; if(progRAF) cancelAnimationFrame(progRAF); document.getElementById('prog').style.width='0'; }
document.getElementById('play').addEventListener('click',function(){ if(!events.length){ setStatus('record something first'); return;} stopPlay(); startPlay(); setStatus('looping your beat'); });
document.getElementById('stop').addEventListener('click',function(){ stopPlay(); recording=false; document.getElementById('rec').classList.remove('on'); setStatus('stopped'); });
document.getElementById('clear').addEventListener('click',function(){ stopPlay(); events=[]; recording=false; document.getElementById('rec').classList.remove('on'); setStatus('cleared'); });
document.getElementById('bpm').addEventListener('input',function(){ bpm=+this.value; document.getElementById('bpmv').textContent=bpm; });
})();
</script>
<?php khb_footer(); ?>
