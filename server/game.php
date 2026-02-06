<?php
function createGrid() {
    return array_fill(0, 10, array_fill(0, 10, 0));
}

function placeShip(&$grid, $size, &$ships) {
    while (true) {
        $horizontal = rand(0, 1);
        if ($horizontal) {
            $row = rand(0, 9);
            $col = rand(0, 10 - $size);

            $coords = [];
            $valid = true;

            for ($i = 0; $i < $size; $i++) {
                if ($grid[$row][$col + $i] === 1) {
                    $valid = false;
                }
                $coords[] = [$row, $col + $i];
            }

            if ($valid) {
                foreach ($coords as [$r,$c]) {
                    $grid[$r][$c] = 1;
                }
                $ships[] = $coords;
                return;
            }
        } else {
            $row = rand(0, 10 - $size);
            $col = rand(0, 9);

            $coords = [];
            $valid = true;

            for ($i = 0; $i < $size; $i++) {
                if ($grid[$row + $i][$col] === 1) {
                    $valid = false;
                }
                $coords[] = [$row + $i, $col];
            }

            if ($valid) {
                foreach ($coords as [$r,$c]) {
                    $grid[$r][$c] = 1;
                }
                $ships[] = $coords;
                return;
            }
        }
    }
}

function startGame() {
    $grid = createGrid();

    $_SESSION['maxTurns'] = 40;
    $_SESSION['turns'] = 0;

    $ships = [];

    placeShip($grid, 2, $ships);
    placeShip($grid, 3, $ships);
    placeShip($grid, 5, $ships);

    $_SESSION['ships'] = $ships;
    $_SESSION['sunkShips'] = [];

    $_SESSION['grid'] = $grid;
    $_SESSION['hits'] = [];
    $_SESSION['misses'] = [];
}

if (!isset($_SESSION['grid']) || !isset($_SESSION['hits']) || !isset($_SESSION['misses'])) {
    startGame('normal');
}