<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} | {{ $brandName }}</title>
    @if($page->meta_description)
    <meta name="description" content="{{ $page->meta_description }}">
    @endif
    
    <!-- AI & SEO Schema Injection -->
    @if($pageSchema)
    <script type="application/ld+json">
    {!! json_encode($pageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    @if($faqSchema)
    <script type="application/ld+json">
    {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    <!-- Tailwind Config for Branding -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $primaryColor }}',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap');
        :root { --primary: {{ $primaryColor }}; }
        body { font-family: 'Outfit', sans-serif; }
        .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.8); }
    </style>
</head>
<body class="bg-gray-50 text-slate-900 antialiased min-h-screen flex flex-col pt-20">

    <!-- Global Header -->
    <nav class="fixed top-0 w-full z-50 glass border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3">
                @if($logo)
                    <img src="{{ str_starts_with($logo, 'http') ? $logo : asset('storage/' . $logo) }}" alt="{{ $brandName }} Logo" class="h-10 w-auto">
                @endif
                <span class="font-bold text-2xl tracking-tight text-slate-900">{{ $brandName }}</span>
            </a>

            <div class="hidden md:flex items-center gap-10 font-semibold text-slate-600">
                @foreach($navItems as $item)
                    @if($item['visible'] ?? true)
                        <a href="{{ $item['href'] }}" class="hover:text-[color:var(--primary)] transition-colors">{{ $item['label'] }}</a>
                    @endif
                @endforeach
                
                <a href="{{ $cta['href'] }}" class="text-white px-6 py-2.5 rounded-full hover:opacity-90 transition-all shadow-lg" style="background-color: var(--primary)">
                    {{ $cta['label'] }}
                </a>
            </div>
            
            <div class="md:hidden">
                <a href="/" class="text-sm font-bold text-[color:var(--primary)]">ACASĂ</a>
            </div>
        </div>
    </nav>

    <!-- Page Hero Banner -->
    <section class="relative pt-32 pb-24 overflow-hidden" style="background: linear-gradient(135deg, {{ $secondaryColor }}, #000)">
        <!-- Subtle Glow Effects -->
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-[color:var(--primary)] opacity-10 blur-[120px] rounded-full -mr-64 -mt-64"></div>
        <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-[color:var(--primary)] opacity-5 blur-[80px] rounded-full -ml-32 -mb-32"></div>
        
        <div class="max-w-7xl mx-auto px-6 relative z-10 text-center">
            <span class="inline-block text-[color:var(--primary)] font-bold tracking-[0.2em] uppercase text-xs mb-4">INFORMAȚII {{ $brandName }}</span>
            <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight">
                {{ $page->title }}
            </h1>
        </div>
    </section>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full -mt-10 relative z-20">
        <article class="bg-white rounded-[2rem] shadow-2xl border border-gray-100 p-8 sm:p-14">
            <div class="prose prose-slate prose-primary max-w-none prose-headings:text-slate-950 prose-a:text-[color:var(--primary)] prose-lg leading-relaxed">
                {!! $page->content !!}
            </div>
            
            @if(!empty($page->faq_data) && is_array($page->faq_data) && count($page->faq_data) > 0)
            <div class="mt-12 pt-12 border-t border-gray-100">
                <h2 class="text-2xl font-bold text-slate-950 mb-8 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center text-white" style="background-color: var(--primary)">?</span>
                    Întrebări Frecvente
                </h2>
                <div class="space-y-6">
                    @foreach($page->faq_data as $qa)
                        @if(isset($qa['question']) && isset($qa['answer']))
                        <div class="bg-gray-50/50 rounded-2xl p-6 border border-gray-100">
                            <h3 class="text-lg font-bold text-slate-900 mb-3">{{ $qa['question'] }}</h3>
                            <p class="text-slate-600 leading-relaxed">{{ $qa['answer'] }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </article>
    </main>

    <!-- Global Footer -->
    <footer class="bg-slate-950 text-white pt-24 pb-12 mt-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-16 mb-20">
                <div class="col-span-2 space-y-8 text-center md:text-left">
                    <a href="/" class="flex items-center gap-3 justify-center md:justify-start">
                        @if($logo)
                            <img src="{{ str_starts_with($logo, 'http') ? $logo : asset('storage/' . $logo) }}" alt="Logo Footer" class="h-10 w-auto invert opacity-80">
                        @endif
                        <span class="font-bold text-3xl tracking-tight text-white">{{ $brandName }}</span>
                    </a>
                    <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                        {{ $footerText }}
                    </p>
                </div>

                <div class="space-y-6">
                    <h5 class="font-bold text-xl text-white">Informații</h5>
                    <ul class="space-y-4 text-slate-400">
                        @foreach($footerLinks as $link)
                            @if($link['visible'] ?? true)
                                <li><a href="{{ $link['href'] }}" class="hover:text-white transition-colors">{{ $link['label'] }}</a></li>
                            @endif
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-6">
                    <h5 class="font-bold text-xl text-white">Social Media</h5>
                    <div class="flex gap-4 flex-wrap justify-center md:justify-start">
                        @if($socials['facebook']) <a href="{{ $socials['facebook'] }}" target="_blank" class="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">FB</a> @endif
                        @if($socials['instagram']) <a href="{{ $socials['instagram'] }}" target="_blank" class="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">IG</a> @endif
                        @if($socials['tiktok']) <a href="{{ $socials['tiktok'] }}" target="_blank" class="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">TT</a> @endif
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-900 pt-12 flex flex-col md:flex-row justify-between items-center text-slate-500 text-sm gap-4">
                <div class="text-center md:text-left">
                    <p>&copy; {{ date('Y') }} Daser Enterprise SRL. Licensed Software – All Rights Reserved</p>
                    <p class="mt-1 opacity-50 text-xs text-slate-600 tracking-wider">v{{ $version }}</p>
                </div>
                <p>Powered by <a href="https://daserdesign.ro" target="_blank" class="font-semibold hover:text-white transition-colors">Daser Technologies</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
