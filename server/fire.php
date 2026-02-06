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
$hits = $_SESSION['hits'] ?? [];
$misses = $_SESSION['misses'] ?? [];
$_SESSION['score'] = $_SESSION['score'] ?? 0;

$key = "$row,$col";

if (in_array($key, $hits) || in_array($key, $misses)) {
    // Return full state so client can safely update UI without missing fields
    echo json_encode([
        'status' => 'duplicate',
        'turns' => $_SESSION['turns'] ?? 0,
        'maxTurns' => $_SESSION['maxTurns'] ?? 0,
        'hits' => count($hits),
        'misses' => count($misses),
        'score' => $_SESSION['score'] ?? 0,
        'gameOver' => false,
        'win' => false,
        'lose' => false,
        'sunk' => null,
        'remainingMoves' => max(0, ($_SESSION['maxTurns'] ?? 0) - ($_SESSION['turns'] ?? 0))
    ]);
    exit;
}

$_SESSION['turns'] = ($_SESSION['turns'] ?? 0) + 1;

if ($grid[$row][$col] === 1) {
    $hits[] = $key;
    $result = 'hit';
    $_SESSION['score'] += 10; // reward for a hit
} else {
    $misses[] = $key;
    $result = 'miss';
    $_SESSION['score'] -= 1; // penalty for a miss
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

$sunkShip = null;

foreach ($_SESSION['ships'] as $index => $ship) {
    if (in_array($index, $_SESSION['sunkShips'])) {
        continue;
    }
    $allHit = true;

    foreach ($ship as [$r,$c]) {
        $cell = $r . "," . $c;
        if (!in_array($cell, $_SESSION['hits'])) {
            $allHit = false;
            break;
        }
    }

    if ($allHit) {
        $_SESSION['sunkShips'][] = $index;
        $sunkShip = count($ship);
        break;
    }
}

$win = count($hits) >= $shipCells;
$lose = $_SESSION['turns'] >= $_SESSION['maxTurns'] && !$win;
$gameOver = $win || $lose;

// If the game just ended, add remaining moves as bonus once
if ($gameOver && empty($_SESSION['finalized'])) {
    $remaining = max(0, ($_SESSION['maxTurns'] ?? 0) - $_SESSION['turns']);
    // add 2 points per remaining move as a completion bonus
    $_SESSION['score'] += $remaining * 2;
    $_SESSION['finalized'] = true;
}

echo json_encode([
    'status' => $result,
    'gameOver' => $gameOver,
    'win' => $win,
    'lose' => $lose,
    'turns' => $_SESSION['turns'],
    'maxTurns' => $_SESSION['maxTurns'],
    'hits' => count($hits),
    'misses' => count($misses),
    'sunk' => $sunkShip,
    'score' => $_SESSION['score'],
    'remainingMoves' => isset($remaining) ? $remaining : max(0, ($_SESSION['maxTurns'] ?? 0) - $_SESSION['turns'])
]);