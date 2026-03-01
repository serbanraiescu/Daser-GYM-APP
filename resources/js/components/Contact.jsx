import React from 'react';

export default function Contact({ contact }) {
    return (
        <section id="contact" className="py-24 bg-slate-900 text-white">
            <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
                <div className="space-y-8">
                    <h2 className="text-4xl md:text-5xl font-extrabold tracking-tight">{contact.title}</h2>
                    {contact.subtitle && <p className="text-xl text-slate-400">{contact.subtitle}</p>}

                    <div className="space-y-6 pt-8">
                        {contact.phone && (
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-2xl text-[color:var(--primary)]">📞</div>
                                <div>
                                    <div className="text-slate-400 text-sm font-bold uppercase tracking-wider">Telefon</div>
                                    <a href={`tel:${contact.phone}`} className="text-xl font-bold hover:text-[color:var(--primary)] transition-colors">{contact.phone}</a>
                                </div>
                            </div>
                        )}
                        {contact.email && (
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-2xl text-[color:var(--primary)]">✉️</div>
                                <div>
                                    <div className="text-slate-400 text-sm font-bold uppercase tracking-wider">Email</div>
                                    <a href={`mailto:${contact.email}`} className="text-xl font-bold hover:text-[color:var(--primary)] transition-colors">{contact.email}</a>
                                </div>
                            </div>
                        )}
                        {contact.address && (
                            <div className="flex items-center gap-4">
                                <div className="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-2xl text-[color:var(--primary)]">📍</div>
                                <div>
                                    <div className="text-slate-400 text-sm font-bold uppercase tracking-wider">Locație</div>
                                    <span className="text-lg font-bold">{contact.address}</span>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {contact.form_enabled ? (
                    <div className="bg-slate-800 p-10 rounded-[3rem]">
                        <form className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-slate-400 mb-2">Numele Tău</label>
                                <input type="text" className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[color:var(--primary)]" />
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-slate-400 mb-2">Email</label>
                                <input type="email" className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[color:var(--primary)]" />
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-slate-400 mb-2">Mesaj</label>
                                <textarea rows="4" className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-[color:var(--primary)]"></textarea>
                            </div>
                            <button type="button" className="w-full py-4 bg-[color:var(--primary)] text-white rounded-xl font-bold hover:opacity-90 transition-opacity">Trimite Mesajul</button>
                        </form>
                    </div>
                ) : (
                    <div className="h-full flex items-center justify-center">
                        {contact.lat && contact.lng ? (
                            <div className="bg-slate-800/50 border border-slate-700 p-8 rounded-[2.5rem] text-center space-y-6 w-full max-w-sm">
                                <div className="text-6xl">📍</div>
                                <div className="space-y-1">
                                    <div className="text-slate-400 text-sm font-bold uppercase tracking-widest">Coordonate GPS</div>
                                    <div className="text-2xl font-black font-mono text-white tracking-tight">
                                        {contact.lat}, {contact.lng}
                                    </div>
                                </div>
                                <a
                                    href={`https://www.google.com/maps/search/?api=1&query=${contact.lat},${contact.lng}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="inline-block px-8 py-3 bg-white text-slate-900 rounded-full font-bold hover:bg-[color:var(--primary)] hover:text-white transition-all shadow-xl"
                                >
                                    Deschide în Google Maps
                                </a>
                            </div>
                        ) : contact.map_embed_url ? (
                            <iframe src={contact.map_embed_url} width="100%" height="400" style={{ border: 0, borderRadius: '2rem' }} loading="lazy"></iframe>
                        ) : (
                            <div className="opacity-20 text-9xl">🗺️</div>
                        )}
                    </div>
                )}
            </div>
        </section>
    );
}
