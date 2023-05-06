<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import '@/assets/scss/organisms/header.scss'
import ApiManager from '@/components/api/apiManager'
import apiPath from '@/assets/ts/apiPath'

interface Emits {
    (e: 'getUserInfo', userId: string): string
}
const emit = defineEmits<Emits>()

const router = useRouter()
const userName = ref<string>('')
// ログアウトリンクが押された場合APIに伝える
const apiManager = new ApiManager()
const execLogout = async () => {
    await apiManager.post(apiPath + 'account/accountManager.php', {
        method: 'logout',
        user_name: userName.value,
    })
    router.push('./login')
}

const getUserInfo = async () => {
    const response = await apiManager.post(
        apiPath + 'account/accountManager.php',
        {
            method: 'getUserData',
        }
    )

    // pixivdlerアクセス時の認証
    if (
        location.href.slice(-4) === '/pix' &&
        response.user_name !== 'Fumiya0719'
    ) {
        router.push('./')
    }
    return response.user_name
}

// 画面読み込み時にログインユーザーIDを取得
onMounted(async () => {
    userName.value = await getUserInfo()
    emit('getUserInfo', userName.value)
})
</script>

<template>
    <header class="header-container">
        <div class="header-left">
            <div class="title-area">
                <a href="./">
                    <h1>ImageDLer</h1>
                    <p class="caption">Twitterの画像自動ダウンローダー</p>
                </a>
            </div>
            <nav class="header-nav">
                <a href="./#/terms-of-use" class="btn-small blue">利用規約</a>
                <a href="./#/privacy-policy" class="btn-small blue">
                    プライバシーポリシー
                </a>
                <a href="./#/pix" class="btn-small blue">pixiv版</a>
            </nav>
        </div>
        <div class="header-account">
            <div v-if="userName !== ''">
                <p>{{ userName }}さん</p>
                <a
                    href="./#/login"
                    class="btn-common blue"
                    @click.prevent.stop="execLogout"
                >
                    ログアウト
                </a>
            </div>
            <div v-else>
                <a href="./#/login" class="btn-common blue">ログイン</a>
                <a href="./#/register-pre" class="btn-common green">
                    アカウント登録
                </a>
            </div>
        </div>
    </header>
</template>
