import React from 'react'
import { Menu } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
import StoreStatusPopover from '@/react/components/StoreStatusPopover'
import ThemeToggle from '@/react/components/ThemeToggle'

function useActivePath() {
    const [path, setPath] = React.useState(() => window.location.pathname)

    React.useEffect(() => {
        const onPopState = () => setPath(window.location.pathname)
        window.addEventListener('popstate', onPopState)
        return () => window.removeEventListener('popstate', onPopState)
    }, [])

    return path
}

export default function PublicNavbar() {
    const path = useActivePath()
    const isAbout = path === '/about'

    return (
        <div className="sticky top-6 z-50 px-4 mb-8">
            <div className="max-w-7xl mx-auto">
                <div className="rounded-2xl border border-border bg-background/70 backdrop-blur-xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] px-4 md:px-6 py-3 md:py-4 flex items-center justify-between">
                    <a href="/" className="flex items-center gap-3 group w-1/3">
                        <div className="relative w-10 h-10 flex items-center justify-center">
                            <div className="absolute inset-0 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-xl rotate-3 transition-transform group-hover:rotate-6" />
                            <div className="absolute inset-0 bg-background rounded-xl rotate-3 scale-[0.9]" />
                            <span className="relative font-semibold text-xl text-foreground">S</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="font-semibold text-lg text-foreground tracking-tight leading-none group-hover:text-primary transition-colors">
                                SIKOPMA
                            </span>
                            <span className="text-[10px] uppercase tracking-[0.2em] text-muted-foreground hidden sm:block">
                                Future Store
                            </span>
                        </div>
                    </a>

                    <div className="hidden md:flex justify-center w-1/3">
                        <StoreStatusPopover />
                    </div>

                    <div className="hidden md:flex items-center justify-end gap-2 w-1/3">
                        <Button
                            asChild
                            variant="ghost"
                            className={[
                                isAbout ? '' : 'bg-accent text-accent-foreground',
                                'text-muted-foreground hover:text-foreground',
                            ].join(' ')}
                        >
                            <a href="/">Katalog</a>
                        </Button>
                        <Button
                            asChild
                            variant="ghost"
                            className={[
                                isAbout ? 'bg-accent text-accent-foreground' : '',
                                'text-muted-foreground hover:text-foreground',
                            ].join(' ')}
                        >
                            <a href="/about">Tentang</a>
                        </Button>
                        <ThemeToggle />
                        <Button
                            asChild
                            className="rounded-xl bg-indigo-600/15 hover:bg-indigo-600/25 text-indigo-700 dark:text-indigo-200 hover:text-indigo-900 dark:hover:text-white border border-indigo-500/30"
                            variant="outline"
                        >
                            <a href="/admin/login">Login</a>
                        </Button>
                    </div>

                    <div className="flex md:hidden items-center gap-3">
                        <div className="scale-90">
                            <StoreStatusPopover />
                        </div>
                        <ThemeToggle />
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    className="border-border bg-background/60 hover:bg-accent"
                                >
                                    <Menu />
                                </Button>
                            </SheetTrigger>
                            <SheetContent className="bg-background border-border text-foreground">
                                <SheetHeader>
                                    <SheetTitle className="text-foreground">Menu</SheetTitle>
                                </SheetHeader>
                                <div className="mt-6 flex flex-col gap-2">
                                    <Button asChild variant="secondary" className="justify-start">
                                        <a href="/">Katalog</a>
                                    </Button>
                                    <Button asChild variant="secondary" className="justify-start">
                                        <a href="/about">Tentang</a>
                                    </Button>
                                    <Button asChild className="justify-start">
                                        <a href="/admin/login">Login System</a>
                                    </Button>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </div>
        </div>
    )
}
