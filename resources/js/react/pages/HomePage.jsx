import React from 'react'
import { Search } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import {
    Card,
    CardContent,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
    Pagination,
    PaginationContent,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Skeleton } from '@/components/ui/skeleton'

import PublicLayout from '@/react/components/PublicLayout'
import useDebouncedValue from '@/react/hooks/useDebouncedValue'
import { api } from '@/react/lib/api'
import { formatRupiah } from '@/react/lib/format'

const BannerCarousel = React.lazy(() => import('@/react/components/BannerCarousel'))

function buildPageList(currentPage, lastPage) {
    const pages = []
    const clampedCurrent = Math.max(1, Math.min(lastPage, currentPage))

    pages.push(1)

    const windowStart = Math.max(2, clampedCurrent - 1)
    const windowEnd = Math.min(lastPage - 1, clampedCurrent + 1)

    if (windowStart > 2) pages.push('…')
    for (let p = windowStart; p <= windowEnd; p += 1) pages.push(p)
    if (windowEnd < lastPage - 1) pages.push('…')

    if (lastPage > 1) pages.push(lastPage)

    return pages
}

function BannerSection({ initialBanners }) {
    const [banners, setBanners] = React.useState(() => (Array.isArray(initialBanners) ? initialBanners : []))
    const [loading, setLoading] = React.useState(() => !Array.isArray(initialBanners))

    React.useEffect(() => {
        if (Array.isArray(initialBanners)) return
        ;(async () => {
            try {
                const res = await api.get('/api/public/banners')
                setBanners(res.data?.data ?? [])
            } finally {
                setLoading(false)
            }
        })()
    }, [])

    if (loading) {
        return (
            <div className="w-full relative">
                <div className="aspect-[16/9] w-full">
                    <Skeleton className="h-full w-full rounded-none" />
                </div>
            </div>
        )
    }

    if (!banners.length) return null

    const slides = banners.map((banner) => ({
        id: banner.id,
        title: banner.title || null,
        alt: banner.title || 'Banner promosi SIKOPMA',
        href: null,
        images: {
            default: banner.images?.default ?? '',
            '1920': banner.images?.['1920'],
            '768': banner.images?.['768'],
            '480': banner.images?.['480'],
        },
    }))

    return (
        <div className="w-full relative">
            <div className="absolute inset-0 bg-gradient-to-b from-background/0 to-background pointer-events-none z-10" />
            <div className="max-w-7xl mx-auto px-4">
                <React.Suspense
                    fallback={
                        <div className="overflow-hidden rounded-2xl border border-border bg-card/20">
                            <div className="aspect-[16/9] w-full">
                                <Skeleton className="h-full w-full rounded-none" />
                            </div>
                        </div>
                    }
                >
                    <BannerCarousel
                        slides={slides}
                        loop={slides.length > 1}
                        autoplayIntervalMs={5000}
                        transitionDuration={40}
                        showArrows={slides.length > 1}
                        showDots={slides.length > 1}
                    />
                </React.Suspense>
            </div>
        </div>
    )
}

