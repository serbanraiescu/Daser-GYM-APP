import React from 'react';

export default function Hero({ hero }) {
    return (
        <section id="acasa" className="relative pt-40 pb-20 overflow-hidden">
            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10">
                <div className="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] opacity-[0.03] blur-[120px] rounded-full" style={{ backgroundColor: 'var(--primary)' }}></div>
                <div className="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] opacity-[0.03] blur-[120px] rounded-full" style={{ backgroundColor: 'var(--primary)' }}></div>
            </div>

            <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
                <div className="space-y-8">
                    <h1 className="text-6xl md:text-8xl font-extrabold leading-[1.1] tracking-tight text-slate-900">
                        {hero.title}
                    </h1>
                    {hero.subtitle && (
                        <p className="text-xl text-slate-500 max-w-lg leading-relaxed">
                            {hero.subtitle}
                        </p>
                    )}

                    <div className="flex flex-wrap gap-4 pt-4">
                        {hero.primary_button && (
                            <a href={hero.primary_button.href} className="px-10 py-5 text-white rounded-2xl font-bold text-lg hover:scale-105 transition-all shadow-2xl shadow-blue-500/30" style={{ backgroundColor: 'var(--primary)' }}>
                                {hero.primary_button.label}
                            </a>
                        )}
                        {hero.secondary_button && (
                            <a href={hero.secondary_button.href} className="px-10 py-5 bg-white text-slate-900 border border-slate-200 rounded-2xl font-bold text-lg hover:bg-slate-50 transition-all">
                                {hero.secondary_button.label}
                            </a>
                        )}
                    </div>
                </div>

                {hero.image_url && (
                    <div className="relative flex justify-center">
                        <div className="w-full h-[500px] bg-slate-100 rounded-[3rem] overflow-hidden shadow-2xl animate-float" style={{ animation: 'float 6s ease-in-out infinite' }}>
                            <img src={hero.image_url} className="w-full h-full object-cover grayscale-[0.2] hover:scale-110 transition-duration-1000" alt="Hero" />
                        </div>
                    </div>
                )}
            </div>
            <style>{`
                @keyframes float {
                    0% { transform: translateY(0px); }
                    50% { transform: translateY(-20px); }
                    100% { transform: translateY(0px); }
                }
            `}</style>
        </section>
    );
}
