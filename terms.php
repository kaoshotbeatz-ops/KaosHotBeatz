<?php
require_once __DIR__ . '/partials.php';
khb_header('Terms & Conditions', '');
?>
<section>
  <div class="wrap" style="max-width:760px">
    <p class="ey">Legal</p>
    <h2>Terms &amp; Conditions</h2>
    <p class="muted">Last updated <?= h(date('F j, Y')) ?></p>

    <div class="card" style="margin-top:24px">
      <h3>1. Accounts</h3>
      <p class="muted">Creating an account requires a valid email address. You are responsible for keeping your login credentials secure and for all activity under your account. We may suspend or terminate accounts used for fraud, abuse, or violation of these terms.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>2. Beats &amp; Licensing</h3>
      <p class="muted">All beats are sold under the license tiers described on our <a href="/licensing.php">Licensing page</a>. Non-exclusive leases do not transfer ownership and may be licensed to other artists until a beat is sold exclusively. Exclusive purchases are final. Producer credit is required on all released work per the license terms.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>3. Cover Recordings &amp; Streaming Tools</h3>
      <p class="muted">Cover recordings and any stem-mixer or preview tools on this site are provided for demonstration, portfolio, and educational purposes only. They are not offered for sale, and no rights to any underlying composition are granted or implied. Rights to original compositions remain with their respective owners.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>4. Payments</h3>
      <p class="muted">Payments are processed securely through PayPal. We do not store your card or bank details. All beat and license sales are final once delivered, except where required by law.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>5. Booking &amp; Studio Sessions</h3>
      <p class="muted">Studio booking requests are confirmations of interest, not guaranteed until confirmed by us directly. Cancellation and rescheduling terms will be communicated at the time of booking.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>6. Prohibited Use</h3>
      <p class="muted">You may not use this site to upload, share, or request removal of copyrighted material you don't have rights to, attempt to bypass license restrictions, or interfere with site security or other users' accounts.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>7. Disclaimer &amp; Limitation of Liability</h3>
      <p class="muted">This site and its tools are provided "as is" without warranties of any kind. We are not liable for indirect, incidental, or consequential damages arising from your use of the site, beats, or any downloaded content.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>8. Changes to These Terms</h3>
      <p class="muted">We may update these terms from time to time. Continued use of the site after changes are posted means you accept the updated terms.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>9. Contact</h3>
      <p class="muted">Questions about these terms? <a href="/contact.php">Contact us</a> or email <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a>.</p>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
