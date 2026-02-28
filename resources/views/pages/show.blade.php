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
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-slate-900 antialiased min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3">
                @if($logo)
                    <img src="{{ str_starts_with($logo, 'http') ? $logo : asset('storage/' . $logo) }}" alt="{{ $brandName }} Logo" class="h-10 w-auto">
                @endif
                <span class="text-xl font-bold tracking-tight text-slate-800">{{ $brandName }}</span>
            </a>
            <a href="/" class="text-sm font-semibold text-primary hover:underline">
                &larr; Înapoi pe Acasă
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full">
        <article class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 sm:p-12">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 mb-8 border-b border-gray-100 pb-6">
                {{ $page->title }}
            </h1>
            
            <div class="prose prose-slate prose-primary max-w-none">
                {!! $page->content !!}
            </div>
            
            @if(!empty($page->faq_data) && is_array($page->faq_data) && count($page->faq_data) > 0)
            <div class="mt-12 pt-8 border-t border-gray-100">
                <h2 class="text-2xl font-bold text-slate-900 mb-6">Întrebări Frecvente</h2>
                <div class="space-y-6">
                    @foreach($page->faq_data as $qa)
                        @if(isset($qa['question']) && isset($qa['answer']))
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-slate-900 mb-2">{{ $qa['question'] }}</h3>
                            <p class="text-slate-600">{{ $qa['answer'] }}</p>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </article>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 py-12 text-center text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center">
            @if($logo)
                <img src="{{ str_starts_with($logo, 'http') ? $logo : asset('storage/' . $logo) }}" alt="Logo Footer" class="h-8 opacity-75 grayscale mb-6">
            @endif
            <p class="text-sm">&copy; {{ date('Y') }} {{ $brandName }}. {{ $copyright }}</p>
        </div>
    </footer>

</body>
</html>
