@extends('layouts.public')

@section('content')

<section class="max-w-6xl mx-auto px-6 pt-16 pb-20 grid md:grid-cols-2 gap-12 items-start">
    <div class="rise rise-1">
        <span class="inline-block bg-[var(--teal-tint)] text-[var(--teal-deep)] text-xs font-medium px-3 py-1.5 rounded-full mb-5">
            Now accepting online registrations
        </span>
        <h1 class="font-display text-5xl md:text-6xl leading-[1.05] text-[var(--teal-deep)] mb-6">
            Care that starts <em class="text-[var(--coral)] not-italic">before</em> you arrive.
        </h1>
        <p class="text-lg text-[var(--ink)]/70 mb-8 max-w-md">
            Register online, tell us what's bothering you, and we'll match you with the right doctor — no queues, no paperwork at the door.
        </p>
        <div class="flex flex-wrap items-center gap-5">
            <a href="{{ route('patient.register') }}"
               class="bg-[var(--coral)] text-white px-7 py-3.5 rounded-full font-medium hover:bg-[var(--coral-deep)] transition-all hover:scale-[1.03] shadow-lg shadow-[var(--coral)]/20 min-h-[44px] inline-flex items-center">
                Register for Consultation
            </a>
            <a href="#services" class="text-[var(--teal-deep)] font-medium text-sm hover:text-[var(--coral)] transition-colors min-h-[44px] inline-flex items-center">
                Explore services →
            </a>
        </div>
    </div>

    <div class="rise rise-2">
        <div class="aspect-[4/3] rounded-[2rem] bg-[var(--teal)] relative overflow-hidden shadow-2xl">
            <div class="absolute inset-0 bg-gradient-to-br from-[var(--teal)] via-[var(--teal-deep)] to-[var(--ink)] opacity-90"></div>
            <div class="absolute bottom-7 left-7 right-7 text-[var(--cream)]">
                <div class="font-display text-3xl mb-1">{{ $doctors->count() }} {{ \Illuminate\Support\Str::plural('Specialist', $doctors->count()) }}</div>
                <div class="text-sm opacity-70">across {{ $departments->count() }} {{ \Illuminate\Support\Str::plural('department', $departments->count()) }}</div>
            </div>
        </div>
    </div>
</section>

<section id="services" class="max-w-6xl mx-auto px-6 py-20 border-t border-[var(--teal)]/10">
    <h2 class="font-display text-3xl text-[var(--teal-deep)] mb-2 rise">Our Departments</h2>
    <p class="text-[var(--ink)]/60 mb-12 rise rise-1">Choose a department to see available doctors, or just register and we'll route you correctly.</p>

    <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
        @forelse($departments as $i => $department)
        <div class="rise bg-white/60 border border-[var(--teal)]/10 rounded-2xl p-6 hover:border-[var(--coral)]/40 hover:-translate-y-1 transition-all duration-300"
             style="animation-delay: {{ 0.05 * $i }}s">
            <div class="w-10 h-10 rounded-full bg-[var(--teal-tint)] flex items-center justify-center text-[var(--teal-deep)] font-display text-lg mb-4" aria-hidden="true">
                {{ substr($department->name, 0, 1) }}
            </div>
            <div class="font-medium text-[var(--teal-deep)]">{{ $department->name }}</div>
            <div class="text-xs text-[var(--ink)]/50 mt-1">
                {{ $doctors->where('department_id', $department->id)->count() }} doctor(s) available
            </div>
        </div>
        @empty
        <p class="rounded-2xl border border-dashed border-[var(--teal)]/25 bg-white/50 px-6 py-10 text-center text-[var(--ink)]/60 col-span-full">Our departments are being set up. Please check back soon, or call us on {{ config('hospital.phone') }}.</p>
        @endforelse
    </div>
</section>

<section id="doctors" class="max-w-6xl mx-auto px-6 py-20 border-t border-[var(--teal)]/10">
    <h2 class="font-display text-3xl text-[var(--teal-deep)] mb-12 rise">Meet the Doctors</h2>
    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-6">
        @forelse($doctors->take(6) as $i => $doctor)
        <div class="rise bg-white/60 border border-[var(--teal)]/10 rounded-2xl p-6" style="animation-delay: {{ 0.06 * $i }}s">
            <div class="w-14 h-14 rounded-full bg-[var(--teal)] text-[var(--cream)] font-display text-xl flex items-center justify-center mb-4" aria-hidden="true">
                {{ substr(str_replace('Dr. ', '', $doctor->name), 0, 1) }}
            </div>
            <div class="font-medium text-[var(--teal-deep)]">{{ $doctor->name }}</div>
            <div class="text-sm text-[var(--ink)]/60">{{ $doctor->specialization }}</div>
            <div class="text-xs text-[var(--coral)] mt-2">{{ $doctor->department->name ?? '' }}</div>
        </div>
        @empty
        <p class="rounded-2xl border border-dashed border-[var(--teal)]/25 bg-white/50 px-6 py-10 text-center text-[var(--ink)]/60 col-span-full">Our doctor profiles will appear here soon.</p>
        @endforelse
    </div>
</section>

<section class="max-w-4xl mx-auto px-6 py-24 text-center">
    <h2 class="font-display text-4xl text-[var(--teal-deep)] mb-4 rise">Ready when you are.</h2>
    <p class="text-[var(--ink)]/60 mb-8 rise rise-1">Registration takes about three minutes.</p>
    <a href="{{ route('patient.register') }}"
       class="inline-flex items-center bg-[var(--coral)] text-white px-8 py-4 rounded-full font-medium hover:bg-[var(--coral-deep)] transition-all hover:scale-[1.03] shadow-lg shadow-[var(--coral)]/20 rise rise-2 min-h-[44px]">
        Start Registration
    </a>
</section>

@endsection