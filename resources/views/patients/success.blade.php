@extends('layouts.public')

@section('title', 'Registration Successful — Amani Hospital')

@section('content')
<section class="max-w-2xl mx-auto px-6 py-24 text-center">
    <div class="rise w-16 h-16 mx-auto mb-6 rounded-full bg-[var(--teal-tint)] flex items-center justify-center">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1F4B47" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20 6L9 17l-5-5"/>
        </svg>
    </div>

    <h1 class="rise rise-1 font-display text-4xl text-[var(--teal-deep)] mb-3">You're registered.</h1>
    <p class="rise rise-2 text-[var(--ink)]/60 mb-10">We've received your consultation request and will contact you to confirm.</p>

    <div class="rise rise-3 bg-white border border-[var(--teal)]/10 rounded-2xl p-8 text-left space-y-4 shadow-sm">
        <div class="flex justify-between items-center border-b border-[var(--teal)]/10 pb-4">
            <span class="text-sm text-[var(--ink)]/50">Patient Number</span>
            <span class="font-display text-xl text-[var(--teal-deep)]">{{ $patient->patient_number }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-[var(--ink)]/50">Full Name</span>
            <span class="font-medium text-[var(--ink)]">{{ $patient->full_name }}</span>
        </div>
        @php $appointment = $patient->appointments()->latest()->first(); @endphp
        @if($appointment)
        <div class="flex justify-between items-center">
            <span class="text-sm text-[var(--ink)]/50">Doctor</span>
            <span class="font-medium text-[var(--ink)]">{{ $appointment->doctor->name ?? 'To be assigned' }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-[var(--ink)]/50">Requested Date</span>
            <span class="font-medium text-[var(--ink)]">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }} at {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-[var(--ink)]/50">Status</span>
            <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-1 rounded-full capitalize">{{ $appointment->status }}</span>
        </div>
        @endif
    </div>

    <p class="rise rise-4 text-xs text-[var(--ink)]/40 mt-6">Please save your patient number — you'll need it for check-in.</p>

    <a href="{{ route('home') }}" class="rise rise-4 inline-flex items-center min-h-[44px] mt-8 text-[var(--teal-deep)] font-medium hover:text-[var(--coral)] transition-colors">
        ← Back to homepage
    </a>
</section>
@endsection