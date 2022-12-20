<script setup lang="ts">
import HeaderComponent from '@/components/organisms/HeaderComponent.vue'
import apiPath from '@/assets/ts/apiPath'
import ApiManager from '@/components/api/apiManager'
import { Register } from '@/assets/interfaces/interfaces'
import '@/assets/scss/accountManager.scss'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import router from '@/router'

// フォーム入力内容
const account = ref<Register>({
    email: '',
    user_id: '',
    password: '',
})
// バリデーションパラメータ
const regex = {
    user_id: '^.{6,20}$',
    password: '^([a-zA-Z0-9]{8,20})$',
}
// 表示用のエラーメッセージ
const errorMessage = ref<string>('')

// バリデーションを実行し入力内容が不正であればエラーを返す
const inputValidation = () => {
    let errorMessage = ''
    if (!new RegExp(regex.user_id).test(account.value.user_id)) {
        errorMessage = 'ユーザーIDの入力内容が空または不正です。'
    }

    if (!new RegExp(regex.password).test(account.value.password)) {
        errorMessage = 'パスワードの入力内容が空または不正です。'
    }
    return errorMessage
}

// アカウント登録
const apiManager = new ApiManager()
const registerAccount = async () => {
    // 入力内容がパターンにマッチしない場合エラーメッセージを出力
    errorMessage.value = inputValidation()
    if (errorMessage.value !== '') return

    // バリデーションを通過したらAPIを叩いてユーザーデータを登録
    const formData = JSON.stringify({ ...account.value })
    const response = await apiManager.post('accountManager.php', account.value)

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
    <HeaderComponent />
    <main>
        <div>This is RegisterComponent</div>
    </main>
</template>
