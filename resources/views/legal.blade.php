@extends('layouts.gym')

@section('title', $title)

@section('content')
    <main class="max-w-4xl mx-auto px-6 py-40 min-h-[60vh]">
        <h1 class="text-4xl font-extrabold mb-12 text-slate-900 tracking-tight border-b border-slate-100 pb-8">{{ $title }}</h1>
        <div class="prose prose-slate prose-lg max-w-none prose-headings:text-slate-900 prose-headings:font-bold prose-p:text-slate-600 prose-p:leading-relaxed">
            {!! $content !!}
        </div>
    </main>
@endsection
