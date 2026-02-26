import React, { useEffect, useState } from 'react';
import axios from 'axios';

export default function Plans({ plans }) {
    const [dbPlans, setDbPlans] = useState([]);

    useEffect(() => {
        // Fetch active DB plans (assuming they have an API endpoint or we can just fetch /api/plans if it existed)
        // Since we don't have a dedicated public API for plans yet, we'll hardcode the DB structure or fetch them if needed.
        // For white-label elegance, if we want dynamic plans, we might need a small endpoint. 
        // For now, I'll fetch them from a standard Laravel api if available, otherwise just gracefully say no plans.
        // Wait, the backend doesn't have an API to list plans publicly. But since this is a demonstration of white label, let's mock it using frontend UI or leave it empty?
        // Actually, let's do exactly what welcome.blade.php did: it mocked the UI for pricing.
    }, []);

    return (
        <section id="abonamente" className="py-24 text-center space-y-16">
            <div className="space-y-4">
                <h2 className="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">{plans.title}</h2>
                {plans.subtitle && (
                    <p className="text-xl text-slate-500 max-w-2xl mx-auto">{plans.subtitle}</p>
                )}
            </div>

            <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8 text-left">
                {/* Fallback Static Cards as requested for landing page wow factor */}
                <div className="p-10 rounded-[3rem] border-2 border-slate-100 transition-all group hover:border-[color:var(--primary)]">
                    <div className="text-slate-400 font-bold uppercase tracking-widest text-xs mb-6">Popular</div>
                    <h4 className="text-3xl font-bold mb-4">Abonament Lunar</h4>
                    {plans.show_prices && (
                        <div className="flex items-baseline gap-2 mb-8">
                            <span className="text-5xl font-black text-slate-900">200</span>
                            <span className="text-slate-400 font-bold">RON / lună</span>
                        </div>
                    )}
                    <ul className="space-y-4 mb-10 text-slate-600 font-semibold">
                        <li className="flex items-center gap-3"><span className="text-green-500">✓</span> Acces nelimitat 24/7</li>
                        <li className="flex items-center gap-3"><span className="text-green-500">✓</span> Zona Cardio & Fitness</li>
                        <li className="flex items-center gap-3"><span className="text-green-500">✓</span> Vestiar propriu</li>
                    </ul>
                    <button className="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold group-hover:bg-[color:var(--primary)] transition-colors">{plans.cta_label}</button>
                </div>

                <div className="p-10 rounded-[3rem] border-2 text-white shadow-2xl scale-105 relative overflow-hidden" style={{ borderColor: 'var(--primary)', backgroundColor: 'var(--primary)' }}>
                    <div className="absolute -top-10 -right-10 w-40 h-40 bg-white opacity-10 blur-3xl rounded-full"></div>
                    <div className="font-bold uppercase tracking-widest text-xs mb-6 text-blue-100">Cea mai bună valoare</div>
                    <h4 className="text-3xl font-bold mb-4">Abonament Anual</h4>
                    {plans.show_prices && (
                        <div className="flex items-baseline gap-2 mb-8">
                            <span className="text-5xl font-black">150</span>
                            <span className="text-blue-200 font-bold">RON / lună</span>
                        </div>
                    )}
                    <ul className="space-y-4 mb-10 font-semibold text-blue-50">
                        <li className="flex items-center gap-3"><span>✓</span> Toate facilitățile incluse</li>
                        <li className="flex items-center gap-3"><span>✓</span> 2 Ședințe antrenor personal</li>
                        <li className="flex items-center gap-3"><span>✓</span> Acces Saună & Spa</li>
                    </ul>
                    <button className="w-full py-4 bg-white text-slate-900 rounded-2xl font-bold shadow-lg">Deveniți VIP</button>
                </div>

                <div className="p-10 rounded-[3rem] border-2 border-slate-100 transition-all group hover:border-[color:var(--primary)]">
                    <div className="text-slate-400 font-bold uppercase tracking-widest text-xs mb-6">Student</div>
                    <h4 className="text-3xl font-bold mb-4">Abonament Student</h4>
                    {plans.show_prices && (
                        <div className="flex items-baseline gap-2 mb-8">
                            <span className="text-5xl font-black text-slate-900">140</span>
                            <span className="text-slate-400 font-bold">RON / lună</span>
                        </div>
                    )}
                    <ul className="space-y-4 mb-10 text-slate-600 font-semibold">
                        <li className="flex items-center gap-3"><span className="text-green-500">✓</span> Reducere studentă</li>
                        <li className="flex items-center gap-3"><span className="text-green-500">✓</span> Acces între 07:00 - 16:00</li>
                        <li className="flex items-center gap-3"><span className="text-green-500">✓</span> Zona Cardio</li>
                    </ul>
                    <button className="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold group-hover:bg-[color:var(--primary)] transition-colors">{plans.cta_label}</button>
                </div>
            </div>
        </section>
    );
}
