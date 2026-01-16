import * as React from 'react'
import Autoplay from 'embla-carousel-autoplay'
import type { EmblaCarouselType } from 'embla-carousel'

import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from '@/components/ui/carousel'

export type BannerImageSources = {
    default: string
    '1920'?: string
    '768'?: string
    '480'?: string
}

export type BannerSlide = {
    id: string | number
    title?: string | null
    alt?: string | null
    href?: string | null
    images: BannerImageSources
    overlay?: React.ReactNode
}

export type BannerCarouselProps = {
    slides: BannerSlide[]
    autoplayIntervalMs?: number
    transitionDuration?: number
    loop?: boolean
    showArrows?: boolean
    showDots?: boolean
    className?: string
    overlayClassName?: string
    animationClassName?: string
    renderOverlay?: (slide: BannerSlide, state: { active: boolean; index: number }) => React.ReactNode
}

function BannerImage({
    slide,
    active,
    scrimClassName,
}: {
    slide: BannerSlide
    active: boolean
    scrimClassName?: string
}) {
    const [loaded, setLoaded] = React.useState(false)
    const [failed, setFailed] = React.useState(false)

    React.useEffect(() => {
        setLoaded(false)
        setFailed(false)
    }, [slide.images?.default])

    const alt = (slide.alt || slide.title || 'Banner') ?? 'Banner'

    return (
        <div className="relative w-full h-full">
            {!loaded && !failed ? (
                <Skeleton className="absolute inset-0 rounded-none" />
            ) : null}

            {!failed ? (
                <picture className="absolute inset-0">
                    {slide.images?.['1920'] ? (
                        <source media="(min-width: 1024px)" srcSet={slide.images['1920']} />
                    ) : null}
                    {slide.images?.['768'] ? (
                        <source media="(min-width: 640px)" srcSet={slide.images['768']} />
                    ) : null}
                    {slide.images?.['480'] ? (
                        <source media="(max-width: 639px)" srcSet={slide.images['480']} />
                    ) : null}
                    <img
                        src={slide.images?.default ?? ''}
                        alt={alt}
                        className={cn(
                            'w-full h-full object-cover',
                            'transition-transform duration-700',
                            active ? 'scale-[1.02]' : 'scale-100',
                            loaded ? 'opacity-100' : 'opacity-0',
                        )}
                        loading="lazy"
                        decoding="async"
                        draggable={false}
                        onLoad={() => setLoaded(true)}
                        onError={() => setFailed(true)}
                    />
                </picture>
            ) : (
                <div className="absolute inset-0 bg-muted flex items-center justify-center text-muted-foreground text-sm">
                    Gagal memuat banner
                </div>
            )}

            <div
                className={cn(
                    'absolute inset-0 pointer-events-none',
                    'bg-gradient-to-t from-black/35 via-black/5 to-transparent',
                    scrimClassName,
                )}
            />
        </div>
    )
}

export default function BannerCarousel({
    slides,
    autoplayIntervalMs = 5000,
    transitionDuration = 40,
    loop = true,
    showArrows = true,
    showDots = true,
    className,
    overlayClassName,
    animationClassName,
    renderOverlay,
}: BannerCarouselProps) {
    const [api, setApi] = React.useState<EmblaCarouselType | null>(null)
    const [selectedIndex, setSelectedIndex] = React.useState(0)
    const [snapCount, setSnapCount] = React.useState(0)

    const onSelect = React.useCallback((emblaApi: EmblaCarouselType) => {
        setSelectedIndex(emblaApi.selectedScrollSnap())
    }, [])

    React.useEffect(() => {
        if (!api) return
        setSnapCount(api.scrollSnapList().length)
        onSelect(api)
        api.on('select', onSelect)
        api.on('reInit', () => {
            setSnapCount(api.scrollSnapList().length)
            onSelect(api)
        })
        return () => {
            api.off('select', onSelect)
        }
    }, [api, onSelect])

    if (!slides?.length) return null

    return (
        <div className={cn('w-full relative', className)}>
            <Carousel
                opts={{
                    loop,
                    duration: transitionDuration,
                }}
                setApi={setApi}
                plugins={[
                    Autoplay({
                        delay: autoplayIntervalMs,
                        stopOnInteraction: true,
                        stopOnMouseEnter: true,
                        stopOnFocusIn: true,
                    }),
                ]}
                className="w-full"
            >
                <CarouselContent className="ml-0">
                    {slides.map((slide, idx) => {
                        const isActive = idx === selectedIndex
                        return (
                            <CarouselItem key={slide.id} className="pl-0">
                                <div className="relative overflow-hidden rounded-2xl border border-border bg-card/20">
                                    <div className="aspect-[16/9] w-full">
                                        <BannerImage slide={slide} active={isActive} />
                                    </div>

                                    <div
                                        className={cn(
                                            'absolute inset-0 flex items-end p-4 sm:p-6 lg:p-8',
                                            overlayClassName,
                                        )}
                                    >
                                        <div
                                            className={cn(
                                                'max-w-3xl',
                                                'transition-all duration-300 ease-out',
                                                isActive
                                                    ? 'opacity-100 translate-y-0'
                                                    : 'opacity-0 translate-y-2',
                                                animationClassName,
                                            )}
                                        >
                                            {renderOverlay
                                                ? renderOverlay(slide, { active: isActive, index: idx })
                                                : slide.overlay ?? (slide.title ? (
                                                      <h3 className="text-foreground text-lg sm:text-2xl lg:text-3xl font-bold drop-shadow-lg">
                                                          {slide.title}
                                                      </h3>
                                                  ) : null)}
                                        </div>
                                    </div>

                                    {slide.href ? (
                                        <a
                                            href={slide.href}
                                            className="absolute inset-0"
                                            aria-label={slide.title ? `Buka ${slide.title}` : 'Buka banner'}
                                        />
                                    ) : null}
                                </div>
                            </CarouselItem>
                        )
                    })}
                </CarouselContent>

                {showArrows ? (
                    <>
                        <CarouselPrevious className="left-4 sm:left-6 bg-background/80 hover:bg-background text-foreground border border-border shadow-sm" />
                        <CarouselNext className="right-4 sm:right-6 bg-background/80 hover:bg-background text-foreground border border-border shadow-sm" />
                    </>
                ) : null}
            </Carousel>

            {showDots && snapCount > 1 ? (
                <div className="absolute left-0 right-0 bottom-3 sm:bottom-4 flex items-center justify-center gap-2">
                    {Array.from({ length: snapCount }).map((_, i) => {
                        const active = i === selectedIndex
                        return (
                            <Button
                                key={i}
                                type="button"
                                variant="ghost"
                                size="icon"
                                className={cn(
                                    'h-7 w-7 rounded-full',
                                    'bg-background/40 hover:bg-background/70',
                                    'border border-border',
                                    active ? 'text-foreground' : 'text-muted-foreground',
                                )}
                                aria-label={`Ke slide ${i + 1}`}
                                aria-current={active ? 'true' : 'false'}
                                onClick={() => api?.scrollTo(i)}
                            >
                                <span
                                    className={cn(
                                        'h-1.5 w-1.5 rounded-full',
                                        active ? 'bg-primary' : 'bg-muted-foreground/70',
                                    )}
                                />
                            </Button>
                        )
                    })}
                </div>
            ) : null}
        </div>
    )
}

