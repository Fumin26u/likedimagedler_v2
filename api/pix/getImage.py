import sys, json
from time import sleep
# pixivpy: pixivからデータを抽出するAPI
from pixivpy3 import *
# import APIkey
import config
# import get_tag_list
import tagList

# Auth接続
aapi = AppPixivAPI()
aapi.auth(refresh_token = config.REFRESH_TOKEN)

# 画像の取得先設定(ブックマークor作品)
def getImagesInfo(id: int, postType: str):
    if postType == "bookmark":
        return aapi.user_bookmarks_illust(id, 'public')
    elif postType == "post":
        return aapi.user_illusts(id)
    else:
        return False

# 投稿日付のフォーマット
def formatPostDate(date: str) -> str:
    return str(date).split('+')[0].replace('T', ' ')

# PHPから送られてきたクエリ文字列から辞書を作成
params = sys.argv[1].split(',')
GET_QUERY = dict()
for param in params:
    array = param.split('=')
    GET_QUERY[array[0]] = array[1]
GET_QUERY['isGetFromPreviousPost'] = True if GET_QUERY['isGetFromPreviousPost'] == '1' else False
GET_QUERY['includeTags'] = True if GET_QUERY['includeTags'] == '1' else False
# print(json.dumps(GET_QUERY))
# sys.exit()

imagesInfo = getImagesInfo(int(GET_QUERY['userID']), GET_QUERY['getPostType'])
if imagesInfo == False:
    print('画像URLの取得に失敗しました。')
    sys.exit()
# print(json.dumps(imagesInfo))

# 画像のリンクを保管する配列
illusts = []
# 残りDL回数
remaining = int(GET_QUERY['getNumberOfPost'])

# 画像URL一覧を作成
# URL取得を継続するかどうかのフラグ
isContinueRefers = True
while isContinueRefers:
    for imageCounter, imageInfo in enumerate(imagesInfo['illusts']):
        # 残りDL数のデクリメント
        remaining -= 1

        # ページのブックマーク数が30以下の場合現在のループで取得を終了
        if len(imagesInfo['illusts']) < 30:
            isContinueRefers = False

        # 次のブックマーク列の作成
        if imageCounter == 29:
            nextUrl = imagesInfo['next_url']
            nextQs = aapi.parse_qs(nextUrl)
            sleep(1)
            if GET_QUERY['getPostType'] == "bookmark":
                imagesInfo = aapi.user_bookmarks_illust(**nextQs)
            elif GET_QUERY['getPostType'] == "post":
                imagesInfo = aapi.user_illusts(**nextQs)
            else:
                isContinueRefers = False
                break
            sleep(1)


        # 取得した画像が指定されたIDの場合ループを終了
        if (
            'suspendID' in GET_QUERY and
            str(imageInfo['id']) == GET_QUERY['suspendID'] and
            GET_QUERY['isGetFromPreviousPost']
        ):
            isContinueRefers = False
            break

        # -------------------- ここからvue/PHP未実装

        # # 投稿日時のフォーマット
        # createdAt = formatPostDate(imageInfo['create_date'])
        # # 期間指定時の取得中断処理
        # # notice: isUsingTerm, startTime, endTimeは未実装
        # # 取得画像の投稿日付が開始日以前の場合
        # if GET_QUERY.isUsingTerm and createdAt < GET_QUERY.startTime:
        #     if GET_QUERY.suspendStartTime:
        #         isContinueRefers = False
        #         break
        #     else:
        #         continue
        # # 取得画像の投稿日付が終了日以後の場合
        # if GET_QUERY.isUsingTerm and createdAt > GET_QUERY.endTime:
        #     if GET_QUERY.suspendEndTime:
        #         isContinueRefers = False
        #         break
        #     else:
        #         continue

        # 作品タグの有無判定
        # tagListはvue未実装、代用で当ファイル頭に設定
        if GET_QUERY['includeTags']:
            illustTagList = []
            for tag in imageInfo['tags']:
                illustTagList.append(tag['name'])

            # 必要情報設定
            # 取得作品のタグとユーザー設定値を比較し一致するタグが無い場合は取得しない
            if len(list(set(tagList.TAG_LIST) & set(illustTagList))) == 0 and tagList.TAG_LIST != []:
                continue
            # 取得作品のタグとユーザー設定値を比較し一致するタグが存在したら取得しない
            if len(list(set(tagList.EX_TAG_LIST) & set(illustTagList))) > 0:
                continue

        # -------------------- ここまでvue/PHP未実装

        # 残りDL数カウンタが0になった場合ループ終了
        if remaining < 0:
            isContinueRefers = False
            break

        # 上記バリデーションを全て通過した場合画像情報を作成して配列に追加
        illustsInfoQueue = dict()
        illustsInfoQueue['postID'] = imageInfo['id']
        illustsInfoQueue['post_time'] = imageInfo['create_date']
        illustsInfoQueue['user'] = imageInfo['user']['name']
        illustsInfoQueue['text'] = imageInfo['title']
        illustsInfoQueue['url'] = 'https://www.pixiv.net/artworks/' + str(imageInfo['id'])
        illustsInfoQueue['images'] = []
        # 画像URLの挿入
        # 画像が1枚の場合
        if len(imageInfo['meta_pages']) == 0:
            illustsInfoQueue['images'].append(imageInfo['meta_single_page']['original_image_url'])
        # 画像が複数枚の場合
        else:
            for metaPage in imageInfo['meta_pages']:
                illustsInfoQueue['images'].append(metaPage['image_urls']['original'])

        illusts.append(illustsInfoQueue)

print(json.dumps(illusts))
