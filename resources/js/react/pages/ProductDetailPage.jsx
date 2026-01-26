import React from 'react'
import { ArrowLeft, BadgeCheck, Box, Clock, Headset, MapPin, Package, Check } from 'lucide-react'

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
                Habis
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

function VariantSelector({ variants, selectedVariant, onSelect }) {
    if (!variants || variants.length === 0) return null

    // Group variants by option type (ukuran, warna, etc.)
    const groupedOptions = {}
    variants.forEach(variant => {
        if (variant.option_values) {
            Object.entries(variant.option_values).forEach(([key, val]) => {
                if (!groupedOptions[key]) {
                    groupedOptions[key] = {
                        name: val.option_name || key,
                        values: new Map()
                    }
                }
                groupedOptions[key].values.set(val.value, {
                    value: val.value,
                    variantId: variant.id
                })
            })
        }
    })

    return (
        <div className="space-y-4">
            <h3 className="text-sm font-medium text-muted-foreground uppercase tracking-wider">
                Pilih Varian ({variants.length} tersedia)
            </h3>
            
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                {variants.map((variant) => {
                    const isSelected = selectedVariant?.id === variant.id
                    const isOutOfStock = variant.stock <= 0
                    const optionLabel = variant.option_values 
                        ? Object.values(variant.option_values).map(v => v.value).join(' / ')
                        : variant.variant_name.split(' - ').pop()

                    return (
                        <button
                            key={variant.id}
                            onClick={() => !isOutOfStock && onSelect(variant)}
                            disabled={isOutOfStock}
                            className={`
                                relative p-3 rounded-xl border-2 transition-all duration-200 text-left
                                ${isSelected 
                                    ? 'border-blue-500 bg-blue-500/10 ring-2 ring-blue-500/20' 
                                    : 'border-border/60 bg-background/40 hover:border-blue-500/50 hover:bg-background/60'
                                }
                                ${isOutOfStock ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}
                            `}
                        >
                            {isSelected && (
                                <div className="absolute -top-1.5 -right-1.5 w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                    <Check className="w-3 h-3 text-white" />
                                </div>
                            )}
                            
                            <div className="font-medium text-foreground text-sm truncate">
                                {optionLabel}
                            </div>
                            
                            <div className="mt-1 flex items-center justify-between gap-2">
                                <span className="text-xs font-semibold text-blue-400">
                                    Rp {formatRupiah(variant.price)}
                                </span>
                                {isOutOfStock ? (
                                    <span className="text-[10px] text-red-400 font-medium">Habis</span>
                                ) : variant.stock <= variant.min_stock ? (
                                    <span className="text-[10px] text-orange-400 font-medium">Sisa {variant.stock}</span>
                                ) : (
                                    <span className="text-[10px] text-emerald-400 font-medium">Stok {variant.stock}</span>
                                )}
                            </div>
                        </button>
                    )
                })}
            </div>
        </div>
    )
}

