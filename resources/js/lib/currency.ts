export const SUPPORTED_CURRENCIES = [
  { code: 'USD', label: 'US Dollar' },
  { code: 'EUR', label: 'Euro' },
  { code: 'GBP', label: 'British Pound' },
  { code: 'INR', label: 'Indian Rupee' },
  { code: 'CAD', label: 'Canadian Dollar' },
  { code: 'AUD', label: 'Australian Dollar' },
]

export const DEFAULT_CURRENCY = 'USD'

export const normalizeCurrency = (value?: string | null) => {
  const code = (value || DEFAULT_CURRENCY).toUpperCase()
  return SUPPORTED_CURRENCIES.some((item) => item.code === code)
    ? code
    : DEFAULT_CURRENCY
}

export const getCurrencySymbol = (currency?: string | null) => {
  const code = normalizeCurrency(currency)
  try {
    const part = new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: code,
      currencyDisplay: 'narrowSymbol',
    }).formatToParts(1).find((item) => item.type === 'currency')

    return part?.value || '$'
  } catch (_error) {
    return '$'
  }
}

export const formatCurrencyFromCents = (amount: number | string, currency?: string | null) => {
  const code = normalizeCurrency(currency)
  const value = Number(amount || 0) / 100

  try {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: code,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value)
  } catch (_error) {
    return `$${value.toFixed(2)}`
  }
}
