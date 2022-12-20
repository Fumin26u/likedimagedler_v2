<?php
$home = './';
require_once('./commonlib.php');

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);

require_once('./controllers/AccountController.php');
$accountController = new AccountController();

$postMethods = ['logout', 'login', 'register', 'getUserData'];
// 指定された方式以外が送られてきた場合はエラーを流し強制終了
if (!isset($post['method']) || !in_array($post['method'], $postMethods)) {
    echo json_encode($accountController->sendErrorLog(), JSON_UNESCAPED_UNICODE);
    exit;
}

$response = [];
// POSTで送られてきた方式によって処理を分岐
switch ($post['method']) {
    case 'logout':
        $response = $accountController->logout();
        break;
    case 'login':
        $response = $accountController->login($post);
        break;
    case 'register':
        $response = $accountController->register($post);
        break;
    case 'getUserData':
        $response = $accountController->getUserData();
        break;
    default:
        $response = $accountController->sendErrorLog();
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);