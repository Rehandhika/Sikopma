import React from 'react'

import AmbientBackground from '@/react/components/AmbientBackground'
import PublicFooter from '@/react/components/PublicFooter'
import PublicNavbar from '@/react/components/PublicNavbar'

export default function PublicLayout({ children }) {
    return (
        <div className="min-h-screen bg-background text-foreground selection:bg-primary/20 selection:text-foreground transition-colors duration-300 ease-out">
            <AmbientBackground />
            <div className="min-h-screen flex flex-col">
                <PublicNavbar />
                <main className="flex-1">{children}</main>
                <PublicFooter />
            </div>
        </div>
    )
}
