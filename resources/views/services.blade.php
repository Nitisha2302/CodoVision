@extends('layouts.app')

@section('title', 'Software Development Services | CodoVision LLP')
@section('meta_description', 'Explore CodoVision LLP services including web development, mobile app development, Flutter, UI/UX design, AI solutions, and automation for startups and businesses.')
@section('meta_keywords', 'CodoVision LLP services, software development services, mobile app development, web development, Flutter, AI development, UI UX design, custom software')
@section('meta_canonical', url('/services'))
@section('og_title', 'Software Development Services | CodoVision LLP')
@section('og_description', 'End-to-end software development services for modern digital products by CodoVision LLP.')
@section('og_url', url('/services'))
@section('og_image', url('/images/logo.png'))
@section('twitter_title', 'Software Development Services | CodoVision LLP')
@section('twitter_description', 'Web, mobile, design, AI, and automation services from CodoVision LLP.')
@section('twitter_image', url('/images/logo.png'))
@section('head_extras')
<x-seo-breadcrumbs :items="[
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Services', 'url' => url('/services')],
]" />
@endsection

@section('content')
@include('partials.page-hero', [
    'heroBadge' => 'Our Services',
    'heroTitle' => 'End-to-End Digital Product Services',
    'heroSubtitle' => 'Web, mobile, design, and automation — explained clearly so you know exactly what you are getting.',
    'heroImage' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="services">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">What We Offer</div>
        <h2>Core <span class="gradient-text">Service Lines</span></h2>
    </div>
    <div class="services-visual-grid">
        @php
            $serviceImages = [
                'web-development' => 'images/ecommerce_website.png',
                'mobile-app-development' => 'images/food_app.png',
                'agentic-ai-solutions' => 'images/projects/wellora-ai-wellness.jpeg',
                'healthcare-rcm-analytics' => 'images/projects/medical-crm-platform.png',
                'ui-ux-design' => 'images/MyTalent.png',
                'automation-solutions' => 'images/iot.png',
            ];
        @endphp
        @foreach($services as $service)
            @php $img = $serviceImages[$service['slug']] ?? 'images/crm.png'; @endphp
            <a href="{{ route('services.detail', $service['slug']) }}" class="service-visual-card card-link animate-on-scroll">
                <div class="service-visual-media">
                    <img src="{{ asset($img) }}" alt="{{ $service['title'] }}">
                    <span class="service-visual-icon {{ $service['icon_class'] }}">{!! $service['icon'] !!}</span>
                </div>
                <div class="service-visual-body">
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['summary'] }}</p>
                    <span class="learn-more">Open details page →</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="section page-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Engagement Models</div>
        <h2>Flexible Ways to <span class="gradient-text">Work Together</span></h2>
    </div>
    <div class="features-grid">
        <div class="feature-card animate-on-scroll">
            <div class="service-icon icon-blue">📌</div>
            <div class="feature-info">
                <h4>Fixed Scope Delivery</h4>
                <p>Clear requirements, milestone-based execution, predictable timeline.</p>
            </div>
        </div>
        <div class="feature-card animate-on-scroll">
            <div class="service-icon icon-pink">🔄</div>
            <div class="feature-info">
                <h4>Agile Product Team</h4>
                <p>Sprint delivery for products that need fast feedback and iteration.</p>
            </div>
        </div>
        <div class="feature-card animate-on-scroll">
            <div class="service-icon icon-orange">🤝</div>
            <div class="feature-info">
                <h4>Dedicated Engineering</h4>
                <p>Long-term partnership for scaling platforms and ongoing optimization.</p>
            </div>
        </div>
    </div>
</section>

@include('partials.cta-premium', [
    'ctaTitle' => 'Not Sure Which Service Fits?',
    'ctaSubtitle' => 'Book a free call — we will recommend the right approach in plain language.',
])
@endsection
