import axios from 'axios'
import { defineStore } from 'pinia'

export const useIncomeStore = defineStore({
  id: 'income',
  state: () => ({
    incomes: [],
    monthlySummary: null,
    isIncomeModalOpen: false,
    showIncomeModal: false,
    incomeData: {
      title: '',
      description: '',
      amount: 0,
      date: '',
      category_id: '',
    },

  }),
  actions: {
    resetIncomeData() {
      this.showIncomeModal = false
      this.incomeData = {
        title: '',
        description: '',
        amount: 0,
        date: '',
        category_id: '',
      }
    },
    fetchIncomes(data: any) {
      return new Promise((resolve, reject) => {
        axios.get('/api/incomes', { params: data })
        .then((response) => {
          this.incomes = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    fetchIncome(id: any) {
      return new Promise((resolve, reject) => {
        axios.get(`/api/incomes/${id}`)
        .then((response) => {
          this.incomes.push(response.data.data)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    fetchMonthlySummary(params: any = {}) {
      return new Promise((resolve, reject) => {
        axios.get('/api/incomes/monthly-summary', { params })
        .then((response) => {
          this.monthlySummary = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    addIncome(data: any) {
      return new Promise((resolve, reject) => {
        axios.post('/api/incomes', data)
        .then((response) => {
          this.incomes.push(response.data.data)
          this.resetIncomeData()
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    updateIncome(data: any) {
      return new Promise((resolve, reject) => {
        axios.put(`/api/incomes/${data.id}`, data)
        .then((response) => {
          const index = this.incomes.findIndex((_i) => _i.id === data.id)
          if (index > -1) {
            this.incomes[index] = response.data.data
          }
          this.resetIncomeData()
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    removeIncome(id: any) {
      return new Promise((resolve, reject) => {
        axios.delete(`/api/incomes/${id}`)
        .then((response) => {
          let index = this.incomes.findIndex((_c) => _c.id === id)
          this.incomes.splice(index, 1)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
  },
})
