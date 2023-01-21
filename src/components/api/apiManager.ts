/* eslint-disable */
import axios from 'axios'
class ApiManager {
    // POST
    async post(
        url: string,
        formData: any = {}
    ): Promise<{ [key: string]: any }> {
        return await axios
            .post(url, formData)
            .then((response) => {
                return response.data
            })
            .catch((error) => {
                console.log(error)
                return {
                    error: true,
                }
            })
    }

    // GET
    async get(url: string, query: any = {}) {
        return await axios
            .get(url, {
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
