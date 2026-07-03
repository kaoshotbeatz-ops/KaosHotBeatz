<?php
require_once __DIR__ . '/_guard.php';
$bookingId = basename($_GET['booking'] ?? '');
$which = ($_GET['which'] ?? '') === 'id' ? 'id' : 'photo';
$bookings = khb_load('bookings');
$booking = null;
foreach ($bookings as $b) if ($b['id'] === $bookingId) { $booking = $b; break; }
if (!$booking) { http_response_code(404); exit('Not found.'); }
$field = $which === 'id' ? 'verify_id' : 'verify_photo';
$file = $booking[$field] ?? '';
if (!$file) { http_response_code(404); exit('No file on record.'); }
$path = __DIR__ . '/../data/uploads/ids/' . $bookingId . '/' . basename($file);
if (!is_file($path)) { http_response_code(404); exit('File missing.'); }
$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$mimes = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . $which . '-' . $bookingId . '.' . $ext . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
