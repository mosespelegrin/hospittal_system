@extends('layouts.public')

@section('title', 'Privacy Policy — '.config('hospital.name'))
@section('description', 'How '.config('hospital.name').' collects, uses and protects your personal and health information.')

@section('content')
{{-- NOTE: Template text. Have it reviewed against Tanzania's Personal Data Protection Act, 2022 before launch. --}}
<article class="max-w-3xl mx-auto px-6 py-16 space-y-6 leading-relaxed">
    <h1 class="font-display text-4xl text-[var(--teal-deep)]">Privacy Policy</h1>
    <p class="text-[var(--ink)]/60">Last updated: {{ now()->format('d F Y') }}</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">What we collect</h2>
    <p>When you book a consultation we collect your name, date of birth, gender, phone number, and optionally your email, address, emergency contact, blood group, allergies, medical history and the reason for your visit.</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">Why we collect it</h2>
    <p>We use this information only to register you as a patient, schedule and confirm your appointment, prepare your doctor for your visit, and contact you about it. We do not sell your information or use it for advertising.</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">Who can see it</h2>
    <p>Access is limited to authorised hospital staff. Doctors can see only the patients they have appointments with. Confirmation emails are sent through our email provider.</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">How long we keep it</h2>
    <p>Medical records are kept for as long as required by health-sector regulations and then securely deleted.</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">Your rights</h2>
    <p>You may ask us to show, correct, export or delete your personal information, or withdraw your consent, by contacting us at <a class="underline" href="mailto:{{ config('hospital.email') }}">{{ config('hospital.email') }}</a>. Some records must be retained by law.</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">Cookies</h2>
    <p>This site uses only essential cookies (session and security). It does not use advertising or analytics cookies. Fonts are loaded from Google Fonts.</p>

    <h2 class="font-display text-2xl text-[var(--teal-deep)] pt-4">Contact</h2>
    <p>{{ config('hospital.name') }} · {{ config('hospital.phone') }} · {{ config('hospital.email') }}</p>
</article>
@endsection
