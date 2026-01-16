import React from 'react'
import { Menu } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet'
import StoreStatusPopover from '@/react/components/StoreStatusPopover'

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
                <div className="rounded-2xl border border-white/10 bg-slate-950/60 backdrop-blur-xl shadow-[0_4px_30px_rgba(0,0,0,0.1)] px-4 md:px-6 py-3 md:py-4 flex items-center justify-between">
                    <a href="/" className="flex items-center gap-3 group w-1/3">
                        <div className="relative w-10 h-10 flex items-center justify-center">
                            <div className="absolute inset-0 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-xl rotate-3 transition-transform group-hover:rotate-6" />
                            <div className="absolute inset-0 bg-slate-950 rounded-xl rotate-3 scale-[0.9]" />
                            <span className="relative font-semibold text-xl text-white">S</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="font-semibold text-lg text-white tracking-tight leading-none group-hover:text-indigo-300 transition-colors">
                                SIKOPMA
                            </span>
                            <span className="text-[10px] uppercase tracking-[0.2em] text-slate-500 hidden sm:block">
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
                                'text-slate-300 hover:text-white',
                                isAbout ? '' : 'bg-white/5',
                            ].join(' ')}
                        >
                            <a href="/">Katalog</a>
                        </Button>
                        <Button
                            asChild
                            variant="ghost"
                            className={[
                                'text-slate-300 hover:text-white',
                                isAbout ? 'bg-white/5' : '',
                            ].join(' ')}
                        >
                            <a href="/about">Tentang</a>
                        </Button>
                        <Button
                            asChild
                            className="rounded-xl bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-200 hover:text-white border border-indigo-500/30"
                            variant="outline"
                        >
                            <a href="/admin/login">Login</a>
                        </Button>
                    </div>

                    <div className="flex md:hidden items-center gap-3">
                        <div className="scale-90">
                            <StoreStatusPopover />
                        </div>
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    className="border-white/10 bg-white/5 hover:bg-white/10"
                                >
                                    <Menu />
                                </Button>
                            </SheetTrigger>
                            <SheetContent className="bg-slate-950 border-white/10 text-slate-200">
                                <SheetHeader>
                                    <SheetTitle className="text-white">Menu</SheetTitle>
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

