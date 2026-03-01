import React from 'react';

export default function Footer({ brand, footer, version }) {
    return (
        <footer className="bg-slate-950 text-white pt-24 pb-12">
            <div className="max-w-7xl mx-auto px-6">
                <div className="grid md:grid-cols-4 gap-16 mb-20">
                    <div className="col-span-2 space-y-8">
                        <a href="/" className="flex items-center gap-3">
                            {brand.logo_url && (
                                <img src={brand.logo_url} alt={brand.name} className="h-10 w-auto invert opacity-80" />
                            )}
                            <span className="font-bold text-3xl tracking-tight text-white">{brand.name}</span>
                        </a>
                        {footer.text_left && (
                            <p className="text-slate-400 text-lg leading-relaxed max-w-md">
                                {footer.text_left}
                            </p>
                        )}
                    </div>

                    <div className="space-y-6">
                        <h5 className="font-bold text-xl">{footer.text_right}</h5>
                        {footer.links && footer.links.length > 0 && (
                            <ul className="space-y-4 text-slate-400">
                                {footer.links.map((link, idx) => link.visible !== false && (
                                    <li key={idx}><a href={link.href} className="hover:text-white transition-colors">{link.label}</a></li>
                                ))}
                            </ul>
                        )}
                    </div>

                    <div className="space-y-6">
                        <h5 className="font-bold text-xl">Social Media</h5>
                        {footer.socials && (
                            <div className="flex gap-4 flex-wrap">
                                {footer.socials.facebook && <a href={footer.socials.facebook} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">FB</a>}
                                {footer.socials.instagram && <a href={footer.socials.instagram} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">IG</a>}
                                {footer.socials.tiktok && <a href={footer.socials.tiktok} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">TT</a>}
                                {footer.socials.youtube && <a href={footer.socials.youtube} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">YT</a>}
                                {footer.socials.whatsapp && <a href={footer.socials.whatsapp} target="_blank" rel="noreferrer" className="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">WA</a>}
                            </div>
                        )}
                    </div>
                </div>

                <div className="border-t border-slate-900 pt-12 flex flex-col md:flex-row justify-between items-center text-slate-500 text-sm gap-4">
                    <div className="text-center md:text-left">
                        <p>&copy; {new Date().getFullYear()} Daser Enterprise SRL. Licensed Software – All Rights Reserved</p>
                        {version && <p className="mt-1 opacity-50">v{version}</p>}
                    </div>
                    <p>Powered by <a href="https://daserdesign.ro" target="_blank" rel="noreferrer" className="font-semibold hover:text-white transition-colors">Daser Technologies</a></p>
                </div>
            </div>
        </footer>
    );
}
