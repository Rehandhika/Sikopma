import React from 'react'
import { User, Mail, Phone, ExternalLink, Quote } from 'lucide-react'

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { cn } from '@/lib/utils'
import { ORGANIZATION_PERIOD } from '@/react/data/organization'

function getInitials(name) {
    const parts = String(name || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean)
    if (!parts.length) return '—'
    const first = parts[0]?.[0] ?? ''
    const last = parts.length > 1 ? parts[parts.length - 1]?.[0] ?? '' : ''
    return (first + last).toUpperCase()
}

function MemberCard({ person, division, onSelect }) {
    return (
        <button 
            type="button"
            onClick={() => onSelect(person)}
            className="w-full text-left group relative flex items-center gap-4 p-4 rounded-2xl border border-border/40 bg-card/40 hover:bg-card/80 hover:border-primary/20 hover:shadow-lg hover:shadow-primary/5 transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
        >
            <div className={cn('relative shrink-0 rounded-full p-0.5 ring-2 ring-offset-2 ring-offset-background transition-colors group-hover:ring-primary/50', division.ringClass)}>
                <Avatar className="h-12 w-12 sm:h-14 sm:w-14">
                    {person.photoUrl ? (
                        <AvatarImage
                            src={person.photoUrl}
                            alt={person.name}
                            loading="lazy"
                            className="object-cover transition-transform duration-500 group-hover:scale-110"
                        />
                    ) : null}
                    <AvatarFallback className="bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <span className="text-xs font-semibold">{getInitials(person.name)}</span>
                    </AvatarFallback>
                </Avatar>
            </div>
            
            <div className="min-w-0 flex-1">
                <div className="flex items-center gap-2 mb-0.5">
                    <h4 className="font-semibold text-foreground text-sm sm:text-base truncate group-hover:text-primary transition-colors">
                        {person.name}
                    </h4>
                    {person.role.includes('Ketua') || person.role.includes('Koordinator') ? (
                        <span className={cn(
                            "px-1.5 py-0.5 rounded-md text-[10px] font-medium uppercase tracking-wider",
                            division.badgeClass
                        )}>
                            {person.role}
                        </span>
                    ) : null}
                </div>
                <p className="text-xs sm:text-sm text-muted-foreground truncate group-hover:text-foreground/80 transition-colors">
                    {person.role} {division.key !== 'bph' ? division.label : ''}
                </p>
            </div>
        </button>
    )
}

