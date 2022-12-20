<?php
$home = './';
require_once('./commonlib.php');

// 非ログイン時強制終了
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === '') {
    exit;
}

use \ZipArchive as ZipArchive;

// 拡張子の判別
function identifyExtension(string $file) {
    switch (substr($file, -4, 4)) {
        case '.jpg':
            return [
                'error' => false,
                'trim' => '.jpg',
                'format' => 'jpg'
            ];
        case '.png':
            return [
                'error' => false,
                'trim' => '.png',
                'format' => 'png'
            ];
        case 'jpeg':
            return [
                'error' => false,
                'trim' => '.jpeg',
                'format' => 'jpeg'
            ];
        case 'jfif':
            return [
                'error' => false,
                'trim' => '.jfif',
                'format' => 'jfif'
            ];
        default:
            return [
                'error' => true,
                'content' => 'フォーマット形式が正しくありません。'
            ];       
    }
}

// DL時のファイル名
$fileName = 'images.zip';
// Formから送られた画像URL一覧
$images = $_GET['images'];

$zip = new ZipArchive();
$zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// 画像を取り込んだZipファイルの作成
foreach ($images as $image) {
    // 拡張子を取り除き最大画素の画像URLを作成
    $extension = identifyExtension($image);
    // ファイル形式が画像以外の場合はスルー
    if ($extension['error']) continue;

    $trim = $extension['trim'];
    $format = $extension['format'];
    $imageUrl = rtrim($image, $trim) . "?format={$format}&name=orig";

    $filePath = $image;
    $curlHandle = curl_init($imageUrl);
    curl_setopt($curlHandle, CURLOPT_HEADER, 0);
    curl_setopt($curlHandle, CURLOPT_NOBODY, 0);

    // タイムアウト値
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, 480);

    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 1);

    $outputImage = curl_exec($curlHandle);
    $status = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
    
    // ファイル取得成功時、Zipファイルに画像を挿入
    if ($status === 200 && mb_strlen($outputImage) != 0) {
        $zip->addFromString(basename($filePath), $outputImage);
    }

    curl_close($curlHandle);
    sleep(1);
}
$zip->close();
// 作成したZipファイルのDL
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"".basename($fileName)."\"");
ob_end_clean();
readfile($fileName);

// Zipファイルを消去
unlink($fileName);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <title>ImageDLer</title>
    <script lang="js">
        {
            // DLが完了したらタブを閉じる
            window.close();
        }
    </script>
</head>
<body>
    <p>ダウンロード処理中です。完了までしばらくお待ちください。</p>
    <p>ダウンロード完了時自動で画面が閉じます。</p>
</body>
</html>