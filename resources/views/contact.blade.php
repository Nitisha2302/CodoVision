@extends('layouts.app')

@section('title', 'CodoVision - Contact')
@section('meta_description', 'Contact CodoVision Software Solutions for web and mobile app development, MVP planning, software consulting, and project execution support.')
@section('meta_keywords', 'contact software company, app development consultation, IT project quote, software consulting services, startup MVP consultation, web development contact')
@section('meta_canonical', url('/contact'))
@section('og_title', 'Contact CodoVision - Start Your Software Project')
@section('og_description', 'Get in touch with CodoVision for app development, software solutions, and project consultation.')
@section('og_url', url('/contact'))
@section('twitter_title', 'Contact CodoVision')
@section('twitter_description', 'Reach out for software project consultation, roadmap, and delivery planning.')

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
            <p>Sector 71, Mohali, Punjab, India</p>
        </div>
    </div>
</section>

<section class="section page-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">What Happens Next</div>
        <h2>After You <span class="gradient-text">Contact Us</span></h2>
    </div>
    <div class="process-grid">
        <div class="process-step animate-on-scroll">
            <div class="process-number">01</div>
            <div class="service-icon icon-blue">📞</div>
            <h3>Discovery Call</h3>
            <p>We understand your goals, users, and timeline.</p>
        </div>
        <div class="process-step animate-on-scroll">
            <div class="process-number">02</div>
            <div class="service-icon icon-pink">📝</div>
            <h3>Solution Draft</h3>
            <p>Roadmap with scope, timeline, and pricing options.</p>
        </div>
        <div class="process-step animate-on-scroll">
            <div class="process-number">03</div>
            <div class="service-icon icon-orange">⚙️</div>
            <h3>Execution Start</h3>
            <p>Kickoff with milestones and weekly updates.</p>
        </div>
        <div class="process-step animate-on-scroll">
            <div class="process-number">04</div>
            <div class="service-icon icon-green">🚀</div>
            <h3>Build & Launch</h3>
            <p>QA, release, and post-launch optimization.</p>
        </div>
    </div>
</section>

@include('partials.cta-premium', [
    'ctaTitle' => 'Ready to Start?',
    'ctaSubtitle' => 'Use the side buttons to call, chat, or book a meeting instantly.',
])
@endsection
