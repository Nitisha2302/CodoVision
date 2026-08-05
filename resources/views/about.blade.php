@extends('layouts.app')

@section('title', 'CodoVision - About')
@section('meta_description', 'Learn about CodoVision Software Solutions, our mission, values, engineering approach, and commitment to delivering reliable digital products for business growth.')
@section('meta_keywords', 'about software company, IT company profile, software engineering team, product development company, digital transformation partner, software development agency India')
@section('meta_canonical', url('/about'))
@section('og_title', 'About CodoVision - Software Engineering Partner')
@section('og_description', 'Discover CodoVision mission, delivery standards, and client-focused engineering approach.')
@section('og_url', url('/about'))
@section('twitter_title', 'About CodoVision')
@section('twitter_description', 'A professional software team focused on reliable product delivery and business outcomes.')

@section('content')
@include('partials.page-hero', [
    'heroBadge' => 'About CodoVision',
    'heroTitle' => 'Who We Are',
    'heroSubtitle' => 'A software team focused on practical digital transformation through design, engineering, and automation.',
    'heroImage' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="about">
    <div class="about-showcase">
        <div class="about-visual-cluster animate-on-scroll">
            <img src="{{ asset('images/Fitzme_ok.png') }}" alt="Project 1" class="about-float-img about-float-1">
            <img src="{{ asset('images/MyTalent.png') }}" alt="Project 2" class="about-float-img about-float-2">
            <img src="{{ asset('images/SIMS.png') }}" alt="Project 3" class="about-float-img about-float-3">
            <img src="{{ asset('images/food_app.png') }}" alt="Project 4" class="about-float-img about-float-4">
            <span class="about-visual-badge">Our Work</span>
        </div>
        <div class="about-copy animate-on-scroll">
            <div class="section-badge">Company Profile</div>
            <h2>Building Products That <span class="gradient-text">Create Impact</span></h2>
            <p class="plain-explainer"><strong>Our mission:</strong> Build reliable, scalable digital products for startups and growing organizations.</p>
            <p class="plain-explainer"><strong>Our vision:</strong> Become a trusted long-term technology partner known for quality and transparency.</p>
        </div>
    </div>
</section>

<section class="section page-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Core Values</div>
        <h2>How We Deliver <span class="gradient-text">Professional Outcomes</span></h2>
    </div>
    <div class="features-grid">
        <div class="feature-card animate-on-scroll">
            <div class="service-icon icon-blue">🎯</div>
            <div class="feature-info">
                <h4>Outcome Focus</h4>
                <p>Every recommendation maps to product growth, performance, or efficiency.</p>
            </div>
        </div>
        <div class="feature-card animate-on-scroll">
            <div class="service-icon icon-pink">🧪</div>
            <div class="feature-info">
                <h4>Engineering Rigor</h4>
                <p>Code quality, testing, and maintainability in every delivery cycle.</p>
            </div>
        </div>
        <div class="feature-card animate-on-scroll">
            <div class="service-icon icon-orange">🤝</div>
            <div class="feature-info">
                <h4>Client Partnership</h4>
                <p>We work as an extension of your team with clear communication.</p>
            </div>
        </div>
    </div>
</section>

<section class="section page-section faq-home-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">FAQ</div>
        <h2>Frequently Asked <span class="gradient-text">Questions</span></h2>
    </div>
    <div class="faq-list animate-on-scroll">
        <div class="faq-item">
            <div class="faq-question">What is your typical project timeline?</div>
            <div class="faq-answer"><p>Web apps: 8–12 weeks. Mobile apps: 12–16 weeks. MVPs can be faster with a focused scope.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Do you provide post-launch support?</div>
            <div class="faq-answer"><p>Yes — bug fixes, updates, monitoring, and maintenance packages.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What technologies do you work with?</div>
            <div class="faq-answer"><p>React, Next.js, Flutter, Laravel, Python, AWS, Firebase, and more.</p></div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Can you help with an existing project?</div>
            <div class="faq-answer"><p>Yes — audit, bug fixes, performance improvements, and continued development.</p></div>
        </div>
    </div>
</section>

@include('partials.cta-premium')
@endsection
