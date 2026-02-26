import React from 'react';

export default function Header({ brand, header }) {
    return (
        <nav className="fixed top-0 w-full z-50 glass border-b border-slate-100" style={{ backdropFilter: 'blur(12px)', WebkitBackdropFilter: 'blur(12px)', background: 'rgba(255, 255, 255, 0.8)' }}>
            <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <a href="/" className="flex items-center gap-3">
                    {brand.logo_url && (
                        <img src={brand.logo_url} alt={brand.name} className="h-10 w-auto" />
                    )}
                    <span className="font-bold text-2xl tracking-tight text-slate-900">{brand.name}</span>
                </a>

                <div className="hidden md:flex items-center gap-10 font-semibold text-slate-600">
                    {header.nav_items?.map((item, idx) => item.visible && (
                        <a key={idx} href={item.href} className="hover:text-primary transition-colors hover:text-[color:var(--primary)]">{item.label}</a>
                    ))}

                    {header.cta_primary && (
                        <a href={header.cta_primary.href} className="text-white px-6 py-2.5 rounded-full hover:opacity-90 transition-all shadow-lg shadow-blue-500/20" style={{ backgroundColor: 'var(--primary)' }}>
                            {header.cta_primary.label}
                        </a>
                    )}
                </div>
            </div>
        </nav>
    );
}
