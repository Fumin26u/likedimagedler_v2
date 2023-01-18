<?php
$home = '../';
require_once $home . 'commonlib.php';

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);

$userID = $_SERVER['HTTP_HOST'] === 'localhost' ? 2 : (int) h($_SESSION['user_id']);
// 保存回数と画像保存総数を更新
$rows = [];
try {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    $st = $pdo->prepare('SELECT dl_count, images_count FROM user WHERE user_id = :user_id');
    $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

} catch (PDOException $e) {
    echo 'データベース接続に失敗しました。';
    if (DEBUG) echo $e;
}
// DLした回数
$dl_count = is_null($rows['dl_count']) ? 0 : $rows['dl_count'];
// 保存した画像の総数
$images_count = is_null($rows['images_count']) ? 0 : $rows['images_count'];
// カウンタを増加
$dl_count += 1;
$images_count += (int) h($post['imageCount']);
try {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    $sql = <<<SQL
        UPDATE user SET
        dl_count = :dl_count,
        images_count = :images_count
        WHERE user_id = :user_id
    SQL;
    $st = $pdo->prepare($sql);
    $st->bindValue(':dl_count', $dl_count, PDO::PARAM_INT);
    $st->bindValue(':images_count', $images_count, PDO::PARAM_INT);
    $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
    $st->execute();

    $pdo->commit();
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました。';
    if (DEBUG) echo $e;
}

// 最新取得画像IDを更新
try {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    $st = $pdo->prepare('SELECT * FROM latest_dl WHERE user_id = :user_id AND twi_id = :twi_id');
    $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
    $st->bindValue(':twi_id', $post['twitterID'], PDO::PARAM_STR);
    $st->execute();
    $rows = $st->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

} catch (PDOException $e) {
    echo 'データベース接続に失敗しました。';
    if (DEBUG) echo $e;
}

try {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    if (count($rows) > 0) {
        $sql = <<<SQL
            UPDATE latest_dl SET
            post_id = :post_id,
            created_at = NOW()
            WHERE
            user_id = :user_id AND twi_id = :twi_id
        SQL;
    } else {
        $sql = <<<SQL
            INSERT INTO latest_dl
            (user_id, post_id, twi_id, created_at)
            VALUES
            (:user_id, :post_id, :twi_id, NOW())
        SQL;
    }
    $st = $pdo->prepare($sql);
    $st->bindValue(':user_id', $userID, PDO::PARAM_INT);
    // ツイートIDは数値だが、桁数が12以上なのでVARCHAR型で保存
    $st->bindValue(':post_id', $post['latestID'], PDO::PARAM_STR);
    $st->bindValue(':twi_id', $post['twitterID'], PDO::PARAM_STR);
    $st->execute();

    $pdo->commit();
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました。';
    if (DEBUG) echo $e;
}

echo json_encode(['content' => 'データベースを更新しました。'], JSON_UNESCAPED_UNICODE);
