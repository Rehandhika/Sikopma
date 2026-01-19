import React, { createContext, useContext, useState, useEffect, useCallback } from 'react'
import { api } from '@/react/lib/api'

// Default settings
const DEFAULT_SETTINGS = {
    timezone: 'Asia/Jakarta',
    timezone_offset: 7,
    timezone_name: 'WIB - Waktu Indonesia Barat',
    date_format: 'd/m/Y',
    time_format: 'H:i',
    datetime_format: 'd/m/Y H:i',
    use_24_hour: true,
    first_day_of_week: 1,
    locale: 'id',
    current_time: new Date().toISOString(),
    current_time_formatted: '',
}

// Indonesian day and month names
const INDONESIAN_DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
const INDONESIAN_MONTHS = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]
const INDONESIAN_SHORT_MONTHS = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
]

// Format date using PHP-like format string
function formatWithPhpFormat(date, format, locale = 'id') {
    if (!date) return '-'
    
    const d = new Date(date)
    if (isNaN(d.getTime())) return '-'
    
    const day = d.getDate()
    const month = d.getMonth()
    const year = d.getFullYear()
    const hours24 = d.getHours()
    const hours12 = hours24 % 12 || 12
    const minutes = d.getMinutes()
    const seconds = d.getSeconds()
    const dayOfWeek = d.getDay()
    const ampm = hours24 >= 12 ? 'PM' : 'AM'
    
    const pad = (n) => n.toString().padStart(2, '0')
    
    const replacements = {
        'd': pad(day),
        'j': day.toString(),
        'm': pad(month + 1),
        'n': (month + 1).toString(),
        'Y': year.toString(),
        'y': year.toString().slice(-2),
        'H': pad(hours24),
        'G': hours24.toString(),
        'h': pad(hours12),
        'g': hours12.toString(),
        'i': pad(minutes),
        's': pad(seconds),
        'A': ampm,
        'a': ampm.toLowerCase(),
        'M': locale === 'id' ? INDONESIAN_SHORT_MONTHS[month] : d.toLocaleDateString('en', { month: 'short' }),
        'F': locale === 'id' ? INDONESIAN_MONTHS[month] : d.toLocaleDateString('en', { month: 'long' }),
        'D': locale === 'id' ? INDONESIAN_DAYS[dayOfWeek].slice(0, 3) : d.toLocaleDateString('en', { weekday: 'short' }),
        'l': locale === 'id' ? INDONESIAN_DAYS[dayOfWeek] : d.toLocaleDateString('en', { weekday: 'long' }),
    }
    
    let result = format
    Object.entries(replacements).forEach(([key, value]) => {
        result = result.replace(new RegExp(key, 'g'), value)
    })
    
    return result
}

// Create context
const DateTimeContext = createContext(null)

// Provider component
export function DateTimeProvider({ children, initialSettings = null }) {
    const [settings, setSettings] = useState(initialSettings || DEFAULT_SETTINGS)
    const [loading, setLoading] = useState(!initialSettings)
    const [error, setError] = useState(null)

    // Fetch settings from API
    const fetchSettings = useCallback(async () => {
        try {
            setLoading(true)
            const response = await api.get('/api/public/datetime-settings')
            const data = response.data?.data || DEFAULT_SETTINGS
            setSettings(data)
            setError(null)
        } catch (err) {
            console.error('Failed to fetch datetime settings:', err)
            setError(err)
            setSettings(DEFAULT_SETTINGS)
        } finally {
            setLoading(false)
        }
    }, [])

    // Load settings on mount if not provided
    useEffect(() => {
        if (!initialSettings) {
            fetchSettings()
        }
    }, [initialSettings, fetchSettings])

    // Format functions
    const formatDate = useCallback((date) => {
        return formatWithPhpFormat(date, settings.date_format, settings.locale)
    }, [settings.date_format, settings.locale])

    const formatTime = useCallback((date) => {
        return formatWithPhpFormat(date, settings.time_format, settings.locale)
    }, [settings.time_format, settings.locale])

    const formatDateTime = useCallback((date) => {
        return formatWithPhpFormat(date, settings.datetime_format, settings.locale)
    }, [settings.datetime_format, settings.locale])

    const formatDateHuman = useCallback((date) => {
        if (!date) return '-'
        const d = new Date(date)
        if (isNaN(d.getTime())) return '-'
        
        const dayName = settings.locale === 'id' ? INDONESIAN_DAYS[d.getDay()] : d.toLocaleDateString('en', { weekday: 'long' })
        const day = d.getDate()
        const monthName = settings.locale === 'id' ? INDONESIAN_MONTHS[d.getMonth()] : d.toLocaleDateString('en', { month: 'long' })
        const year = d.getFullYear()
        
        return `${dayName}, ${day} ${monthName} ${year}`
    }, [settings.locale])

    const formatDateTimeHuman = useCallback((date) => {
        if (!date) return '-'
        const dateStr = formatDateHuman(date)
        const timeStr = formatTime(date)
        return `${dateStr} ${timeStr}`
    }, [formatDateHuman, formatTime])

    const diffForHumans = useCallback((date) => {
        if (!date) return '-'
        const d = new Date(date)
        if (isNaN(d.getTime())) return '-'
        
        const now = new Date()
        const diffMs = now - d
        const diffSec = Math.floor(diffMs / 1000)
        const diffMin = Math.floor(diffSec / 60)
        const diffHour = Math.floor(diffMin / 60)
        const diffDay = Math.floor(diffHour / 24)
        const diffWeek = Math.floor(diffDay / 7)
        const diffMonth = Math.floor(diffDay / 30)
        const diffYear = Math.floor(diffDay / 365)
        
        const isId = settings.locale === 'id'
        
        if (diffSec < 60) return isId ? 'baru saja' : 'just now'
        if (diffMin < 60) return isId ? `${diffMin} menit yang lalu` : `${diffMin} minutes ago`
        if (diffHour < 24) return isId ? `${diffHour} jam yang lalu` : `${diffHour} hours ago`
        if (diffDay < 7) return isId ? `${diffDay} hari yang lalu` : `${diffDay} days ago`
        if (diffWeek < 4) return isId ? `${diffWeek} minggu yang lalu` : `${diffWeek} weeks ago`
        if (diffMonth < 12) return isId ? `${diffMonth} bulan yang lalu` : `${diffMonth} months ago`
        return isId ? `${diffYear} tahun yang lalu` : `${diffYear} years ago`
    }, [settings.locale])

    const value = {
        settings,
        loading,
        error,
        formatDate,
        formatTime,
        formatDateTime,
        formatDateHuman,
        formatDateTimeHuman,
        diffForHumans,
        refresh: fetchSettings,
        timezone: settings.timezone,
        locale: settings.locale,
        INDONESIAN_DAYS,
        INDONESIAN_MONTHS,
        INDONESIAN_SHORT_MONTHS,
    }

    return (
        <DateTimeContext.Provider value={value}>
            {children}
        </DateTimeContext.Provider>
    )
}

// Hook to use datetime context
export function useDateTime() {
    const context = useContext(DateTimeContext)
    if (!context) {
        throw new Error('useDateTime must be used within a DateTimeProvider')
    }
    return context
}

export default DateTimeContext
