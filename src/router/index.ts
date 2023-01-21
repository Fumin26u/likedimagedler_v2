import { createRouter, createWebHashHistory, RouteRecordRaw } from 'vue-router'
import index from '../views/index.vue'
import pix from '../views/pix.vue'
import register from '@/views/register.vue'
import preRegister from '@/views/register-pre.vue'
import login from '@/views/login.vue'

const routes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: 'twi',
        component: index,
    },
    {
        path: '/pix',
        name: 'pix',
        component: pix,
    },
    {
        path: '/register',
        name: 'register',
        component: register,
    },
    {
        path: '/register-pre',
        name: 'register-pre',
        component: preRegister,
    },
    {
        path: '/login',
        name: 'login',
        component: login,
    },
]

const router = createRouter({
    history: createWebHashHistory(process.env.BASE_URL),
    routes,
})

export default router
