import sys, json
from time import sleep
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
from selenium.webdriver.common.action_chains import ActionChains

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

# ツイート情報の取得
def getTweet(query, header, interval):
    # 待機時間
    INTERVAL = interval
    # 初期リンク
    initUrl = 'https://twitter.com/' + query['twitterID']
    if query['getTweetType'] == 'liked_tweets':
        initUrl += 'likes'

    # ドライバの設定
    options = Options()
    options.add_argument('--headless')
    options.add_argument('--disable-gpu')

    driver = webdriver.Chrome('./chromedriver', options=options)
    driver.get(initUrl)
    WebDriverWait(driver, 10).until(EC.visibility_of_element_located((By.TAG_NAME, 'article')))
    # 画像に直飛びできるリンクを踏む。
    # 以下のclassをすべて持つaタグを取得しクリック
    # css-4rbku5 css-18t94o4 css-1dbjc4n r-1loqt21 r-1pi2tsx r-1ny4l3l

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.5615.139 Safari/537.36'
}

print(json.dumps(GET_QUERY))