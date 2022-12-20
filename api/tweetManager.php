<?php
$home = './';

require_once './commonlib.php';
require_once '../vendor/autoload.php';
require_once './apiKey.php';
use Abraham\TwitterOAuth\TwitterOAuth;

function setCurl($req) {
    $bearer_token = BEARER_TOKEN;

    // リクエストヘッダの作成
    $header = [
        'Authorization: Bearer ' . $bearer_token,
        'Content-Type: application/json',
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $req);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    return $curl;
}

// 前回DLした画像以降を取得がtrueの場合DBから該当IDを取得
$latestDL = '';
$userID = $_SERVER['HTTP_HOST'] === 'localhost' ? 2 : (int) h($_SESSION['user_id']);
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


$connection = new TwitterOAuth(API_KEY, API_KEY_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

// Set APIVersion to 2
$connection->setApiVersion('2');

// スクリーンネームを数値IDに変換
$endPoint = 'https://api.twitter.com/2/users/by/username/';
$requestUrl = $endPoint . h($_GET['twitterID']);
// TwitterAPIを叩いてユーザーの数値IDを取得
$response = json_decode(curl_exec(setCurl($requestUrl)));
$twitterUserID = $response->data->id;

// 取得するツイートの種類
$tweetType = h($_GET['getTweetType']);
// 画像ツイート一覧を取得
$endPoint = "https://api.twitter.com/2/users/{$twitterUserID}/{$tweetType}";
// 取得ツイート数
$getTweetCount = (int) h($_GET['getNumberOfTweet']);
// 1度に取得するツイート数
$getTweetCountOnce = 10;
// 取得したツイート数
$tweetCounter = 0;
// 次ページのToken
$paginationToken = '';
// ツイート情報一覧(返す値)
$tweetList = [];

while ($getTweetCount > 0) {
    $getTweetCount -= $getTweetCountOnce;
    
    // TwitterAPIに送るパラメータクエリ
    $query = [
        'max_results' => $getTweetCountOnce,
        'expansions' => 'attachments.media_keys,author_id',
        'tweet.fields' => 'created_at',
        'media.fields' => 'url,preview_image_url',
        'user.fields' => 'username',
    ];

    // ツイート一覧取得時終了日時が指定されていた場合クエリに設定する
    if ($tweetType === 'tweets' && isset($_GET['endTime'])) {
        $query['end_time'] = h($_GET['endTime']);
    }

    // ペジネーショントークンが存在する場合クエリに設定
    if ($paginationToken !== '') $query['pagination_token'] = $paginationToken;

    $requestUrl = $endPoint . '?' . http_build_query($query);

    // TwitterAPIを叩き、ツイート情報を取得
    $response = curl_exec(setCurl($requestUrl));
    $tweetListQueue = json_decode($response, true);

    // ユーザ一覧を[ユーザーID] => [ユーザー名]の連想配列に変更
    $tweetUsers = [];
    foreach ($tweetListQueue['includes']['users'] as $user) {
        $tweetUsers[$user['id']] = $user['name'];
    }

    // 画像を[メディアキー] => [URL]の連想配列に変更
    // 動画の場合はサムネ(preview_image_url)
    $tweetMedias = [];
    foreach($tweetListQueue['includes']['media'] as $media) {
        if (isset($media['url']) || isset($media['preview_image_url'])) {
            $tweetMedias[$media['media_key']] = 
                $media['type'] === 'photo' ? 
                $media['url'] : $media['preview_image_url'];
        }
    } 

    // ツイート情報を整形して一覧に挿入
    foreach ($tweetListQueue['data'] as $tweet) {
        // 取得したツイートのIDが前回保存したツイートIDだった場合、キューへの挿入を終了
        if ($latestDL !== '' && $tweet['id'] === $latestDL) break 2;

        // 画像ツイートでない場合キューに挿入しない
        if (!isset($tweet['attachments']['media_keys'])) continue;

        $tweetList[$tweetCounter]['postID'] = $tweet['id'];
        $tweetList[$tweetCounter]['post_time'] = DateTime::createFromFormat('Y-m-d H:i:s', $tweet['created_at']);
        $tweetList[$tweetCounter]['user'] = $tweetUsers[$tweet['author_id']];
        $tweetList[$tweetCounter]['text'] = substr($tweet['text'], 0, -24);
        $tweetList[$tweetCounter]['images'] = [];
        $tweetList[$tweetCounter]['url'] = substr($tweet['text'], -24);

        // データのメディアキーから画像を挿入
        if (isset($tweet['attachments'])) {
            foreach ($tweet['attachments']['media_keys'] as $mediaKey) {
                $tweetList[$tweetCounter]['images'][] = $tweetMedias[$mediaKey];
            }
        }

        $tweetCounter++;
    }

    $paginationToken = $tweetListQueue['meta']['next_token'];
}

echo json_encode([
    'tweetInfo' => $tweetList,
], JSON_UNESCAPED_UNICODE);