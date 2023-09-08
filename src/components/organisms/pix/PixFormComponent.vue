<script setup lang="ts">
import { ref } from 'vue'
import '@/assets/scss/organisms/pixForm.scss'
import ApiManager from '@/components/api/apiManager'
import { PixPostInfo, PixPostImage } from '@/assets/interfaces/interfaces'
import { pixUserData as search } from '@/assets/ts/userData'
import apiPath from '@/assets/ts/apiPath'

const errorMessage = ref<string>('')
// 入力フォームのバリデーション
const inputValidation = (): string => {
    let error = ''
    if (search.value.userID === null || search.value.userID === 0) {
        error = 'ユーザーIDが入力されていません。'
    }

    const numPost = parseInt(search.value.getNumberOfPost)
    if (isNaN(numPost)) {
        error = '取得作品数は数値で入力してください。'
    }
    if (numPost < 10 || numPost > 300) {
        error = '取得できる作品の最小値は10, 最大値は300です。'
    }
    return error
}

const pixPostInfo = ref<PixPostInfo[]>([])
const apiManager = new ApiManager()
const isLoadImages = ref<boolean>(false)
// 画像情報の取得
const getImage = async () => {
    isLoadImages.value = true
    errorMessage.value = inputValidation()
    if (errorMessage.value !== '') return

    const response = await apiManager.post(
        apiPath + 'pix/pixImageManager.php',
        {
            method: 'get',
            content: search.value,
        }
    )

    pixPostInfo.value = response.map((post: PixPostInfo) => {
        return {
            postID: post.postID,
            post_time: post.post_time,
            user: post.user,
            text: post.text,
            url: post.url,
            images: post.images.map((image: PixPostImage, index: number) => {
                return {
                    id: `${post.postID}_${index}`,
                    url: image,
                    selected: true,
                }
            }),
        }
    })
    isLoadImages.value = false
}

// 画像情報から画像URLのみを抜き出す
const getSelectedImagesFromPosts = (pixPosts: PixPostInfo[]) => {
    const images: string[] = []
    pixPosts.map((post) => {
        post.images.map((image) => {
            if (image.selected) images.push(image.url)
        })
    })

    return images
}

// 画像のダウンロード
const dlImage = async () => {
    isLoadImages.value = true
    // 画像URL一覧の作成
    const imagePaths = getSelectedImagesFromPosts(pixPostInfo.value)

    // 画像URL一覧をAPIに送り画像をDL
    const downloadResponse = await apiManager.post(
        apiPath + 'pix/pixImageManager.php',
        {
            method: 'download',
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
    link.href = apiPath + 'pix/images.zip'
    link.click()

    // DL完了時、DL回数・枚数と最新DL画像の投稿IDを更新
    const updateInfoResponse = await apiManager.post(
        apiPath + 'pix/pixImageManager.php',
        {
            method: 'updateInfo',
            content: {
                imageCount: imagePaths.length,
                latestID: pixPostInfo.value[0].postID,
                pixUserID: search.value.userID,
            },
        }
    )
    // zipファイルと画像ディレクトリを一括消去
    const removeResponse = await apiManager.post(
        apiPath + 'pix/pixImageManager.php',
        { method: 'remove' }
    )

    if (!updateInfoResponse.isSuccessUpdate) {
        errorMessage.value = updateInfoResponse.content
        return
    }
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
                        ユーザーID
                        <em>*</em>
                    </dt>
                    <dd><input v-model="search.userID" type="number" /></dd>
                </div>
                <div>
                    <dt>
                        取得内容
                        <em>*</em>
                    </dt>
                    <dd class="radio-list">
                        <div>
                            <input
                                id="get-bookmark"
                                v-model="search.getPostType"
                                type="radio"
                                value="bookmark"
                            />
                            <label for="get-bookmark">ブックマーク</label>
                        </div>
                        <div>
                            <input
                                id="get-post"
                                v-model="search.getPostType"
                                type="radio"
                                value="post"
                            />
                            <label for="get-post">作品</label>
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
                            v-model="search.getNumberOfPost"
                            type="number"
                            min="10"
                            max="300"
                            step="10"
                        />
                    </dd>
                </div>
                <div>
                    <dt>取得を中断するID</dt>
                    <dd>
                        <input
                            v-model="search.suspendID"
                            type="number"
                            min="1"
                        />
                    </dd>
                </div>
                <div>
                    <dt>詳細設定</dt>
                    <dd>
                        <input
                            id="get-pre"
                            v-model="search.isGetFromPreviousPost"
                            type="checkbox"
                        />
                        <label for="get-pre">取得を中断するIDを設定</label>
                        <input
                            id="include-tags"
                            v-model="search.includeTags"
                            type="checkbox"
                        />
                        <label for="include-tags">タグフィルターを設定</label>
                    </dd>
                </div>
                <div v-show="isLoadImages" class="btn-cover"></div>
                <button class="btn-common green" @click="getImage()">
                    作品を取得
                </button>
            </dl>
        </section>
        <p>{{ errorMessage }}</p>
        <section v-if="pixPostInfo.length > 0" class="post-list">
            <div v-show="isLoadImages" class="btn-cover"></div>
            <div class="title-area">
                <h2>取得投稿一覧</h2>
                <p v-if="pixPostInfo.length > 0" class="caption">
                    取得投稿数: {{ pixPostInfo.length }}
                </p>
            </div>
            <div class="dl-image-area">
                <button class="btn-common green" @click="dlImage()">
                    ダウンロード
                </button>
                <p class="caption">※選択している画像をDLします。</p>
            </div>
            <div
                v-for="pixPost in pixPostInfo"
                :key="pixPost.postID"
                class="post-info"
            >
                <h3 class="user-name">{{ pixPost.user }}</h3>
                <p class="pix-post-text">{{ pixPost.text }}</p>
                <div class="pix-post-image">
                    <div
                        v-for="(image, index) in pixPost.images"
                        :key="image.id"
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
                            {{ `${index + 1}枚目: ${image.id}` }}
                        </label>
                    </div>
                </div>
                <div class="post-url">
                    <p>作品元リンク</p>
                    <a :href="pixPost.url">{{ pixPost.url }}</a>
                </div>
            </div>
        </section>
    </main>
</template>
