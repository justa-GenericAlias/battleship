<?php
session_start();
require __DIR__ . '/game.php';

header('Content-Type: application/json');

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data || !isset($data['cell'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$cell = $data['cell'];

if (!preg_match('/^[A-J](10|[1-9])$/', $cell)) {
    echo json_encode(['error' => 'Invalid cell']);
    exit;
}

$row = ord($cell[0]) - 65;
$col = intval(substr($cell, 1)) - 1;

$grid = $_SESSION['grid'];
$hits = $_SESSION['hits'];
$misses = $_SESSION['misses'];

$key = "$row,$col";

if (in_array($key, $hits) || in_array($key, $misses)) {
    echo json_encode(['status' => 'duplicate']);
    exit;
}

$_SESSION['turns'] = ($_SESSION['turns'] ?? 0) + 1;

if ($grid[$row][$col] === 1) {
    $hits[] = $key;
    $result = 'hit';
} else {
    $misses[] = $key;
    $result = 'miss';
}

$_SESSION['hits'] = $hits;
$_SESSION['misses'] = $misses;

$shipCells = 0;
foreach ($grid as $r) {
    foreach ($r as $c) {
        if ($c === 1) $shipCells++;
    }
}

if (!isset($_SESSION['ships'])) {
    $_SESSION['ships'] = []; // fallback, should normally be set by startGame()
}

if (!isset($_SESSION['sunkShips'])) {
    $_SESSION['sunkShips'] = [];
}

$sunkShipMessage = null;
if (!isset($_SESSION['sunkShips'])) {
    $_SESSION['sunkShips'] = [];
}

foreach ($_SESSION['ships'] as $ship) {
    if (count(array_diff($ship, $_SESSION['hits'])) === 0 &&
        !in_array($ship, $_SESSION['sunkShips'], true)) {
        $sunkShipMessage = "You sunk a ship of size " . count($ship) . "!";
        $_SESSION['sunkShips'][] = $ship;
    }
}

$win = count($hits) >= $shipCells;
$lose = $_SESSION['turns'] >= $_SESSION['maxTurns'] && !$win;
$gameOver = $win || $lose;

echo json_encode([
    'status' => $result,
    'gameOver' => $gameOver,
    'win' => $win,
    'lose' => $lose,
    'turns' => $_SESSION['turns'],
    'maxTurns' => $_SESSION['maxTurns'],
    'hits' => count($hits),
    'misses' => count($misses),
    'sunkShipMessage' => $sunkShipMessage
]);