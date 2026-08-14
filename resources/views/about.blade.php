@extends('layouts.app')

@section('title', 'About CodoVision LLP | Raghav Tomar & Nitisha Goyal')
@section('meta_description', 'Learn about CodoVision LLP, a software development company founded by Raghav Tomar and Nitisha Goyal. We build mobile apps, web platforms, AI solutions, and custom software.')
@section('meta_keywords', 'About CodoVision LLP, CodoVision LLP, Raghav Tomar, Nitisha Goyal, Raghav Tomar CodoVision, Nitisha Goyal CodoVision, software development company')
@section('meta_canonical', url('/about'))
@section('og_title', 'About CodoVision LLP | Founders Raghav Tomar & Nitisha Goyal')
@section('og_description', 'CodoVision LLP is led by founders Raghav Tomar and Nitisha Goyal, delivering reliable software products for startups and businesses.')
@section('og_url', url('/about'))
@section('og_image', url('/images/logo.png'))
@section('twitter_title', 'About CodoVision LLP')
@section('twitter_description', 'Meet CodoVision LLP and founders Raghav Tomar and Nitisha Goyal.')
@section('twitter_image', url('/images/logo.png'))
@section('head_extras')
<x-seo-breadcrumbs :items="[
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'About', 'url' => url('/about')],
]" />
@endsection

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
