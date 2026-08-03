@extends('layouts.app')

@section('title', 'Trispark - Process')

@section('content')
@include('partials.page-hero', [
    'heroBadge' => 'Our Process',
    'heroTitle' => 'How We Work',
    'heroSubtitle' => 'A clear, step-by-step delivery model — you always know what happens next.',
    'heroImage' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="process">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Delivery Model</motion>
        <h2>Four Phases to <span class="gradient-text">Launch</span></h2>
        <p class="section-subtitle">Business outcomes, engineering quality, and timelines — aligned from day one.</p>
    </div>

    <div class="process-grid">
        @foreach($processPhases as $phase)
            <div class="process-step animate-on-scroll" id="{{ $phase['slug'] }}">
                <div class="process-number">{{ $phase['number'] }}</div>
                <div class="service-icon {{ $phase['icon_class'] }}">{{ $phase['icon'] }}</div>
                <h3>{{ $phase['title'] }}</h3>
                <p>{{ $phase['summary'] }}</p>
            </div>
        @endforeach
    </div>
</section>

<section class="section">
    <div class="section-header">
        <div class="section-badge">🧭 Detailed Methodology</div>
        <h2>Step-by-Step Delivery Detail</h2>
        <p class="section-subtitle">Each phase includes clear activities and measurable outputs so every stakeholder understands progress.</p>
    </div>

    <div class="process-details-grid">
        @foreach($processPhases as $phase)
            <article class="detail-card">
                <h3>{{ $phase['number'] }}. {{ $phase['title'] }}</h3>
                <p>{{ $phase['summary'] }}</p>

                <h4>Key Activities</h4>
                <ul class="service-features">
                    @foreach($phase['activities'] as $activity)
                        <li>{{ $activity }}</li>
                    @endforeach
                </ul>

                <h4>Phase Deliverables</h4>
                <ul class="service-features">
                    @foreach($phase['deliverables'] as $deliverable)
                        <li>{{ $deliverable }}</li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </div>
</section>

<section class="section">
    <div class="features-grid">
        <div class="feature-card">
            <div class="service-icon icon-blue">📅</div>
            <div class="feature-info">
                <h4>Milestone Visibility</h4>
                <p>Weekly execution checkpoints and delivery status shared with your team.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="service-icon icon-pink">🧪</div>
            <div class="feature-info">
                <h4>Quality Assurance</h4>
                <p>Test coverage, regression checks, and release validation for stable production rollout.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="service-icon icon-orange">📈</div>
            <div class="feature-info">
                <h4>Post-Launch Growth</h4>
                <p>Monitoring, product analytics, and iterative optimization after go-live.</p>
            </div>
        </div>
    </div>
</section>

@include('partials.cta-premium')
@endsection
