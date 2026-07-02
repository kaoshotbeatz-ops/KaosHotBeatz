<?php
require_once __DIR__ . '/lib.php';
header('Content-Type: application/json');
if (!csrf_ok()) { http_response_code(403); echo json_encode(['error' => 'Bad token']); exit; }
$action = $_GET['action'] ?? ''; $id = $_GET['id'] ?? '';
$bookings = khb_load('bookings');
$idx = null; foreach ($bookings as $i => $b) if ($b['id'] === $id) { $idx = $i; break; }
if ($idx === null) { echo json_encode(['error' => 'Booking not found']); exit; }
$bk = $bookings[$idx];

if ($action === 'create') {
    $res = paypal_request('POST', '/v2/checkout/orders', [
        'intent' => 'CAPTURE',
        'purchase_units' => [[
            'amount' => ['currency_code' => 'USD', 'value' => number_format(DEPOSIT_AMOUNT, 2, '.', '')],
            'description' => 'Studio deposit — ' . $bk['service_name'] . ' ' . $bk['date'] . ' ' . $bk['time'],
        ]],
    ]);
    if (empty($res['id'])) { echo json_encode(['error' => $res['error'] ?? 'Could not create order']); exit; }
    echo json_encode(['id' => $res['id']]); exit;
}

if ($action === 'capture') {
    $body = json_decode(file_get_contents('php://input'), true);
    $orderID = $body['orderID'] ?? '';
    $res = paypal_request('POST', "/v2/checkout/orders/{$orderID}/capture");
    if (($res['status'] ?? '') !== 'COMPLETED') { echo json_encode(['error' => 'Deposit not completed']); exit; }
    $bookings[$idx]['status'] = 'confirmed';
    $bookings[$idx]['paypal_order'] = $orderID;
    $bookings[$idx]['deposit_paid'] = DEPOSIT_AMOUNT;
    khb_save('bookings', $bookings);
    @mail(SITE_EMAIL, 'Session booked — ' . $bk['date'] . ' ' . $bk['time'],
        $bk['name'] . ' booked ' . $bk['service_name'] . ' on ' . $bk['date'] . ' at ' . $bk['time'] .
        ". Deposit paid. Email: " . $bk['email'] . ' Phone: ' . $bk['phone'] . "\nNotes: " . $bk['notes'],
        'From: no-reply@kaoshotbeatz.com');
    @mail($bk['email'], 'Your KAOS Hot Beatz session is confirmed',
        "Thanks {$bk['name']}! Your {$bk['service_name']} is locked for {$bk['date']} at {$bk['time']}. Deposit received; balance due at the session.",
        'From: ' . SITE_EMAIL);
    echo json_encode(['ok' => true]); exit;
}
echo json_encode(['error' => 'Unknown action']);
