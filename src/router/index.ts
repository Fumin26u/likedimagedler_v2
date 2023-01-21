import { createRouter, createWebHashHistory, RouteRecordRaw } from 'vue-router'
import index from '../views/index.vue'
import pix from '../views/pix.vue'
import register from '@/views/account/register.vue'
import preRegister from '@/views/account/register-pre.vue'
import login from '@/views/account/login.vue'
import terms from '@/views/termsofuse/terms.vue'
import privacyPolicy from '@/views/termsofuse/privacyPolicy.vue'

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
    {
        path: '/terms-of-use',
        name: 'terms-of-use',
        component: terms,
    },
    {
        path: '/privacy-policy',
        name: 'privacy-policy',
        component: privacyPolicy,
    },
]

const router = createRouter({
    history: createWebHashHistory(process.env.BASE_URL),
    routes,
})

export default router
