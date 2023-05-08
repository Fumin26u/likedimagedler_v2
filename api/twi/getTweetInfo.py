import sys, json, pprint
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

    # 必要なツイート情報を取得
    # 同様にツイートIDを取得するために以下のclassをすべて持つaタグのhref属性を取得
    # css-4rbku5 css-18t94o4 css-1dbjc4n r-1loqt21 r-1pi2tsx r-1ny4l3l

    # ツイート取得数上限か、前回保存した画像のIDに達するまで、次の記事にスクロールしてツイートを読み込む
    for i in range(query['getNumberOfTweet']):
        scrollAndSuspendGetTweet(driver, i, str(query['suspendID']))
        sleep(INTERVAL)

    tweetInfo = []
    # 読み込んだツイートからツイート情報を取得する
    for article in driver.find_elements_by_tag_name('article'):
        tweetInfo.append(getEachTweet(article))

    # ドライバを終了
    driver.quit()

# ドライバのスクロール処理
def scrollAndSuspendGetTweet(driver, nextArticle: int, suspendId: str):
    # 毎回大量のarticleを読み込むことになりそうなので新しく出現したarticleのみ読み込むようにしたい
    articles = driver.find_elements_by_tag_name('article')

    # 現在読み込まれている最後のツイートのIDを取得し、既に取得済みならスクロールを終了
    lastArticleId = getTweetId(articles[-1])
    if str(lastArticleId) == suspendId: return

    # 指定した場所にスクロール
    scrollTo = articles[nextArticle]
    actions = ActionChains(driver)
    actions.move_to_element(scrollTo)
    actions.perform()

# 対象のツイートが取得済みかどうか判定
def getTweetId(target):
    url = target.find_element_by_css_selector('.css-4rbku5.css-18t94o4.css-1dbjc4n.r-1loqt21.r-1pi2tsx.r-1ny4l3l').get_attribute('href')
    # urlをスラッシュ毎に分割し、3番目の要素がIDなのでそれを取得し返却
    return url.split('/')[3]

# 個々のツイート情報を取得
def getEachTweet(article):
    tweetInfo = dict()
    # ユーザー名の取得

    # ツイート内容を取得

    # 画像のdivタグ内のimgタグのsrc属性の取得
    # memo 複数枚対応する
    images = article.find_element_by_css_selector('.css-1dbjc4n.r-1p0dtai.r-1mlwlqe.r-1d2f490.r-11wrixw.r-61z16t.r-1udh08x.r-u8s1d.r-zchlnj.r-ipm5af.r-417010').find_elements_by_tag_name('img')
    for image in images:
        tweetInfo['images'] = image.get_attribute('src')

    # ツイート元のURLを取得
    tweetInfo['url'] = 'https://twitter.com' + article.find_element_by_css_selector('.css-4rbku5.css-18t94o4.css-1dbjc4n.r-1loqt21.r-1pi2tsx.r-1ny4l3l').get_attribute('href')
    return tweetInfo
    

headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.5615.139 Safari/537.36'
}

print(json.dumps(GET_QUERY))