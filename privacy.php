<?php
require_once __DIR__ . '/partials.php';
khb_header('Privacy Policy', '');
?>
<section>
  <div class="wrap" style="max-width:760px">
    <p class="ey">Legal</p>
    <h2>Privacy Policy</h2>
    <p class="muted">Last updated <?= h(date('F j, Y')) ?></p>

    <div class="card" style="margin-top:24px">
      <h3>What we collect</h3>
      <p class="muted">When you create an account, purchase a beat, or submit a booking or contact form, we collect the information you provide: name, email address, and any message content. We do not collect or store payment card details — those are handled directly by PayPal.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>How we use it</h3>
      <p class="muted">We use your information to process orders, deliver downloads, respond to booking and contact requests, and manage your account. We do not sell or share your personal information with third parties for marketing purposes.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>Cookies</h3>
      <p class="muted">This site uses a session cookie to keep you signed in and to remember your cart. No third-party advertising or tracking cookies are used.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>Data retention</h3>
      <p class="muted">Account and order records are kept as long as your account is active, or as required for tax and business records. You may request deletion of your account and associated data at any time.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>Your rights</h3>
      <p class="muted">You can request a copy of the personal data we hold about you, ask us to correct it, or ask us to delete it, subject to legal recordkeeping requirements. Contact us to make a request.</p>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>Contact</h3>
      <p class="muted">Questions about this policy or your data? <a href="/contact.php">Contact us</a> or email <a href="mailto:<?= h(SITE_EMAIL) ?>"><?= h(SITE_EMAIL) ?></a>.</p>
    </div>
  </div>
</section>
<?php khb_footer(); ?>
