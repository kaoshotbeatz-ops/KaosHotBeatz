<?php
require_once __DIR__ . '/lib.php';
require_member();
$member = current_member();
$token = $_GET['t'] ?? '';
$dls = khb_load('downloads');
$idx = null;
foreach ($dls as $i => $dl) if ($dl['token'] === $token && $dl['member'] === $member['id']) { $idx = $i; break; }
if ($idx === null) { http_response_code(404); exit('Invalid download link.'); }
$dl = $dls[$idx];
if ($dl['used'] >= $dl['max']) { http_response_code(410); exit('Download limit reached — contact us to reset.'); }

$beat = null; foreach (khb_load('beats') as $b) if ($b['id'] === $dl['beat']) { $beat = $b; break; }
if (!$beat) { http_response_code(404); exit('Beat file missing.'); }

// Pick the file that matches the license tier (falls back to master).
$field = ['mp3' => 'file_mp3', 'wav' => 'file_wav', 'stems' => 'file_stems', 'excl' => 'file_stems'];
$file = $beat[$field[$dl['tier']] ?? 'file_wav'] ?? ($beat['file_wav'] ?? $beat['file_mp3'] ?? '');
$path = __DIR__ . '/assets/beats/' . basename($file);
if (!$file || !file_exists($path)) { http_response_code(404); exit('File not uploaded yet — contact us and we\'ll send it directly.'); }

$dls[$idx]['used']++;
khb_save('downloads', $dls);

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', $beat['title']) . '-' . $dl['tier'] . '.' . pathinfo($file, PATHINFO_EXTENSION) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
