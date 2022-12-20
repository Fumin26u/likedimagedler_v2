type AccountPostMethod = 'logout' | 'login' | 'register'

export interface Register {
    method: AccountPostMethod
    email: string
    user_name: string
    password: string
}

export interface Login {
    method: AccountPostMethod
    user_name: string
    password: string
}

export interface TwiSearch {
    twitterID: string
    getTweetType: 'liked_tweets' | 'tweets' | 'bookmarks'
    getNumberOfTweet: string
    isGetFromPreviousTweet: boolean
}