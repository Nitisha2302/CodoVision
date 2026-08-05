@extends('layouts.app')

@section('title', 'Contact CodoVision | Software Company Mohali Punjab')
@section('meta_description', 'Contact CodoVision Software Solutions at F547 PH-8A Industrial Area, Sector 75, Mohali. Get quotes for web development, mobile app development, MVP planning, and software consulting.')
@section('meta_keywords', 'contact CodoVision, CodoVision Mohali, CodoVision address, CodoVision Sector 75, software company Mohali contact, app development consultation Mohali, IT project quote Punjab, software consulting CodoVision, startup MVP consultation Mohali, web development contact Chandigarh, CodoVision phone, info@codovision.tech')
@section('meta_canonical', url('/contact'))
@section('og_title', 'Contact CodoVision - Start Your Software Project')
@section('og_description', 'Get in touch with CodoVision in Mohali for app development, software solutions, and project consultation.')
@section('og_url', url('/contact'))
@section('twitter_title', 'Contact CodoVision')
@section('twitter_description', 'Reach out for software project consultation, roadmap, and delivery planning in Mohali.')
@section('head_extras')
@php $companyAddress = config('portfolio.company_address_parts'); @endphp
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "ContactPage",
  "name": "Contact CodoVision",
  "url": "{{ url('/contact') }}",
  "mainEntity": {
    "@@type": "Organization",
    "name": "CodoVision",
    "email": "{{ config('portfolio.company_email') }}",
    "telephone": "{{ config('portfolio.company_phone') }}",
    "address": {
      "@@type": "PostalAddress",
      "streetAddress": "{{ $companyAddress['street'] }}",
      "addressLocality": "{{ $companyAddress['locality'] }}",
      "addressRegion": "{{ $companyAddress['region'] }}",
      "postalCode": "{{ $companyAddress['postal'] }}",
      "addressCountry": "{{ $companyAddress['country'] }}"
    }
  }
}
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
