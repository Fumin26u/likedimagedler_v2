import sys, json, os, random, string, time
import requests

def generateRandomString(strLength: int) -> str:
    strArray = [random.choice(string.ascii_letters + string.digits) for i in range(strLength)]
    return ''.join(strArray)

# format引数が付いている画像URLから、どの形式でフォーマットされているかを取得
def getFormatMethod(url):
    target = 'format='
    index = url.find(target)
    if (index != -1) and index + len(target) + 3 <= len(url):
        formatMethod = url[index + len(target):index + len(target) + 3]
        if formatMethod == 'jpg':
            return 'jpg'
        elif formatMethod == 'jpe':
            return 'jpeg'
        elif formatMethod == 'jfi':
            return 'jfif'
        elif formatMethod == 'web':
            return 'webp'
        elif formatMethod == 'png':
            return 'png'
        else:
            return 'jpg'
    else:
        return False

# 保存先のパス
SAVE_PATH = './images'
# 指定されたフォルダが存在しない場合新規作成
if not os.path.exists(SAVE_PATH):
    os.mkdir(SAVE_PATH)

illustUrls = sys.argv[1].split(',')
for illustUrl in illustUrls:
    time.sleep(1)
    # &が0AND0に変換されているので、元に戻す
    undoUrl = illustUrl.replace('0AND0', '&')
    response = requests.get(undoUrl, stream=True)
    if response.status_code == 200:
        file_name = generateRandomString(12) + '.' + getFormatMethod(undoUrl)
        with open('./images/' + file_name, 'wb') as file:
            for chunk in response.iter_content(chunk_size=8192):
                file.write(chunk)
    else:
        print(json.dumps({
            'error': True,
            'content': '画像の保存に失敗しました。'
        }))
        sys.exit()

print(json.dumps({
    'error': False,
    'content': sys.argv
}))