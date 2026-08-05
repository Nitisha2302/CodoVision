@extends('layouts.app')

@section('title', 'CodoVision - ' . $project['title'])

@section('content')
@php
    $technologyIndex = collect(config('portfolio.technologies'))->keyBy('name');
    $matchingTechnologies = collect($project['tags'])
        ->map(fn($tag) => $technologyIndex->get($tag))
        ->filter()
        ->values();
@endphp
@php
    $projectImage = str_starts_with($project['image'], 'http') ? $project['image'] : asset($project['image']);
@endphp
@include('partials.page-hero', [
    'heroBadge' => 'Project Case Study',
    'heroTitle' => $project['title'],
    'heroSubtitle' => $project['description'],
    'heroImage' => $projectImage,
])

<section class="section detail-page page-section">
    <div class="project-tags detail-meta animate-on-scroll" style="justify-content:center;margin-bottom:24px;">
        @foreach($project['tags'] as $tag)
            <span class="project-tag">{{ $tag }}</span>
        @endforeach
    </div>

    <div class="detail-hero-image animate-on-scroll">
        <img src="{{ $projectImage }}" alt="{{ $project['title'] }}">
    </div>

    <div class="detail-grid">
        <article class="detail-card">
            <h3>Industry</h3>
            <p>{{ $project['industry'] ?? 'Product Engineering' }}</p>
        </article>
        <article class="detail-card">
            <h3>Duration</h3>
            <p>{{ $project['duration'] ?? '12 weeks' }}</p>
        </article>
        <article class="detail-card">
            <h3>Platform</h3>
            <p>{{ $project['platform'] ?? 'Web + Mobile' }}</p>
        </article>
    </div>

    <div class="detail-grid detail-grid-three">
        <article class="detail-card">
            <h3>Overview</h3>
            <p>{{ $project['summary'] }}</p>
        </article>

        <article class="detail-card">
            <h3>Challenge</h3>
            <p>{{ $project['challenge'] }}</p>
        </article>

        <article class="detail-card">
            <h3>Solution</h3>
            <p>{{ $project['solution'] }}</p>
        </article>
    </div>

    @if(!empty($project['features']))
        <article class="detail-card">
            <h3>Key Features</h3>
            <ul class="service-features">
                @foreach($project['features'] as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    @if(!empty($project['notice']))
        <article class="detail-card" style="border-color:rgba(45, 212, 191, 0.28);">
            <h3>Responsible Use Notice</h3>
            <p>{{ $project['notice'] }}</p>
        </article>
    @endif

    <article class="detail-card">
        <h3>Results</h3>
        <ul class="service-features">
            @foreach($project['results'] as $result)
                <li>{{ $result }}</li>
            @endforeach
        </ul>
    </article>

    @if(!empty($project['metrics']))
        <article class="detail-card">
            <h3>Impact Metrics</h3>
            <ul class="service-features">
                @foreach($project['metrics'] as $metric)
                    <li>{{ $metric }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    @if($matchingTechnologies->isNotEmpty())
        <section class="section" style="padding: 30px 0 0;">
            <div class="section-header">
                <div class="section-badge">🧠 Technology Detail</div>
                <h2>Technology Used in This Project</h2>
            </div>
            <div class="tech-expertise-grid">
                @foreach($matchingTechnologies as $technology)
                    <article class="detail-card">
                        <h3>{{ $technology['name'] }}</h3>
                        <p class="stack-category">{{ $technology['category'] }}</p>
                        <p>{{ $technology['summary'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($similarProjects->isNotEmpty())
        <section class="section" style="padding: 30px 0 0;">
            <div class="section-header">
                <div class="section-badge">🧭 Similar Projects</div>
                <h2>Related Case Studies</h2>
            </div>
            <div class="projects-grid">
                @foreach($similarProjects as $similarProject)
                    @php
                        $similarProjectImage = str_starts_with($similarProject['image'], 'http') ? $similarProject['image'] : asset($similarProject['image']);
                    @endphp
                    <a href="{{ route('projects.detail', $similarProject['slug']) }}" class="project-card card-link">
                        <div class="project-image">
                            <img src="{{ $similarProjectImage }}" alt="{{ $similarProject['title'] }}">
                        </div>
                        <div class="project-info">
                            <h3>{{ $similarProject['title'] }}</h3>
                            <p>{{ $similarProject['summary'] }}</p>
                            <div class="project-tags">
                                @foreach(array_slice($similarProject['tags'], 0, 4) as $tag)
                                    <span class="project-tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="detail-actions">
        <a href="{{ route('projects') }}" class="btn-outline" style="text-decoration: none; display: inline-flex; align-items: center;">← Back to Projects</a>
        <button class="btn-primary js-whatsapp">Start a similar project →</button>
    </div>
@include('partials.cta-premium', ['ctaTitle' => 'Want a project like this?', 'ctaSubtitle' => 'Tell us your idea — we will map the fastest path to launch.'])
@endsection
