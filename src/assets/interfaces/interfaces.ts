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

// Twitter関連
export interface TwiSearch {
    twitterID: string
    getTweetType: 'liked_tweets' | 'tweets' | 'bookmarks'
    getNumberOfTweet: string
    isGetFromPreviousTweet: boolean
}

export interface TweetImage {
    id: string
    url: string
    selected: boolean
}

export interface TweetInfo {
    postID: string
    post_time: boolean
    user: string
    text: string
    images: TweetImage[]
    url: string
}

// pixiv関連
export interface PixSearch {
    userID: number
    getPostType: string
    getNumberOfPost: string
    isGetFromPreviousPost: boolean
    includeTags: boolean
    suspendID: string
}

export interface PixPostInfo {
    postID: string
    post_time: boolean
    user: string
    text: string
    images: TweetImage[]
    url: string
}

export interface PixPostImage {
    id: string
    url: string
    selected: boolean
}
