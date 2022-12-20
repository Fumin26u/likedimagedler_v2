<script setup lang="ts">
import '@/assets/scss/header.scss'
import ApiManager from '@/components/api/apiManager'
import router from '@/router';
import { ref, onMounted } from 'vue'

interface Emits {
    (e: 'getUserInfo', userId: string): string
}
// eslint-disable-next-line
const emit = defineEmits<Emits>()

const user_name = ref<string>('')
// ログアウトリンクが押された場合APIに伝える
const apiManager = new ApiManager()
const execLogout = async () => {
    await apiManager.post('accountManager.php', {
        method: 'logout',
        user_name: user_name.value
    })
    router.push('./login')
}

const getUserInfo = async () => {
    const response = await apiManager.post('accountManager.php', {
        method: 'getUserData',
    })
    return response.user_name
}

// 画面読み込み時にログインユーザーIDを取得
onMounted(async () => {
    user_name.value = await getUserInfo()
    emit('getUserInfo', user_name.value)
})
</script>

<template>
    <header class="header-container">
        <div class="header-left">
            <div class="title-area">
                <a href="./#/">
                    <h1>ImageDLer</h1>
                    <p class="caption">Twitterの画像自動ダウンローダー</p>
                </a>
            </div>
            <nav class="header-nav">
                <a href="" class="btn-small blue">注意事項</a>
                <a href="" class="btn-small blue">更新履歴</a>
            </nav>
        </div>
        <div class="header-account">
            <div v-if="user_name !== ''">
                <p>{{ user_name }}さん</p>
                <a href="./#/login" class="btn-common blue" @click.prevent.stop="execLogout">
                    ログアウト
                </a>
            </div>
            <div v-else>
                <a href="./#/login" class="btn-common blue">ログイン</a>
                <a href="./#/register" class="btn-common green">アカウント登録</a>
            </div>
        </div>
    </header>
</template>
