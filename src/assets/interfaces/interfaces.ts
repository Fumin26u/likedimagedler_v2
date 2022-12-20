export interface Register {
    method: 'logout' | 'login' | 'register'
    email: string
    user_name: string
    password: string
}