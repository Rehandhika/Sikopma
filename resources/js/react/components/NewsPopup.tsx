import * as React from 'react'
import { ChevronLeft, ChevronRight, X } from 'lucide-react'
import * as DialogPrimitive from '@radix-ui/react-dialog'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const VIEWED_NEWS_KEY = 'viewedNewsIds'

export type NewsImageSources = {
    default: string | null
    mobile?: string
    tablet?: string
    desktop?: string
}

export type NewsItem = {
    id: number
    title?: string
    content?: string
    link?: string
    images: NewsImageSources
    published_at: string
}

export type NewsPopupProps = {
    news: NewsItem[]
    onClose: () => void
}

/**
 * Get viewed news IDs from session storage
 */
function getViewedNewsIds(): number[] {
    try {
        const stored = sessionStorage.getItem(VIEWED_NEWS_KEY)
        return stored ? JSON.parse(stored) : []
    } catch {
        return []
    }
}

/**
 * Store viewed news IDs in session storage
 */
function storeViewedNewsIds(ids: number[]): void {
    try {
        sessionStorage.setItem(VIEWED_NEWS_KEY, JSON.stringify(ids))
    } catch {
        // Graceful degradation if sessionStorage is unavailable
    }
}

/**
 * Filter out already-viewed news items
 */
function filterUnviewedNews(news: NewsItem[]): NewsItem[] {
    const viewedIds = getViewedNewsIds()
    return news.filter((item) => !viewedIds.includes(item.id))
}

/**
 * Mark news items as viewed
 */
function markNewsAsViewed(newsIds: number[]): void {
    const existingIds = getViewedNewsIds()
    const updatedIds = Array.from(new Set([...existingIds, ...newsIds]))
    storeViewedNewsIds(updatedIds)
}

function NewsImage({ newsItem }: { newsItem: NewsItem }) {
    const [loaded, setLoaded] = React.useState(false)
    const [failed, setFailed] = React.useState(false)

    React.useEffect(() => {
        setLoaded(false)
        setFailed(false)
    }, [newsItem.images?.default])

    if (!newsItem.images?.default) {
        return null
    }

    return (
        <div className="relative w-full h-full overflow-hidden bg-muted">
            {!loaded && !failed && (
                <div className="absolute inset-0 animate-pulse bg-muted" />
            )}

            {!failed ? (
                <picture>
                    {newsItem.images?.desktop && (
                        <source media="(min-width: 1024px)" srcSet={newsItem.images.desktop} />
                    )}
                    {newsItem.images?.tablet && (
                        <source media="(min-width: 640px)" srcSet={newsItem.images.tablet} />
                    )}
                    {newsItem.images?.mobile && (
                        <source media="(max-width: 639px)" srcSet={newsItem.images.mobile} />
                    )}
                    <img
                        src={newsItem.images.default}
                        alt={newsItem.title || 'News image'}
                        className={cn(
                            'w-full h-full object-cover transition-opacity duration-300',
                            loaded ? 'opacity-100' : 'opacity-0'
                        )}
                        loading="lazy"
                        onLoad={() => setLoaded(true)}
                        onError={() => setFailed(true)}
                    />
                </picture>
            ) : (
                <div className="absolute inset-0 flex items-center justify-center text-muted-foreground text-sm">
                    Gagal memuat gambar
                </div>
            )}
        </div>
    )
}

