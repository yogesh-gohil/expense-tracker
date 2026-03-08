import axios from 'axios'
import { defineStore } from 'pinia'
import router from '@/js/router'


export const useAuthStore = defineStore({
  id: 'auth',
  state: () => ({
    currentUser: null,
    isLoggedIn: false,
    loginData: {
      email: '',
      password: '',
      remember: '',
    },
    registerData: {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
    },
  }),
  actions: {
    login(data: any) {
      return new Promise((resolve, reject) => {
        axios.get('/sanctum/csrf-cookie').then((response) => {
          if (response) {
            axios.post('/api/login', data)
              .then(async (response) => {
                await this.getAuthUser()
                resolve(response)
              })
              .catch((err) => {
                reject(err)
              })
          }
        })
      })
    },
    getAuthUser() {
      return new Promise((resolve, reject) => {
        axios
          .get('/api/me')
          .then((response) => {
            this.currentUser = response.data
            this.isLoggedIn = true
            resolve(response)
          })
          .catch((err) => {
            reject(err)
          })
      })
    },
    register(data: any) {
      return new Promise((resolve, reject) => {
        axios.post('/api/register', data)
        .then((response) => {
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },

    logout() {
      return new Promise((resolve, reject) => {
        axios
          .post('/logout')
          .then((response) => {
            router.replace('/login')
            resolve(response)
          })
          .catch((err) => {
            router.replace('/login')
            reject(err)
          })
      })
    },
  },
})
