<?php
require '../vendor/autoload.php';
require_once('./apiKey.php');
use Abraham\TwitterOAuth\TwitterOAuth;

$connection = new TwitterOAuth(API_KEY, API_KEY_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

// Set APIVersion to 2
$connection->setApiVersion('2');
$endPoint = 'https://api.twitter.com/2/users/by/username/';