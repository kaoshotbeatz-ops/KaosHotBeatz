<?php
require_once __DIR__ . '/lib.php';
header('Content-Type: application/json');
if (!csrf_ok()) { http_response_code(403); echo json_encode(['error' => 'Bad token']); exit; }
$member = current_member();
if (!$member) { http_response_code(401); echo json_encode(['error' => 'Sign in required']); exit; }
$d = cart_detail();
if (!$d['items']) { echo json_encode(['error' => 'Cart empty']); exit; }

$items = [];
foreach ($d['items'] as $it) {
    $items[] = [
        'name' => substr($it['beat']['title'] . ' — ' . $it['tier_name'], 0, 127),
        'quantity' => '1',
        'unit_amount' => ['currency_code' => 'USD', 'value' => number_format($it['price'], 2, '.', '')],
    ];
}
$res = paypal_request('POST', '/v2/checkout/orders', [
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'amount' => [
            'currency_code' => 'USD',
            'value' => number_format($d['total'], 2, '.', ''),
            'breakdown' => ['item_total' => ['currency_code' => 'USD', 'value' => number_format($d['total'], 2, '.', '')]],
        ],
        'items' => $items,
    ]],
]);
if (empty($res['id'])) { echo json_encode(['error' => $res['error'] ?? 'Could not create order']); exit; }
echo json_encode(['id' => $res['id']]);
