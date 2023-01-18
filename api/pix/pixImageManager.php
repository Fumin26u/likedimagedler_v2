<?php
$home = '../';
require_once $home . 'commonlib.php';
require_once './controllers/ImageController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

$imageController = new ImageController();
$response = [];
switch ($post['method']) {
    case 'get':
        $response = $imageController->get($post['content']);
        break;
    case 'download':
        $response = $imageController->download($post['content'], 'images.zip', './images');
        break;
    case 'remove':
        $response = $imageController->remove('./images/');
        // access deniedへの暫定処置
        exec("del .\images.zip");
        break;
    case 'updateInfo':
        $response = $imageController->updateInfo($post['content']);
        break;
    default:
        $response = [
            'error' => true,
            'content' => 'パラメータが不正です。'
        ];
        break;
}
echo json_encode($response, JSON_THROW_ON_ERROR);
