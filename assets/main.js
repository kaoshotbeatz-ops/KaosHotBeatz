// KAOS HOT BEATZ — front-end: preview player + MPC pad hero animation.
(function () {
  // Sticky preview player
  var bar, audio, npEl;
  function ensureBar() {
    if (bar) return;
    bar = document.createElement('div');
    bar.id = 'player-bar';
    bar.innerHTML = '<span class="np mono"></span><audio controls></audio>' +
      '<button class="btn sm ghost" id="pb-close">✕</button>';
    document.body.appendChild(bar);
    audio = bar.querySelector('audio');
    npEl = bar.querySelector('.np');
    bar.querySelector('#pb-close').onclick = stopAll;
    audio.addEventListener('ended', stopAll);
  }
  var current = null;
  function stopAll() {
    if (audio) { audio.pause(); }
    if (bar) bar.classList.remove('show');
    document.querySelectorAll('.play.playing').forEach(function (b) { b.classList.remove('playing'); b.textContent = '▶'; });
    current = null;
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.play');
    if (!btn) return;
    ensureBar();
    var src = btn.getAttribute('data-src');
    var title = btn.getAttribute('data-title') || 'Preview';
    if (current === btn) { stopAll(); return; }
    document.querySelectorAll('.play.playing').forEach(function (b) { b.classList.remove('playing'); b.textContent = '▶'; });
    btn.classList.add('playing'); btn.textContent = '⏸';
    npEl.textContent = '♪ ' + title;
    audio.src = src; audio.play().catch(function(){});
    bar.classList.add('show');
    current = btn;
  });

  // Hero pad blink
  var pads = document.querySelectorAll('.pad-grid i');
  if (pads.length) {
    var seed = 1;
    setInterval(function () {
      pads.forEach(function (p) { p.classList.remove('lit'); });
      seed = (seed * 1103515245 + 12345) & 0x7fffffff;
      var a = seed % pads.length, b = (seed >> 4) % pads.length;
      pads[a].classList.add('lit'); pads[b].classList.add('lit');
    }, 620);
  }
})();
