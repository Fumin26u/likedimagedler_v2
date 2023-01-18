// ローカルの場合Xamppを使用しているので絶対パスでAPIのURIを取得
import { ref } from 'vue'
const apiPath = ref<string>('')
if (process.browser) {
    apiPath.value =
        location.origin === 'http://localhost:3000'
            ? 'http://localhost/likedimagedler_v2/api/'
            : './api/'
}

export default apiPath.value
