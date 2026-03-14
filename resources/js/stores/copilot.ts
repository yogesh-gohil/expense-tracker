import axios from 'axios'
import { defineStore } from 'pinia'

export const useCopilotStore = defineStore({
  id: 'copilot',
  state: () => ({
    prompt: '',
    result: null,
    isLoading: false,
    error: null,
    abortController: null as AbortController | null,
  }),
  actions: {
    reset() {
      this.result = null
      this.error = null
    },
    stop() {
      if (this.abortController) {
        this.abortController.abort()
      }
      this.isLoading = false
      this.abortController = null
    },
    copilot() {
      return new Promise((resolve, reject) => {
        const prompt = this.prompt?.trim()
        if (!prompt) {
          this.error = 'Please enter a message first.'
          return reject(new Error('Prompt is required'))
        }
        this.isLoading = true
        this.error = null
        this.abortController = new AbortController()
        let data = {
          prompt,
        }
        axios.post('/api/copilot', data, { signal: this.abortController.signal })
        .then((response) => {
          this.result = response.data.data
          resolve(response)
        }).catch((e) => {
          if (e?.name === 'CanceledError' || e?.code === 'ERR_CANCELED') {
            this.error = null
            return reject(e)
          }
          this.error = 'Could not analyze that message. Please try again.'
          reject(e)
        }).finally(() => {
          this.isLoading = false
          this.abortController = null
        })
      })
    },
  },
})
