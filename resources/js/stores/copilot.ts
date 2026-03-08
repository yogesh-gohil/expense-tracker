import axios from 'axios'
import { defineStore } from 'pinia'

export const useCopilotStore = defineStore({
  id: 'copilot',
  state: () => ({
    prompt: 'Today I spent ₹800 at Domino’s Pizza',
  }),
  actions: {
    copilot() {
      return new Promise((resolve, reject) => {
        let data = {
          prompt: this.prompt,
        }
        axios.post('/api/copilot', data)
        .then((response) => {
          this.prompt = response.data.prompt
          resolve(response)
        }).catch((e) => {
          reject(e)
        })
      })
    },
  },
})
