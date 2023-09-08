<?php
$home = '../';

require_once $home . 'commonlib.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

// POSTで渡ったURL一覧から画像をダウンロード
$urls = $post['content'];
$queue = [];
for ($i = 0; $i < count($urls); $i++) {
    // &が入っているとexecが正しく実行されないようなので仮文字列に変換
    $replaceUrl = str_replace('&', '0AND0', $urls[$i]);
    $queue[] = $replaceUrl;
    // 20枚ごとにurlを分割してdlを行う(リンク数が多いと正常にDLできない為)
    if ($i % 20 === 0 || $i === count($urls) - 1) {
        $query = h(implode(',', $queue));
        exec("python dlImage.py $query", $output);
        $result = json_decode($output[0]);
        if ($result->error) break;

        $queue = [];
    }
}

$response = [
    'isSuccessDownload' => true,
    'content' => ''
];
// 正常にDL出来なかった場合エラーを返却して終了
if ($result->error) {
    $response['isSuccessDownload'] = false;
    $response['content'] = '画像のダウンロードに失敗しました。';
    echo json_encode($response, JSON_THROW_ON_ERROR);
    exit;
}

// DLが完了した場合zip変換
$zipFileName = 'images.zip';
exec("zip -r $zipFileName ./images");

if (file_exists($zipFileName)) {
    $response['content'] = 'zipファイルを作成しました。';
} else {
    $response['isSuccessDownload'] = false;
    $response['content'] = '指定されたzipファイルは存在しません。';
}

echo json_encode($response, JSON_THROW_ON_ERROR);
// echo json_encode(['content' => $result], JSON_THROW_ON_ERROR);