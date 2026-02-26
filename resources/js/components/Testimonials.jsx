import React from 'react';

export default function Testimonials({ testimonials }) {
    if (!testimonials.items || testimonials.items.length === 0) return null;

    return (
        <section className="py-24 bg-white text-center space-y-16">
            <h2 className="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">{testimonials.title}</h2>
            <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-8 text-left">
                {testimonials.items.map((item, idx) => item.visible !== false && (
                    <div key={idx} className="p-10 rounded-[3rem] bg-slate-50 border border-slate-100 hover:shadow-lg transition-all">
                        <div className="flex items-center gap-4 mb-6">
                            {item.photo_url ? (
                                <img src={'/storage/' + item.photo_url} alt={item.name} className="w-16 h-16 rounded-full object-cover" />
                            ) : (
                                <div className="w-16 h-16 rounded-full bg-slate-200 flex items-center justify-center text-slate-500 font-bold text-xl">
                                    {item.name.charAt(0)}
                                </div>
                            )}
                            <div>
                                <h4 className="font-bold text-lg text-slate-900">{item.name}</h4>
                                <p className="text-slate-500 text-sm">{item.role}</p>
                            </div>
                        </div>
                        <p className="text-slate-700 leading-relaxed italic">"{item.text}"</p>
                    </div>
                ))}
            </div>
        </section>
    );
}
