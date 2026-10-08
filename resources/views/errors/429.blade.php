@extends('layouts.public')

@section('title', 'Too many requests — '.config('hospital.name'))
@section('noindex', '1')

@section('content')
<section class="max-w-xl mx-auto px-6 py-28 text-center">
    <p class="font-display text-7xl text-[var(--coral)] mb-4" aria-hidden="true">429</p>
    <h1 class="font-display text-3xl text-[var(--teal-deep)] mb-3">Too many requests</h1>
    <p class="text-[var(--ink)]/65 mb-8">You've made too many attempts in a short time. Please wait a minute and try again.</p>
    <a href="{{ route('home') }}" class="inline-flex items-center min-h-[44px] bg-[var(--teal)] text-[var(--cream)] px-6 py-2.5 rounded-full font-medium hover:bg-[var(--teal-deep)] transition-colors">Back to homepage</a>
</section>
@endsection
