<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import HeaderComponent from '@/components/organisms/HeaderComponent.vue'
import ApiManager from '@/components/api/apiManager'
import apiPath from '@/assets/ts/apiPath'
import { Register } from '@/assets/interfaces/interfaces'
import '@/assets/scss/accountManager.scss'

// フォーム入力内容
const account = ref<Register>({
    method: 'register',
    email: '',
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
const router = useRouter()
const apiManager = new ApiManager()
const registerAccount = async () => {
    // 入力内容がパターンにマッチしない場合エラーメッセージを出力
    errorMessage.value = inputValidation()
    if (errorMessage.value !== '') return

    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const response = await apiManager.post(
        apiPath + 'account/accountManager.php',
        account.value
    )

    // 入力内容が不正の場合
    if (response.error) {
        errorMessage.value = response.content
        return
    }

    // 返答でエラーが無い場合は指定ページにリダイレクト
    alert('アカウントを登録しました。')
    router.push('./login')
}
</script>

<template>
    <div>
        <HeaderComponent />
        <main class="main-container register-component">
            <div class="title-area">
                <h2>ユーザー新規登録</h2>
                <p>{{ errorMessage }}</p>
            </div>
            <div class="form-area">
                <a href="./#/login">既にアカウント登録している場合はこちら</a>
                <form @submit.prevent="registerAccount()">
                    <dl>
                        <div>
                            <dt>メールアドレス</dt>
                            <dd>
                                <input
                                    id="email"
                                    v-model="account.email"
                                    type="email"
                                    required
                                />
                            </dd>
                        </div>
                        <div>
                            <dt>ユーザー名</dt>
                            <dd>
                                <p class="caption">6文字以上20文字以下</p>
                                <input
                                    id="user_name"
                                    v-model="account.user_name"
                                    type="text"
                                    autocomplete="username"
                                    :pattern="regex.user_name"
                                    required
                                />
                            </dd>
                        </div>
                        <div>
                            <dt>パスワード</dt>
                            <dd>
                                <p class="caption">
                                    半角英数字8文字以上20文字以下
                                </p>
                                <input
                                    id="password"
                                    v-model="account.password"
                                    type="password"
                                    autocomplete="current-password"
                                    :pattern="regex.password"
                                    required
                                />
                            </dd>
                        </div>
                    </dl>
                    <button type="submit" class="btn-common green submit">
                        登録
                    </button>
                </form>
            </div>
        </main>
    </div>
</template>
