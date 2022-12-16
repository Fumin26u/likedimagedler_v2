<script setup lang="ts">
import '@/assets/scss/twiForm.scss'
</script>
<template>
    <section class="search-form">
        <div class="title">
            <h2>検索フォーム</h2>
        </div>
        <dl class="search-box">
            <div>
                <dt>Twitter ID<em>*</em></dt>
                <dd><input type="text" /></dd>
            </div>
            <div>
                <dt>取得内容<em>*</em></dt>
                <dd class="radio-list">
                    <div>
                        <input type="radio" id="get-like" />
                        <label for="get-like">いいね</label>
                    </div>
                    <div>
                        <input type="radio" id="get-tweet" />
                        <label for="get-tweet">ツイート</label>
                    </div>
                    <div>
                        <input type="radio" id="get-bookmark" disabled />
                        <label for="get-bookmark">ブックマーク</label>
                    </div>
                </dd>
            </div>
            <div>
                <dt>取得ツイート数<em>*</em><br />(最大200)</dt>
                <dd><input type="number" /></dd>
            </div>
            <div>
                <dt>詳細設定</dt>
                <dd>
                    <input type="checkbox" id="get-pre" />
                    <label for="get-pre">前回DLした画像以降を取得</label>
                </dd>
            </div>
            <button class="btn-common green">画像を取得</button>
        </dl>
    </section>
</template>
