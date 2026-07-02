<?php
require_once __DIR__ . '/lib.php';
$id = $_GET['id'] ?? '';
$beat = null;
foreach (khb_load('beats') as $b) if ($b['id'] === $id) { $beat = $b; break; }
if (!$beat) { http_response_code(404); exit('Beat not found.'); }
$audio = $beat['preview'] ? '/assets/beats/' . rawurlencode($beat['preview']) : '';
// deterministic variation per beat
$seed = crc32($beat['id']);
$modes = ['spin', 'windmill', 'toprock'];
$mode = $modes[$seed % 3];
$accents = ['#e11d1d', '#ff8a1e', '#00e0a4', '#ffd152', '#ff2e6e'];
$accent = $accents[($seed >> 3) % count($accents)];
$spd = 0.9 + (($seed >> 5) % 7) * 0.12; // 0.9–1.6s
?><!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= h($beat['title']) ?> — KAOS HOT BEATZ clip</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&family=Permanent+Marker&display=swap">
<style>
*{margin:0;box-sizing:border-box}
:root{--acc:<?= $accent ?>}
html,body{height:100%;background:#08080a;overflow:hidden;font-family:"Anton",Impact,sans-serif}
.stage{position:relative;width:min(100vw,calc(100vh*9/16));height:100vh;margin:0 auto;overflow:hidden;
  background:radial-gradient(70% 50% at 50% 30%,rgba(225,29,29,.18),transparent 60%),#08080a}
/* brick */
.stage::before{content:"";position:absolute;inset:0;opacity:.10;
  background-image:repeating-linear-gradient(0deg,#000 0 3px,transparent 3px 44px),repeating-linear-gradient(90deg,#000 0 3px,transparent 3px 88px)}
/* graffiti scroll */
.graf{position:absolute;top:16%;left:0;white-space:nowrap;font-family:"Anton";font-size:20vh;color:transparent;
  -webkit-text-stroke:2px var(--acc);opacity:.16;animation:gscroll 14s linear infinite}
@keyframes gscroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
/* skyline */
.sky{position:absolute;bottom:0;left:0;width:200%;height:22%;display:flex;animation:sky 40s linear infinite;opacity:.6}
.sky div{flex:0 0 5%;background:#0c0c10;border-right:2px solid #000;align-self:flex-end}
@keyframes sky{from{transform:translateX(0)}to{transform:translateX(-50%)}}
/* turntable */
.tt{position:absolute;top:26%;left:50%;transform:translateX(-50%);width:52vh;height:52vh;border-radius:50%;
  background:repeating-radial-gradient(circle,#0c0c0e 0 3px,#161619 3px 6px);box-shadow:0 0 0 8px #111,0 0 60px rgba(0,0,0,.7);
  animation:spin 3s linear infinite}
.tt::after{content:"";position:absolute;inset:0;margin:auto;width:34%;height:34%;border-radius:50%;background:radial-gradient(circle,var(--acc) 60%,#000 61%)}
@keyframes spin{to{transform:translateX(-50%) rotate(360deg)}}
/* breakdancer */
.bboy{position:absolute;bottom:22%;left:50%;width:26vh;height:26vh;transform-origin:50% 70%;z-index:3}
.bboy svg{width:100%;height:100%;filter:drop-shadow(0 6px 12px rgba(0,0,0,.6))}
.m-spin .bboy{animation:bspin <?= $spd ?>s linear infinite}
.m-windmill .bboy{animation:bspin <?= $spd*1.4 ?>s linear infinite}
.m-toprock .bboy{animation:btop <?= $spd ?>s ease-in-out infinite}
@keyframes bspin{from{transform:translateX(-50%) rotate(0)}to{transform:translateX(-50%) rotate(360deg)}}
@keyframes btop{0%,100%{transform:translateX(-50%) rotate(-14deg)}50%{transform:translateX(-50%) rotate(14deg)}}
/* text */
.top{position:absolute;top:5%;left:0;right:0;text-align:center;z-index:5}
.top .brand{font-family:"Permanent Marker";color:var(--acc);font-size:4.4vh;letter-spacing:1px}
.title{position:absolute;bottom:8%;left:0;right:0;text-align:center;z-index:5;padding:0 4vw}
.title h1{color:#fff;font-size:8.4vh;line-height:.92;text-transform:uppercase;text-shadow:4px 4px 0 #000}
.title .g{color:#cdb;font-family:"Permanent Marker";font-size:3vh;margin-top:1.2vh}
/* waveform */
.wave{position:absolute;bottom:3%;left:0;right:0;display:flex;justify-content:center;align-items:flex-end;gap:.6vh;height:7vh;z-index:5}
.wave i{width:1.1vh;background:var(--acc);border-radius:2px;animation:eq .7s ease-in-out infinite}
@keyframes eq{0%,100%{height:22%}50%{height:100%}}
/* play overlay */
#go{position:absolute;inset:0;z-index:9;display:flex;flex-direction:column;align-items:center;justify-content:center;
  background:rgba(0,0,0,.55);cursor:pointer;color:#fff;text-align:center}
#go .p{width:16vh;height:16vh;border-radius:50%;border:4px solid var(--acc);display:grid;place-items:center;font-size:8vh;color:var(--acc)}
#go .t{margin-top:3vh;font-family:"Permanent Marker";font-size:3vh}
.hint{position:absolute;top:12%;left:0;right:0;text-align:center;color:#888;font-family:"Permanent Marker";font-size:2.2vh;z-index:5}
.playing #go{display:none}
</style></head>
<body class="m-<?= $mode ?>">
<div class="stage" id="stage">
  <div class="graf"><?php for($i=0;$i<6;$i++) echo 'KAOSHOTBEATZ '; ?></div>
  <div class="sky"><?php for($i=0;$i<40;$i++){ $hh=20+(($seed>>$i%20)%70); echo '<div style="height:'.$hh.'%"></div>'; } ?></div>
  <div class="tt"></div>
  <div class="bboy">
    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="#0a0a0a" stroke="#000" stroke-width="2">
      <circle cx="50" cy="24" r="11" fill="#111"/>
      <rect x="42" y="34" width="16" height="30" rx="7" fill="#151515"/>
      <path d="M44 40 L18 30 L14 36 L42 50 Z" fill="#151515"/>
      <path d="M56 40 L84 52 L82 60 L54 52 Z" fill="#151515"/>
      <path d="M46 62 L26 84 L34 90 L52 68 Z" fill="#1a1a1a"/>
      <path d="M54 62 L78 78 L72 86 L50 70 Z" fill="#1a1a1a"/>
      <circle cx="50" cy="24" r="11" fill="none" stroke="var(--acc)" stroke-width="1.5"/>
    </svg>
  </div>
  <div class="top"><div class="brand">KAOS HOT BEATZ</div></div>
  <div class="hint">◉ screen-record 30s → post it</div>
  <div class="title"><h1><?= h($beat['title']) ?></h1><div class="g"><?= h($beat['genre'] ?? 'Hip-Hop') ?> · Prod. KAOS</div></div>
  <div class="wave"><?php for($i=0;$i<22;$i++) echo '<i style="animation-delay:'.($i*0.05).'s"></i>'; ?></div>
  <div id="go"><div class="p">▶</div><div class="t">tap to start the beat</div></div>
</div>
<?php if($audio): ?>
<audio id="a" src="<?= $audio ?>" loop></audio>
<script>
var go=document.getElementById('go'),a=document.getElementById('a'),st=document.getElementById('stage');
go.onclick=function(){ a.play().then(function(){ document.body.classList.add('playing'); }).catch(function(){}); };
</script>
<?php endif; ?>
</body></html>
