import axios from 'axios'
import apiPath from '@/assets/ts/apiPath'

class ApiManager {
    // POST
    async post(
        url: string,
        formData: any = {}
    ): Promise<{ [key: string]: any }> {
        const formUrl = apiPath + url
        console.log(formUrl)
        return await axios
            .post(formUrl, formData)
            .then((response) => {
                return response.data
            })
            .catch((response) => {
                console.log(response)
                return {
                    error: true,
                }
            })
    }

    // GET
    async get(url: string, query: any = {}) {
        const formUrl = apiPath + url

        return await axios
            .get(formUrl, {
                params: query,
            })
            .then((response) => {
                if (response.data === '') {
                    return {
                        error: true,
                        content: 'データの取得に失敗しました。',
                    }
                }

                return {
                    error: false,
                    content: response.data,
                }
            })
            .catch((error) => {
                console.log(error)
                return {
                    error: true,
                    content: null,
                }
            })
    }
}

export default ApiManager
