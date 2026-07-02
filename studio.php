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
      <audio id="silentUnlock" playsinline style="display:none" src="/assets/drums/pad3.wav" preload="auto"></audio>
      <div class="xl-lcd">
        <div class="xl-lcd-scan"></div>
        <div class="xl-lcd-row1">
          <span class="xl-brand">KAOS<em>XL</em></span>
          <span class="xl-qtz" id="lcdQtz">QTZ 1/16</span>
          <span class="xl-dot"></span>
        </div>
        <div class="xl-lcd-big"><span id="lcdBpm">090</span><small>BPM</small><span id="lcdBar">1:1</span></div>
        <canvas class="xl-scope" id="scope" width="600" height="60"></canvas>
        <div class="xl-lcd-status" id="lcdStatus">TAP TO LOAD KIT</div>
      </div>
      <div class="lab-top">
        <button class="btn sm rec" id="rec">● REC</button>
        <button class="btn ghost sm" id="play">▶ PLAY</button>
        <button class="btn ghost sm" id="stop">■ STOP</button>
        <button class="btn ghost sm" id="clear">CLEAR</button>
        <span class="bpm">BPM <input type="range" id="bpm" min="60" max="150" value="90"><b id="bpmv" class="mono">90</b></span>
        <button class="btn ghost sm" id="tapTempo">TAP</button>
        <span class="live" id="status">load the kit ↑</span>
      </div>
      <div class="bank-row">
        <button class="btn sm bank on" id="bankDrums" data-bank="0">🥁 DRUMS</button>
        <button class="btn ghost sm bank" id="bankMelody" data-bank="1">🎹 SOUL MELODY</button>
        <span class="muted" style="font-size:.8rem;margin-left:auto">mix both into one loop — hits keep their own bank</span>
      </div>
      <div class="bank-row">
        <span class="mono muted" style="font-size:.72rem">QUANTIZE</span>
        <button class="btn ghost sm qz" data-qz="0">OFF</button>
        <button class="btn ghost sm qz" data-qz="4">1/4</button>
        <button class="btn ghost sm qz" data-qz="8">1/8</button>
        <button class="btn sm qz on" data-qz="16">1/16</button>
        <span class="muted" style="font-size:.8rem;margin-left:auto">snaps every recorded hit onto the beat grid</span>
      </div>
      <div class="pads" id="pads">
        <?php foreach ($PADS as $i => $p): ?>
        <div class="pad" data-i="<?= $i ?>"><span class="nm" data-drum="<?= h($p[0]) ?>" data-mel="<?= h($MELODY[$i][0]) ?>"><?= h($p[0]) ?></span><span class="k"><?= h($p[1]) ?></span></div>
        <?php endforeach; ?>
      </div>
      <div class="progress"><i id="prog"></i></div>
    </div>
    <p class="muted" style="text-align:center;margin-top:14px;font-size:.85rem">Keys: 1 2 3 4 · Q W E R · A S D F · Z X C V</p>

    <div class="machine xl-skin" style="margin-top:26px">
      <h3 style="margin:0 0 6px">Turntable &amp; Mixer</h3>
      <p class="muted" style="font-size:.85rem;margin-bottom:16px">Drag the vinyl to scratch it. Hit ▶ Spin for straight playback. Watch the meters bounce with your beat.</p>
      <div class="deck-mixer">
        <div class="deck">
          <div class="platter-wrap">
            <div class="vinyl" id="vinyl"><div class="vlabel">CREAM</div></div>
            <div class="tonearm" id="tonearm"><div class="headshell"></div></div>
          </div>
          <div class="deck-controls">
            <button class="btn ghost sm" id="ttPlay">▶ Spin</button>
            <button class="btn ghost sm" id="ttStop">■ Stop</button>
          </div>
          <div class="scratch-src">
            <label class="mono muted" style="font-size:.7rem">SCRATCH SRC</label>
            <select id="scratchSrc">
              <option value="0">Soul Loop (Cream)</option>
              <option value="1">Hype Tag 1</option>
              <option value="2">Hype Tag 2</option>
              <option value="3">Hype Tag 3</option>
            </select>
            <p class="muted" style="font-size:.72rem;margin-top:6px">Hype Tags are synthesized in-browser — not sampled vocals. Send your own recorded ad-libs and I'll load those instead.</p>
          </div>
        </div>
        <div class="mixer">
          <div class="strip"><label>DRUMS</label><button class="mute" id="xDrums">M</button><div class="meter"><i id="mDrums"></i></div><input type="range" orient="vertical" id="gDrums" min="0" max="1.5" step="0.01" value="1"></div>
          <div class="strip"><label>MELODY</label><button class="mute" id="xMelody">M</button><div class="meter"><i id="mMelody"></i></div><input type="range" orient="vertical" id="gMelody" min="0" max="1.5" step="0.01" value="1"></div>
          <div class="strip"><label>KEYS</label><button class="mute" id="xKeys">M</button><div class="meter"><i id="mKeys"></i></div><input type="range" orient="vertical" id="gKeys" min="0" max="1.5" step="0.01" value="1"></div>
          <div class="strip"><label>TT</label><button class="mute" id="xTT">M</button><div class="meter"><i id="mTT"></i></div><input type="range" orient="vertical" id="gTT" min="0" max="1.5" step="0.01" value="1"></div>
          <div class="strip master"><label>MASTER</label><button class="mute" id="xMaster">M</button><div class="meter"><i id="mMaster"></i></div><input type="range" orient="vertical" id="gMaster" min="0" max="1.5" step="0.01" value="1"></div>
        </div>
      </div>
    </div>

    <div class="machine xl-skin" style="margin-top:26px">
      <h3 style="margin:0 0 6px">Compressor Rack</h3>
      <p class="muted" style="font-size:.85rem;margin-bottom:16px">Real dynamics processing — a drum-bus compressor and an SSL-style master glue compressor. Watch the gain-reduction meter pull down when it's working.</p>
      <div class="comp-rack">
        <div class="comp-strip">
          <div class="comp-head"><span>DRUM BUS COMP</span><button class="btn ghost sm comp-on" id="compDrumOn" data-target="drum">ON</button></div>
          <div class="comp-row"><label>THRESH</label><input type="range" id="cd-th" min="-40" max="0" value="-18"><b class="mono" id="cd-thv">-18dB</b></div>
          <div class="comp-row"><label>RATIO</label><input type="range" id="cd-ra" min="1" max="20" value="4"><b class="mono" id="cd-rav">4:1</b></div>
          <div class="comp-row"><label>ATTACK</label><input type="range" id="cd-at" min="1" max="100" value="3"><b class="mono" id="cd-atv">3ms</b></div>
          <div class="comp-row"><label>RELEASE</label><input type="range" id="cd-re" min="20" max="1000" value="250"><b class="mono" id="cd-rev">250ms</b></div>
          <div class="comp-row"><label>MAKEUP</label><input type="range" id="cd-mk" min="0" max="200" value="100"><b class="mono" id="cd-mkv">1.0x</b></div>
          <div class="gr-row"><label>GR</label><div class="grmeter"><i id="cd-gr"></i></div></div>
        </div>
        <div class="comp-strip">
          <div class="comp-head"><span>MASTER BUS <em>(SSL-style glue)</em></span><button class="btn ghost sm comp-on" id="compMasterOn" data-target="master">ON</button></div>
          <div class="comp-row"><label>THRESH</label><input type="range" id="cm-th" min="-40" max="0" value="-12"><b class="mono" id="cm-thv">-12dB</b></div>
          <div class="comp-row"><label>RATIO</label><input type="range" id="cm-ra" min="1" max="20" value="3"><b class="mono" id="cm-rav">3:1</b></div>
          <div class="comp-row"><label>ATTACK</label><input type="range" id="cm-at" min="1" max="100" value="10"><b class="mono" id="cm-atv">10ms</b></div>
          <div class="comp-row"><label>RELEASE</label><input type="range" id="cm-re" min="20" max="1000" value="300"><b class="mono" id="cm-rev">300ms</b></div>
          <div class="comp-row"><label>MAKEUP</label><input type="range" id="cm-mk" min="0" max="200" value="115"><b class="mono" id="cm-mkv">1.15x</b></div>
          <div class="gr-row"><label>GR</label><div class="grmeter"><i id="cm-gr"></i></div></div>
        </div>
      </div>
    </div>

    <div class="machine xl-skin" style="margin-top:26px">
      <h3 style="margin:0 0 6px">Play the Keys</h3>
      <div class="key-top">
        <label class="mono muted" style="font-size:.8rem">SOUND</label>
        <select id="voiceSel">
          <option value="0">Rhodes</option>
          <option value="1">Chord</option>
          <option value="2">Stab</option>
          <option value="3">Bells</option>
        </select>
        <label class="mono muted" style="font-size:.8rem;margin-left:14px">OCTAVE</label>
        <button class="btn ghost sm" id="octDown">−</button>
        <span class="mono" id="octLbl" style="min-width:52px;text-align:center;color:var(--gold)">OCT 0</span>
        <button class="btn ghost sm" id="octUp">+</button>
      </div>
      <div class="keys" id="keys">
        <?php $NOTES = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B','C']; foreach ($NOTES as $semi => $n): ?>
        <div class="key<?= (strpos($n,'#')!==false)?' sharp':'' ?>" data-semi="<?= $semi ?>"><?= h($n) ?></div>
        <?php endforeach; ?>
      </div>
      <p class="muted" style="text-align:center;margin-top:10px;font-size:.85rem">Tap the keys to play — switch the sound above.</p>
    </div>
  </div>
