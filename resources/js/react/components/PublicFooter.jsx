import React from 'react'

export default function PublicFooter() {
    return (
        <footer className="mt-auto border-t border-border bg-background/80 backdrop-blur-lg">
            <div className="max-w-7xl mx-auto px-6 py-10">
                <div className="flex flex-col md:flex-row justify-between items-center gap-6">
                    <div className="flex items-center gap-2">
                        <div className="w-2 h-2 bg-indigo-500 rounded-full animate-pulse" />
                        <span className="text-xs text-muted-foreground tracking-widest uppercase">
                            SIKOPMA System
                        </span>
                    </div>

                    <p className="text-xs text-muted-foreground font-mono">
                        {new Date().getFullYear()} © Developed for Students
                    </p>
                </div>
            </div>
        </footer>
    )
}
