import sys, json
from time import sleep
from selenium import webdriver

# twitterID: '',
# getTweetType: 'liked_tweets',
# getNumberOfTweet: '100',
# isGetFromPreviousTweet: true,

# PHPから送られてきたクエリ文字列から辞書を作成
def makeDictFromQuery(query: str):
    params = query.split(',')
    query = dict()
    for param in params:
        array = param.split('=')
        query[array[0]] = array[1]
    query['isGetFromPreviousTweet'] = True if query['isGetFromPreviousTweet'] == '1' else False
    return query
GET_QUERY = makeDictFromQuery(sys.argv[1])

# 初期リンク
INIT_URL = 'https://twitter.com/' + GET_QUERY['twitterID'] + '/likes'

print(json.dumps(GET_QUERY))