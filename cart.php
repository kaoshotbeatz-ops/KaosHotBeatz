<?php
require_once __DIR__ . '/partials.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    if (isset($_POST['remove'])) cart_remove((int)$_POST['remove']);
    if (isset($_POST['clear'])) cart_clear();
    header('Location: /cart.php'); exit;
}
$d = cart_detail();
$member = current_member();
khb_header('Cart', '');
?>
<section>
  <div class="wrap" style="max-width:800px">
    <h2>Your Cart</h2>
    <?php if (!$d['items']): ?>
      <div class="card"><p class="muted">Cart's empty. <a href="/beats.php">Go find a beat →</a></p></div>
    <?php else: ?>
      <div class="beat-list" style="margin-bottom:20px">
        <?php foreach ($d['items'] as $it): ?>
        <div class="beat" style="grid-template-columns:1fr auto auto">
          <div class="meta"><div class="t"><?= h($it['beat']['title']) ?></div><div class="s"><?= h($it['tier_name']) ?></div></div>
          <div class="price mono"><?= money($it['price']) ?></div>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="remove" value="<?= $it['idx'] ?>"><button class="btn sm ghost">✕</button></form>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="card" style="display:flex;justify-content:space-between;align-items:center">
        <div><span class="muted">Total</span><br><span class="mono" style="font-size:1.6rem;color:var(--amber)"><?= money($d['total']) ?></span></div>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="clear" value="1"><button class="btn ghost sm">Clear cart</button></form>
      </div>

      <?php if (!$member): ?>
        <div class="notice ok" style="margin-top:20px">Sign in so we can deliver your files and save your purchase history. <a href="/member/login.php?next=/cart.php">Sign in</a> · <a href="/member/register.php?next=/cart.php">Create account</a></div>
      <?php elseif (!PAYPAL_CLIENT): ?>
        <div class="notice err" style="margin-top:20px">Checkout not configured yet — add your PayPal credentials to <code>config.php</code> to enable payments.</div>
      <?php else: ?>
        <div id="paypal-button-container" style="margin-top:24px"></div>
        <div id="pay-msg"></div>
        <script src="https://www.paypal.com/sdk/js?client-id=<?= h(PAYPAL_CLIENT) ?>&currency=USD"></script>
        <script>
        paypal.Buttons({
          createOrder: function(){ return fetch('/paypal-create.php',{method:'POST',headers:{'X-CSRF-Token':'<?= h(csrf_token()) ?>'}}).then(r=>r.json()).then(d=>d.id); },
          onApprove: function(data){
            return fetch('/paypal-capture.php',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':'<?= h(csrf_token()) ?>'},body:JSON.stringify({orderID:data.orderID})})
              .then(r=>r.json()).then(function(res){
                if(res.ok){ window.location='/member/account.php?purchased=1'; }
                else { document.getElementById('pay-msg').innerHTML='<div class="notice err">'+(res.error||'Payment could not be completed.')+'</div>'; }
              });
          }
        }).render('#paypal-button-container');
        </script>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php khb_footer(); ?>
