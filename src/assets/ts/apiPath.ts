// ローカルの場合Xamppを使用しているので絶対パスでAPIのURIを取得
const apiPath =
    location.origin === 'http://localhost:8080'
        ? 'http://localhost/likedimagedler_v2/api/'
        : './api/'

export default apiPath
