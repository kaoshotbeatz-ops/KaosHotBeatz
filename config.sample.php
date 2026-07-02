<?php
// Copy this file to config.php and fill in real values before deploying.
// config.php is git-ignored and never uploaded to source control.

define('SITE_NAME',  'KAOS HOT BEATZ');
define('SITE_EMAIL', 'beats@kaoshotbeatz.com');   // where sales/booking notices go

// Admin panel password (used at /admin/)
define('ADMIN_PASS', 'change-me-to-something-strong');

// PayPal REST credentials — create an app at https://developer.paypal.com/dashboard/
define('PAYPAL_ENV',    'sandbox');   // 'sandbox' for testing, 'live' when ready to take real money
define('PAYPAL_CLIENT', '');          // Client ID
define('PAYPAL_SECRET', '');          // Secret

// Studio session deposit (USD) charged to lock a booking
define('DEPOSIT_AMOUNT', 50.00);
