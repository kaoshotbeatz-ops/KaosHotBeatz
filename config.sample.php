<?php
// Copy this file to config.php and fill in real values before deploying.
// config.php is git-ignored and never uploaded to source control.

define('SITE_NAME',  'KAOS HOT BEATZ');
define('SITE_EMAIL', 'beats@kaoshotbeatz.com');   // where sales/booking notices go

// Artist identity (shown across the site)
define('ARTIST_TAGLINE', 'NY · Long Island Hip-Hop Producer');
define('ARTIST_GENRES',  'Soul · Hip-Hop · Boom Bap · Raw · Gospel');
define('SUNO_URL',       'https://suno.com/@kaoshotbeatz');
define('INSTAGRAM_URL',  'https://instagram.com/kaosbeatz');
define('BEATSTARS_URL',  'https://www.beatstars.com/kaoshotbeatz');
define('STAT_PLAYS',     '36K+');   // update as it grows
define('STAT_SONGS',     '25+');

// Admin panel password (used at /admin/)
define('ADMIN_PASS', 'change-me-to-something-strong');

// PayPal REST credentials — create an app at https://developer.paypal.com/dashboard/
define('PAYPAL_ENV',    'sandbox');   // 'sandbox' for testing, 'live' when ready to take real money
define('PAYPAL_CLIENT', '');          // Client ID
define('PAYPAL_SECRET', '');          // Secret

// Studio session deposit (USD) charged to lock a booking
define('DEPOSIT_AMOUNT', 50.00);
