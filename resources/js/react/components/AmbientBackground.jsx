import React from 'react'

export default function AmbientBackground() {
    return (
        <div className="fixed inset-0 overflow-hidden pointer-events-none -z-10">
            <div className="absolute top-[-10%] left-1/4 w-[600px] h-[600px] bg-indigo-300/25 dark:bg-indigo-900/10 rounded-full blur-[120px] animate-pulse" />
            <div className="absolute bottom-[-10%] right-1/4 w-[500px] h-[500px] bg-purple-300/20 dark:bg-purple-900/10 rounded-full blur-[100px]" />
            <div className="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-[0.03] dark:opacity-[0.03]" />
        </div>
    )
}
