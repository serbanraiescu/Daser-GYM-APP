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

            <div className={`max-w-7xl mx-auto px-6 grid gap-8 text-left ${plans.items.length === 1 ? 'md:grid-cols-1 max-w-lg' :
                    plans.items.length === 2 ? 'md:grid-cols-2 max-w-4xl' :
                        'md:grid-cols-3'
                }`}>
                {plans.items.map((plan, index) => {
                    const isPremium = plan.name.toLowerCase().includes('vip') || plan.is_featured;

                    return (
                        <div
                            key={index}
                            className={`p-10 rounded-[3rem] border-2 transition-all group relative overflow-hidden flex flex-col h-full ${isPremium
                                    ? 'text-white shadow-2xl scale-105 z-10'
                                    : 'border-slate-100 hover:border-[color:var(--primary)] bg-white/50 backdrop-blur-sm'
                                }`}
                            style={isPremium ? {
                                borderColor: 'var(--primary)',
                                backgroundColor: 'var(--primary)',
                                backgroundImage: 'linear-gradient(135deg, var(--primary) 0%, #1e1b4b 100%)'
                            } : {}}
                        >
                            {isPremium && (
                                <div className="absolute -top-10 -right-10 w-40 h-40 bg-white opacity-10 blur-3xl rounded-full"></div>
                            )}

                            <div className={`font-bold uppercase tracking-widest text-[10px] mb-6 ${isPremium ? 'text-blue-100' : 'text-slate-400'}`}>
                                {isPremium ? 'Cea mai bună valoare' : 'Abonament Gym'}
                            </div>

                            <h4 className="text-3xl font-black mb-4 tracking-tight">{plan.name}</h4>

                            {plans.show_prices && (
                                <div className="flex items-baseline gap-2 mb-8">
                                    <span className={`text-5xl font-black ${isPremium ? 'text-white' : 'text-slate-900'}`}>
                                        {Math.round(plan.price)}
                                    </span>
                                    <span className={`font-bold ${isPremium ? 'text-blue-200' : 'text-slate-400'}`}>
                                        RON / {plan.duration}
                                    </span>
                                </div>
                            )}

                            {plan.description && (
                                <p className={`mb-8 text-sm leading-relaxed ${isPremium ? 'text-blue-100' : 'text-slate-500'}`}>
                                    {plan.description}
                                </p>
                            )}

                            <ul className={`space-y-4 mb-10 font-semibold flex-grow ${isPremium ? 'text-blue-50' : 'text-slate-600'}`}>
                                {plan.features.map((feature, fIndex) => (
                                    <li key={fIndex} className="flex items-center gap-3">
                                        <span className={isPremium ? "text-white" : "text-[color:var(--primary)]"}>✓</span>
                                        {feature}
                                    </li>
                                ))}
                            </ul>

                            <button className={`w-full py-4 rounded-2xl font-bold transition-all shadow-lg ${isPremium
                                    ? 'bg-white text-slate-900 hover:scale-[1.02]'
                                    : 'bg-slate-900 text-white group-hover:bg-[color:var(--primary)]'
                                }`}>
                                {plans.cta_label}
                            </button>
                        </div>
                    );
                })}

                {plans.items.length === 0 && (
                    <div className="col-span-full py-12 text-center text-slate-400 font-medium italic">
                        Niciun abonament activ momentan. Te rugăm să revii curând!
                    </div>
                )}
            </div>
        </section>
    );
}
