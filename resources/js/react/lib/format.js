export function formatRupiah(value) {
    const number = typeof value === 'number' ? value : Number(value)
    if (Number.isNaN(number)) return '-'
    return new Intl.NumberFormat('id-ID').format(number)
}

