@php
    $meetingSlots = config('portfolio.meeting_slots', []);
    $meetingTypes = config('portfolio.meeting_types', []);
    $minDate = now()->toDateString();
    $maxDate = now()->addDays(60)->toDateString();
@endphp

<section class="section page-section meeting-booking-section" id="book-meeting">
    <div class="section-header section-header-compact animate-on-scroll">
        <div class="section-badge">Book a Meeting</div>
        <h2>Choose Your <span class="gradient-text">Date & Slot</span></h2>
        <p class="section-subtitle">Select a date, time slot, and share meeting details. We’ll email the confirmation to you.</p>
    </div>

    @if(session('meeting_success'))
        <div class="meeting-alert meeting-alert-success animate-on-scroll" role="status">
            {{ session('meeting_success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="meeting-alert meeting-alert-error animate-on-scroll" role="alert">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('meeting.book') }}" class="detail-card booking-form meeting-booking-form animate-on-scroll" id="meetingBookingForm">
        @csrf

        <div class="meeting-form-block">
            <h3>1. Meeting schedule</h3>
            <div class="form-grid">
                <label class="meeting-field">
                    <span>Preferred date *</span>
                    <div class="meeting-date-picker" id="meetingDatePicker">
                        <input
                            type="date"
                            name="meeting_date"
                            id="meetingDate"
                            value="{{ old('meeting_date') }}"
                            min="{{ $minDate }}"
                            max="{{ $maxDate }}"
                            required
                        >
                        <button type="button" class="meeting-date-calendar-btn" id="meetingDateOpenBtn" aria-label="Open calendar">
                            📅
                        </button>
                    </div>
                </label>

                <label class="meeting-field">
                    <span>Meeting type *</span>
                    <select name="meeting_type" required>
                        <option value="">Select meeting type</option>
                        @foreach($meetingTypes as $type)
                            <option value="{{ $type }}" @selected(old('meeting_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="meeting-slot-wrap">
                <span class="meeting-slot-label">Available time slots * <small>(IST)</small></span>
                <input type="hidden" name="meeting_slot" id="meetingSlotInput" value="{{ old('meeting_slot') }}" required>
                <div class="meeting-slot-grid" id="meetingSlotGrid">
                    @foreach($meetingSlots as $slot)
                        <button
                            type="button"
                            class="meeting-slot-btn {{ old('meeting_slot') === $slot ? 'is-selected' : '' }}"
                            data-slot="{{ $slot }}"
                        >
                            {{ $slot }}
                        </button>
                    @endforeach
                </div>
                <p class="meeting-slot-hint" id="meetingSlotHint">Pick a Monday–Saturday date, then choose a slot.</p>
            </div>
        </div>

        <div class="meeting-form-block">
            <h3>2. About the meeting *</h3>
            <textarea
                name="meeting_about"
                placeholder="What would you like to discuss? Project idea, goals, timeline, budget range, or any questions..."
                required
                minlength="10"
            >{{ old('meeting_about') }}</textarea>
        </div>

        <div class="meeting-form-block">
            <h3>3. Your contact details</h3>
            <div class="form-grid">
                <label class="meeting-field">
                    <span>Full name *</span>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required maxlength="120">
                </label>
                <label class="meeting-field">
                    <span>Email *</span>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required maxlength="180">
                </label>
                <label class="meeting-field meeting-field-full">
                    <span>Phone number *</span>
                    <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+91 XXXXX XXXXX" required maxlength="40">
                </label>
            </div>
        </div>

        <div class="meeting-form-actions">
            <button type="submit" class="btn-primary" id="meetingSubmitBtn">Confirm Meeting →</button>
            <p class="meeting-note">Required fields marked *. Confirmation is sent to your email after submit.</p>
        </div>
    </form>
</section>
