import { createRouter, createWebHashHistory, RouteRecordRaw } from 'vue-router'
import TwiImage from '../views/TwiImage.vue'
import PixImage from '../views/PixImage.vue'
import AccountRegister from '@/views/AccountRegister.vue'
import AccountLogin from '@/views/AccountLogin.vue'

const routes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: 'twi',
        component: TwiImage,
    },
    {
        path: '/pix',
        name: 'pix',
        component: PixImage,
    },
    {
        path: '/register',
        name: 'register',
        component: AccountRegister,
    },
    {
        path: '/login',
        name: 'login',
        component: AccountLogin,
    },
]

const router = createRouter({
    history: createWebHashHistory(process.env.BASE_URL),
    routes,
})

export default router
