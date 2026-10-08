@extends('layouts.public')

@section('title', 'Book a Consultation — Amani Hospital')
@section('description', 'Book a consultation at Amani Hospital in about three minutes. Choose a department and time and we will match you with an available doctor.')

@section('content')
<section class="max-w-3xl mx-auto px-6 py-16">

    <div class="mb-10 rise">
        <span class="inline-block bg-[var(--teal-tint)] text-[var(--teal-deep)] text-xs font-medium px-3 py-1.5 rounded-full mb-4">
            Takes about 3 minutes
        </span>
        <h1 class="font-display text-4xl text-[var(--teal-deep)] mb-2">Register for a Consultation</h1>
        <p class="text-[var(--ink)]/60">Fill in your details below. Fields marked <span class="text-[var(--coral)]">*</span> are required.</p>
    </div>

    @if ($errors->any())
    <div class="rise bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-4 mb-8" role="alert">
        <p class="font-medium mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside text-sm space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if ($departments->isEmpty())
    <div class="rounded-2xl border border-dashed border-[var(--teal)]/25 bg-white/60 px-6 py-12 text-center" role="status">
        <h2 class="font-display text-2xl text-[var(--teal-deep)] mb-2">Online booking isn't available yet</h2>
        <p class="text-[var(--ink)]/65">No departments are open for booking right now. Please call us on {{ config('hospital.phone') }}.</p>
    </div>
    @else
    <form method="POST" action="{{ route('patient.store') }}" class="space-y-10" id="booking-form" novalidate>
        @csrf

        {{-- Section 1: Personal Information --}}
        <div class="rise bg-white/70 border border-[var(--teal)]/10 rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-[var(--teal)] text-[var(--cream)] flex items-center justify-center text-sm font-medium" aria-hidden="true">1</div>
                <h2 class="font-display text-xl text-[var(--teal-deep)]">Personal Information</h2>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label for="full_name" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Full Name <span class="text-[var(--coral)]">*</span></label>
                    <input type="text" id="full_name" name="full_name" autocomplete="name" value="{{ old('full_name') }}" required
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('full_name') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Date of Birth <span class="text-[var(--coral)]">*</span></label>
                    <input type="date" id="date_of_birth" name="date_of_birth" autocomplete="bday" min="1900-01-01" value="{{ old('date_of_birth') }}" required
                        max="{{ now()->toDateString() }}"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('date_of_birth') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="gender" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Gender <span class="text-[var(--coral)]">*</span></label>
                    <select id="gender" name="gender" required
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                        <option value="">Select…</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                    </select>
                    @error('gender') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Phone Number <span class="text-[var(--coral)]">*</span></label>
                    <input type="tel" id="phone" name="phone" autocomplete="tel" inputmode="tel" value="{{ old('phone') }}" required placeholder="07XX XXX XXX"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('phone') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" autocomplete="email" value="{{ old('email') }}"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('email') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Address</label>
                    <textarea id="address" name="address" autocomplete="street-address" rows="2"
                        class="w-full px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">{{ old('address') }}</textarea>
                    @error('address') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="emergency_contact" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Emergency Contact (name & phone)</label>
                    <input type="text" id="emergency_contact" name="emergency_contact" inputmode="tel" value="{{ old('emergency_contact') }}"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('emergency_contact') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Medical Information --}}
        <div class="rise bg-white/70 border border-[var(--teal)]/10 rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-[var(--teal)] text-[var(--cream)] flex items-center justify-center text-sm font-medium" aria-hidden="true">2</div>
                <h2 class="font-display text-xl text-[var(--teal-deep)]">Medical Information</h2>
                <span class="text-xs text-[var(--ink)]/40">(optional, helps your doctor prepare)</span>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label for="blood_group" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Blood Group</label>
                    <select id="blood_group" name="blood_group"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                        <option value="">Unknown</option>
                        @foreach(['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg)
                            <option value="{{ $bg }}" @selected(old('blood_group') === $bg)>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label for="allergies" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Known Allergies</label>
                    <textarea id="allergies" name="allergies" maxlength="2000" rows="2" placeholder="e.g. Penicillin, none known…"
                        class="w-full px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">{{ old('allergies') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="medical_history" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Relevant Medical History</label>
                    <textarea id="medical_history" name="medical_history" maxlength="5000" rows="3" placeholder="Chronic conditions, past surgeries, current medication…"
                        class="w-full px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">{{ old('medical_history') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Section 3: Appointment Request --}}
        <div class="rise bg-white/70 border border-[var(--teal)]/10 rounded-2xl p-6 md:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-8 h-8 rounded-full bg-[var(--teal)] text-[var(--cream)] flex items-center justify-center text-sm font-medium" aria-hidden="true">3</div>
                <h2 class="font-display text-xl text-[var(--teal-deep)]">Appointment Request</h2>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div>
                    <label for="department_id" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Department <span class="text-[var(--coral)]">*</span></label>
                    <select id="department_id" name="department_id" required
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                        <option value="">Select department…</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="doctor_id" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Preferred Doctor <span class="text-xs text-[var(--ink)]/40 font-normal">(optional)</span></label>
                    <select id="doctor_id" name="doctor_id"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                        <option value="">No preference — assign any available doctor</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" data-department="{{ $doctor->department_id }}"
                                @selected((string) old('doctor_id') === (string) $doctor->id)>
                                {{ $doctor->name }} — {{ $doctor->department->name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Preferred Date <span class="text-[var(--coral)]">*</span></label>
                    <input type="date" id="appointment_date" name="appointment_date" value="{{ old('appointment_date') }}" required
                        min="{{ now()->toDateString() }}"
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('appointment_date') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-[var(--ink)] mb-1.5">Preferred Time <span class="text-[var(--coral)]">*</span></label>
                    <input type="time" id="appointment_time" name="appointment_time" value="{{ old('appointment_time') }}" required
                        class="w-full min-h-[44px] px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">
                    @error('appointment_time') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="chief_complaint" class="block text-sm font-medium text-[var(--ink)] mb-1.5">What's bothering you? <span class="text-[var(--coral)]">*</span></label>
                    <textarea id="chief_complaint" name="chief_complaint" maxlength="2000" rows="3" required
                        placeholder="Briefly describe your symptoms or reason for visit…"
                        class="w-full px-4 py-2.5 rounded-xl border border-[var(--teal)]/20 bg-white focus:border-[var(--teal)] focus:ring-2 focus:ring-[var(--teal)]/20 outline-none transition-all">{{ old('chief_complaint') }}</textarea>
                    @error('chief_complaint') <p class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="rise flex flex-col md:flex-row items-center gap-4 justify-between pt-2">
            <div class="max-w-sm">
                <label class="flex items-start gap-3 text-xs text-[var(--ink)]/70 min-h-[44px]">
                    <input type="checkbox" name="consent" value="1" required @checked(old('consent'))
                        class="mt-0.5 h-4 w-4 rounded border-[var(--teal)]/40 text-[var(--teal)] focus:ring-[var(--teal)]"
                        @error('consent') aria-invalid="true" aria-describedby="consent-error" @enderror>
                    <span>I agree to the <a href="{{ route('privacy') }}" target="_blank" rel="noopener" class="underline text-[var(--teal-deep)]">privacy policy</a> and to being contacted by {{ config('hospital.name') }} about this appointment. <span class="text-[var(--coral)]">*</span></span>
                </label>
                @error('consent') <p id="consent-error" class="text-red-700 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" id="submit-btn"
                class="w-full md:w-auto disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100 bg-[var(--coral)] text-white px-8 py-3.5 rounded-full font-medium hover:bg-[var(--coral-deep)] transition-all hover:scale-[1.02] shadow-lg shadow-[var(--coral)]/20 min-h-[44px]">
                <span data-label>Submit Registration</span>
                <span data-busy class="hidden" role="status">Submitting…</span>
            </button>
        </div>
    </form>
    @endif
</section>

@unless($departments->isEmpty())
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    // Prevent double submission and show progress
    (function () {
        const form = document.getElementById('booking-form');
        const btn = document.getElementById('submit-btn');
        const setBusy = (busy) => {
            btn.disabled = busy;
            if (busy) { btn.setAttribute('aria-busy', 'true'); } else { btn.removeAttribute('aria-busy'); }
            btn.querySelector('[data-label]').classList.toggle('hidden', busy);
            btn.querySelector('[data-busy]').classList.toggle('hidden', !busy);
        };
        form.addEventListener('submit', function (e) {
            if (btn.disabled) { e.preventDefault(); return; }
            setBusy(true);
        });
        // Re-enable when restored from the back/forward cache
        window.addEventListener('pageshow', function (ev) { if (ev.persisted) { setBusy(false); } });
    })();
    // Progressive enhancement: filter doctor dropdown by selected department
    const deptSelect = document.getElementById('department_id');
    const doctorSelect = document.getElementById('doctor_id');
    const allDoctorOptions = Array.from(doctorSelect.options);

    deptSelect.addEventListener('change', function () {
        const selectedDept = this.value;
        doctorSelect.innerHTML = '';
        doctorSelect.appendChild(allDoctorOptions[0].cloneNode(true)); // "No preference" option

        allDoctorOptions.slice(1).forEach(opt => {
            if (!selectedDept || opt.dataset.department === selectedDept) {
                doctorSelect.appendChild(opt.cloneNode(true));
            }
        });
    });
</script>
@endunless
@endsection