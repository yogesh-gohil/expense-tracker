import axios from 'axios'
import { defineStore } from 'pinia'

export const useCategoryStore = defineStore({
  id: 'category',
  state: () => ({
    categories: [],
    pagination: null,
    lastFetchParams: null,
    showCategoryModal: false,
    categoryData: {
      name: '',
      type: 'EXPENSE',
      description: '',
    },

  }),
  actions: {
    resetCategoryData() {
      this.showCategoryModal = false
      this.categoryData = {
        name: '',
        type: 'EXPENSE',
        description: '',
      }
    },
    fetchCategories(params: any) {
      return new Promise((resolve, reject) => {
        this.lastFetchParams = params
        axios.get('/api/categories', { params })
        .then((response) => {
          this.categories = response.data.data
          this.pagination = response.data.meta ?? {
            current_page: 1,
            last_page: 1,
            per_page: this.categories.length,
            total: this.categories.length,
            from: this.categories.length ? 1 : 0,
            to: this.categories.length,
          }
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    refreshCategories() {
      if (!this.lastFetchParams)
        return Promise.resolve()
      return this.fetchCategories(this.lastFetchParams)
    },
    fetchCategory(id: any) {
      return new Promise((resolve, reject) => {
        axios.post(`/api/categories/${id}`)
        .then((response) => {
          this.categories.push(response.data.data)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    addCategory(data: any) {
      return new Promise((resolve, reject) => {
        axios.post('/api/categories', data)
        .then(async (response) => {
          if (this.lastFetchParams) {
            await this.fetchCategories(this.lastFetchParams)
          } else {
            this.categories.push(response.data.data)
          }
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    updateCategory(data: any) {
      return new Promise((resolve, reject) => {
        axios.put(`/api/categories/${data.id}`, data)
        .then(async (response) => {
          if (this.lastFetchParams) {
            await this.fetchCategories(this.lastFetchParams)
          } else {
            let index = this.categories.findIndex((_c) => _c.id === data.id)
            this.categories[index] = response.data.data
          }
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    removeCategory(id: any) {
      return new Promise((resolve, reject) => {
        axios.delete(`/api/categories/${id}`)
        .then(async (response) => {
          if (this.lastFetchParams) {
            await this.fetchCategories(this.lastFetchParams)
          } else {
            let index = this.categories.findIndex((_c) => _c.id === id)
            this.categories.splice(index, 1)
          }
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
  },
})
