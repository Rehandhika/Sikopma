import React from 'react'

export default function useDebouncedValue(value, delayMs) {
    const [debounced, setDebounced] = React.useState(value)

    React.useEffect(() => {
        const handle = setTimeout(() => setDebounced(value), delayMs)
        return () => clearTimeout(handle)
    }, [value, delayMs])

    return debounced
}

