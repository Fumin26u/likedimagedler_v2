<script setup lang="ts">
import { ref, onMounted, useRouter } from '@nuxtjs/composition-api'
import '@/assets/scss/organisms/header.scss'
import ApiManager from '@/components/api/apiManager'
import apiPath from '~/assets/ts/apiPath';

interface Emits {
    (e: 'getUserInfo', userId: string): string
}
const emit = defineEmits<Emits>()

const router = useRouter()
const userName = ref<string>('')
// ログアウトリンクが押された場合APIに伝える
const apiManager = new ApiManager()
const execLogout = async () => {
    await apiManager.post(apiPath + 'accountManager.php', {
        method: 'logout',
        user_name: userName.value,
    })
    router.push('./login')
}

const getUserInfo = async () => {
    const response = await apiManager.post(apiPath + 'accountManager.php', {
        method: 'getUserData',
    })
    console.log(response)
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
                    <p class="caption">Twitter/pixivの画像自動ダウンローダー</p>
                </a>
            </div>
            <nav class="header-nav">
                <a href="" class="btn-small blue">注意事項</a>
                <a href="" class="btn-small blue">更新履歴</a>
                <a href="./pix" class="btn-small blue">pixiv版</a>
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
                <a href="./#/register" class="btn-common green">
                    アカウント登録
                </a>
            </div>
        </div>
    </header>
</template>
