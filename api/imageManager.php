<?php
$home = './';
require_once('./commonlib.php');
use \ZipArchive;

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);

// 拡張子の判別
function identifyExtension(string $file) {
    switch (substr($file, -4, 4)) {
        case '.jpg':
            return [
                'trim' => '.jpg',
                'format' => 'jpg'
            ];
        case '.png':
            return [
                'trim' => '.png',
                'format' => 'png'
            ];
        case 'jpeg':
            return [
                'trim' => '.jpeg',
                'format' => 'jpeg'
            ];
        case 'jfif':
            return [
                'trim' => '.jfif',
                'format' => 'jfif'
            ];
        default:
            return 'フォーマット形式が正しくありません。';      
    }
}

// DL時のファイル名
$fileName = 'images.zip';
// Formから送られた画像URL一覧
$images = $post['images'];

$zip = new ZipArchive();
$st = $zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// 画像を取り込んだZipファイルの作成
foreach ($images as $image) {
    // 拡張子を取り除き最大画素の画像URLを作成
    list($trim, $format) = identifyExtension($image);
    $imageUrl = rtrim($image, $trim) . "?format={$format}.&name=orig";

    $filePath = $image;
    $curlHandle = curl_init($imageUrl);
    curl_setopt($curlHandle, CURLOPT_HEADER, 0);
    curl_setopt($curlHandle, CURLOPT_NOBODY, 0);

    // タイムアウト値
    curl_setopt($curlHandle, CURLOPT_TIMEOUT, 4800);

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

echo json_encode($post, JSON_UNESCAPED_UNICODE);