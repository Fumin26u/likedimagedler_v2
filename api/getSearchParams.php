<?php
$home = './';
require_once('./commonlib.php');

echo json_encode(
    ['uri' => substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?'))], JSON_UNESCAPED_UNICODE
);