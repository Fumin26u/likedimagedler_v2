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

$connection = new TwitterOAuth(API_KEY, API_KEY_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

// Set APIVersion to 2
$connection->setApiVersion('2');

// スクリーンネームを数値IDに変換
$endPoint = 'https://api.twitter.com/2/users/by/username/';
$requestUrl = $endPoint . h($_GET['twitterID']);

echo curl_exec(setCurl($requestUrl));