export default function NewsPopup({ news, onClose }: NewsPopupProps): React.ReactElement | null {
    // Filter out already-viewed news on mount
    const [unviewedNews] = React.useState(() => filterUnviewedNews(news))
    const [currentIndex, setCurrentIndex] = React.useState(0)
    const [open, setOpen] = React.useState(true)

    // Don't show popup if no unviewed news
    if (unviewedNews.length === 0) {
        return null
    }

    const currentNews = unviewedNews[currentIndex]
    const totalNews = unviewedNews.length

    // Navigation handlers with loop support
    const handleNext = () => {
        setCurrentIndex((prev) => (prev + 1) % totalNews)
    }

    const handlePrevious = () => {
        setCurrentIndex((prev) => (prev - 1 + totalNews) % totalNews)
    }

    // Keyboard navigation
    React.useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if (!open) return

            switch (event.key) {
                case 'ArrowRight':
                    event.preventDefault()
                    handleNext()
                    break
                case 'ArrowLeft':
                    event.preventDefault()
                    handlePrevious()
                    break
                case 'Escape':
                    event.preventDefault()
                    handleClose()
                    break
            }
        }

        window.addEventListener('keydown', handleKeyDown)
        return () => window.removeEventListener('keydown', handleKeyDown)
    }, [open, currentIndex, totalNews])

    // Prevent background scroll when popup is open
    React.useEffect(() => {
        if (open) {
            document.body.style.overflow = 'hidden'
        } else {
            document.body.style.overflow = ''
        }

        return () => {
            document.body.style.overflow = ''
        }
    }, [open])

    const handleClose = () => {
        // Mark all unviewed news as viewed
        const newsIds = unviewedNews.map((item) => item.id)
        markNewsAsViewed(newsIds)
        
        setOpen(false)
        onClose()
    }

    const handleOpenChange = (isOpen: boolean) => {
        if (!isOpen) {
            handleClose()
        }
    }

    return (
        <DialogPrimitive.Root open={open} onOpenChange={handleOpenChange}>
            <DialogPrimitive.Portal>
                <DialogPrimitive.Overlay className="fixed inset-0 z-50 bg-black/80 md:bg-black/60 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0" />
                <DialogPrimitive.Content
                    className={cn(
                        'fixed left-[50%] top-[50%] z-50 translate-x-[-50%] translate-y-[-50%] gap-0 border bg-background shadow-2xl duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] rounded-xl md:rounded-2xl',
                        'h-[65vh] md:h-[80vh] aspect-[9/16]',
                        'overflow-hidden',
                        'p-0'
                )}
            >
                <DialogPrimitive.Title className="sr-only">
                    {currentNews.title || 'Detail Berita'}
                </DialogPrimitive.Title>

                {/* Custom close button - positioned like Instagram story close button */}
                <button
                    type="button"
                    className={cn(
                        'absolute right-2 top-2 z-10',
                        'rounded-full bg-black/50 hover:bg-black/70 backdrop-blur-sm',
                        'p-1 text-white transition-all hover:scale-110',
                        'focus:outline-none focus:ring-1 focus:ring-white/50 focus:ring-offset-0',
                        'disabled:pointer-events-none'
                    )}
                    onClick={handleClose}
                    aria-label="Close"
                >
                    <X className="h-3 w-3" />
                    <span className="sr-only">Close</span>
                </button>

                <div className="relative h-full flex flex-col">
                    {/* Image - takes most of the space */}
                    {currentNews.images?.default && (
                        <div className="flex-1 min-h-0 relative">
                            {currentNews.link ? (
                                <a 
                                    href={currentNews.link} 
                                    target="_blank" 
                                    rel="noopener noreferrer"
                                    className="block w-full h-full cursor-pointer"
                                    onClick={(e: React.MouseEvent) => {
                                        // Prevent navigation controls from interfering
                                        e.stopPropagation()
                                    }}
                                >
                                    <NewsImage newsItem={currentNews} />
                                </a>
                            ) : (
                                <NewsImage newsItem={currentNews} />
                            )}
                        </div>
                    )}

                    {/* Content overlay - absolute positioned at bottom with pointer-events-none */}
                    {(currentNews.title || currentNews.content) && (
                        <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 via-black/60 to-transparent px-3 py-3 sm:px-4 sm:py-4 pointer-events-none">
                            {/* Title - Optional */}
                            {currentNews.title && (
                                <h2 className="text-sm sm:text-base font-bold mb-1 pr-6 leading-tight text-white drop-shadow-lg">
                                    {currentNews.title}
                                </h2>
                            )}

                            {/* Content - Optional */}
                            {currentNews.content && (
                                <div
                                    className="prose prose-xs max-w-none text-white/90 text-xs line-clamp-2 drop-shadow"
                                    dangerouslySetInnerHTML={{ __html: currentNews.content }}
                                />
                            )}
                        </div>
                    )}

                    {/* Navigation controls - absolute positioned at bottom with pointer-events-auto */}
                    {totalNews > 1 && (
                        <div className="absolute bottom-0 left-0 right-0 flex items-center justify-between px-2 py-1.5 sm:px-3 sm:py-2 bg-black/30 backdrop-blur-sm pointer-events-auto z-10">
                            {/* Previous button */}
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={(e: React.MouseEvent) => {
                                    e.preventDefault()
                                    e.stopPropagation()
                                    handlePrevious()
                                }}
                                className="gap-0 h-6 w-6 p-0 hover:bg-white/20 text-white"
                            >
                                <ChevronLeft className="h-3 w-3" />
                                <span className="sr-only">Prev</span>
                            </Button>

                            {/* Position indicator */}
                            <div className="flex items-center gap-1">
                                {/* Dot indicators */}
                                <div className="flex items-center gap-0.5">
                                    {Array.from({ length: totalNews }).map((_, index) => (
                                        <button
                                            key={index}
                                            type="button"
                                            onClick={(e: React.MouseEvent) => {
                                                e.preventDefault()
                                                e.stopPropagation()
                                                setCurrentIndex(index)
                                            }}
                                            className={cn(
                                                'h-1 w-1 rounded-full transition-all',
                                                index === currentIndex
                                                    ? 'bg-white w-2.5'
                                                    : 'bg-white/40 hover:bg-white/60'
                                            )}
                                            aria-label={`Ke berita ${index + 1}`}
                                            aria-current={index === currentIndex ? 'true' : 'false'}
                                        />
                                    ))}
                                </div>

                                {/* Position text */}
                                <span className="text-[10px] text-white/90 whitespace-nowrap font-medium ml-1">
                                    {currentIndex + 1}/{totalNews}
                                </span>
                            </div>

                            {/* Next button */}
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={(e: React.MouseEvent) => {
                                    e.preventDefault()
                                    e.stopPropagation()
                                    handleNext()
                                }}
                                className="gap-0 h-6 w-6 p-0 hover:bg-white/20 text-white"
                            >
                                <span className="sr-only">Next</span>
                                <ChevronRight className="h-3 w-3" />
                            </Button>
                        </div>
                    )}
                </div>
            </DialogPrimitive.Content>
        </DialogPrimitive.Portal>
        </DialogPrimitive.Root>
    )
}

// Export utility functions for testing
export { getViewedNewsIds, storeViewedNewsIds, filterUnviewedNews, markNewsAsViewed, VIEWED_NEWS_KEY }
