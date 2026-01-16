const STORAGE_KEY = 'theme'

const VALID = new Set(['light', 'dark'])

export function getThemePreference() {
    try {
        const stored = window.localStorage.getItem(STORAGE_KEY)
        if (stored && VALID.has(stored)) return stored
    } catch {
    }
    return 'dark'
}

export function applyTheme(preference, { save = true } = {}) {
    const resolved = VALID.has(preference) ? preference : 'dark'
    const root = document.documentElement

    root.classList.toggle('dark', resolved === 'dark')
    root.dataset.theme = resolved
    root.style.colorScheme = resolved

    if (save) {
        try {
            window.localStorage.setItem(STORAGE_KEY, resolved)
        } catch {
        }
    }
}
