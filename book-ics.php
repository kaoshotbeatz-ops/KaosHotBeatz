<?php
require_once __DIR__ . '/lib.php';
$TZ = new DateTimeZone('America/New_York');

function ics_escape($s) { return preg_replace('/([,;\\\\])/', '\\\\$1', str_replace("\n", '\\n', $s)); }
function ics_event($bk, $TZ) {
    $dt  = DateTime::createFromFormat('Y-m-d H:i', $bk['date'] . ' ' . $bk['time'], $TZ);
    if (!$dt) return '';
    $end = (clone $dt)->modify('+' . ($bk['duration'] ?? 120) . ' minutes');
    $uS  = (clone $dt)->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
    $uE  = (clone $end)->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');
    $stamp = gmdate('Ymd\THis\Z');
    $out  = "BEGIN:VEVENT\r\n";
    $out .= "UID:" . $bk['id'] . "@kaoshotbeatz.com\r\n";
    $out .= "DTSTAMP:$stamp\r\n";
    $out .= "DTSTART:$uS\r\n";
    $out .= "DTEND:$uE\r\n";
    $out .= "SUMMARY:" . ics_escape('KAOS Hot Beatz — ' . ($bk['service_name'] ?? 'Session')) . "\r\n";
    $out .= "DESCRIPTION:" . ics_escape('Studio session with KAOS Hot Beatz. Booked by ' . ($bk['name'] ?? '') . '. ' . ($bk['notes'] ?? '')) . "\r\n";
    $out .= "LOCATION:" . ics_escape('KAOS Hot Beatz Studio') . "\r\n";
    $out .= "STATUS:CONFIRMED\r\n";
    $out .= "END:VEVENT\r\n";
    return $out;
}

$bookings = khb_load('bookings');
$body = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//KAOS Hot Beatz//Booking//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";

// Owner feed: all upcoming bookings (token-protected) — subscribe once, see every session on your phone.
if (isset($_GET['feed'])) {
    $token = defined('CAL_FEED_TOKEN') ? CAL_FEED_TOKEN : '';
    if (!$token || !hash_equals($token, (string)$_GET['feed'])) { http_response_code(403); exit('Forbidden'); }
    $body .= "X-WR-CALNAME:KAOS Hot Beatz — Sessions\r\n";
    foreach ($bookings as $bk) if (($bk['status'] ?? '') !== 'cancelled') $body .= ics_event($bk, $TZ);
    $body .= "END:VCALENDAR\r\n";
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: inline; filename="kaoshotbeatz-sessions.ics"');
    echo $body; exit;
}

// Single booking .ics download
$id = $_GET['id'] ?? '';
$bk = null; foreach ($bookings as $b) if ($b['id'] === $id) { $bk = $b; break; }
if (!$bk) { http_response_code(404); exit('Not found'); }
$body .= ics_event($bk, $TZ) . "END:VCALENDAR\r\n";
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="kaoshotbeatz-session.ics"');
echo $body;
