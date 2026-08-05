@extends('layouts.app')

@section('title', 'CodoVision - ' . $service['title'])

@section('content')
@php
    $technologyIndex = collect(config('portfolio.technologies'))->keyBy('name');
    $recommendedTechnologies = collect($service['recommended_technologies'] ?? [])
        ->map(fn($name) => $technologyIndex->get($name))
        ->filter()
        ->values();
@endphp
@include('partials.page-hero', [
    'heroBadge' => 'Service Detail',
    'heroTitle' => $service['title'],
    'heroSubtitle' => $service['description'],
    'heroImage' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1600&q=90',
])

<section class="section detail-page page-section">
    <div class="detail-meta animate-on-scroll" style="text-align:center;margin-bottom:24px;">
        <span class="project-tag">Estimated Timeline: {{ $service['timeline'] }}</span>
    </div>

    <div class="detail-grid">
        <article class="detail-card animate-on-scroll">
            <h3>What You Get</h3>
            <ul class="service-features">
                @foreach($service['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </article>

        <article class="detail-card animate-on-scroll">
            <h3>Delivery Scope</h3>
            <ul class="service-features">
                @foreach($service['deliverables'] as $deliverable)
                    <li>{{ $deliverable }}</li>
                @endforeach
            </ul>
        </article>
    </div>

    @if($recommendedTechnologies->isNotEmpty())
        <section class="section" style="padding: 20px 0 0;">
            <div class="section-header">
                <div class="section-badge">⚙️ Recommended Stack</div>
                <h2>Technologies Commonly Used for This Service</h2>
            </div>
            <div class="tech-expertise-grid">
                @foreach($recommendedTechnologies as $technology)
                    <article class="detail-card">
                        <h3>{{ $technology['name'] }}</h3>
                        <p class="stack-category">{{ $technology['category'] }}</p>
                        <p>{{ $technology['summary'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($similarServices->isNotEmpty())
        <section class="section" style="padding: 25px 0 0;">
            <div class="section-header">
                <div class="section-badge">🔁 Similar Services</div>
                <h2>Related Services You May Need</h2>
            </div>
            <div class="services-grid">
                @foreach($similarServices as $similarService)
                    <a href="{{ route('services.detail', $similarService['slug']) }}" class="service-card card-link">
                        <div class="service-icon {{ $similarService['icon_class'] }}">{{ $similarService['icon'] }}</div>
                        <h3>{{ $similarService['title'] }}</h3>
                        <p>{{ $similarService['summary'] }}</p>
                        <span class="learn-more">View service details →</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="detail-actions">
        <a href="{{ route('services') }}" class="btn-outline" style="text-decoration: none; display: inline-flex; align-items: center;">← Back to Services</a>
        <button class="btn-primary js-whatsapp">Discuss this service →</button>
    </div>
@include('partials.cta-premium', ['ctaTitle' => 'Interested in this service?', 'ctaSubtitle' => 'Book a free call and get a clear plan for your project.'])
@endsection
