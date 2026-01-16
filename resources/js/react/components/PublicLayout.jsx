import React from 'react'

import AmbientBackground from '@/react/components/AmbientBackground'
import PublicFooter from '@/react/components/PublicFooter'
import PublicNavbar from '@/react/components/PublicNavbar'

export default function PublicLayout({ children }) {
    return (
        <div className="min-h-screen bg-slate-950 text-slate-300 selection:bg-indigo-500/30 selection:text-indigo-200">
            <AmbientBackground />
            <div className="min-h-screen flex flex-col">
                <PublicNavbar />
                <main className="flex-1">{children}</main>
                <PublicFooter />
            </div>
        </div>
    )
}

