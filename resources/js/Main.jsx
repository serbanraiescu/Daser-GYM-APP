import React, { useEffect, useState } from 'react';
import axios from 'axios';
import Header from './components/Header';
import Hero from './components/Hero';
import Features from './components/Features';
import Plans from './components/Plans';
import Testimonials from './components/Testimonials';
import Contact from './components/Contact';
import Footer from './components/Footer';

export default function Main() {
    const [config, setConfig] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/website-config')
            .then(res => {
                setConfig(res.data);

                // Inject Theme Colors
                if (res.data.theme) {
                    document.documentElement.style.setProperty('--primary', res.data.theme.primary_color);
                    document.documentElement.style.setProperty('--secondary', res.data.theme.secondary_color);
                }

                // Inject Titles & Favicons dynamically
                if (res.data.brand) {
                    document.title = `${res.data.brand.name} | Fitness de Elită`;
                    if (res.data.brand.favicon_url) {
                        let link = document.querySelector("link[rel~='icon']");
                        if (!link) {
                            link = document.createElement('link');
                            link.rel = 'icon';
                            document.head.appendChild(link);
                        }
                        link.href = res.data.brand.favicon_url;
                    }
                }
            })
            .catch(err => console.error("Could not load website configuration.", err))
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary" style={{ borderColor: 'var(--primary, #3b82f6)' }}></div>
            </div>
        );
    }

    if (!config) {
        return <div className="p-10 text-center">Bază de date neconfigurată.</div>;
    }

    return (
        <div className="bg-white text-slate-900 overflow-x-hidden min-h-screen flex flex-col font-sans">
            <Header brand={config.brand} header={config.header} />

            <main className="flex-1">
                {config.hero?.enabled && <Hero hero={config.hero} />}
                {config.features?.enabled && <Features features={config.features} />}
                {config.plans?.enabled && <Plans plans={config.plans} />}
                {config.testimonials?.enabled && <Testimonials testimonials={config.testimonials} />}
                {config.contact?.enabled && <Contact contact={config.contact} />}
            </main>

            <Footer footer={config.footer} brand={config.brand} />
        </div>
    );
}
