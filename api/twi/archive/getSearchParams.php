<?php
$home = '../';
require_once $home . 'commonlib.php';

echo json_encode(
    ['uri' => substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?'))], JSON_UNESCAPED_UNICODE
);
