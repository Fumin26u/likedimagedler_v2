import json
from flask import Flask, request, jsonify

# import userdata.json
with open('../userdata.json', 'r') as file:
    data = json.load(file)
    
twitterInfo = data["twitter"]

# FlaskでVueから送られたクエリを受け取る
app = Flask(__name__)
get = {}
@app.route('/api/twi/tweetManager', methods=['GET'])
def getTweetManager():
    try:
        get = request.get_json()
        
        # 最新のダウンロード画像のポストIDを取得
        latestDl = ''
        if (get['isGetFromPreviousTweet'] == 'true'):
            latestDl = data['post']
    except Exception as e:
        return jsonify({'error': str(e)})   

# クエリを基にスクレイピング
from getTweetInfo import twitterLogin, getTweet
import twitterPassword

twitterLogin(get['twitterID'], twitterPassword.USER_PASSWORD)
tweetInfo = getTweet(get)

# スクレイピングで取得したツイート情報を返却
        

