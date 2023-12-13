<script setup lang="ts">
import { ref } from 'vue'
import '@/assets/scss/organisms/twiForm.scss'
import ApiManager from '@/components/api/apiManager'
import { TweetInfo, TweetImage } from '@/assets/interfaces/interfaces'
import { twiUserData as search } from '@/assets/ts/userData'
import apiPath from '@/assets/ts/apiPath'
import versionLog from '@/assets/ts/versions'

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
    if (numTweet < 5 || numTweet > 3000) {
        error = '取得できるツイートの最小値は10, 最大値は3000です。'
    }
    return error
}

// APIから画像付きツイートを取得
const tweetInfo = ref<TweetInfo[]>([])
const apiManager = new ApiManager()
const isLoadImages = ref<boolean>(false)
const getTweet = async () => {
    isLoadImages.value = true
    // 入力フォームのバリデーションを行いエラーがある場合は中断
    errorMessage.value = inputValidation()
    if (errorMessage.value !== '') return

    const url = apiPath + 'twi/tweetManager.php'
    const response = await apiManager.get(url, search.value)
    // それぞれの画像にDL可否判定の値を追加
    console.log(response)
    tweetInfo.value = response.content.map((tweet: TweetInfo) => {
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
    isLoadImages.value = false
}

// 画像のダウンロード
const getSelectedImagesFromTweets = (tweets: TweetInfo[]) => {
    const images: string[] = []
    tweets.map((tweet) => {
        tweet.images.map((image) => {
            if (image.selected) images.push(image.url)
        })
    })

    return images
}

const dlImage = async () => {
    isLoadImages.value = true
    // 選択した画像一覧の配列を作成
    const imagePaths = getSelectedImagesFromTweets(tweetInfo.value)
    // DLする画像一覧のURLクエリを取得
    const downloadResponse = await apiManager.post(
        apiPath + 'twi/imageManager.php',
        {
            content: imagePaths,
        }
    )

    // 画像のDLとzipファイルの作成に成功した場合、zipをDLする
    if (!downloadResponse.isSuccessDownload) {
        errorMessage.value = downloadResponse.content
        return
    }

    const link = document.createElement('a')
    link.download = 'images.zip'
    link.href = apiPath + 'twi/images.zip'
    link.click()

    // APIを叩いて保存回数と画像保存枚数、最新取得画像を更新
    await apiManager.post(apiPath + 'twi/imageInfoManager.php', {
        imageCount: imagePaths.length,
        latestID: tweetInfo.value[0].postID,
        twitterID: search.value.twitterID,
    })

    // zipファイルと画像ディレクトリを一括消去
    const removeResponse = await apiManager.post(
        apiPath + 'twi/removeImage.php'
    )

    if (!removeResponse.isSuccessRemove) {
        errorMessage.value = removeResponse.content
        return
    }
    isLoadImages.value = false
}
</script>
<template>
    <main class="main-container twi-template">
        <section class="search-form">
            <div class="title-area">
                <h2>検索フォーム</h2>
                <small>
                    <a href="./#/terms-of-use">利用規約</a>
                    と
                    <a href="./#/privacy-policy">プライバシーポリシー</a>
                    の確認をお願いします。
                    <br />
                    [ツイートを取得]ボタン押下時点で上記に同意したとみなします。
                </small>
            </div>
            <dl class="search-box">
                <div>
                    <dt>
                        Twitter ID
                        <em>*</em>
                    </dt>
                    <dd><input v-model="search.twitterID" type="text" /></dd>
                </div>
                <div>
                    <dt>
                        取得内容
                        <em>*</em>
                    </dt>
                    <dd class="radio-list">
                        <div>
                            <input
                                id="get-like"
                                v-model="search.getTweetType"
                                type="radio"
                                value="liked_tweets"
                            />
                            <label for="get-like">いいね</label>
                        </div>
                        <div>
                            <input
                                id="get-tweet"
                                v-model="search.getTweetType"
                                type="radio"
                                value="tweets"
                                disabled
                            />
                            <label for="get-tweet">ツイート</label>
                        </div>
                        <div>
                            <input
                                id="get-bookmark"
                                v-model="search.getTweetType"
                                type="radio"
                                value="bookmarks"
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
                            v-model="search.getNumberOfTweet"
                            type="number"
                            min="5"
                            max="1000"
                            step="5"
                        />
                    </dd>
                </div>
                <div>
                    <dt>詳細設定</dt>
                    <dd>
                        <input
                            id="get-pre"
                            v-model="search.isGetFromPreviousTweet"
                            type="checkbox"
                        />
                        <label for="get-pre">前回DLした画像以降を取得</label>
                    </dd>
                </div>
                <div v-show="isLoadImages" class="btn-cover"></div>
                <button class="btn-common green" @click="getTweet()">
                    ツイートを取得
                </button>
            </dl>
        </section>
        <p>{{ errorMessage }}</p>
        <section v-if="tweetInfo.length > 0" class="tweet-list post-list">
            <div v-show="isLoadImages" class="btn-cover"></div>
            <div class="title-area">
                <h2>取得ツイート一覧</h2>
                <p v-if="tweetInfo.length > 0" class="caption">
                    取得ツイート数: {{ tweetInfo.length }}
                </p>
            </div>
            <div class="dl-image-area">
                <button class="btn-common green" @click="dlImage()">
                    ダウンロード
                </button>
                <p class="caption">※選択している画像をDLします。</p>
            </div>
            <div
                v-for="tweet in tweetInfo"
                :key="tweet.postID"
                class="post-info"
            >
                <h3 class="user-name">{{ tweet.user }}</h3>
                <p class="tweet-text">{{ tweet.text }}</p>
                <div
                    v-for="image in tweet.images"
                    :key="image.id"
                    class="tweet-image"
                >
                    <input
                        :id="image.id"
                        v-model="image.selected"
                        type="checkbox"
                    />
                    <label
                        :for="image.id"
                        :class="!image.selected ? 'not-selected' : ''"
                    >
                        <img :src="image.url" :alt="tweet.text" />
                    </label>
                </div>
                <div class="post-url">
                    <a :href="tweet.url">ツイート元リンク</a>
                </div>
            </div>
        </section>
        <section class="version">
            <h3>更新履歴</h3>
            <dl class="version-list">
                <div v-for="(version, index) in versionLog" :key="index">
                    <dt>{{ version.date }}</dt>
                    <dd>
                        <p class="version-number">Ver. {{ version.version }}</p>
                        <p>{{ version.content }}</p>
                    </dd>
                </div>
            </dl>
        </section>
    </main>
</template>
