import React from 'react'
import { ArrowRight, Search } from 'lucide-react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel'
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

function BannerSection() {
    const [banners, setBanners] = React.useState([])
    const [loading, setLoading] = React.useState(true)

    React.useEffect(() => {
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
                <div className="aspect-[16/5] w-full">
                    <Skeleton className="h-full w-full rounded-none" />
                </div>
            </div>
        )
    }

    if (!banners.length) return null

    return (
        <div className="w-full relative group">
            <div className="absolute inset-0 bg-gradient-to-b from-slate-950/0 to-slate-950 pointer-events-none z-10" />
            <div className="max-w-7xl mx-auto px-4">
                <Carousel opts={{ loop: banners.length > 1 }} className="w-full">
                    <CarouselContent>
                        {banners.map((banner) => (
                            <CarouselItem key={banner.id}>
                                <div className="relative overflow-hidden rounded-2xl border border-white/10 bg-slate-900/20">
                                    <div className="aspect-[16/5] w-full">
                                        <picture>
                                            {banner.images?.['1920'] ? (
                                                <source
                                                    media="(min-width: 1024px)"
                                                    srcSet={banner.images['1920']}
                                                />
                                            ) : null}
                                            {banner.images?.['768'] ? (
                                                <source
                                                    media="(min-width: 640px)"
                                                    srcSet={banner.images['768']}
                                                />
                                            ) : null}
                                            {banner.images?.['480'] ? (
                                                <source media="(max-width: 639px)" srcSet={banner.images['480']} />
                                            ) : null}
                                            <img
                                                src={banner.images?.default ?? ''}
                                                alt={banner.title || 'Banner promosi SIKOPMA'}
                                                className="w-full h-full object-cover"
                                                loading="lazy"
                                                decoding="async"
                                                draggable={false}
                                            />
                                        </picture>
                                    </div>
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent" />
                                    {banner.title ? (
                                        <div className="absolute bottom-0 left-0 right-0 p-4 md:p-8">
                                            <div className="max-w-7xl mx-auto">
                                                <h3 className="text-white text-lg md:text-2xl lg:text-3xl font-bold drop-shadow-lg">
                                                    {banner.title}
                                                </h3>
                                            </div>
                                        </div>
                                    ) : null}
                                </div>
                            </CarouselItem>
                        ))}
                    </CarouselContent>
                    {banners.length > 1 ? (
                        <>
                            <CarouselPrevious className="left-6 bg-white/90 hover:bg-white text-slate-900 border-0 opacity-0 group-hover:opacity-100 transition-opacity" />
                            <CarouselNext className="right-6 bg-white/90 hover:bg-white text-slate-900 border-0 opacity-0 group-hover:opacity-100 transition-opacity" />
                        </>
                    ) : null}
                </Carousel>
            </div>
        </div>
    )
}