</section>

<script>
(function(){
var AC=null, master=null, ready=false, curBank=0;
var drumGain=null, melodyGain=null, keysGain=null, ttGain=null;
var drumComp=null, drumMakeup=null, masterComp=null, masterMakeup=null;
var masterAn=null, drumAn=null, melodyAn=null, keysAn=null, ttAn=null;
var buffers=[new Array(16), new Array(16)]; // [0]=drums, [1]=soul melody
var KITS=[{dir:'drums',done:0,total:16},{dir:'melody',done:0,total:16}];
function mkAnalyser(c,src){ var a=c.createAnalyser(); a.fftSize=256; src.connect(a); return a; }
function ctx(){
  if(!AC){
    AC=new (window.AudioContext||window.webkitAudioContext)();
    master=AC.createGain(); master.gain.value=1.0;
    drumGain=AC.createGain(); melodyGain=AC.createGain(); keysGain=AC.createGain(); ttGain=AC.createGain();

    // drum bus compressor
    drumComp=AC.createDynamicsCompressor(); drumComp.threshold.value=-18; drumComp.knee.value=6; drumComp.ratio.value=4; drumComp.attack.value=0.003; drumComp.release.value=0.25;
    drumMakeup=AC.createGain(); drumMakeup.gain.value=1.0;
    drumGain.connect(drumComp); drumComp.connect(drumMakeup); drumMakeup.connect(master);

    melodyGain.connect(master); keysGain.connect(master); ttGain.connect(master);

    // master bus (SSL-style glue) compressor
    masterComp=AC.createDynamicsCompressor(); masterComp.threshold.value=-12; masterComp.knee.value=6; masterComp.ratio.value=3; masterComp.attack.value=0.01; masterComp.release.value=0.3;
    masterMakeup=AC.createGain(); masterMakeup.gain.value=1.15;
    master.connect(masterComp); masterComp.connect(masterMakeup); masterMakeup.connect(AC.destination);

    drumAn=mkAnalyser(AC,drumMakeup); melodyAn=mkAnalyser(AC,melodyGain); keysAn=mkAnalyser(AC,keysGain); ttAn=mkAnalyser(AC,ttGain);
    masterAn=mkAnalyser(AC,masterMakeup);
    buildHypeTags(AC);
    startMeters(); startCompMeters();
  }
  if(AC.state==='suspended') AC.resume();
  return AC;
}
function setStatus(s){ document.getElementById('status').textContent=s; var l=document.getElementById('lcdStatus'); if(l) l.textContent=s.toUpperCase(); }

// ---- live level meters (mixer) ----
var _mbuf=new Uint8Array(256);
function meterLevel(an){ if(!an) return 0; an.getByteTimeDomainData(_mbuf); var sum=0; for(var i=0;i<_mbuf.length;i++){ var v=(_mbuf[i]-128)/128; sum+=v*v; } return Math.sqrt(sum/_mbuf.length); }
function setMeter(id,lvl){ var el=document.getElementById(id); if(el) el.style.height=Math.min(100,lvl*280)+'%'; }
function startMeters(){ function loop(){ setMeter('mDrums',meterLevel(drumAn)); setMeter('mMelody',meterLevel(melodyAn)); setMeter('mKeys',meterLevel(keysAn)); setMeter('mTT',meterLevel(ttAn)); setMeter('mMaster',meterLevel(masterAn)); requestAnimationFrame(loop); } loop(); }

// ---- synthesized "hype tag" scratch stabs (no samples — pure math, zero copyright risk) ----
var hypeBuffers=[];
function makeHypeBuffer(c,f0,f1,dur){
  var sr=c.sampleRate, len=Math.floor(sr*dur), buf=c.createBuffer(1,len,sr), d=buf.getChannelData(0);
  for(var i=0;i<len;i++){
    var t=i/sr, freq=f0+(f1-f0)*(t/dur);
    var env=Math.min(1,t/0.008)*Math.pow(1-t/dur,1.6);
    var v=Math.sin(2*Math.PI*freq*t)+0.5*Math.sin(2*Math.PI*freq*2.01*t)+0.28*Math.sin(2*Math.PI*freq*3.03*t);
    d[i]=v*env*0.55;
  }
  return buf;
}
function buildHypeTags(c){ hypeBuffers=[ makeHypeBuffer(c,260,140,0.32), makeHypeBuffer(c,180,320,0.26), makeHypeBuffer(c,220,90,0.4) ]; }

// ---- compressor gain-reduction meters (reads the node's live .reduction, in dB) ----
function startCompMeters(){ function loop(){
  if(drumComp){ var g1=document.getElementById('cd-gr'); if(g1) g1.style.width=Math.min(100,Math.abs(drumComp.reduction)*5)+'%'; }
  if(masterComp){ var g2=document.getElementById('cm-gr'); if(g2) g2.style.width=Math.min(100,Math.abs(masterComp.reduction)*5)+'%'; }
  requestAnimationFrame(loop);
} loop(); }

// ---- fallback synth (used only if a sample fails to load) ----
var _nb=null; function noise(){ if(_nb) return _nb; var c=ctx(),b=c.createBuffer(1,c.sampleRate,c.sampleRate),d=b.getChannelData(0); for(var i=0;i<d.length;i++) d[i]=Math.random()*2-1; _nb=b; return b; }
function synth(i,t){ var c=ctx(); if(i===2||i===3||i===5||i===6||i===7||i===10||i===11){ var s=c.createBufferSource(); s.buffer=noise(); var g=c.createGain(),f=c.createBiquadFilter(); f.type='highpass'; f.frequency.value=(i===2||i===3||i===10)?1200:7000; var dur=(i===6)?0.3:0.05; g.gain.setValueAtTime(0.5,t); g.gain.exponentialRampToValueAtTime(0.001,t+dur); s.connect(f).connect(g).connect(drumGain); s.start(t); s.stop(t+dur+0.05); } else { var o=c.createOscillator(),g2=c.createGain(); o.type='sine'; o.frequency.setValueAtTime(120,t); o.frequency.exponentialRampToValueAtTime(45,t+0.4); g2.gain.setValueAtTime(1,t); g2.gain.exponentialRampToValueAtTime(0.001,t+0.5); o.connect(g2).connect(drumGain); o.start(t); o.stop(t+0.55); } }

function playSample(bank,i,when){ var b=buffers[bank][i]; if(!b){ if(bank===0) synth(i,when||ctx().currentTime); return; } var s=ctx().createBufferSource(); s.buffer=b; var g=ctx().createGain(); g.gain.value=0.95; s.connect(g).connect(bank===0?drumGain:melodyGain); s.start(when||ctx().currentTime); }
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
  if(silentEl){ silentEl.volume=0.05; silentEl.currentTime=0; var p=silentEl.play(); if(p&&p.catch) p.catch(function(){}); setTimeout(function(){ try{ silentEl.pause(); }catch(e){} },250); }
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
var quantizeDiv=16; // 0=off, 4=quarter, 8=eighth, 16=sixteenth
function quantize(t){
  if(!quantizeDiv) return t;
  var stepDur=beatDur()/(quantizeDiv/4);
  var snapped=Math.round(t/stepDur)*stepDur;
  var d=loopDur(); if(snapped>=d) snapped-=d; if(snapped<0) snapped=0;
  return snapped;
}
function hit(i){ var now=ctx().currentTime; trigger(curBank,i,now); if(recording){ var raw=(now-recStart)%loopDur(); events.push({i:i,bank:curBank,t:quantize(raw)}); } }
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

// ---- quantize switch ----
var qzBtns=[].slice.call(document.querySelectorAll('.qz'));
qzBtns.forEach(function(btn){ btn.addEventListener('click',function(){
  quantizeDiv=+btn.getAttribute('data-qz');
  qzBtns.forEach(function(b){ b.classList.toggle('on', b===btn); });
  var lbl=quantizeDiv?('1/'+quantizeDiv):'OFF';
  var lq=document.getElementById('lcdQtz'); if(lq) lq.textContent='QTZ '+lbl;
  setStatus('quantize: '+lbl);
}); });

// ---- oscilloscope: draws the live master waveform, reacts to every hit ----
var scopeEl=document.getElementById('scope'), scopeCtx=scopeEl.getContext('2d');
var _wbuf=new Uint8Array(256);
function drawScope(){
  var w=scopeEl.width, h=scopeEl.height;
  scopeCtx.clearRect(0,0,w,h);
  if(masterAn){
    masterAn.getByteTimeDomainData(_wbuf);
    scopeCtx.beginPath(); scopeCtx.lineWidth=2; scopeCtx.strokeStyle='#1e2a12';
    for(var i=0;i<_wbuf.length;i++){ var x=(i/_wbuf.length)*w; var y=(_wbuf[i]/255)*h; if(i===0) scopeCtx.moveTo(x,y); else scopeCtx.lineTo(x,y); }
    scopeCtx.stroke();
  }
  requestAnimationFrame(drawScope);
}
drawScope();

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

// ---- tap tempo ----
var tapTimes=[];
function applyBpm(v){ bpm=v; document.getElementById('bpm').value=v; document.getElementById('bpmv').textContent=v; var lb=document.getElementById('lcdBpm'); if(lb) lb.textContent=(v<100?'0':'')+v; }
function doTap(){
  var now=performance.now();
  if(tapTimes.length && now-tapTimes[tapTimes.length-1]>2200) tapTimes=[];
  tapTimes.push(now); if(tapTimes.length>8) tapTimes.shift();
  if(tapTimes.length>=2){
    var iv=[]; for(var i=1;i<tapTimes.length;i++) iv.push(tapTimes[i]-tapTimes[i-1]);
    var avg=iv.reduce(function(a,b){return a+b;},0)/iv.length;
    var v=Math.round(60000/avg); v=Math.max(60,Math.min(150,v));
    applyBpm(v); setStatus('tap tempo — '+v+' bpm');
  } else { setStatus('tap again to set tempo…'); }
}
var tapBtn=document.getElementById('tapTempo');
tapBtn.addEventListener('click',function(e){ e.preventDefault(); doTap(); });
tapBtn.addEventListener('touchstart',function(e){ e.preventDefault(); doTap(); },{passive:false});

// ---- mixer faders ----
function bindFader(id,getNode){ var el=document.getElementById(id); if(!el) return; el.addEventListener('input',function(){ ctx(); var n=getNode(); if(n) n.gain.value=+this.value; }); }
bindFader('gDrums',function(){return drumGain;});
bindFader('gMelody',function(){return melodyGain;});
bindFader('gKeys',function(){return keysGain;});
bindFader('gTT',function(){return ttGain;});
bindFader('gMaster',function(){return master;});

// ---- turntable: scratch (drag the vinyl) + spin (straight playback) ----
var vinylEl=document.getElementById('vinyl'), tonearmEl=document.getElementById('tonearm');
var ttScratchSrc=null, ttLoopSrc=null, ttPlayhead=0, ttDragging=false, ttLastAngle=0, scratchSrcIdx=0;
document.getElementById('scratchSrc').addEventListener('change',function(){ scratchSrcIdx=+this.value; ttPlayhead=0; });
function ttBuffer(){ return scratchSrcIdx===0 ? buffers[1][0] : hypeBuffers[scratchSrcIdx-1]; }
function armDown(){ if(tonearmEl) tonearmEl.classList.add('down'); }
function armUp(){ if(tonearmEl) tonearmEl.classList.remove('down'); }
function angleAt(x,y){ var r=vinylEl.getBoundingClientRect(); var cx=r.left+r.width/2, cy=r.top+r.height/2; return Math.atan2(y-cy,x-cx); }
function scratchAt(offset,rate){
  var buf=ttBuffer(); if(!buf) return;
  ctx();
  if(ttScratchSrc){ try{ ttScratchSrc.stop(); }catch(e){} }
  var s=AC.createBufferSource(); s.buffer=buf; s.playbackRate.value=Math.max(-3.5,Math.min(3.5,rate));
  s.connect(ttGain); s.start(0, Math.max(0,Math.min(buf.duration-0.05,offset)));
  ttScratchSrc=s; setTimeout(function(){ try{ s.stop(); }catch(e){} },100);
}
function ttDown(x,y){ if(!ttBuffer()){ setStatus('still loading…'); return; } ttDragging=true; ttLastAngle=angleAt(x,y); vinylEl.classList.remove('spin'); armDown(); if(ttLoopSrc){ try{ttLoopSrc.stop();}catch(e){} ttLoopSrc=null; } }
function ttMove(x,y){ if(!ttDragging) return; var buf=ttBuffer(); if(!buf) return; var a=angleAt(x,y); var d=a-ttLastAngle; if(d>Math.PI) d-=2*Math.PI; if(d<-Math.PI) d+=2*Math.PI; ttLastAngle=a;
  var dt=d*(buf.duration/(2*Math.PI))*2.4; ttPlayhead=Math.max(0,Math.min(buf.duration-0.05,ttPlayhead+dt));
  var rate=dt/0.016; if(Math.abs(rate)<0.15) rate=rate<0?-0.15:0.15; scratchAt(ttPlayhead,rate); }
function ttUp(){ ttDragging=false; if(!ttLoopSrc) armUp(); }
vinylEl.addEventListener('mousedown',function(e){ e.preventDefault(); ttDown(e.clientX,e.clientY); });
document.addEventListener('mousemove',function(e){ ttMove(e.clientX,e.clientY); });
document.addEventListener('mouseup',ttUp);
vinylEl.addEventListener('touchstart',function(e){ e.preventDefault(); var t=e.touches[0]; ttDown(t.clientX,t.clientY); },{passive:false});
vinylEl.addEventListener('touchmove',function(e){ e.preventDefault(); var t=e.touches[0]; ttMove(t.clientX,t.clientY); },{passive:false});
vinylEl.addEventListener('touchend',ttUp);
document.getElementById('ttPlay').addEventListener('click',function(){ var buf=ttBuffer(); if(!buf){ setStatus('still loading…'); return; } ctx(); if(ttLoopSrc){ try{ttLoopSrc.stop();}catch(e){} } var s=AC.createBufferSource(); s.buffer=buf; s.loop=true; s.connect(ttGain); s.start(0); ttLoopSrc=s; vinylEl.classList.add('spin'); armDown(); setStatus('turntable spinning'); });
document.getElementById('ttStop').addEventListener('click',function(){ if(ttLoopSrc){ try{ttLoopSrc.stop();}catch(e){} ttLoopSrc=null; } vinylEl.classList.remove('spin'); armUp(); });

// ---- mixer mutes (remember prior fader value, restore on un-mute) ----
function bindMute(btnId,faderId,getNode){
  var btn=document.getElementById(btnId), fader=document.getElementById(faderId), prev=1;
  btn.addEventListener('click',function(){
    ctx(); var n=getNode(); var muted=btn.classList.toggle('on');
    if(muted){ prev=+fader.value; if(n) n.gain.value=0; }
    else { fader.value=prev; if(n) n.gain.value=prev; }
  });
}
bindMute('xDrums','gDrums',function(){return drumGain;});
bindMute('xMelody','gMelody',function(){return melodyGain;});
bindMute('xKeys','gKeys',function(){return keysGain;});
bindMute('xTT','gTT',function(){return ttGain;});
bindMute('xMaster','gMaster',function(){return master;});

// ---- keyboard: pitch-shifted one-shots, switchable voice, switchable octave ----
var VOICE_IDX=[11,12,13,14]; // melody bank indices: Rhodes stab, Chord shot, Stab, Bells
var curVoice=0, octave=0;
document.getElementById('voiceSel').addEventListener('change',function(){ curVoice=+this.value; });
function setOctave(o){ octave=Math.max(-2,Math.min(2,o)); document.getElementById('octLbl').textContent='OCT '+(octave>0?'+':'')+octave; }
document.getElementById('octUp').addEventListener('click',function(){ setOctave(octave+1); });
document.getElementById('octDown').addEventListener('click',function(){ setOctave(octave-1); });
function playKey(semi,el){
  var idx=VOICE_IDX[curVoice]; var buf=buffers[1][idx];
  if(!buf){ setStatus('still loading…'); return; }
  ctx(); var s=AC.createBufferSource(); s.buffer=buf; s.playbackRate.value=Math.pow(2,(semi+octave*12)/12);
  var g=AC.createGain(); g.gain.value=1; s.connect(g).connect(keysGain); s.start(0);
  if(el){ el.classList.add('active'); setTimeout(function(){ el.classList.remove('active'); },160); }
}
[].slice.call(document.querySelectorAll('.key')).forEach(function(el){
  var semi=+el.getAttribute('data-semi');
  el.addEventListener('mousedown',function(e){ e.preventDefault(); playKey(semi,el); });
  el.addEventListener('touchstart',function(e){ e.preventDefault(); playKey(semi,el); },{passive:false});
});

// ---- compressor rack: live knob binding + on/off (bypass = transparent params) ----
function bindComp(prefix,getComp,getMakeup,defaults){
  function upd(){
    var comp=getComp(), mk=getMakeup(); if(!comp) return;
    var th=+document.getElementById(prefix+'-th').value, ra=+document.getElementById(prefix+'-ra').value,
        at=+document.getElementById(prefix+'-at').value, re=+document.getElementById(prefix+'-re').value,
        mkv=+document.getElementById(prefix+'-mk').value/100;
    document.getElementById(prefix+'-thv').textContent=th+'dB';
    document.getElementById(prefix+'-rav').textContent=ra+':1';
    document.getElementById(prefix+'-atv').textContent=at+'ms';
    document.getElementById(prefix+'-rev').textContent=re+'ms';
    document.getElementById(prefix+'-mkv').textContent=mkv.toFixed(2)+'x';
    var btn=document.querySelector('[data-target="'+(prefix==='cd'?'drum':'master')+'"]');
    var on=btn ? !btn.classList.contains('off') : true;
    comp.threshold.value = on ? th : 0;
    comp.ratio.value = on ? ra : 1;
    comp.attack.value = on ? at/1000 : 0.001;
    comp.release.value = on ? re/1000 : 0.05;
    comp.knee.value = on ? 6 : 0;
    if(mk) mk.gain.value = on ? mkv : 1;
  }
  ['th','ra','at','re','mk'].forEach(function(k){ var el=document.getElementById(prefix+'-'+k); if(el) el.addEventListener('input',function(){ ctx(); upd(); }); });
  return upd;
}
var updDrumComp=bindComp('cd',function(){return drumComp;},function(){return drumMakeup;});
var updMasterComp=bindComp('cm',function(){return masterComp;},function(){return masterMakeup;});
function bindCompToggle(btnId,updFn){
  var btn=document.getElementById(btnId);
  btn.addEventListener('click',function(){ btn.classList.toggle('off'); btn.textContent = btn.classList.contains('off') ? 'OFF' : 'ON'; ctx(); updFn(); });
}
bindCompToggle('compDrumOn',function(){ updDrumComp(); });
bindCompToggle('compMasterOn',function(){ updMasterComp(); });
})();
</script>
<?php khb_footer(); ?>
