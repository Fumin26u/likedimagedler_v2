<script setup lang="ts">
import '@/assets/scss/header.scss'
import { ref } from 'vue'
const user_id = ref<string>('')
</script>

<template>
    <header class="header-container">
        <div class="header-left">
            <div class="title-area">
                <h1>ImageDLer</h1>
                <p class="caption">Twitterの画像自動ダウンローダー</p>
            </div>
            <nav class="header-nav">
                <a href="">注意事項</a>
                <a href="">更新履歴</a>
            </nav>
        </div>
        <div class="header-account">
            <div v-if="user_id !== ''">
                <p>{{ user_id }}さん</p>
                <a href="" class="btn-common blue">ログアウト</a>
            </div>
            <div v-else>
                <a href="" class="btn-common blue">ログイン</a>
                <a href="" class="btn-common green">アカウント登録</a>
            </div>
        </div>
    </header>
</template>
