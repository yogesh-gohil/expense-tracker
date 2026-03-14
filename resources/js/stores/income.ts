import axios from 'axios'
import { defineStore } from 'pinia'

export const useIncomeStore = defineStore({
  id: 'income',
  state: () => ({
    incomes: [],
    pagination: null,
    lastFetchParams: null,
    monthlySummary: null,
    isIncomeModalOpen: false,
    showIncomeModal: false,
    incomeData: {
      title: '',
      description: '',
      amount: 0,
      date: new Date().toISOString().slice(0, 10),
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
        date: new Date().toISOString().slice(0, 10),
        category_id: '',
      }
    },
    fetchIncomes(data: any) {
      return new Promise((resolve, reject) => {
        this.lastFetchParams = data
        axios.get('/api/incomes', { params: data })
        .then((response) => {
          this.incomes = response.data.data
          this.pagination = response.data.meta ?? {
            current_page: 1,
            last_page: 1,
            per_page: this.incomes.length,
            total: this.incomes.length,
            from: this.incomes.length ? 1 : 0,
            to: this.incomes.length,
          }
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    refreshIncomes() {
      if (!this.lastFetchParams)
        return Promise.resolve()
      return this.fetchIncomes(this.lastFetchParams)
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
        .then(async (response) => {
          if (this.lastFetchParams) {
            await this.fetchIncomes(this.lastFetchParams)
          } else {
            this.incomes.push(response.data.data)
          }
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
        .then(async (response) => {
          if (this.lastFetchParams) {
            await this.fetchIncomes(this.lastFetchParams)
          } else {
            const index = this.incomes.findIndex((_i) => _i.id === data.id)
            if (index > -1) {
              this.incomes[index] = response.data.data
            }
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
        .then(async (response) => {
          if (this.lastFetchParams) {
            await this.fetchIncomes(this.lastFetchParams)
          } else {
            let index = this.incomes.findIndex((_c) => _c.id === id)
            this.incomes.splice(index, 1)
          }
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
  },
})
