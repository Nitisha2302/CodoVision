@extends('layouts.app')

@section('title', 'CodoVision - Service Packages')

@section('content')
@php
    $packages = config('portfolio.packages', []);
    $technologies = collect(config('portfolio.technologies', []));
    $allProjects = collect(config('portfolio.projects', []))->values();
    $technologyOptions = $technologies->pluck('name')->values();
    $tiers = [
        ['slug' => 'basic', 'name' => 'Basic', 'multiplier' => 1.0, 'timeline' => '2-4 weeks'],
        ['slug' => 'advanced', 'name' => 'Advanced', 'multiplier' => 1.8, 'timeline' => '4-8 weeks'],
        ['slug' => 'premium', 'name' => 'Premium', 'multiplier' => 2.7, 'timeline' => '8-14 weeks'],
    ];
@endphp

@include('partials.page-hero', [
    'heroBadge' => 'Service Packages',
    'heroTitle' => 'Technology Packages',
    'heroSubtitle' => 'Basic, Advanced, and Premium plans — pick what fits your stage and budget.',
    'heroImage' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1600&q=90',
])

<section class="section page-section" id="packages">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Plans</div>
        <h2>Choose Your <span class="gradient-text">Package</span></h2>
        <p class="section-subtitle">Filter by technology and package type to find the right plan.</p>
    </div>

    <div class="detail-card package-filter-card filter-ui-card">
        <div class="filter-ui-head">
            <div>
                <p class="filter-ui-kicker">Smart Package Finder</p>
                <h3>Choose Technology and Package Type</h3>
                <p class="filter-ui-text">Choose a technology first, then select package type. We will show only relevant plans and related projects.</p>
            </div>
            <span class="filter-ui-icon" aria-hidden="true">🎯</span>
        </div>
        <div class="filter-step-row">
            <span class="filter-step-chip active">Step 1: Select Technology</span>
            <span class="filter-step-chip">Step 2: Select Package Type</span>
        </div>
        <div class="form-grid package-filter-grid">
            <div>
                <label class="form-label" for="technologyFilter">Filter by Technology</label>
                <select id="technologyFilter" class="filter-ui-select">
                    <option value="">All Technologies</option>
                    @foreach($technologyOptions as $technologyName)
                        <option value="{{ $technologyName }}">{{ $technologyName }}</option>
                    @endforeach
                    <option value="Any Stack">Any Stack</option>
                </select>
            </div>
            <div>
                <label class="form-label" for="packageMode">Package Type</label>
                <select id="packageMode" class="filter-ui-select">
                    <option value="fixed">Fixed Packages</option>
                    <option value="all">All Packages</option>
                    <option value="tier">Technology Tier Packages</option>
                    <option value="custom">Custom Package</option>
                </select>
            </div>
        </div>
        <p class="filter-ui-hint">Showing default fixed plans first. Pick a technology to unlock tailored Basic, Advanced, and Premium options.</p>
    </div>

    <div class="services-grid" id="packageCards">
        @foreach($technologies as $technology)
            @php
                $basePrice = match ($technology['category']) {
                    'Frontend' => 900,
                    'Backend' => 1200,
                    'Mobile' => 1400,
                    'Cloud' => 1300,
                    'Database' => 1000,
                    'AI & Automation' => 1600,
                    'DevOps' => 1450,
                    'Performance' => 1250,
                    'Language' => 1050,
                    'Design' => 850,
                    'Frontend Motion' => 950,
                    default => 1100,
                };
            @endphp
            @foreach($tiers as $tier)
                @php
                    $tierPrice = '$' . number_format((int) round($basePrice * $tier['multiplier']));
                    $tierId = strtolower(str_replace([' ', '.'], ['-', ''], $technology['name'])) . '-' . $tier['slug'];
                @endphp
                <article class="service-card package-card" data-package-id="{{ $tierId }}" data-mode="tier" data-tech="{{ $technology['name'] }}">
                    <h3>{{ $technology['name'] }} - {{ $tier['name'] }}</h3>
                    <p class="package-price">{{ $tierPrice }}</p>
                    <p class="review-meta">Timeline: {{ $tier['timeline'] }}</p>
                    <p>Best for {{ strtolower($technology['category']) }} solutions with {{ strtolower($tier['name']) }} scope.</p>
                    <div class="project-tags" style="margin: 10px 0 14px;">
                        <span class="project-tag">{{ $technology['name'] }}</span>
                        <span class="project-tag">{{ $technology['category'] }}</span>
                        <span class="project-tag">{{ $tier['name'] }}</span>
                    </div>
                    <h4 class="mini-heading">Includes</h4>
                    <ul class="service-features">
                        <li>{{ $technology['name'] }} implementation setup</li>
                        <li>{{ $tier['name'] }} feature set delivery</li>
                        <li>Testing + deployment support</li>
                        <li>Documentation and handover</li>
                    </ul>
                    <h4 class="mini-heading">Deliverables</h4>
                    <ul class="service-features">
                        <li>Architecture plan for {{ $technology['name'] }}</li>
                        <li>Production-ready module delivery</li>
                        <li>Performance and QA checklist</li>
                    </ul>
                    <button type="button" class="btn-primary js-select-package" data-package-id="{{ $tierId }}">Book {{ $tier['name'] }} Plan</button>
                </article>
            @endforeach
        @endforeach

        @foreach($packages as $package)
            <article class="service-card package-card" data-package-id="{{ $package['id'] }}" data-mode="{{ $package['id'] === 'custom' ? 'custom' : 'fixed' }}" data-tech="{{ implode('|', $package['technology_focus']) }}" data-default="{{ $package['id'] === 'starter-web' ? '1' : '0' }}">
                <h3>{{ $package['name'] }}</h3>
                <p class="package-price">{{ $package['price'] }}</p>
                <p class="review-meta">Timeline: {{ $package['timeline'] }}</p>
                <p>{{ $package['best_for'] }}</p>

                <div class="project-tags" style="margin: 10px 0 14px;">
                    @foreach($package['technology_focus'] as $focus)
                        <span class="project-tag">{{ $focus }}</span>
                    @endforeach
                </div>

                <h4 class="mini-heading">Included Features</h4>
                <ul class="service-features">
                    @foreach($package['includes'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <h4 class="mini-heading">Delivery in This Package</h4>
                <ul class="service-features">
                    @foreach($package['deliverables'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>

                <p class="review-meta"><strong>Our value:</strong> {{ $package['our_value'] }}</p>
                <button type="button" class="btn-primary js-select-package" data-package-id="{{ $package['id'] }}">Book This Package</button>
            </article>
        @endforeach
    </div>
</section>

<section class="section">
    <div class="section-header">
        <div class="section-badge">🧭 Related Projects</div>
        <h2>Projects Based on Selected Technology</h2>
        <p class="section-subtitle">Filter technology above to see relevant real projects with complete detail pages.</p>
    </div>
    <div class="projects-grid" id="packageProjectCards">
        @foreach($allProjects as $project)
            @php
                $packageProjectImage = str_starts_with($project['image'], 'http') ? $project['image'] : asset($project['image']);
            @endphp
            <a href="{{ route('projects.detail', $project['slug']) }}" class="project-card card-link" data-project-tech="{{ implode('|', $project['tags']) }}">
                <div class="project-image">
                    <img src="{{ $packageProjectImage }}" alt="{{ $project['title'] }}">
                </div>
                <div class="project-info">
                    <h3>{{ $project['title'] }}</h3>
                    <p>{{ $project['summary'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>

<section class="section">
    <div class="section-header">
        <div class="section-badge">📊 Pricing Comparison</div>
        <h2>CodoVision vs Typical Platform Pricing</h2>
        <p class="section-subtitle">Transparent comparison to evaluate scope, quality, and delivery support.</p>
    </div>

    <div class="detail-card">
        <div class="table-wrap">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Technology Focus</th>
                        <th>CodoVision Price</th>
                        <th>Typical Platform Range</th>
                        <th>Timeline</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                        <tr>
                            <td>{{ $package['name'] }}</td>
                            <td>{{ implode(', ', $package['technology_focus']) }}</td>
                            <td>{{ $package['price'] }}</td>
                            <td>{{ $package['other_platforms'] }}</td>
                            <td>{{ $package['timeline'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="section" id="booking-form">
    <div class="section-header">
        <div class="section-badge">📝 Package Booking Request</div>
        <h2>Submit Your Details and Package Choice</h2>
        <p class="section-subtitle">Professional intake form with requirement details. Booking details are sent to your business email automatically.</p>
    </div>

    @if(session('booking_success'))
        <div class="detail-card" style="border-color: rgba(16, 185, 129, 0.5); margin-bottom: 20px;">
            <p>{{ session('booking_success') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('package.book') }}" class="detail-card booking-form">
        @csrf
        <div class="form-grid">
            <input type="text" name="name" placeholder="Your Full Name" required>
            <input type="email" name="email" placeholder="Business Email" required>
            <input type="text" name="mobile" placeholder="Mobile Number" required>
            <input type="text" name="country" placeholder="Country" required>
            <input type="text" name="company_name" placeholder="Company / Brand Name">
            <select name="package_mode" id="packageModeForm" required>
                <option value="fixed">Fixed Package</option>
                <option value="tier">Technology Tier Package</option>
                <option value="custom">Custom Package</option>
            </select>
            <select name="selected_technology" id="selectedTech" required>
                <option value="">Preferred Technology</option>
                @foreach($technologyOptions as $technologyName)
                    <option value="{{ $technologyName }}">{{ $technologyName }}</option>
                @endforeach
            </select>
            <select name="package_id" id="packageSelect" required>
                <option value="">Select Package</option>
                @foreach($technologies as $technology)
                    @foreach($tiers as $tier)
                        @php
                            $tierId = strtolower(str_replace([' ', '.'], ['-', ''], $technology['name'])) . '-' . $tier['slug'];
                        @endphp
                        <option value="{{ $tierId }}">{{ $technology['name'] }} - {{ $tier['name'] }}</option>
                    @endforeach
                @endforeach
                @foreach($packages as $package)
                    <option value="{{ $package['id'] }}">{{ $package['name'] }} - {{ $package['price'] }}</option>
                @endforeach
            </select>
            <input type="text" name="estimated_budget" placeholder="Estimated Budget (e.g. $3k-$6k)">
            <textarea name="required_features" placeholder="Required features: user login, admin panel, payments, notifications, reports, etc."></textarea>
            <textarea name="message" placeholder="Additional notes: preferred timeline, competitors, references, goals."></textarea>
        </div>
        <button type="submit" class="btn-primary">Book Package Now →</button>
    </form>
</section>

@include('partials.cta-premium')
@endsection