function ProductsSection() {
    const [search, setSearch] = React.useState('')
    const debouncedSearch = useDebouncedValue(search, 350)
    const [category, setCategory] = React.useState('')

    const [categories, setCategories] = React.useState([])
    const [products, setProducts] = React.useState(null)
    const [loading, setLoading] = React.useState(true)
    const [page, setPage] = React.useState(1)

    React.useEffect(() => {
        ;(async () => {
            const res = await api.get('/api/public/categories')
            setCategories(res.data?.data ?? [])
        })()
    }, [])

    React.useEffect(() => {
        setPage(1)
    }, [debouncedSearch, category])

    React.useEffect(() => {
        let cancelled = false

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
    }, [debouncedSearch, category, page])

    const items = Array.isArray(products?.data) ? products.data : []
    const currentPage = Number(products?.current_page ?? page)
    const lastPage = Number(products?.last_page ?? 1)

    return (
        <div className="max-w-7xl mx-auto px-4 py-12 relative z-20">
            <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <h1 className="text-3xl font-bold text-white tracking-tight mb-2">
                        Katalog Produk
                    </h1>
                    <p className="text-slate-400">
                        Temukan kebutuhan harianmu dengan harga terbaik.
                    </p>
                </div>
            </div>

            <div className="bg-slate-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-4 mb-10 shadow-xl">
                <div className="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div className="md:col-span-8 relative">
                        <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <Search className="h-4 w-4" />
                        </div>
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Cari produk (nama, SKU)..."
                            className="h-[52px] pl-10 bg-slate-950/50 border-white/10 text-white placeholder:text-slate-600 focus-visible:ring-indigo-500/40"
                        />
                    </div>

                    <div className="md:col-span-4">
                        <Select
                            value={category ? category : 'all'}
                            onValueChange={(v) => setCategory(v === 'all' ? '' : v)}
                        >
                            <SelectTrigger className="h-[52px] bg-slate-950/50 border-white/10 text-slate-200 focus:ring-indigo-500/40">
                                <SelectValue placeholder="Semua Kategori" />
                            </SelectTrigger>
                            <SelectContent className="bg-slate-900/95 border-white/10">
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
                            className="overflow-hidden bg-slate-900/40 border-white/5 shadow-none"
                        >
                            <div className="aspect-square">
                                <Skeleton className="h-full w-full rounded-none" />
                            </div>
                            <CardContent className="p-5 space-y-3">
                                <Skeleton className="h-3 w-20 bg-white/10" />
                                <Skeleton className="h-4 w-full bg-white/10" />
                                <Skeleton className="h-4 w-3/4 bg-white/10" />
                                <Skeleton className="h-6 w-1/2 bg-white/10" />
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
                                <Card
                                    key={p.id}
                                    className="group relative overflow-hidden bg-slate-900/40 border-white/5 shadow-none transition-all duration-300 hover:bg-slate-800/60 hover:border-indigo-500/30 hover:shadow-[0_0_20px_rgba(99,102,241,0.15)] hover:-translate-y-1"
                                >
                                    <div className="aspect-square relative overflow-hidden bg-slate-800">
                                        {p.image_medium_url ? (
                                            <img
                                                src={p.image_medium_url}
                                                alt={p.name}
                                                className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 opacity-90 group-hover:opacity-100"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-slate-600 bg-slate-800/50">
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

                                    <CardHeader className="p-5 pb-3">
                                        {p.category ? (
                                            <CardDescription className="text-[10px] text-indigo-400 uppercase tracking-widest font-semibold">
                                                {p.category}
                                            </CardDescription>
                                        ) : null}
                                        <CardTitle className="text-sm md:text-base font-medium text-slate-100 line-clamp-2 min-h-[2.5rem] group-hover:text-indigo-300 transition-colors">
                                            {p.name}
                                        </CardTitle>
                                    </CardHeader>

                                    <CardContent className="px-5 pb-5 pt-0">
                                        <div className="flex items-center space-x-2 mb-3 text-xs">
                                            <span className="text-slate-500">
                                                Stok:{' '}
                                                <span className="text-slate-300 font-medium">
                                                    {stock}
                                                </span>
                                            </span>
                                        </div>
                                        <div className="flex items-end justify-between">
                                            <div className="flex flex-col">
                                                <span className="text-xs text-slate-500 mb-1">
                                                    Harga
                                                </span>
                                                <span className="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 group-hover:from-indigo-400 group-hover:to-cyan-400 transition-all duration-300">
                                                    Rp {formatRupiah(p.price)}
                                                </span>
                                            </div>
                                            <Button
                                                asChild
                                                size="icon"
                                                variant="outline"
                                                className="h-9 w-9 rounded-full bg-white/5 hover:bg-indigo-600 text-slate-400 hover:text-white border-white/10"
                                            >
                                                <a href={`/products/${p.slug}`} aria-label={`Lihat ${p.name}`}>
                                                    <ArrowRight className="h-4 w-4" />
                                                </a>
                                            </Button>
                                        </div>
                                    </CardContent>

                                    <CardFooter className="hidden" />
                                </Card>
                            )
                        })}
                    </div>

                    <div className="mt-8 px-4 py-3 bg-slate-900/50 rounded-xl border border-white/5">
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
                                            'text-slate-200',
                                            currentPage <= 1 ? 'pointer-events-none opacity-50' : '',
                                        ].join(' ')}
                                    />
                                </PaginationItem>

                                {buildPageList(currentPage, lastPage).map((p, idx) => (
                                    <PaginationItem key={`${p}-${idx}`}>
                                        {p === '…' ? (
                                            <span className="px-2 text-slate-500">…</span>
                                        ) : (
                                            <PaginationLink
                                                href="#"
                                                isActive={p === currentPage}
                                                onClick={(e) => {
                                                    e.preventDefault()
                                                    setPage(p)
                                                }}
                                                className={[
                                                    'border-white/10',
                                                    p === currentPage
                                                        ? 'bg-white/5 text-white'
                                                        : 'text-slate-300',
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
                                            'text-slate-200',
                                            currentPage >= lastPage ? 'pointer-events-none opacity-50' : '',
                                        ].join(' ')}
                                    />
                                </PaginationItem>
                            </PaginationContent>
                        </Pagination>
                    </div>
                </>
            ) : (
                <div className="bg-slate-900/40 rounded-3xl border border-dashed border-slate-700 p-20 text-center backdrop-blur-sm">
                    <div className="w-20 h-20 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                        <Search className="h-8 w-8" />
                    </div>
                    <h3 className="text-xl font-semibold text-white mb-2">Tidak Ada Produk</h3>
                    <p className="text-slate-500">
                        Coba ubah kata kunci pencarian atau kategori.
                    </p>
                </div>
            )}
        </div>
    )
}

export default function HomePage() {
    return (
        <PublicLayout>
            <BannerSection />
            <ProductsSection />
        </PublicLayout>
    )
}

