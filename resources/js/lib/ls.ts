export default {
  get(key: string) {
    return localStorage.getItem(key) ? localStorage.getItem(key) : null
  },

  set(key: string, val: any) {
    localStorage.setItem(key, val)
  },

  remove(key: string) {
    localStorage.removeItem(key)
  },
}
