<?php
session_start();
session_destroy();
session_start();

require __DIR__ . '/game.php';

echo json_encode(['status' => 'reset']);