export default function ProductDetailPage({ slug, initialData }) {
    const seededProduct =
        initialData?.product && initialData.product.slug === slug ? initialData.product : null
    const seededAbout = initialData?.about ?? null

    const [product, setProduct] = React.useState(() => seededProduct)
    const [about, setAbout] = React.useState(() => seededAbout)
    const [loading, setLoading] = React.useState(() => !(seededProduct && seededAbout))
    const [selectedVariant, setSelectedVariant] = React.useState(null)

    React.useEffect(() => {
        if (seededProduct && seededAbout) return
        let cancelled = false

        ;(async () => {
            setLoading(true)
            try {
                const [productRes, aboutRes] = await Promise.all([
                    api.get(`/api/publik/produk/${encodeURIComponent(slug)}`),
                    api.get('/api/publik/tentang'),
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
    }, [slug, seededProduct, seededAbout])

    // Get variants array (handle both snake_case and camelCase)
    const variants = product?.active_variants || product?.activeVariants || []

    // Auto-select first available variant
    React.useEffect(() => {
        if (product?.has_variants && variants.length > 0 && !selectedVariant) {
            const firstAvailable = variants.find(v => v.stock > 0) || variants[0]
            setSelectedVariant(firstAvailable)
        }
    }, [product, variants, selectedVariant])

    const address = normalizeContactValue(about?.contact_address)
    const whatsapp = normalizeContactValue(about?.contact_whatsapp)
    const waDigits = whatsapp ? whatsapp.replace(/[^\d]/g, '') : null

    // Determine display values based on variant selection
    const displayPrice = product?.has_variants && selectedVariant 
        ? selectedVariant.price 
        : product?.price
    const displayStock = product?.has_variants && selectedVariant 
        ? selectedVariant.stock 
        : (product?.has_variants ? product?.total_stock : product?.stock)
    const displayMinStock = product?.has_variants && selectedVariant 
        ? selectedVariant.min_stock 
        : product?.min_stock
    const displaySku = product?.has_variants && selectedVariant 
        ? selectedVariant.sku 
        : product?.sku

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
                                        loading="eager"
                                        fetchPriority="high"
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

                                {/* Variant count badge */}
                                {!loading && product?.has_variants && product?.variant_count > 0 && (
                                    <div className="absolute bottom-4 left-4">
                                        <Badge className="bg-blue-500/20 border-blue-500/30 text-blue-300">
                                            <Package className="h-3 w-3 mr-1.5" />
                                            {product.variant_count} Varian
                                        </Badge>
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="lg:col-span-7">
                            <Card className="bg-card/60 backdrop-blur-xl border-border rounded-2xl lg:rounded-3xl shadow-xl relative overflow-hidden">
                                <div className="absolute top-0 right-0 w-64 h-64 bg-blue-500/10 rounded-full blur-[80px] -z-10" />

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
                                        {displaySku ? (
                                            <span className="text-xs font-mono text-muted-foreground">
                                                SKU: {displaySku}
                                            </span>
                                        ) : null}
                                    </div>

                                    <CardTitle className="text-2xl md:text-3xl lg:text-4xl font-bold text-foreground leading-tight">
                                        {loading ? 'Memuat…' : product?.name ?? 'Produk'}
                                    </CardTitle>
                                    
                                    {/* Selected variant name */}
                                    {product?.has_variants && selectedVariant && (
                                        <p className="text-sm text-blue-400 mt-2">
                                            Varian: {selectedVariant.variant_name.split(' - ').pop()}
                                        </p>
                                    )}
                                </CardHeader>

                                <CardContent className="p-5 lg:p-8 pt-0 space-y-6">
                                    {/* Price and Stock Section */}
                                    <div className="bg-background/50 rounded-2xl p-5 lg:p-6 border border-border/60">
                                        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-0">
                                            <div>
                                                <p className="text-sm text-muted-foreground mb-1">
                                                    {product?.has_variants ? 'Harga Varian' : 'Harga Satuan'}
                                                </p>
                                                <div className="text-3xl lg:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">
                                                    {loading ? '—' : `Rp ${formatRupiah(displayPrice)}`}
                                                </div>
                                                {/* Price range for variant products */}
                                                {product?.has_variants && product?.price_range && 
                                                 product.price_range.min !== product.price_range.max && (
                                                    <p className="text-xs text-muted-foreground mt-1">
                                                        Range: Rp {formatRupiah(product.price_range.min)} - Rp {formatRupiah(product.price_range.max)}
                                                    </p>
                                                )}
                                            </div>
                                            <div className="text-left sm:text-right pt-4 sm:pt-0 border-t sm:border-t-0 border-border/60">
                                                <p className="text-sm text-muted-foreground mb-2">Status Stok</p>
                                                {loading ? (
                                                    <Badge variant="outline" className="border-border text-muted-foreground">
                                                        Memuat…
                                                    </Badge>
                                                ) : (
                                                    <StockBadge stock={displayStock} minStock={displayMinStock} />
                                                )}
                                                {/* Total stock for variant products */}
                                                {product?.has_variants && (
                                                    <p className="text-xs text-muted-foreground mt-1">
                                                        Total: {product.total_stock} unit
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    {/* Variant Selector */}
                                    {product?.has_variants && variants.length > 0 && (
                                        <div className="bg-background/30 rounded-2xl p-5 border border-border/40">
                                            <VariantSelector 
                                                variants={variants}
                                                selectedVariant={selectedVariant}
                                                onSelect={setSelectedVariant}
                                            />
                                        </div>
                                    )}

                                    <div className="bg-blue-900/20 border border-blue-500/20 rounded-xl p-4 flex items-start gap-3">
                                        <span className="text-blue-400 mt-0.5">
                                            <BadgeCheck className="h-4 w-4" />
                                        </span>
                                        <p className="text-sm text-blue-700 dark:text-blue-200/90 leading-relaxed">
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


                </div>
            </div>
        </PublicLayout>
    )
}
