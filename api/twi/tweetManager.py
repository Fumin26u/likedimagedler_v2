import json
from flask import Flask, request, jsonify

with open('../userdata.json', 'r') as file:
    data = json.load(file)
    
twitterInfo = data["twitter"]

app = Flask(__name__)
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
        

