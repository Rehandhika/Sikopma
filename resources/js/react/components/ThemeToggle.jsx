import React from 'react'
import { Moon, Sun } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { applyTheme, getThemePreference } from '@/react/lib/theme'

export default function ThemeToggle() {
    const [theme, setTheme] = React.useState(() => getThemePreference())

    React.useEffect(() => {
        applyTheme(theme, { save: false })
    }, [theme])

    return (
        <Button
            variant="ghost"
            size="icon"
            className="rounded-xl text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
            aria-label={theme === 'dark' ? 'Aktifkan light mode' : 'Aktifkan dark mode'}
            title={theme === 'dark' ? 'Light mode' : 'Dark mode'}
            onClick={() => {
                const next = theme === 'dark' ? 'light' : 'dark'
                setTheme(next)
                applyTheme(next)
            }}
        >
            <span className="relative h-4 w-4">
                <Sun className="absolute inset-0 h-4 w-4 transition-all duration-300 ease-out dark:scale-0 dark:opacity-0" />
                <Moon className="absolute inset-0 h-4 w-4 scale-0 opacity-0 transition-all duration-300 ease-out dark:scale-100 dark:opacity-100" />
            </span>
        </Button>
    )
}
