@extends('layouts.app')

@section('title', 'Contact CodoVision LLP | Software Development Company')
@section('meta_description', 'Contact CodoVision LLP at info@codovision.tech for mobile app development, Flutter, web development, AI solutions, and custom software consulting.')
@section('meta_keywords', 'contact CodoVision LLP, CodoVision LLP, info@codovision.tech, software company contact, app development consultation')
@section('meta_canonical', url('/contact'))
@section('og_title', 'Contact CodoVision LLP')
@section('og_description', 'Get in touch with CodoVision LLP for software project consultation and delivery planning.')
@section('og_url', url('/contact'))
@section('og_image', url('/images/logo.png'))
@section('twitter_title', 'Contact CodoVision LLP')
@section('twitter_description', 'Reach out for software project consultation and roadmap planning.')
@section('twitter_image', url('/images/logo.png'))
@section('head_extras')
<x-seo-breadcrumbs :items="[
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Contact', 'url' => url('/contact')],
]" />
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ContactPage',
    'name' => 'Contact CodoVision LLP',
    'url' => url('/contact'),
    'mainEntity' => [
        '@id' => 'https://codovision.tech/#organization',
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
@include('partials.page-hero', [
    'heroBadge' => 'Contact Us',
    'heroTitle' => 'Let’s Build Something Great',
    'heroSubtitle' => 'Tell us about your project — we respond within 24 hours with clear next steps.',
    'heroImage' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="contact">
    <div class="services-grid">
        <div class="service-card animate-on-scroll">
            <div class="service-icon icon-blue">✉</div>
            <h3>Email</h3>
            <p>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=info@codovision.tech" target="_blank" style="color: inherit; text-decoration: none;">
                    info@codovision.tech
                </a>
            </p>
        </div>
        <div class="service-card animate-on-scroll">
            <div class="service-icon icon-green">📞</div>
            <h3>Phone / WhatsApp</h3>
            <p><a href="tel:+917973776933" style="color: inherit; text-decoration: none;">+91 79737-76933</a></p>
            <button class="btn-primary js-whatsapp" style="margin-top: 10px;">Chat on WhatsApp</button>
        </div>
        <div class="service-card animate-on-scroll">
            <div class="service-icon icon-orange">📍</div>
            <h3>Location</h3>
            <p>{{ config('portfolio.company_address') }}</p>
        </div>
    </div>
</section>
@include('partials.meeting-booking')
<section class="section page-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">What Happens Next</div>
        <h2>After You <span class="gradient-text">Book a Meeting</span></h2>
    </div>
    <div class="process-grid">
        <div class="process-step animate-on-scroll">
            <div class="process-number">01</div>
            <div class="service-icon icon-blue">📅</div>
            <h3>Pick Slot</h3>
            <p>Choose date, time, and share what you want to discuss.</p>
        </div>
        <div class="process-step animate-on-scroll">
            <div class="process-number">02</div>
            <div class="service-icon icon-pink">✉</div>
            <h3>Get Confirmation</h3>
            <p>Meeting details are emailed to you instantly.</p>
        </div>
        <div class="process-step animate-on-scroll">
            <div class="process-number">03</div>
            <div class="service-icon icon-orange">📞</div>
            <h3>Discovery Call</h3>
            <p>We understand your goals, users, and timeline.</p>
        </div>
        <div class="process-step animate-on-scroll">
            <div class="process-number">04</div>
            <div class="service-icon icon-green">🚀</div>
            <h3>Clear Next Steps</h3>
            <p>Roadmap, scope options, and recommended path forward.</p>
        </div>
    </div>
</section>

@include('partials.cta-premium', [
    'ctaTitle' => 'Ready to Start?',
    'ctaSubtitle' => 'Book a meeting above, or use the side buttons to call or chat instantly.',
])
@endsection
