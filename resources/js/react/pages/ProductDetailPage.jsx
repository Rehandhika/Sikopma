import React from 'react'
import { ArrowLeft, BadgeCheck, Box, Clock, Headset, MapPin } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { api } from '@/react/lib/api'
import { formatRupiah } from '@/react/lib/format'
import PublicLayout from '@/react/components/PublicLayout'

function normalizeContactValue(value) {
    if (!value) return null
    if (value === '-') return null
    return String(value).trim() || null
}

function StockBadge({ stock, minStock }) {
    const s = Number(stock ?? 0)
    const ms = Number(minStock ?? 0)

    if (s <= 0) {
        return (
            <Badge className="bg-red-500/15 text-red-700 dark:text-red-300 border-red-500/30">
                Habis ({s})
            </Badge>
        )
    }

    if (s <= ms) {
        return (
            <Badge className="bg-orange-500/15 text-orange-700 dark:text-orange-300 border-orange-500/30">
                Sisa {s}
            </Badge>
        )
    }

    return (
        <Badge className="bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30">
            Tersedia ({s})
        </Badge>
    )
}

export default function ProductDetailPage({ slug }) {
    const [product, setProduct] = React.useState(null)
    const [about, setAbout] = React.useState(null)
    const [loading, setLoading] = React.useState(true)

    React.useEffect(() => {
        let cancelled = false

        ;(async () => {
            setLoading(true)
            try {
                const [productRes, aboutRes] = await Promise.all([
                    api.get(`/api/public/products/${encodeURIComponent(slug)}`),
                    api.get('/api/public/about'),
                ])
                if (!cancelled) {
                    setProduct(productRes.data?.data ?? null)
                    setAbout(aboutRes.data?.data ?? null)
                }
            } finally {
                if (!cancelled) setLoading(false)
            }
        })()

        return () => {
            cancelled = true
        }
    }, [slug])

    const address = normalizeContactValue(about?.contact_address)
    const whatsapp = normalizeContactValue(about?.contact_whatsapp)
    const waDigits = whatsapp ? whatsapp.replace(/[^\d]/g, '') : null

    return (
        <PublicLayout>
            <div className="pb-20">
                <div className="border-b border-border bg-background/70 backdrop-blur-sm py-4">
                    <div className="max-w-7xl mx-auto px-4">
                        <nav className="flex items-center gap-2 text-sm">
                            <Button
                                asChild
                                variant="ghost"
                                size="sm"
                                className="text-muted-foreground hover:text-foreground"
                            >
                                <a href="/" aria-label="Kembali ke katalog">
                                    <ArrowLeft className="h-4 w-4" />
                                </a>
                            </Button>
                            <span className="text-muted-foreground/50">/</span>
                            <span className="text-muted-foreground">Produk</span>
                            <span className="text-muted-foreground/50">/</span>
                            <span className="text-foreground font-medium truncate max-w-[240px]">
                                {loading ? 'Memuat…' : product?.name ?? 'Produk'}
                            </span>
                        </nav>
                    </div>
                </div>

                <div className="max-w-7xl mx-auto px-4 py-6 lg:py-10">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">
                        <div className="lg:col-span-5 space-y-4 lg:space-y-6">
                            <div className="aspect-square bg-muted rounded-2xl lg:rounded-3xl overflow-hidden border border-border relative group shadow-2xl shadow-black/20">
                                {loading ? (
                                    <div className="w-full h-full bg-muted/40 animate-pulse" />
                                ) : product?.image_large_url ? (
                                    <img
                                        src={product.image_large_url}
                                        alt={product?.name ?? 'Produk'}
                                        className="w-full h-full object-cover"
                                        decoding="async"
                                    />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-muted-foreground">
                                        <Box className="h-14 w-14 opacity-40" />
                                    </div>
                                )}

                                {!loading && product?.is_featured ? (
                                    <div className="absolute top-4 left-4">
                                        <Badge className="bg-yellow-500/20 border-yellow-500/30 text-yellow-300">
                                            <BadgeCheck className="h-4 w-4 mr-2" />
                                            Unggulan
                                        </Badge>
                                    </div>
                                ) : null}
                            </div>
                        </div>

                        <div className="lg:col-span-7">
                            <Card className="bg-card/60 backdrop-blur-xl border-border rounded-2xl lg:rounded-3xl shadow-xl relative overflow-hidden">
                                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[80px] -z-10" />

                                <CardHeader className="p-5 lg:p-8 pb-4">
                                    <div className="flex items-center justify-between gap-4 mb-2">
                                        {product?.category ? (
                                            <Badge
                                                variant="secondary"
                                                className="bg-accent text-primary border-border"
                                            >
                                                {product.category}
                                            </Badge>
                                        ) : (
                                            <span />
                                        )}
                                        {product?.sku ? (
                                            <span className="text-xs font-mono text-muted-foreground">
                                                SKU: {product.sku}
                                            </span>
                                        ) : null}
                                    </div>

                                    <CardTitle className="text-2xl md:text-3xl lg:text-4xl font-bold text-foreground leading-tight">
                                        {loading ? 'Memuat…' : product?.name ?? 'Produk'}
                                    </CardTitle>
                                </CardHeader>

                                <CardContent className="p-5 lg:p-8 pt-0 space-y-6">
                                    <div className="bg-background/50 rounded-2xl p-5 lg:p-6 border border-border/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-0">
                                        <div>
                                            <p className="text-sm text-muted-foreground mb-1">Harga Satuan</p>
                                            <div className="text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">
                                                {loading ? '—' : `Rp ${formatRupiah(product?.price)}`}
                                            </div>
                                        </div>
                                        <div className="text-left sm:text-right pt-4 sm:pt-0 border-t sm:border-t-0 border-border/60">
                                            <p className="text-sm text-muted-foreground mb-2">Status Stok</p>
                                            {loading ? (
                                                <Badge variant="outline" className="border-border text-muted-foreground">
                                                    Memuat…
                                                </Badge>
                                            ) : (
                                                <StockBadge stock={product?.stock} minStock={product?.min_stock} />
                                            )}
                                        </div>
                                    </div>

                                    <div className="bg-indigo-900/20 border border-indigo-500/20 rounded-xl p-4 flex items-start gap-3">
                                        <span className="text-indigo-400 mt-0.5">
                                            <BadgeCheck className="h-4 w-4" />
                                        </span>
                                        <p className="text-sm text-indigo-700 dark:text-indigo-200/90 leading-relaxed">
                                            Pembelian dilakukan secara langsung di Koperasi. Silakan kunjungi kami pada jam operasional.
                                        </p>
                                    </div>

                                    <Button
                                        asChild
                                        variant="outline"
                                        className="w-full h-12 rounded-xl bg-background/60 hover:bg-accent text-foreground border-border"
                                    >
                                        <a href="/">Kembali ke Katalog</a>
                                    </Button>

                                    {product?.description ? (
                                        <>
                                            <Separator className="bg-border" />
                                            <div className="space-y-2">
                                                <h3 className="text-lg font-semibold text-foreground">Deskripsi</h3>
                                                <p className="text-sm text-foreground/90 leading-relaxed">
                                                    {product.description}
                                                </p>
                                            </div>
                                        </>
                                    ) : null}
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                    <div className="mt-8 lg:mt-12 grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-6">
                        <Card className="bg-card/40 border-border/60 rounded-2xl text-center">
                            <CardContent className="p-6 space-y-2">
                                <Clock className="h-6 w-6 text-indigo-400 mx-auto" />
                                <div className="text-foreground font-medium">Jam Operasional</div>
                                <div className="text-sm text-muted-foreground">
                                    Lihat detail di halaman Tentang
                                </div>
                                <Button asChild variant="ghost" className="text-primary">
                                    <a href="/about">Buka Tentang</a>
                                </Button>
                            </CardContent>
                        </Card>

                        <Card className="bg-card/40 border-border/60 rounded-2xl text-center">
                            <CardContent className="p-6 space-y-2">
                                <MapPin className="h-6 w-6 text-indigo-400 mx-auto" />
                                <div className="text-foreground font-medium">Lokasi</div>
                                <div className="text-sm text-muted-foreground">
                                    {address ?? 'Alamat belum tersedia'}
                                </div>
                            </CardContent>
                        </Card>

                        <Card className="bg-card/40 border-border/60 rounded-2xl text-center">
                            <CardContent className="p-6 space-y-2">
                                <Headset className="h-6 w-6 text-indigo-400 mx-auto" />
                                <div className="text-foreground font-medium">Bantuan</div>
                                <div className="text-sm text-muted-foreground">
                                    {whatsapp ? `WhatsApp: ${whatsapp}` : 'Hubungi admin'}
                                </div>
                                {waDigits ? (
                                    <Button asChild variant="ghost" className="text-primary">
                                        <a href={`https://wa.me/${waDigits}`} target="_blank" rel="noreferrer">
                                            Chat WhatsApp
                                        </a>
                                    </Button>
                                ) : null}
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </PublicLayout>
    )
}
