import axios from 'axios'
import { defineStore } from 'pinia'

export const useExpenseStore = defineStore({
  id: 'expense',
  state: () => ({
    expenses: [],
    monthlySummary: null,
    showExpenseModal: false,
    expenseData: {
      title: '',
      description: '',
      amount: 0,
      date: '',
      category_id: '',
    },

  }),
  actions: {
    resetExpenseData() {
      this.showExpenseModal = false
      this.expenseData = {
        title: '',
        description: '',
        amount: 0,
        date: '',
        category_id: '',
      }
    },
    fetchExpenses(data: any) {
      return new Promise((resolve, reject) => {
        axios.get('/api/expenses', { params: data })
        .then((response) => {
          this.expenses = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    fetchMonthlySummary(params: any = {}) {
      return new Promise((resolve, reject) => {
        axios.get('/api/expenses/monthly-summary', { params })
        .then((response) => {
          this.monthlySummary = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    fetchExpense(id: any) {
      return new Promise((resolve, reject) => {
        axios.post(`/api/expenses/${id}`)
        .then((response) => {
          this.expenses.push(response.data.data)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    addExpense(data: any) {
      return new Promise((resolve, reject) => {
        axios.post('/api/expenses', data)
        .then((response) => {
          this.expenses.push(response.data.data)
          this.resetExpenseData()
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    updateExpense(data: any) {
      return new Promise((resolve, reject) => {
        axios.put(`/api/expenses/${data.id}`, data)
        .then((response) => {
          let index = this.expenses.findIndex((_e) => _e.id === data.id)
          this.expenses[index] = response.data.data
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
    removeExpense(id: any) {
      return new Promise((resolve, reject) => {
        axios.delete(`/api/expenses/${id}`)
        .then((response) => {
          let index = this.expenses.findIndex((_c) => _c.id === id)
          this.expenses.splice(index, 1)
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
  },
})
