import React from 'react';

export default function Features({ features }) {
    if (!features.items || features.items.length === 0) return null;

    return (
        <section className="py-24 bg-slate-50">
            <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
                {features.items.map((item, idx) => item.visible && (
                    <div key={idx} className="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-4 hover:shadow-xl transition-shadow group">
                        {item.icon && (
                            <div className="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform" style={{ color: 'var(--primary)', backgroundColor: 'var(--primary)15' }}>
                                {item.icon}
                            </div>
                        )}
                        <h3 className="text-2xl font-bold text-slate-900">{item.title}</h3>
                        <p className="text-slate-500 leading-relaxed">{item.text}</p>
                    </div>
                ))}
            </div>
        </section>
    );
}
