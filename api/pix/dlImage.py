import sys, json, os, random, string, time
# pixivpy: pixivからデータを抽出するAPI
from pixivpy3 import *
# import APIkey
import config

illusts = sys.argv[1].split(',')
GET_QUERY = dict()
for index, illust in enumerate(illusts):
    GET_QUERY[index] = illust

# Auth接続
aapi = AppPixivAPI()
aapi.auth(refresh_token = config.REFRESH_TOKEN)

# ランダム文字列の生成
def generateRandomString(strLength: int) -> str:
    strArray = [random.choice(string.ascii_letters + string.digits) for i in range(strLength)]
    return ''.join(strArray)

# 保存先のパス
SAVE_PATH = './images'
# 指定されたフォルダが存在しない場合新規作成
if not os.path.exists(SAVE_PATH):
    os.mkdir(SAVE_PATH)

time.sleep(1)
# ダウンロード処理
for illust in illusts:
    # ファイル名の設定
    file_name = generateRandomString(12) + '.jpg'
    # ダウンロード処理
    aapi.download(illust, path = SAVE_PATH, name = file_name)
    time.sleep(1)

print(json.dumps({
    'error': False,
    'content': 'download success'
}))
