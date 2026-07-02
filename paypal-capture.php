<?php
require_once __DIR__ . '/lib.php';
header('Content-Type: application/json');
if (!csrf_ok()) { http_response_code(403); echo json_encode(['error' => 'Bad token']); exit; }
$member = current_member();
if (!$member) { http_response_code(401); echo json_encode(['error' => 'Sign in required']); exit; }
$body = json_decode(file_get_contents('php://input'), true);
$orderID = $body['orderID'] ?? '';
if (!$orderID) { echo json_encode(['error' => 'Missing order']); exit; }

$res = paypal_request('POST', "/v2/checkout/orders/{$orderID}/capture");
$status = $res['status'] ?? '';
if ($status !== 'COMPLETED') { echo json_encode(['error' => 'Payment not completed', 'detail' => $res['error'] ?? $status]); exit; }

// Record the order, issue download tokens, remove exclusives from store.
$d = cart_detail();
$orders = khb_load('orders');
$beats = khb_load('beats');
$lineItems = [];
foreach ($d['items'] as $it) {
    $token = issue_download($member['id'], $it['beat']['id'], $it['tier']);
    $lineItems[] = ['beat' => $it['beat']['id'], 'title' => $it['beat']['title'],
                    'tier' => $it['tier'], 'tier_name' => $it['tier_name'],
                    'price' => $it['price'], 'download' => $token];
    if ($it['tier'] === 'excl') {
        foreach ($beats as &$b) if ($b['id'] === $it['beat']['id']) $b['sold_exclusive'] = true;
    }
}
unset($b);
khb_save('beats', $beats);
$orders[] = [
    'id' => khb_uuid(), 'paypal_order' => $orderID, 'member' => $member['id'],
    'member_email' => $member['email'], 'items' => $lineItems, 'total' => $d['total'], 'ts' => time(),
];
khb_save('orders', $orders);
cart_clear();

@mail(SITE_EMAIL, 'New beat sale — ' . money($d['total']),
    $member['name'] . ' (' . $member['email'] . ") purchased " . count($lineItems) . " license(s).",
    'From: no-reply@kaoshotbeatz.com');

echo json_encode(['ok' => true]);
