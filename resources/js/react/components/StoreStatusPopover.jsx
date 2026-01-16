import React from 'react'

import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Separator } from '@/components/ui/separator'
import { api } from '@/react/lib/api'

export default function StoreStatusPopover() {
    const [status, setStatus] = React.useState(null)
    const [loading, setLoading] = React.useState(true)
    const [error, setError] = React.useState(false)

    const load = React.useCallback(async (signal) => {
        try {
            const res = await api.get('/api/public/store-status', { signal })
            setStatus(res.data?.data ?? null)
            setError(false)
        } catch (e) {
            if (signal?.aborted) return
            setError(true)
        }
    }, [])

    React.useEffect(() => {
        const controller = new AbortController()
        let intervalId
        ;(async () => {
            try {
                await load(controller.signal)
            } finally {
                setLoading(false)
            }
            intervalId = window.setInterval(() => load(controller.signal), 10_000)
        })()
        return () => {
            controller.abort()
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
                        'h-9 rounded-full border-border bg-background/60 hover:bg-accent',
                        error
                            ? 'text-muted-foreground'
                            : isOpen
                              ? 'text-emerald-600 dark:text-emerald-300'
                              : 'text-rose-600 dark:text-red-300',
                    ].join(' ')}
                >
                    <span
                        className={[
                            'inline-flex h-2 w-2 rounded-full',
                            error ? 'bg-muted-foreground/50' : isOpen ? 'bg-emerald-500' : 'bg-rose-500',
                        ].join(' ')}
                    />
                    <span className="text-xs font-mono tracking-widest">
                        {loading ? 'LOADING' : error ? 'OFFLINE' : isOpen ? 'OPEN NOW' : 'CLOSED'}
                    </span>
                </Button>
            </PopoverTrigger>
            <PopoverContent
                className="w-80 bg-popover/95 backdrop-blur-2xl border-border text-popover-foreground"
                sideOffset={12}
            >
                <div className="space-y-4">
                    <div className="space-y-1 text-center">
                        <div className="text-base font-semibold text-foreground">
                            {error ? 'STATUS TIDAK TERSEDIA' : isOpen ? 'KOPERASI BUKA' : 'KOPERASI TUTUP'}
                        </div>
                        <div className="text-xs text-muted-foreground font-mono">
                            {error
                                ? 'Tidak dapat menghubungi server'
                                : isOpen
                                  ? 'Silakan datang bertransaksi'
                                  : 'Kami sedang tidak beroperasi'}
                        </div>
                    </div>

                    <div className="rounded-xl border border-border bg-muted/40 p-4 space-y-3">
                        <div className="space-y-1">
                            <div className="text-[10px] uppercase tracking-wider text-muted-foreground font-bold">
                                Keterangan
                            </div>
                            <div className="text-sm text-foreground leading-snug">
                                {error ? 'Periksa koneksi backend (kopma.test) atau coba refresh.' : reason || '-'}
                            </div>
                        </div>

                        {!isOpen && nextOpenTime ? (
                            <>
                                <Separator className="bg-border" />
                                <div className="space-y-1">
                                    <div className="text-[10px] uppercase tracking-wider text-muted-foreground font-bold">
                                        Buka Kembali
                                    </div>
                                    <div className="text-sm text-foreground">{nextOpenTime}</div>
                                </div>
                            </>
                        ) : null}
                    </div>

                    {isOpen && attendees.length > 0 ? (
                        <div className="space-y-3">
                            <div className="text-[10px] uppercase tracking-wider text-muted-foreground font-bold text-center">
                                Petugas Jaga
                            </div>
                            <div className="flex flex-wrap justify-center gap-2">
                                {attendees.map((name) => (
                                    <div
                                        key={name}
                                        className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-700 dark:text-emerald-300"
                                    >
                                        <div className="w-1.5 h-1.5 bg-emerald-500 rounded-full" />
                                        {name}
                                    </div>
                                ))}
                            </div>
                        </div>
                    ) : null}

                    <div className="text-[10px] text-center text-muted-foreground font-mono border-t border-border pt-3">
                        LIVE SYSTEM STATUS
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    )
}
