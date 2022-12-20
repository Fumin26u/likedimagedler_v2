<script setup lang="ts">
import '@/assets/scss/twiForm.scss'
import axios from '@/components/api/setApiKey'
import { TwiSearch } from '@/assets/interfaces/interfaces'
import { ref } from 'vue'

// 入力フォームの値
const search = ref<TwiSearch>({
    twitterID: '',
    getTweetType: 'like',
    getNumberOfTweet: '100',
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
        error =  '取得できるツイートの最小値は10, 最大値は300です。'
    }
    return error
}

// 
const getTweet = () => {

}
</script> 
<template>
    <section class="search-form">
        <div class="title">
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
                            value="like"
                            v-model="search.getTweetType"
                        />
                        <label for="get-like">いいね</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            id="get-tweet"
                            value="tweet"
                            v-model="search.getTweetType"
                        />
                        <label for="get-tweet">ツイート</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            id="get-bookmark"
                            value="bookmark"
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
            <button @click="getTweet" class="btn-common green">画像を取得</button>
        </dl>
    </section>
</template>
