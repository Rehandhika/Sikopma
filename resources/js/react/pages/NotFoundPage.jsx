import React from 'react'

import PublicLayout from '@/react/components/PublicLayout'
import { Button } from '@/components/ui/button'

export default function NotFoundPage() {
    return (
        <PublicLayout>
            <div className="max-w-7xl mx-auto px-4 py-20">
                <div className="max-w-xl mx-auto text-center space-y-4">
                    <h1 className="text-3xl font-semibold text-white">Halaman Tidak Ditemukan</h1>
                    <p className="text-slate-400">
                        Link yang Anda buka tidak tersedia. Kembali ke katalog untuk melanjutkan.
                    </p>
                    <Button asChild className="rounded-xl">
                        <a href="/">Kembali ke Katalog</a>
                    </Button>
                </div>
            </div>
        </PublicLayout>
    )
}

