import React from 'react'
import { createRoot } from 'react-dom/client'
import AboutPage from './pages/AboutPage'
import HomePage from './pages/HomePage'
import NotFoundPage from './pages/NotFoundPage'
import ProductDetailPage from './pages/ProductDetailPage'

const rootElement = document.getElementById('react-public')

if (rootElement) {
    const page = rootElement.dataset.page || 'home'
    const slug = rootElement.dataset.slug || ''

    let Page = HomePage
    if (page === 'about') Page = AboutPage
    if (page === 'product') Page = (props) => <ProductDetailPage {...props} slug={slug} />
    if (!['home', 'about', 'product'].includes(page)) Page = NotFoundPage

    createRoot(rootElement).render(
        <React.StrictMode>
            <Page />
        </React.StrictMode>,
    )
}

