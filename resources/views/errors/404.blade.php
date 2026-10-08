@extends('layouts.public')

@section('title', 'Page not found — '.config('hospital.name'))
@section('noindex', '1')

@section('content')
<section class="max-w-xl mx-auto px-6 py-28 text-center">
    <p class="font-display text-7xl text-[var(--coral)] mb-4" aria-hidden="true">404</p>
    <h1 class="font-display text-3xl text-[var(--teal-deep)] mb-3">Page not found</h1>
    <p class="text-[var(--ink)]/65 mb-8">The page you're looking for doesn't exist or the link has expired.</p>
    <a href="{{ route('home') }}" class="inline-flex items-center min-h-[44px] bg-[var(--teal)] text-[var(--cream)] px-6 py-2.5 rounded-full font-medium hover:bg-[var(--teal-deep)] transition-colors">Back to homepage</a>
</section>
@endsection
