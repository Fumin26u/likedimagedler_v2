<?php
$home = '../';

require_once $home . 'commonlib.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

// DBから最新取得画像IDを取得
$latestDL = '';
$userID = $_SERVER['HTTP_HOST'] === 'localhost' ? 2 : (int) h($_SESSION['user_id']);
if ($_GET['isGetFromPreviousTweet'] === 'true') {
    try {
        $pdo = dbConnect();

        // latest_dlテーブルの確認
        $st = $pdo->prepare('SELECT post_id FROM latest_dl WHERE user_id = :user_id AND twi_id = :twi_id');
        $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
        $st->bindValue(':twi_id', h($_GET['twitterID']), PDO::PARAM_STR);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);

        $pdo = null;
        // latest_dlテーブルに前回保存した画像の投稿IDがある場合、変数に挿入
        $latestDL = $row !== false ? $row['post_id'] : '';
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました。';
        if (DEBUG) echo $e;
    }
}

// フォーム内容をpythonに送る用の文字列に変換
function convertDictToString($key, $value): string {
    return h($key).'='.h($value);
}

$queryArray = [];
foreach ($_GET as $key => $param) {
    if ($param === '' || is_null($param)) continue;
    $queryArray[] = convertDictToString($key, $param);
}

// 最新取得画像IDが存在する場合クエリに追加
if ($latestDL !== '') {
    $queryArray[] = "suspendID=$latestDL";
}

// クエリを実行
$query = implode(',', $queryArray);
exec("python getTweetInfo.py $query", $output);
echo json_encode(json_decode($output[0]), JSON_UNESCAPED_UNICODE);
// echo json_encode(['content' => $query], JSON_UNESCAPED_UNICODE);
