<script setup lang="ts">
import '@/assets/scss/twiForm.scss'
import ApiManager from '@/components/api/apiManager'
import {
    TwiSearch,
    TweetInfo,
    TweetImage,
} from '@/assets/interfaces/interfaces'
import { ref } from 'vue'

// 入力フォームの値
const search = ref<TwiSearch>({
    twitterID: '',
    getTweetType: 'liked_tweets',
    getNumberOfTweet: '10',
    isGetFromPreviousTweet: true,
})

const errorMessage = ref<string>('')
// 入力フォームのバリデーション
const inputValidation = (): string => {
    let error = ''
    if (search.value.twitterID === '') {
        error = 'Twitter IDが入力されていません。'
    }

    const numTweet = parseInt(search.value.getNumberOfTweet)
    if (isNaN(numTweet)) {
        error = '取得ツイート数は数値で入力してください。'
    }
    if (numTweet < 10 || 300 < numTweet) {
        error = '取得できるツイートの最小値は10, 最大値は300です。'
    }
    return error
}

// APIから画像付きツイートを取得
const tweetInfo = ref<TweetInfo[]>([])
const apiManager = new ApiManager()
const getTweet = async () => {
    // 入力フォームのバリデーションを行いエラーがある場合は中断
    errorMessage.value = inputValidation()
    if (errorMessage.value !== '') return

    const response = await apiManager.get('tweetManager.php', search.value)
    // それぞれの画像にDL可否判定の値を追加
    tweetInfo.value = response.content.tweetInfo.map((tweet: TweetInfo) => {
        return {
            postID: tweet.postID,
            post_time: tweet.post_time,
            user: tweet.user,
            text: tweet.text,
            url: tweet.url,
            images: tweet.images.map((image: TweetImage, index: number) => {
                return {
                    id: `${tweet.postID}_${index}`,
                    url: image,
                    selected: true,
                }
            }),
        }
    })
    console.log(tweetInfo.value)
}
</script>
<template>
    <section class="search-form">
        <div class="title-area">
            <h2>検索フォーム</h2>
        </div>
        <dl class="search-box">
            <div>
                <dt>
                    Twitter ID
                    <em>*</em>
                </dt>
                <dd><input type="text" v-model="search.twitterID" /></dd>
            </div>
            <div>
                <dt>
                    取得内容
                    <em>*</em>
                </dt>
                <dd class="radio-list">
                    <div>
                        <input
                            type="radio"
                            id="get-like"
                            value="liked_tweets"
                            v-model="search.getTweetType"
                        />
                        <label for="get-like">いいね</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            id="get-tweet"
                            value="tweets"
                            v-model="search.getTweetType"
                        />
                        <label for="get-tweet">ツイート</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            id="get-bookmark"
                            value="bookmarks"
                            v-model="search.getTweetType"
                            disabled
                        />
                        <label for="get-bookmark">ブックマーク</label>
                    </div>
                </dd>
            </div>
            <div>
                <dt>
                    取得ツイート数
                    <em>*</em>
                    <br />
                    (最大300)
                </dt>
                <dd>
                    <input
                        type="number"
                        v-model="search.getNumberOfTweet"
                        min="10"
                        max="300"
                        step="10"
                    />
                </dd>
            </div>
            <div>
                <dt>詳細設定</dt>
                <dd>
                    <input
                        type="checkbox"
                        id="get-pre"
                        v-model="search.isGetFromPreviousTweet"
                    />
                    <label for="get-pre">前回DLした画像以降を取得</label>
                </dd>
            </div>
            <button @click="getTweet()" class="btn-common green">
                画像を取得
            </button>
        </dl>
    </section>
    <p>{{ errorMessage }}</p>
    <section class="tweet-list" v-if="tweetInfo.length > 0">
        <div class="title-area">
            <h2>取得ツイート一覧</h2>
            <p v-if="tweetInfo.length > 0" class="caption">
                取得ツイート数: {{ tweetInfo.length }}
            </p>
        </div>
        <div class="dl-image-area">
            <button @click="dlTweet()" class="btn-common green">
                ダウンロード
            </button>
            <p class="caption">※選択している画像をDLします。</p>
        </div>
        <div v-for="tweet in tweetInfo" :key="tweet.postID" class="tweet-info">
            <h3 class="user-name">{{ tweet.user }}</h3>
            <p class="tweet-text">{{ tweet.text }}</p>
            <div
                v-for="image in tweet.images"
                :key="image.id"
                class="tweet-image"
            >
                <input
                    type="checkbox"
                    v-model="image.selected"
                    :id="image.id"
                />
                <label
                    :for="image.id"
                    :class="!image.selected ? 'not-selected' : ''"
                >
                    <img :src="image.url" :alt="tweet.text" />
                </label>
            </div>
            <div class="tweet-url">
                <p>ツイート元リンク</p>
                <a :href="tweet.url">{{ tweet.url }}</a>
            </div>
        </div>
    </section>
</template>
