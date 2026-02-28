<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $gymName) | Fitness de Elită</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap');
        body { 
            font-family: 'Outfit', sans-serif; 
            --primary: {{ $primaryColor }};
        }
        .bg-primary { background-color: var(--primary); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        .glass {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
    @yield('styles')
</head>
<body class="bg-white text-slate-900 overflow-x-hidden">
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 glass border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-3">
                    @if($gymLogo)
                        <img src="/storage/{{ $gymLogo }}" alt="{{ $gymName }}" class="h-10 w-auto">
                    @endif
                    <span class="font-bold text-2xl tracking-tight text-slate-900">{{ $gymName }}</span>
                </a>
            </div>
            <div class="hidden md:flex items-center gap-10 font-semibold text-slate-600">
                <a href="/#acasa" class="hover:text-primary transition-colors">Acasa</a>
                <a href="/#abonamente" class="hover:text-primary transition-colors">Abonamente</a>
                <a href="/#contact" class="hover:text-primary transition-colors">Contact</a>
                <a href="/app/login" class="bg-primary text-white px-6 py-2.5 rounded-full hover:opacity-90 transition-all shadow-lg shadow-blue-500/20">
                    Autentificare
                </a>
            </div>
        </div>
    </nav>

    @yield('content')

    <!-- Footer -->
    <footer id="contact" class="bg-slate-950 text-white pt-24 pb-12">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-16 mb-20">
                <div class="col-span-2 space-y-8">
                    <a href="/" class="flex items-center gap-3">
                        @if($gymLogo)
                            <img src="/storage/{{ $gymLogo }}" alt="{{ $gymName }}" class="h-10 w-auto invert">
                        @endif
                        <span class="font-bold text-3xl tracking-tight">{{ $gymName }}</span>
                    </a>
                    <p class="text-slate-400 text-lg leading-relaxed max-w-md">
                        Misiunea noastră este să oferim un mediu premium și inspirațional pentru oricine dorește să își depășească limitele și să trăiască o viață mai sănătoasă.
                    </p>
                </div>
                <div class="space-y-6">
                    <h5 class="font-bold text-xl">Informații</h5>
                    <ul class="space-y-4 text-slate-400">
                        <li><a href="/politica-confidentialitate" class="hover:text-white transition-colors">Politica de Confidențialitate</a></li>
                        <li><a href="/termeni-si-conditii" class="hover:text-white transition-colors">Termeni și Condiții</a></li>
                        <li><a href="/#abonamente" class="hover:text-white transition-colors">Tarife Facilități</a></li>
                    </ul>
                </div>
                <div class="space-y-6">
                    <h5 class="font-bold text-xl">Social Media</h5>
                    <div class="flex gap-4">
                        @if($facebook)
                            <a href="{{ $facebook }}" target="_blank" class="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">FB</a>
                        @endif
                        @if($instagram)
                            <a href="{{ $instagram }}" target="_blank" class="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">IG</a>
                        @endif
                        @if($tiktok)
                            <a href="{{ $tiktok }}" target="_blank" class="w-12 h-12 rounded-full border border-slate-800 flex items-center justify-center hover:bg-white hover:text-black transition-all">TT</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-900 pt-12 flex flex-col md:flex-row justify-between items-center text-slate-500 text-sm gap-4">
                <div class="text-center md:text-left">
                    <p>&copy; {{ date('Y') }} Daser Enterprise SRL. Licensed Software – All Rights Reserved</p>
                </div>
                <p>Powered by <a href="https://daserdesign.ro" target="_blank" class="font-semibold hover:text-white transition-colors">Daser Technologies</a></p>
            </div>
        </div>
    </footer>
    @yield('scripts')
</body>
</html>
