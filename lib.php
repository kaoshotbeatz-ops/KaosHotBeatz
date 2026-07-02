<?php
// KaosHotBeatz — shared core library.
// Flat-file JSON storage (no DB), member auth, cart, CSRF, PayPal helpers.
// Same security posture as the rest of the Omar Huertas LLC sites.

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) session_start();
date_default_timezone_set('America/New_York');

$__cfg = __DIR__ . '/config.php';
if (file_exists($__cfg)) require $__cfg;
// Fallbacks so the site runs before config.php is created.
if (!defined('SITE_NAME'))     define('SITE_NAME', 'KAOS HOT BEATZ');
if (!defined('SITE_EMAIL'))    define('SITE_EMAIL', 'beats@kaoshotbeatz.com');
if (!defined('PAYPAL_ENV'))    define('PAYPAL_ENV', 'sandbox');          // sandbox | live
if (!defined('PAYPAL_CLIENT')) define('PAYPAL_CLIENT', '');              // set in config.php
if (!defined('PAYPAL_SECRET')) define('PAYPAL_SECRET', '');              // set in config.php
if (!defined('DEPOSIT_AMOUNT'))define('DEPOSIT_AMOUNT', 50.00);          // studio session deposit
if (!defined('ARTIST_TAGLINE'))define('ARTIST_TAGLINE', 'NY · Long Island Hip-Hop Producer');
if (!defined('ARTIST_GENRES')) define('ARTIST_GENRES', 'Soul · Hip-Hop · Boom Bap · Raw · Gospel');
if (!defined('SUNO_URL'))       define('SUNO_URL', 'https://suno.com/@kaoshotbeatz');
if (!defined('INSTAGRAM_URL'))  define('INSTAGRAM_URL', 'https://instagram.com/kaoshotbeatz');
if (!defined('STAT_PLAYS'))     define('STAT_PLAYS', '36K+');
if (!defined('STAT_SONGS'))     define('STAT_SONGS', '25+');

// Extract a Suno song ID from a full URL or bare ID (for iframe embeds).
function suno_id($v) {
    $v = trim($v);
    if (preg_match('~/song/([a-z0-9-]+)~i', $v, $m)) return $m[1];
    return preg_replace('~[^a-z0-9-]~i', '', $v);
}

// ---- Data helpers (JSON files under /data, protected by .htaccess) ----
function khb_path($name) { return __DIR__ . '/data/' . $name . '.json'; }
function khb_load($name) {
    $p = khb_path($name);
    if (!file_exists($p)) return [];
    $d = json_decode(file_get_contents($p), true);
    return is_array($d) ? $d : [];
}
function khb_save($name, $data) {
    $dir = dirname(khb_path($name));
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return @file_put_contents(khb_path($name), json_encode($data, JSON_PRETTY_PRINT), LOCK_EX) !== false;
}
function khb_uuid() { return bin2hex(random_bytes(8)); }
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES); }
function money($n) { return '$' . number_format((float)$n, 2); }

// ---- CSRF ----
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function csrf_token() { return $_SESSION['csrf']; }
function csrf_field() { return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">'; }
function csrf_ok() {
    $s = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return $s !== '' && hash_equals($_SESSION['csrf'], $s);
}

// ---- Members ----
function current_member() {
    if (empty($_SESSION['member_id'])) return null;
    foreach (khb_load('members') as $m) if ($m['id'] === $_SESSION['member_id']) return $m;
    return null;
}
function require_member() {
    if (!current_member()) { header('Location: /member/login.php?next=' . urlencode($_SERVER['REQUEST_URI'])); exit; }
}

// ---- License tiers offered on every beat ----
function license_tiers() {
    return [
        'mp3'    => ['name' => 'MP3 Lease',        'price' => 29.99, 'desc' => 'Tagless MP3. Up to 5,000 streams. Non-exclusive.'],
        'wav'    => ['name' => 'WAV Lease',         'price' => 49.99, 'desc' => 'MP3 + WAV. Up to 10,000 streams. Non-exclusive.'],
        'stems'  => ['name' => 'Trackout / Stems',  'price' => 99.99, 'desc' => 'MP3 + WAV + individual track stems. Up to 50,000 streams.'],
        'excl'   => ['name' => 'Exclusive',         'price' => 299.99,'desc' => 'Full ownership transfer. Beat removed from store. Unlimited use.'],
    ];
}

// ---- Cart (session) : items = [ ['beat'=>id,'tier'=>key], ... ] ----
function cart() { return $_SESSION['cart'] ?? []; }
function cart_add($beatId, $tier) {
    $c = cart();
    foreach ($c as $i) if ($i['beat'] === $beatId && $i['tier'] === $tier) return; // no dupes
    $c[] = ['beat' => $beatId, 'tier' => $tier];
    $_SESSION['cart'] = $c;
}
function cart_remove($idx) { $c = cart(); unset($c[$idx]); $_SESSION['cart'] = array_values($c); }
function cart_clear() { $_SESSION['cart'] = []; }
function cart_detail() {
    $beats = khb_load('beats'); $tiers = license_tiers(); $out = []; $total = 0;
    foreach (cart() as $idx => $i) {
        $beat = null; foreach ($beats as $b) if ($b['id'] === $i['beat']) { $beat = $b; break; }
        if (!$beat || !isset($tiers[$i['tier']])) continue;
        $price = $tiers[$i['tier']]['price']; $total += $price;
        $out[] = ['idx' => $idx, 'beat' => $beat, 'tier' => $i['tier'],
                  'tier_name' => $tiers[$i['tier']]['name'], 'price' => $price];
    }
    return ['items' => $out, 'total' => $total];
}

// ---- PayPal REST (Orders v2) ----
function paypal_base() {
    return PAYPAL_ENV === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
}
function paypal_token() {
    $ch = curl_init(paypal_base() . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => PAYPAL_CLIENT . ':' . PAYPAL_SECRET,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $res = json_decode(curl_exec($ch), true); curl_close($ch);
    return $res['access_token'] ?? null;
}
function paypal_request($method, $path, $payload = null) {
    $tok = paypal_token();
    if (!$tok) return ['error' => 'PayPal auth failed — check credentials in config.php'];
    $ch = curl_init(paypal_base() . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $tok],
    ]);
    if ($payload !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $res = json_decode(curl_exec($ch), true); curl_close($ch);
    return $res ?: ['error' => 'PayPal request failed'];
}

// ---- Secure download tokens (issued after a captured order) ----
function issue_download($memberId, $beatId, $tier) {
    $dls = khb_load('downloads');
    $token = bin2hex(random_bytes(16));
    $dls[] = ['token' => $token, 'member' => $memberId, 'beat' => $beatId, 'tier' => $tier,
              'ts' => time(), 'used' => 0, 'max' => 5];
    khb_save('downloads', $dls);
    return $token;
}
