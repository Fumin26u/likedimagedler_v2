<script setup lang="ts">
import HeaderComponent from '../organisms/HeaderComponent.vue'
import apiPath from '@/assets/ts/apiPath'
import ApiManager from '@/components/api/apiManager'
import { Login } from '@/assets/interfaces/interfaces'
import '@/assets/scss/accountManager.scss'
import { ref } from 'vue'
import router from '@/router'

// フォーム入力内容
const account = ref<Login>({
    method: 'login',
    user_name: '',
    password: '',
})
// バリデーションパラメータ
const regex = {
    user_name: '^.{6,20}$',
    password: '^([a-zA-Z0-9]{8,20})$',
}
// 表示用のエラーメッセージ
const errorMessage = ref<string>('')

// バリデーションを実行し入力内容が不正であればエラーを返す
const inputValidation = () => {
    let errorMessage = ''
    if (!new RegExp(regex.user_name).test(account.value.user_name)) {
        errorMessage = 'ユーザーIDの入力内容が空または不正です。'
    }

    if (!new RegExp(regex.password).test(account.value.password)) {
        errorMessage = 'パスワードの入力内容が空または不正です。'
    }
    return errorMessage
}

// アカウント登録
const apiManager = new ApiManager()
const loginAccount = async () => {
    // 入力内容がパターンにマッチしない場合エラーメッセージを出力
    errorMessage.value = inputValidation()
    if (errorMessage.value !== '') return

    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const response = await apiManager.post('accountManager.php', account.value)

    // 入力内容が不正の場合
    if (response.error) {
        errorMessage.value = response.content
        return
    }
    console.log(response)

    // 返答でエラーが無い場合は指定ページにリダイレクト
    router.push('./')
}
</script>

<template>
    <HeaderComponent />
    <main class="main-container login-component">
        <div class="title-area">
            <h2>ユーザー新規登録</h2>
            <p>{{ errorMessage }}</p>
        </div>
        <div class="form-area">
            <a href="./#/register">アカウント未登録の場合はこちら</a>
            <form @submit.prevent="loginAccount()">
                <dl>
                    <div>
                        <dt>ユーザー名</dt>
                        <dd>
                            <p class="caption">6文字以上20文字以下</p>
                            <input
                                type="text"
                                id="user_name"
                                v-model="account.user_name"
                                autocomplete="username"
                                :pattern="regex.user_name"
                                required
                            />
                        </dd>
                    </div>
                    <div>
                        <dt>パスワード</dt>
                        <dd>
                            <p class="caption">半角英数字8文字以上20文字以下</p>
                            <input
                                type="password"
                                id="password"
                                v-model="account.password"
                                autocomplete="current-password"
                                :pattern="regex.password"
                                required
                            />
                        </dd>
                    </div>
                </dl>
                <button type="submit" class="btn-common green submit">
                    ログイン
                </button>
            </form>
        </div>
    </main>
</template>
