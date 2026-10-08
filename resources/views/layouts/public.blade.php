<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $siteName = config('hospital.name');
        $pageTitle = trim($__env->yieldContent('title')) ?: $siteName.' — '.config('hospital.tagline');
        $pageDescription = trim($__env->yieldContent('description')) ?: config('hospital.description');
        $canonical = url()->current();
        $ogImage = asset('og-image.png');
        $nonce = request()->attributes->get('csp_nonce');
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    @hasSection('noindex')
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow">
    @endif
    <meta name="theme-color" content="#1F4B47">
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="Amani">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="48x48">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $siteName }} — {{ config('hospital.tagline') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    @hasSection('noindex')
    @else
    <script type="application/ld+json" nonce="{{ $nonce }}">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Hospital',
        'name' => $siteName,
        'url' => url('/'),
        'logo' => asset('icon-512.png'),
        'image' => $ogImage,
        'telephone' => config('hospital.phone'),
        'email' => config('hospital.email'),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    <style nonce="{{ $nonce }}">
        :root {
            --cream: #FAF6EE;
            --cream-deep: #F1EADA;
            --ink: #1C2B27;
            --teal: #1F4B47;
            --teal-deep: #143634;
            --teal-tint: #E8F0EE;
            --coral: #E1663F;
            --coral-deep: #C4502D;
        }
        body { background: var(--cream); color: var(--ink); font-family: 'IBM Plex Sans', sans-serif; }
        .font-display { font-family: 'Fraunces', serif; }
        .grain::before {
            content: ""; position: fixed; inset: 0; pointer-events: none; z-index: 40;
            opacity: 0.035;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='120'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='2'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        }
        @media (prefers-reduced-motion: reduce) { .rise { animation: none !important; } * { scroll-behavior: auto !important; transition: none !important; } }
        @keyframes riseIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        .rise { animation: riseIn 0.7s cubic-bezier(.16,1,.3,1) both; }
        .rise-1 { animation-delay: .05s; } .rise-2 { animation-delay: .15s; }
        .rise-3 { animation-delay: .25s; } .rise-4 { animation-delay: .35s; }

        :focus-visible {
    outline: 2px solid var(--coral);
    outline-offset: 2px;
    border-radius: 4px;
}
.skip-link {
    position: absolute; left: -999px; top: 0; z-index: 50;
    background: var(--teal-deep); color: var(--cream);
    padding: 0.75rem 1.25rem; border-radius: 0 0 0.5rem 0;
}
.skip-link:focus { left: 0; }

    </style>
</head>
<body class="grain antialiased">
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <header class="border-b border-[var(--teal)]/10 sticky top-0 z-30 bg-[var(--cream)]/90 backdrop-blur-sm">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-display text-2xl tracking-tight text-[var(--teal-deep)]">
                Amani<span class="text-[var(--coral)]">.</span>Hospital
            </a>
            <nav aria-label="Primary" class="hidden md:flex items-center gap-8 text-sm font-medium text-[var(--teal-deep)]/80">
                <a href="{{ route('home') }}#services" class="hover:text-[var(--coral)] transition-colors">Services</a>
                <a href="{{ route('home') }}#doctors" class="hover:text-[var(--coral)] transition-colors">Doctors</a>
                <a href="{{ route('home') }}#contact" class="hover:text-[var(--coral)] transition-colors">Contact</a>
            </nav>
            <a href="{{ route('patient.register') }}"
               class="bg-[var(--teal)] text-[var(--cream)] px-5 py-2.5 rounded-full text-sm font-medium hover:bg-[var(--teal-deep)] transition-all hover:scale-[1.03] shadow-sm">
                Book a Consultation
            </a>
        </div>
    </header>

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="bg-[var(--teal-deep)] text-[var(--cream)]/70 mt-24">
        <div class="max-w-6xl mx-auto px-6 py-12 grid md:grid-cols-3 gap-8 text-sm">
            <div>
                <div class="font-display text-xl text-[var(--cream)] mb-2">Amani.Hospital</div>
                <p>Patient-first care, from registration to recovery.</p>
            </div>
            <div>
                <div class="text-[var(--cream)] font-medium mb-2">Working Hours</div>
                <p>Mon – Sat: 7:00 AM – 8:00 PM</p>
                <p>Emergency: 24/7</p>
            </div>
            <div id="contact">
                <div class="text-[var(--cream)] font-medium mb-2">Contact</div>
                <p><a href="tel:{{ preg_replace('/[^+\d]/', '', config('hospital.phone')) }}" class="hover:text-[var(--cream)] underline-offset-2 hover:underline">{{ config('hospital.phone') }}</a></p>
                <p><a href="mailto:{{ config('hospital.email') }}" class="hover:text-[var(--cream)] underline-offset-2 hover:underline">{{ config('hospital.email') }}</a></p>
                @if(config('hospital.address'))<p>{{ config('hospital.address') }}</p>@endif
            </div>
        </div>
        <div class="border-t border-[var(--cream)]/10 text-center text-xs py-4">
            &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved. · <a href="{{ route('privacy') }}" class="underline-offset-2 hover:underline">Privacy Policy</a>
        </div>
    </footer>

</body>
</html>