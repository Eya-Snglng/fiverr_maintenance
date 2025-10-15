<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

function json_error($message, $status = 400) {
    http_response_code($status);
    echo json_encode([ 'success' => false, 'error' => $message ]);
    exit;
}

if (!is_array($payload)) {
    json_error('Invalid JSON payload.');
}

$item = $payload['item'] ?? null;
$cash = $payload['cash'] ?? null;
$quantity = $payload['quantity'] ?? null;

if ($item === null || $cash === null || $quantity === null || $item === '' || $cash === '' || $quantity === '') {
    json_error('One or more fields are empty. Provide item, cash, and quantity.');
}

$items = [
    'peewee-sizzling-bbq' => ['name' => 'PeeWee Sizzling BBQ', 'price' => 1.50],
    'ri-chee-crunchy-snack' => ['name' => 'Ri-Chee Crunchy Snack', 'price' => 0.95],
    'viva-caramel-candy' => ['name' => 'Viva Caramel Candy', 'price' => 0.60],
];

if (!isset($items[$item])) {
    json_error('Unknown item.');
}

if (!is_numeric($cash) || !is_numeric($quantity)) {
    json_error('Cash and quantity must be numeric values.');
}

$cash = (float)$cash;
$quantity = (int)$quantity;

if ($quantity <= 0) {
    json_error('Quantity must be at least 1.');
}

$price = (float)$items[$item]['price'];
$total = $price * $quantity;

if ($cash < $total) {
    echo json_encode([
        'success' => false,
        'error' => 'Insufficient cash.',
        'total' => round($total, 2)
    ]);
    exit;
}

$change = $cash - $total;

echo json_encode([
    'success' => true,
    'item' => $item,
    'quantity' => $quantity,
    'price' => round($price, 2),
    'total' => round($total, 2),
    'cash' => round($cash, 2),
    'change' => round($change, 2)
]);
