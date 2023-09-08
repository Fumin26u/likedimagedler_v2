import { ref } from 'vue'
import { PixSearch, TwiSearch } from '@/assets/interfaces/interfaces'

export const pixUserData = ref<PixSearch>({
    userID: 0,
    getPostType: '',
    getNumberOfPost: '50',
    isGetFromPreviousPost: true,
    includeTags: false,
    suspendID: '',
})

export const twiUserData = ref<TwiSearch>({
    twitterID: '',
    getTweetType: 'liked_tweets',
    getNumberOfTweet: '50',
    isGetFromPreviousTweet: true,
})
