import React from 'react'

import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Separator } from '@/components/ui/separator'
import { api } from '@/react/lib/api'

export default function StoreStatusPopover() {
    const [status, setStatus] = React.useState(null)
    const [loading, setLoading] = React.useState(true)

    const load = React.useCallback(async () => {
        const res = await api.get('/api/public/store-status')
        setStatus(res.data?.data ?? null)
    }, [])

    React.useEffect(() => {
        let intervalId
        ;(async () => {
            try {
                await load()
            } finally {
                setLoading(false)
            }
            intervalId = window.setInterval(load, 10_000)
        })()
        return () => {
            if (intervalId) window.clearInterval(intervalId)
        }
    }, [load])

    const isOpen = Boolean(status?.is_open)
    const reason = status?.reason ?? ''
    const attendees = Array.isArray(status?.attendees) ? status.attendees : []
    const nextOpenTime = status?.next_open_time ?? null

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    className={[
                        'h-9 rounded-full border-white/10 bg-white/5 hover:bg-white/10',
                        isOpen ? 'text-green-300' : 'text-red-300',
                    ].join(' ')}
                >
                    <span
                        className={[
                            'inline-flex h-2 w-2 rounded-full',
                            isOpen ? 'bg-green-400' : 'bg-red-400',
                        ].join(' ')}
                    />
                    <span className="text-xs font-mono tracking-widest">
                        {loading ? 'LOADING' : isOpen ? 'OPEN NOW' : 'CLOSED'}
                    </span>
                </Button>
            </PopoverTrigger>
            <PopoverContent
                className="w-80 bg-slate-900/95 backdrop-blur-2xl border-white/10 text-slate-200"
                sideOffset={12}
            >
                <div className="space-y-4">
                    <div className="space-y-1 text-center">
                        <div className="text-base font-semibold text-white">
                            {isOpen ? 'KOPERASI BUKA' : 'KOPERASI TUTUP'}
                        </div>
                        <div className="text-xs text-slate-400 font-mono">
                            {isOpen ? 'Silakan datang bertransaksi' : 'Kami sedang tidak beroperasi'}
                        </div>
                    </div>

                    <div className="rounded-xl border border-white/10 bg-white/5 p-4 space-y-3">
                        <div className="space-y-1">
                            <div className="text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                Keterangan
                            </div>
                            <div className="text-sm text-slate-200 leading-snug">
                                {reason || '-'}
                            </div>
                        </div>

                        {!isOpen && nextOpenTime ? (
                            <>
                                <Separator className="bg-white/10" />
                                <div className="space-y-1">
                                    <div className="text-[10px] uppercase tracking-wider text-slate-500 font-bold">
                                        Buka Kembali
                                    </div>
                                    <div className="text-sm text-slate-200">{nextOpenTime}</div>
                                </div>
                            </>
                        ) : null}
                    </div>

                    {isOpen && attendees.length > 0 ? (
                        <div className="space-y-3">
                            <div className="text-[10px] uppercase tracking-wider text-slate-500 font-bold text-center">
                                Petugas Jaga
                            </div>
                            <div className="flex flex-wrap justify-center gap-2">
                                {attendees.map((name) => (
                                    <div
                                        key={name}
                                        className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-green-500/10 border border-green-500/20 text-xs text-green-300"
                                    >
                                        <div className="w-1.5 h-1.5 bg-green-400 rounded-full" />
                                        {name}
                                    </div>
                                ))}
                            </div>
                        </div>
                    ) : null}

                    <div className="text-[10px] text-center text-slate-600 font-mono border-t border-white/10 pt-3">
                        LIVE SYSTEM STATUS
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    )
}

