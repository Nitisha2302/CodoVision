@extends('layouts.app')

@section('title', 'CodoVision - Projects')
@section('meta_description', 'View CodoVision project portfolio and case studies across mobile apps, web platforms, logistics, healthcare, education, and enterprise software delivery.')
@section('meta_keywords', 'software development portfolio, mobile app case studies, web development projects, IT company portfolio, app development examples, logistics software project, healthcare app case study, enterprise software case studies')
@section('meta_canonical', url('/projects'))
@section('og_title', 'CodoVision Projects - Real Software Case Studies')
@section('og_description', 'Explore real-world software projects delivered by CodoVision for startups and enterprises.')
@section('og_url', url('/projects'))
@section('twitter_title', 'CodoVision Projects - Case Studies')
@section('twitter_description', 'Real project outcomes across app, web, and enterprise software solutions.')

@section('content')
@include('partials.page-hero', [
    'heroBadge' => 'Portfolio',
    'heroTitle' => 'Recent Projects',
    'heroSubtitle' => 'Mobile, web, healthcare, education, and business automation — delivered with measurable outcomes.',
    'heroImage' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="projects">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Case Studies</div>
        <h2>Explore Our <span class="gradient-text">Work</span></h2>
        <p class="section-subtitle">Filter by technology to find projects similar to your idea.</p>
    </div>
    <div class="detail-card package-filter-card filter-ui-card" style="margin-bottom: 22px;">
        <div class="filter-ui-head">
            <div>
                <p class="filter-ui-kicker">Project Discovery</p>
                <h3>Filter Projects by Technology</h3>
                <p class="filter-ui-text">Choose a stack to instantly view only relevant project case studies.</p>
            </div>
            <span class="filter-ui-icon" aria-hidden="true">🧩</span>
        </div>
        <div class="form-grid package-filter-grid">
            <div>
                <label class="form-label" for="projectTechnologyFilter">Technology</label>
                <select id="projectTechnologyFilter" class="filter-ui-select">
                    <option value="">All Technologies</option>
                    @foreach($technologies as $technology)
                        <option value="{{ $technology['name'] }}">{{ $technology['name'] }}</option>
                    @endforeach
                </select>
                <p class="filter-ui-hint">Tip: start with one technology to compare similar project outcomes faster.</p>
            </div>
        </div>
    </div>

    <div class="projects-grid" id="projectCards">
        @foreach($projects as $project)
            @php
                $projectImage = str_starts_with($project['image'], 'http') ? $project['image'] : asset($project['image']);
            @endphp
            <a href="{{ route('projects.detail', $project['slug']) }}" class="project-card card-link animate-on-scroll" data-project-tech="{{ implode('|', $project['tags']) }}">
                <div class="project-image">
                    <img src="{{ $projectImage }}" alt="{{ $project['title'] }}">
                </div>
                <div class="project-info">
                    <h3>{{ $project['title'] }}</h3>
                    <p>{{ $project['summary'] }}</p>
                    <p class="review-meta">{{ $project['industry'] }} · {{ $project['duration'] }} · {{ $project['platform'] }}</p>
                    <div class="project-tags">
                        @foreach($project['tags'] as $tag)
                            <span class="project-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <span class="learn-more">Open details page →</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="section">
    <div class="section-header">
        <div class="section-badge">📊 Delivery Outcomes</div>
        <h2>What Project Success Means</h2>
        <p class="section-subtitle">We track outcome-focused metrics to validate business value after delivery.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="service-icon icon-blue">⚡</div>
            <div class="feature-info">
                <h4>Speed to Market</h4>
                <p>Fast MVP and release cycles with production-ready engineering standards.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="service-icon icon-pink">📈</div>
            <div class="feature-info">
                <h4>User Engagement</h4>
                <p>UX and performance improvements focused on retention and conversion.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="service-icon icon-orange">🧭</div>
            <div class="feature-info">
                <h4>Operational Clarity</h4>
                <p>Dashboards and automation that improve internal decision-making and workflow speed.</p>
            </div>
        </div>
    </div>
</section>

@include('partials.reviews-professional', ['testimonials' => $testimonials, 'reviewId' => 'projects-review'])
<div id="reviewGrid" style="display:none;" aria-hidden="true"></div>

<section class="section">
    <div class="section-header">
        <div class="section-badge">✍️ Add Review</div>
        <h2>Share Your Experience</h2>
        <p class="section-subtitle">Client feedback appears on the website in real time after submission.</p>
    </div>
    <form id="reviewForm" class="detail-card booking-form" enctype="multipart/form-data">
        <div class="form-grid">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="text" name="role" placeholder="Your Role" required>
            <input type="text" name="company" placeholder="Company Name" required>
            <input type="text" name="project" placeholder="Project Name" required>
            <input type="email" name="email" placeholder="Work Email (optional)">
            <input type="file" name="profile_image" accept=".jpg,.jpeg,.png,.webp">
            <select name="rating" required>
                <option value="">Rating</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Very Good</option>
                <option value="3">3 - Good</option>
                <option value="2">2 - Fair</option>
                <option value="1">1 - Poor</option>
            </select>
            <textarea name="review" placeholder="Write your review..." required></textarea>
        </div>
        <button type="submit" class="btn-primary">Submit Review →</button>
        <p id="reviewMessage" class="review-meta" style="margin-top: 10px;"></p>
    </form>
</section>

@include('partials.cta-premium')
@endsection
