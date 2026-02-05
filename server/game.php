<?php
function createGrid() {
    return array_fill(0, 10, array_fill(0, 10, 0));
}

function placeShip(&$grid, $size) {
    while (true) {
        $horizontal = rand(0, 1);

        if ($horizontal) {
            $row = rand(0, 9);
            $col = rand(0, 10 - $size);

            $valid = true;
            for ($i = 0; $i < $size; $i++) {
                if ($grid[$row][$col + $i] === 1) {
                    $valid = false;
                }
            }

            if ($valid) {
                for ($i = 0; $i < $size; $i++) {
                    $grid[$row][$col + $i] = 1;
                }
                return;
            }
        } else {
            $row = rand(0, 10 - $size);
            $col = rand(0, 9);

            $valid = true;
            for ($i = 0; $i < $size; $i++) {
                if ($grid[$row + $i][$col] === 1) {
                    $valid = false;
                }
            }

            if ($valid) {
                for ($i = 0; $i < $size; $i++) {
                    $grid[$row + $i][$col] = 1;
                }
                return;
            }
        }
    }
}

function startGame() {
    $grid = createGrid();

    $_SESSION['maxTurns'] = 25;
    $_SESSION['turns'] = 0;

    placeShip($grid, 2);
    placeShip($grid, 3);
    placeShip($grid, 5);

    $_SESSION['grid'] = $grid;
    $_SESSION['hits'] = [];
    $_SESSION['misses'] = [];
}

if (!isset($_SESSION['grid']) || !isset($_SESSION['hits']) || !isset($_SESSION['misses'])) {
    startGame();
}