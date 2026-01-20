<script>
    (() => {
        const STORAGE_KEY = 'theme'
        const valid = new Set(['light', 'dark'])

        const getPref = () => {
            try {
                const v = localStorage.getItem(STORAGE_KEY)
                if (v && valid.has(v)) return v
            } catch {}
            return 'dark'
        }

        const root = document.documentElement
        let theme = getPref()
        try {
            if (!localStorage.getItem(STORAGE_KEY)) localStorage.setItem(STORAGE_KEY, 'dark')
        } catch {}
        root.classList.toggle('dark', theme === 'dark')
        root.dataset.theme = theme
        root.style.colorScheme = theme
    })()
</script>
<?php /**PATH C:\laragon\www\Kopma\resources\views/public/partials/theme-init.blade.php ENDPATH**/ ?>