<?php
$home = '../';

require_once $home . 'commonlib.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

function removeImage(string $filePath) {
    // ファイルが存在しない場合
    if (!file_exists($filePath)) return ['isSuccessRemove' => true, 'content' => 'ファイルを削除しました。'];
    
    // ファイルが存在する場合
    if (is_file($filePath)) {
        unlink($filePath);
        return ['isSuccessRemove' => true, 'content' => 'ファイルを削除しました。'];
    }
    
    // ディレクトリの場合
    if ($handle = opendir($filePath)) {
        while (($file = readdir($handle)) !== false) {
            // ディレクトリの場合無操作
            if ($file === '.' || $file === '..') continue;
            // ファイルの場合再起呼び出しをして削除
            removeImage($filePath . $file);
        }
        // ディレクトリを閉じて削除
        closedir($handle);
        rmdir($filePath);
    }
    
    return file_exists($filePath)
        ? ['isSuccessRemove' => false, 'content' => 'ファイルの削除に失敗しました。']
        : ['isSuccessRemove' => true, 'content' => 'ファイルを削除しました。'];
}

$filePath = './images/';
$response = removeImage($filePath);
exec("del .\images.zip");
echo json_encode($response, JSON_THROW_ON_ERROR);