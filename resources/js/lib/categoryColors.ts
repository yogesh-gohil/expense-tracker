const tones = [
  {
    dot: 'bg-emerald-500',
    text: 'text-emerald-700 dark:text-emerald-300',
    border: 'border-emerald-200 dark:border-emerald-900/60',
  },
  {
    dot: 'bg-sky-500',
    text: 'text-sky-700 dark:text-sky-300',
    border: 'border-sky-200 dark:border-sky-900/60',
  },
  {
    dot: 'bg-amber-500',
    text: 'text-amber-700 dark:text-amber-300',
    border: 'border-amber-200 dark:border-amber-900/60',
  },
  {
    dot: 'bg-rose-500',
    text: 'text-rose-700 dark:text-rose-300',
    border: 'border-rose-200 dark:border-rose-900/60',
  },
  {
    dot: 'bg-violet-500',
    text: 'text-violet-700 dark:text-violet-300',
    border: 'border-violet-200 dark:border-violet-900/60',
  },
  {
    dot: 'bg-cyan-500',
    text: 'text-cyan-700 dark:text-cyan-300',
    border: 'border-cyan-200 dark:border-cyan-900/60',
  },
  {
    dot: 'bg-lime-500',
    text: 'text-lime-700 dark:text-lime-300',
    border: 'border-lime-200 dark:border-lime-900/60',
  },
  {
    dot: 'bg-indigo-500',
    text: 'text-indigo-700 dark:text-indigo-300',
    border: 'border-indigo-200 dark:border-indigo-900/60',
  },
]

const neutralTone = {
  dot: 'bg-muted-foreground/40',
  text: 'text-muted-foreground',
  border: 'border-border',
}

const hashName = (value?: string | null) => {
  if (!value) return 0
  let hash = 0
  for (let i = 0; i < value.length; i += 1) {
    hash = (hash + value.charCodeAt(i) * (i + 1)) % 997
  }
  return hash
}

export const getCategoryTone = (name?: string | null) => {
  if (!name) return neutralTone
  const index = hashName(name) % tones.length
  return tones[index]
}
