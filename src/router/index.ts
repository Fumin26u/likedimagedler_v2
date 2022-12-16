import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router'
import TwiImage from '../views/TwiImage.vue'
import PixImage from '../views/PixImage.vue'

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
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
})

export default router
