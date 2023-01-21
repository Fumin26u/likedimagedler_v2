<script setup lang="ts">
import { ref } from 'vue'
import HeaderComponent from '@/components/organisms/HeaderComponent.vue'
import ApiManager from '@/components/api/apiManager'
import apiPath from '@/assets/ts/apiPath'
import '@/assets/scss/accountManager.scss'

// フォーム入力内容
const account = ref<{ [key: string]: string }>({
    method: 'register-pre',
    email: '',
})
// 結果表示用のメッセージ
const message = ref<string>('')

// アカウント登録
const apiManager = new ApiManager()
const registerAccount = async () => {
    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const response = await apiManager.post(
        apiPath + 'account/accountManager.php',
        account.value
    )

    // 入力内容が不正の場合
    if (response.error) {
        message.value = response.content
        return
    } else {
        message.value =
            '入力したメールアドレスに確認メールを送信しました。ご確認ください。'
    }
}
</script>

<template>
    <div>
        <HeaderComponent />
        <main class="main-container register-component">
            <div class="title-area">
                <h2>ユーザー仮登録</h2>
                <p>{{ message }}</p>
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
                    </dl>
                    <button type="submit" class="btn-common green submit">
                        仮登録
                    </button>
                </form>
            </div>
        </main>
    </div>
</template>
