@extends('layouts.app')

@section('title', 'Trispark - Technologies')

@section('content')
@include('partials.page-hero', [
    'heroBadge' => 'Technology Stack',
    'heroTitle' => 'Technologies We Use and Why',
    'heroSubtitle' => 'Every tool is chosen for speed, stability, and scale — explained in plain language.',
    'heroImage' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="technologies">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Our Stack</div>
        <h2>Production-Ready <span class="gradient-text">Technologies</span></h2>
        <p class="section-subtitle">Full project case studies are in the Projects section.</p>
    </div>

    <div class="tech-expertise-grid">
        @foreach($technologies as $technology)
            <article class="detail-card animate-on-scroll">
                <h3>{{ $technology['name'] }}</h3>
                <p class="stack-category">{{ $technology['category'] }}</p>
                <p>{{ $technology['summary'] }}</p>
                <p class="stack-usecase"><strong>Where we use it:</strong> {{ $technology['use_cases'] }}</p>
                @if(!empty($technology['similar_stack']))
                    <div class="project-tags" style="margin-top: 12px;">
                        @foreach($technology['similar_stack'] as $similar)
                            <span class="project-tag">{{ $similar }}</span>
                        @endforeach
                    </div>
                @endif
            </article>
        @endforeach
    </div>
    <div class="center-link-row">
        <a href="{{ route('projects') }}" class="btn-primary" style="text-decoration:none; display:inline-flex;">View All Real Projects →</a>
    </div>
</section>

<section class="section">
    <div class="section-header">
        <div class="section-badge">⚙️ Architecture Philosophy</div>
        <h2>Engineering Principles Behind Every Build</h2>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="service-icon icon-blue">🔐</div>
            <div class="feature-info">
                <h4>Security by Default</h4>
                <p>Auth controls, secure data handling, and hardened deployments are built in from day one.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="service-icon icon-pink">🧱</div>
            <div class="feature-info">
                <h4>Modular Systems</h4>
                <p>Clean architecture and reusable components help teams scale features without rewrites.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="service-icon icon-orange">🚀</div>
            <div class="feature-info">
                <h4>Performance Focus</h4>
                <p>Optimized APIs, caching, and efficient frontend rendering for consistent user experience.</p>
            </div>
        </div>
    </div>
</section>

@include('partials.cta-premium')
@endsection
