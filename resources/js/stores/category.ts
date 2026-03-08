import axios from 'axios'
import { defineStore } from 'pinia'

export const useCategoryStore = defineStore({
  id: 'category',
  state: () => ({
    categories: [],
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
        axios.get('/api/categories', { params })
        .then((response) => {
          this.categories = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
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
        .then((response) => {
          this.categories.push(response.data.data)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    updateCategory(data: any) {
      return new Promise((resolve, reject) => {
        axios.put(`/api/categories/${data.id}`, data)
        .then((response) => {
          let index = this.categories.findIndex((_c) => _c.id === data.id)
          this.categories[index] = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    removeCategory(id: any) {
      return new Promise((resolve, reject) => {
        axios.delete(`/api/categories/${id}`)
        .then((response) => {
          let index = this.categories.findIndex((_c) => _c.id === id)
          this.categories.splice(index, 1)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
  },
})