function ProductsSection({ initialCategories, initialProducts }) {
    const [search, setSearch] = React.useState('')
    const debouncedSearch = useDebouncedValue(search, 350)
    const [category, setCategory] = React.useState('')

    const [categories, setCategories] = React.useState(() =>
        Array.isArray(initialCategories) ? initialCategories : [],
    )
    const [products, setProducts] = React.useState(() => initialProducts ?? null)
    const [loading, setLoading] = React.useState(() => !initialProducts)
    const [page, setPage] = React.useState(1)
    const initialAppliedRef = React.useRef(false)

    React.useEffect(() => {
        if (Array.isArray(initialCategories)) return
        ;(async () => {
            const res = await api.get('/api/public/categories')
            setCategories(res.data?.data ?? [])
        })()
    }, [initialCategories])

    React.useEffect(() => {
        setPage(1)
    }, [debouncedSearch, category])

    React.useEffect(() => {
        let cancelled = false

        if (
            !initialAppliedRef.current &&
            initialProducts &&
            !debouncedSearch &&
            !category &&
            page === 1
        ) {
            initialAppliedRef.current = true
            setLoading(false)
            return () => {
                cancelled = true
            }
        }

        ;(async () => {
            setLoading(true)
            try {
                const res = await api.get('/api/public/products', {
                    params: {
                        search: debouncedSearch || undefined,
                        category: category || undefined,
                        page,
                        per_page: 12,
                    },
                })
                if (!cancelled) {
                    setProducts(res.data ?? null)
                }
            } finally {
                if (!cancelled) setLoading(false)
            }
        })()

        return () => {
            cancelled = true
        }
    }, [debouncedSearch, category, page, initialProducts])

    const items = Array.isArray(products?.data) ? products.data : []
    const currentPage = Number(products?.current_page ?? page)
    const lastPage = Number(products?.last_page ?? 1)

    return (
        <div className="max-w-7xl mx-auto px-4 py-12 relative z-20">
            <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-foreground tracking-tight mb-2">
                        Katalog Produk
                    </h1>
                    <p className="text-muted-foreground">
                        Temukan kebutuhan harianmu dengan harga terbaik.
                    </p>
                </div>
            </div>

            <div className="bg-card/60 backdrop-blur-md border border-border rounded-2xl p-4 mb-10 shadow-xl">
                <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div className="md:col-span-8 relative">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-foreground">
                            <Search className="h-4 w-4" />
                        </div>
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari produk (nama, SKU)..."
                            className="h-[52px] pl-10 bg-background/60 border-border text-foreground placeholder:text-muted-foreground/70 focus-visible:ring-ring/40"
                        />
                    </div>

                    <div className="md:col-span-4">
                        <Select
                            value={category ? category : 'all'}
                            onValueChange={(v) => setCategory(v === 'all' ? '' : v)}
                        >
                            <SelectTrigger className="h-[52px] bg-background/60 border-border text-foreground focus:ring-ring/40">
                                <SelectValue placeholder="Semua Kategori" />
                            </SelectTrigger>
                            <SelectContent className="bg-popover border-border">
                                <SelectItem value="all">Semua Kategori</SelectItem>
                                {categories.map((c) => (
                                    <SelectItem key={c} value={c}>
                                        {c}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            {loading ? (
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                    {Array.from({ length: 12 }).map((_, idx) => (
                        <Card
                            key={idx}
                            className="overflow-hidden bg-card/40 border-border/60 shadow-none"
                        >
                            <div className="aspect-square">
                                <Skeleton className="h-full w-full rounded-none" />
                            </div>
                            <CardContent className="p-5 space-y-3">
                                <Skeleton className="h-3 w-20" />
                                <Skeleton className="h-4 w-full" />
                                <Skeleton className="h-4 w-3/4" />
                                <Skeleton className="h-6 w-1/2" />
                            </CardContent>
                        </Card>
                    ))}
                </div>
            ) : items.length ? (
                <>
                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                        {items.map((p) => {
                            const stock = Number(p.stock ?? 0)
                            const minStock = Number(p.min_stock ?? 0)
                            const isOut = stock <= 0
                            const isLow = !isOut && stock <= minStock

                            return (
                                <a
                                    key={p.id}
                                    href={`/products/${p.slug}`}
                                    className="group relative overflow-hidden bg-card/50 border border-border/60 rounded-xl shadow-none transition-all duration-300 hover:bg-card/70 hover:border-indigo-500/30 hover:shadow-[0_0_20px_rgba(99,102,241,0.15)] hover:-translate-y-1 block cursor-pointer"
                                >
                                    <div className="aspect-square relative overflow-hidden bg-muted rounded-t-xl">
                                        {p.image_medium_url ? (
                                            <img
                                                src={p.image_thumbnail_url || p.image_medium_url}
                                                alt={p.name}
                                                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 opacity-90 group-hover:opacity-100"
                                                loading="lazy"
                                                fetchPriority="low"
                                                sizes="(min-width: 1024px) 25vw, 50vw"
                                                decoding="async"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-muted-foreground bg-muted/60">
                                                <div className="text-4xl opacity-50">□</div>
                                            </div>
                                        )}

                                        <div className="absolute top-3 right-3 flex flex-col gap-2 items-end">
                                            {p.is_featured ? (
                                                <Badge className="bg-yellow-500/20 text-yellow-300 border-yellow-500/30">
                                                    Featured
                                                </Badge>
                                            ) : null}
                                        </div>

                                        <div className="absolute bottom-3 left-3">
                                            {isOut ? (
                                                <Badge className="bg-red-500/90 text-white border-transparent">
                                                    Habis
                                                </Badge>
                                            ) : isLow ? (
                                                <Badge className="bg-orange-500/90 text-white border-transparent">
                                                    Sisa {stock}
                                                </Badge>
                                            ) : null}
                                        </div>
                                    </div>

                                    <div className="p-5 pb-3">
                                        {p.category ? (
                                            <p className="text-[10px] text-indigo-400 uppercase tracking-widest font-semibold mb-1">
                                                {p.category}
                                            </p>
                                        ) : null}
                                        <h3 className="text-sm md:text-base font-medium text-card-foreground line-clamp-2 min-h-[2.5rem] group-hover:text-primary transition-colors">
                                            {p.name}
                                        </h3>
                                    </div>

                                    <div className="px-5 pb-5 pt-0">
                                        <div className="flex items-center space-x-2 mb-3 text-xs">
                                            <span className="text-muted-foreground">
                                                Stok:{' '}
                                                <span className="text-foreground font-medium">
                                                    {stock}
                                                </span>
                                            </span>
                                        </div>
                                        <div className="flex flex-col">
                                            <span className="text-xs text-muted-foreground mb-1">
                                                Harga
                                            </span>
                                            <span className="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-foreground to-muted-foreground group-hover:from-indigo-500 group-hover:to-cyan-500 transition-all duration-300">
                                                Rp {formatRupiah(p.price)}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            )
                        })}
                    </div>

                    <div className="mt-8 px-4 py-3 bg-card/50 rounded-xl border border-border/60">
                        <Pagination>
                            <PaginationContent>
                                <PaginationItem>
                                    <PaginationPrevious
                                        href="#"
                                        onClick={(e) => {
                                            e.preventDefault()
                                            setPage((p) => Math.max(1, p - 1))
                                        }}
                                        className={[
                                            'text-foreground',
                                            currentPage <= 1 ? 'pointer-events-none opacity-50' : '',
                                        ].join(' ')}
                                    />
                                </PaginationItem>

                                {buildPageList(currentPage, lastPage).map((p, idx) => (
                                    <PaginationItem key={`${p}-${idx}`}>
                                        {p === '…' ? (
                                            <span className="px-2 text-muted-foreground">…</span>
                                        ) : (
                                            <PaginationLink
                                                href="#"
                                                isActive={p === currentPage}
                                                onClick={(e) => {
                                                    e.preventDefault()
                                                    setPage(p)
                                                }}
                                                className={[
                                                    'border-border',
                                                    p === currentPage
                                                        ? 'bg-accent text-accent-foreground'
                                                        : 'text-muted-foreground',
                                                ].join(' ')}
                                            >
                                                {p}
                                            </PaginationLink>
                                        )}
                                    </PaginationItem>
                                ))}

                                <PaginationItem>
                                    <PaginationNext
                                        href="#"
                                        onClick={(e) => {
                                            e.preventDefault()
                                            setPage((p) => Math.min(lastPage, p + 1))
                                        }}
                                        className={[
                                            'text-foreground',
                                            currentPage >= lastPage ? 'pointer-events-none opacity-50' : '',
                                        ].join(' ')}
                                    />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                </>
            ) : (
                <div className="bg-card/40 rounded-3xl border border-dashed border-border p-20 text-center backdrop-blur-sm">
                    <div className="w-20 h-20 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-6 text-muted-foreground">
                        <Search className="h-8 w-8" />
                    </div>
                    <h3 className="text-xl font-semibold text-foreground mb-2">Tidak Ada Produk</h3>
                    <p className="text-muted-foreground">
                        Coba ubah kata kunci pencarian atau kategori.
                    </p>
                </div>
            )}
        </div>
    )
}

export default function HomePage({ initialData }) {
    const initialBanners = initialData?.banners
    const initialCategories = initialData?.categories
    const initialProducts = initialData?.products

    return (
        <PublicLayout>
            <BannerSection initialBanners={initialBanners} />
            <ProductsSection initialCategories={initialCategories} initialProducts={initialProducts} />
        </PublicLayout>
    )
}