function MemberDetailModal({ person, division, open, onOpenChange }) {
    if (!person) return null

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md p-0 gap-0 overflow-hidden border-border bg-background/95 backdrop-blur-xl">
                <DialogHeader className="p-0">
                    <DialogTitle className="sr-only">Detail Anggota: {person.name}</DialogTitle>
                    <div className="relative h-24 bg-gradient-to-br from-primary/10 via-background to-background">
                        <div className="absolute inset-0 bg-grid-white/5 [mask-image:linear-gradient(0deg,transparent,black)]" />
                    </div>
                </DialogHeader>

                <div className="px-6 pb-6 -mt-12 relative">
                    {/* Header Profile */}
                    <div className="flex flex-col items-center text-center mb-6">
                        <div className={cn('relative rounded-full p-1 ring-4 ring-background mb-4 bg-background', division.ringClass)}>
                            <Avatar className="h-24 w-24">
                                {person.photoUrl ? (
                                    <AvatarImage src={person.photoUrl} alt={person.name} className="object-cover" />
                                ) : null}
                                <AvatarFallback className="bg-muted text-2xl font-bold text-muted-foreground">
                                    {getInitials(person.name)}
                                </AvatarFallback>
                            </Avatar>
                        </div>
                        
                        <h3 className="text-xl font-bold text-foreground mb-1">
                            {person.name}
                        </h3>
                        
                        <div className="flex items-center gap-2 mb-4">
                            <Badge variant="secondary" className={cn("rounded-full font-medium", division.badgeClass)}>
                                {person.role} {division.key !== 'bph' ? division.label : ''}
                            </Badge>
                            <span className="text-xs text-muted-foreground font-medium px-2 py-0.5 rounded-full bg-muted">
                                {ORGANIZATION_PERIOD.periodLabel.split(' ')[1]}
                            </span>
                        </div>

                        {/* Tagline / Quote */}
                        {person.tagline && (
                            <div className="relative max-w-sm mx-auto bg-muted/30 px-4 py-3 rounded-xl border border-border/50">
                                <Quote className="absolute top-2 left-2 w-3 h-3 text-muted-foreground/40 rotate-180" />
                                <p className="text-sm text-muted-foreground italic leading-relaxed px-2">
                                    "{person.tagline}"
                                </p>
                                <Quote className="absolute bottom-2 right-2 w-3 h-3 text-muted-foreground/40" />
                            </div>
                        )}
                    </div>

                    {/* Bio & Details */}
                    <div className="space-y-4">
                        {/* 
                        {person.bio && (
                            <div className="space-y-1.5">
                                <h4 className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Tentang</h4>
                                <p className="text-sm text-foreground/90 leading-relaxed">
                                    {person.bio}
                                </p>
                            </div>
                        )} 
                        */}

                        {/* Highlights (Removed) */}
                        {/* 
                        {person.highlights && person.highlights.length > 0 && (
                            <div className="space-y-2">
                                <h4 className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Fokus Area</h4>
                                <div className="flex flex-wrap gap-1.5">
                                    {person.highlights.map((tag) => (
                                        <Badge key={tag} variant="outline" className="text-xs py-0.5 bg-background/50">
                                            {tag}
                                        </Badge>
                                    ))}
                                </div>
                            </div>
                        )} 
                        */}

                        {/* Contact Actions */}
                        {(person.links?.email || person.links?.whatsapp || person.links?.instagram) && (
                            <div className="pt-4 mt-4 border-t border-border flex gap-2">
                                {person.links.email && (
                                    <Button size="sm" variant="outline" className="flex-1 gap-2 h-9" asChild>
                                        <a href={`mailto:${person.links.email}`}>
                                            <Mail className="w-3.5 h-3.5" />
                                            <span className="text-xs">Email</span>
                                        </a>
                                    </Button>
                                )}
                                {person.links.whatsapp && (
                                    <Button size="sm" variant="outline" className="flex-1 gap-2 h-9" asChild>
                                        <a href={`https://wa.me/${person.links.whatsapp.replace(/\D/g, '')}`} target="_blank" rel="noreferrer">
                                            <Phone className="w-3.5 h-3.5" />
                                            <span className="text-xs">WhatsApp</span>
                                        </a>
                                    </Button>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    )
}

function DivisionSection({ division, onMemberClick }) {
    const leaders = Array.isArray(division.leaders) ? division.leaders : []
    const members = Array.isArray(division.members) ? division.members : []
    const allMembers = [...leaders, ...members]

    if (!allMembers.length) return null

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-3">
                <h3 className="text-lg font-bold text-foreground tracking-tight">
                    {division.fullLabel}
                </h3>
                <div className="h-px flex-1 bg-border/60" />
            </div>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                {allMembers.map((member) => (
                    <MemberCard 
                        key={member.id} 
                        person={member} 
                        division={division}
                        onSelect={() => onMemberClick(member, division)}
                    />
                ))}
            </div>
        </div>
    )
}

export default function OrganizationSection() {
    const divisions = Array.isArray(ORGANIZATION_PERIOD?.divisions) ? ORGANIZATION_PERIOD.divisions : []
    const [selectedMember, setSelectedMember] = React.useState(null)
    const [selectedDivision, setSelectedDivision] = React.useState(null)

    const handleMemberClick = (member, division) => {
        setSelectedMember(member)
        setSelectedDivision(division)
    }

    return (
        <section className="py-12 sm:py-16">
            <div className="text-center mb-10 sm:mb-14">
                <h2 className="text-3xl sm:text-4xl font-extrabold text-foreground tracking-tight mb-3">
                    Struktur Organisasi
                </h2>
                <p className="text-muted-foreground text-lg">
                    {ORGANIZATION_PERIOD.periodLabel}
                </p>
            </div>

            <div className="space-y-12 sm:space-y-16">
                {divisions.map((division) => (
                    <DivisionSection 
                        key={division.key} 
                        division={division} 
                        onMemberClick={handleMemberClick}
                    />
                ))}
            </div>

            <MemberDetailModal 
                person={selectedMember} 
                division={selectedDivision} 
                open={!!selectedMember} 
                onOpenChange={(open) => !open && setSelectedMember(null)}
            />
        </section>
    )
}
