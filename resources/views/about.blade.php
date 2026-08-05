@extends('layouts.app')

@section('title', 'About CodoVision | Software Engineering Company in Mohali')
@section('meta_description', 'Learn about CodoVision Software Solutions in Mohali — our mission, values, engineering approach, and commitment to delivering reliable digital products for business growth.')
@section('meta_keywords', 'about CodoVision, CodoVision company profile, CodoVision Software Solutions, CodoVision Mohali, CodoVision team, software engineering team Mohali, IT company profile Punjab, product development company India, digital transformation partner, software development agency India, best IT company Mohali')
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

@include('partials.founder')

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
