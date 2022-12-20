import axios from 'axios'
const axios_instance = axios.create()
axios_instance.interceptors.request.use((config) => {
    config.headers = {
        'Authorization': process.env.BEARER_TOKEN,
    }
    return config
})

export default axios_instance
