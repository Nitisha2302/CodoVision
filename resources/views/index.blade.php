@extends('layouts.app')

@section('title', 'CodoVision - Home')
@section('meta_description', 'Professional app development agency for startups and businesses. CodoVision builds scalable MVPs, marketplace apps, delivery platforms, and AI-powered mobile apps with fast execution.')
@section('meta_keywords', 'software development company, IT company, web development company, mobile app development company, custom software development services, startup MVP development, marketplace app development, food delivery app development, logistics app development, healthcare app development, education app development, ecommerce development, SaaS development, flutter app development, react native development, laravel development company, AI app development, cloud deployment services, DevOps services, UI UX design services, enterprise software development, product engineering services, app maintenance and support')
@section('meta_canonical', url('/'))
@section('og_title', 'CodoVision - Software Development & Mobile App Development Company')
@section('og_description', 'CodoVision builds scalable web and mobile apps, MVPs, and enterprise software for startups and businesses.')
@section('og_image', asset('images/logo.png'))
@section('twitter_title', 'CodoVision - Software Development & Mobile App Development Company')
@section('twitter_description', 'Custom software, mobile apps, startup MVPs, and enterprise-grade digital product engineering.')
@section('twitter_image', asset('images/logo.png'))
@section('head_extras')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "ProfessionalService",
  "name": "CodoVision",
  "url": "{{ url('/') }}",
  "description": "Startup MVP, mobile app, marketplace app, and AI integration development services.",
  "areaServed": "Global",
  "serviceType": [
    "Startup MVP Development",
    "Marketplace App Development",
    "Delivery App Development",
    "AI App Development",
    "Web Development",
    "Mobile App Development",
    "Enterprise Software Development"
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "Organization",
  "name": "CodoVision",
  "url": "{{ url('/') }}",
  "logo": "{{ asset('images/logo.png') }}",
  "sameAs": [
    "https://www.linkedin.com/company/codovisiontech/home/"
  ]
}
</script>
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebSite",
  "name": "CodoVision",
  "url": "{{ url('/') }}"
}
</script>
@endsection

@section('content')
@php
    $heroSlides = [
        [
            'badge' => 'AI & Product Engineering',
            'title' => 'Building Custom Software',
            'highlight' => 'with Smart Technology',
            'description' => 'We design and build <strong>AI-ready mobile and web products</strong> that match your business goals, improve efficiency, and help you launch faster.',
            'image' => 'https://images.unsplash.com/photo-1677442136019-21780ecad995?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'Request a Quote',
        ],
        [
            'badge' => 'Mobile & Web Apps',
            'title' => 'Empowering Businesses with',
            'highlight' => 'Digital App Solutions',
            'description' => 'From idea to App Store — we deliver <strong>user-friendly apps</strong> that engage customers and simplify daily operations.',
            'image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'Book Strategy Call',
        ],
        [
            'badge' => 'Enterprise Delivery',
            'title' => 'Advanced IT Solutions',
            'highlight' => 'That Scale With You',
            'description' => 'Secure architecture, cloud deployment, and <strong>long-term support</strong> so your product keeps growing without technical debt.',
            'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'View Case Studies',
        ],
        [
            'badge' => 'IoT & Automation',
            'title' => 'Connected Products for',
            'highlight' => 'Modern Businesses',
            'description' => 'Integrate devices, dashboards, and real-time data into one <strong>reliable platform</strong> your team can trust.',
            'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'Discuss Your Idea',
        ],
        [
            'badge' => 'Limited-Time Offer',
            'title' => 'Get Free Discovery for',
            'highlight' => 'Your Next Product',
            'description' => 'Book this month and receive a <strong>complimentary strategy + architecture session</strong> for your MVP planning.',
            'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'Claim Offer',
        ],
        [
            'badge' => 'Client Story',
            'title' => 'From Idea to Launch in',
            'highlight' => '8 Weeks',
            'description' => 'See how we helped a startup launch faster with clear milestones, streamlined delivery, and measurable business impact.',
            'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'Read Success Story',
        ],
        [
            'badge' => '6+ Years Experience',
            'title' => 'Trusted by Teams that',
            'highlight' => 'Need Reliable Delivery',
            'description' => 'Our experience across industries helps reduce risk, improve product quality, and accelerate execution.',
            'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1400&h=1050&q=90',
            'cta' => 'Work With Experts',
        ],
    ];

    $serviceImages = [
        'web-development' => 'images/ecommerce_website.png',
        'mobile-app-development' => 'images/food_app.png',
        'agentic-ai-solutions' => 'images/projects/wellora-ai-wellness.jpeg',
        'healthcare-rcm-analytics' => 'images/projects/medical-crm-platform.png',
        'ui-ux-design' => 'images/MyTalent.png',
        'automation-solutions' => 'images/iot.png',
    ];

    $serviceIconSvg = [
        'web-development' => '<svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="13" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 21h8M10 17v4M14 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        'mobile-app-development' => '<svg viewBox="0 0 24 24" fill="none"><rect x="8" y="2.5" width="8" height="19" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M11 5h2M11 18.5h2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        'agentic-ai-solutions' => '<svg viewBox="0 0 24 24" fill="none"><path d="M8 4.5A3.5 3.5 0 0111.5 8v1M16 4.5A3.5 3.5 0 0012.5 8v1M7 19.5A3.5 3.5 0 0110.5 16v-1M17 19.5a3.5 3.5 0 00-3.5-3.5v-1" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><rect x="5" y="9" width="14" height="6" rx="3" stroke="currentColor" stroke-width="1.7"/><circle cx="9" cy="12" r=".8" fill="currentColor"/><circle cx="15" cy="12" r=".8" fill="currentColor"/></svg>',
        'healthcare-rcm-analytics' => '<svg viewBox="0 0 24 24" fill="none"><path d="M4 19V9m5 10V5m5 14v-7m5 7V3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M3 19h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
        'ui-ux-design' => '<svg viewBox="0 0 24 24" fill="none"><path d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'automation-solutions' => '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.8"/><path d="M19.4 15a7.8 7.8 0 00.1-6l1.8-1.4-1.7-3-2.2.8a8.4 8.4 0 00-5.2-2.1L11.7 1H8.3l-.5 2.3a8.4 8.4 0 00-5.2 2.1l-2.2-.8-1.7 3L.5 9a7.8 7.8 0 000 6L-1.3 16.4l1.7 3 2.2-.8a8.4 8.4 0 005.2 2.1l.5 2.3h3.4l.5-2.3a8.4 8.4 0 005.2-2.1l2.2.8 1.7-3L19.4 15z" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/></svg>',
    ];

    $solutions = [
        ['title' => 'Food Delivery App', 'image' => 'images/food_app.png', 'desc' => 'Order tracking, payments, and admin panel in one product.'],
        ['title' => 'Healthcare Platform', 'image' => 'images/workout.png', 'desc' => 'Patient workflows, wearables sync, and health analytics.'],
        ['title' => 'Education App', 'image' => 'images/education.png', 'desc' => 'Student portals, attendance, and parent communication.'],
        ['title' => 'E-Commerce Store', 'image' => 'images/ecommerce.png', 'desc' => 'Catalog, checkout, inventory, and growth-ready storefront.'],
        ['title' => 'Logistics System', 'image' => 'images/logistic.png', 'desc' => 'Dispatch, fleet tracking, and delivery performance dashboards.'],
        ['title' => 'Travel Booking', 'image' => 'images/travel.png', 'desc' => 'Search, booking flow, and partner management tools.'],
    ];

    $aboutImages = ['images/Fitzme_ok.png', 'images/anukool.png', 'images/MyTalent.png', 'images/SIMS.png'];
    $techMarquee = ['Laravel', 'React', 'Next.js', 'Flutter', 'React Native', 'Firebase', 'AWS', 'Docker', 'Python', 'TypeScript', 'MySQL', 'PostgreSQL', 'Redis', 'Figma'];
    $innovationProjects = collect([
        config('portfolio.projects.medical-crm'),
        config('portfolio.projects.agentic-ai-platform'),
        config('portfolio.projects.wellora-ai'),
        config('portfolio.projects.food-marketplace-platform'),
    ])->filter();
@endphp

{{-- Hero carousel --}}
<section class="hero-carousel" id="home" aria-label="Hero">
    <div class="hero-glow hero-glow-a" aria-hidden="true"></div>
    <div class="hero-glow hero-glow-b" aria-hidden="true"></div>

    <div class="hero-carousel-inner">
        @foreach($heroSlides as $index => $slide)
            <article class="hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-slide="{{ $index }}">
                <div class="hero-content">
                    <div class="badge pulse-badge">{{ $slide['badge'] }}</div>
                    <h1>
                        {{ $slide['title'] }}<br>
                        <span class="gradient-text">{{ $slide['highlight'] }}</span>
                    </h1>
                    <p class="hero-lead">{!! $slide['description'] !!}</p>
                    <div class="hero-buttons">
                        @if($index === 2)
                            <a href="{{ route('projects') }}" class="btn-primary">{{ $slide['cta'] }} →</a>
                        @else
                            <a href="{{ route('contact') }}" class="btn-primary">{{ $slide['cta'] }} →</a>
                        @endif
                        <a href="{{ route('projects') }}" class="btn-secondary">Explore Portfolio →</a>
                    </div>
                    <div class="hero-proof-row">
                        <span class="hero-proof-chip">★ 4.9 Client Rating</span>
                        <span class="hero-proof-chip">50+ Projects Delivered</span>
                        <span class="hero-proof-chip">6+ Years Experience</span>
                    </div>
                </div>

                <div class="hero-visual hero-visual-fixed">
                    <div class="hero-image-frame float-slow">
                        <img src="{{ $slide['image'] }}" alt="{{ strip_tags($slide['highlight']) }} illustration" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                        <span class="hero-frame-shine" aria-hidden="true"></span>
                        <span class="hero-frame-ring" aria-hidden="true"></span>
                    </div>
                    <div class="stat-badge success">
                        <div class="stat-icon success-icon" aria-hidden="true">
                            <svg class="stat-icon-svg" viewBox="0 0 24 24" fill="none">
                                <path d="M4 16l5-5 3 3 6-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 7h4v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <div>On-Time Delivery</div>
                            <div>98%</div>
                        </div>
                    </div>
                    <div class="stat-badge users">
                        <div class="stat-icon users-icon" aria-hidden="true">
                            <svg class="stat-icon-svg" viewBox="0 0 24 24" fill="none">
                                <circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.8"/>
                                <circle cx="16" cy="10" r="2.5" stroke="currentColor" stroke-width="1.8"/>
                                <path d="M4.5 18c.8-2.5 2.6-4 4.5-4s3.7 1.5 4.5 4M13.5 18c.5-1.8 1.8-3 3.5-3 1.2 0 2.3.6 3 1.6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <div>Active Users</div>
                            <div>100K+</div>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="hero-carousel-controls">
        <button type="button" class="hero-nav-btn" id="heroPrev" aria-label="Previous slide">‹</button>
        <div class="hero-dots" id="heroDots">
            @foreach($heroSlides as $index => $slide)
                <button type="button" class="hero-dot {{ $index === 0 ? 'is-active' : '' }}" data-slide="{{ $index }}" aria-label="Go to slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
        <button type="button" class="hero-nav-btn" id="heroNext" aria-label="Next slide">›</button>
    </div>

    <div class="scroll-down" onclick="document.querySelector('.trusted-section').scrollIntoView({behavior: 'smooth'})">
        <span class="scroll-down-text">Scroll Down</span>
        <div class="scroll-icon"></div>
    </div>
</section>

{{-- Quick value strip --}}
<section class="trusted-section">
    <p class="trusted-kicker">What You Get With CodoVision</p>
    <div class="trusted-grid">
        <article class="trusted-item animate-on-scroll">
            <span class="trusted-dot"></span>
            <div>
                <h3>Clear Roadmaps</h3>
                <p class="trusted-desc">Every step explained in simple language.</p>
            </div>
        </article>
        <article class="trusted-item animate-on-scroll">
            <span class="trusted-dot"></span>
            <div>
                <h3>Scalable Architecture</h3>
                <p class="trusted-desc">Built to grow with your users and revenue.</p>
            </div>
        </article>
        <article class="trusted-item animate-on-scroll">
            <span class="trusted-dot"></span>
            <div>
                <h3>Fast MVP Launch</h3>
                <p class="trusted-desc">Go live in weeks, not months.</p>
            </div>
        </article>
        <article class="trusted-item animate-on-scroll">
            <span class="trusted-dot"></span>
            <div>
                <h3>Long-Term Support</h3>
                <p class="trusted-desc">We stay with you after launch.</p>
            </div>
        </article>
    </div>
</section>

{{-- About + stats --}}
<section class="section about-showcase-section">
    <div class="about-showcase">
        <div class="about-visual-cluster animate-on-scroll">
            @foreach($aboutImages as $i => $img)
                <img src="{{ asset($img) }}" alt="CodoVision project showcase {{ $i + 1 }}" class="about-float-img about-float-{{ $i + 1 }}">
            @endforeach
            <span class="about-visual-badge">Live Products</span>
        </div>
        <div class="about-copy animate-on-scroll">
            <div class="section-badge">About CodoVision</div>
            <h2>Enhance Your Business with <span class="gradient-text">Smart Software</span></h2>
            <p class="section-subtitle about-lead">
                We are a product engineering team helping startups and enterprises build mobile apps, web platforms, and automation systems — explained clearly, delivered on time.
            </p>
            <p class="plain-explainer">
                <strong>In simple words:</strong> You share your idea → we plan features → design screens → build the product → test → launch → support you after go-live.
            </p>
            <div class="about-stats-grid">
                <article class="about-stat-card">
                    <h3 class="counter" data-target="6">0</h3>
                    <p>Years in Business</p>
                </article>
                <article class="about-stat-card">
                    <h3><span class="counter" data-target="50">0</span>+</h3>
                    <p>Projects Delivered</p>
                </article>
                <article class="about-stat-card">
                    <h3><span class="counter" data-target="30">0</span>+</h3>
                    <p>Happy Clients</p>
                </article>
                <article class="about-stat-card">
                    <h3><span class="counter" data-target="100">0</span>K+</h3>
                    <p>End Users Served</p>
                </article>
            </div>
            <a href="{{ route('about') }}" class="btn-primary" style="text-decoration:none;display:inline-flex;margin-top:24px;">Learn About Our Team →</a>
        </div>
    </div>
</section>

{{-- Services with images --}}
<section class="section" id="services">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Our Services</div>
        <h2>Accelerating Growth Through <span class="gradient-text">Digital Innovation</span></h2>
        <p class="section-subtitle">Pick a service below — each card shows what we build and why it helps your business.</p>
    </div>
    <div class="services-visual-grid">
        @foreach($services ?? [] as $service)
            @php
                $img = $serviceImages[$service['slug']] ?? 'images/crm.png';
                $iconSvg = $serviceIconSvg[$service['slug']] ?? '<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/></svg>';
            @endphp
            <a href="{{ route('services.detail', $service['slug']) }}" class="service-visual-card card-link animate-on-scroll">
                <div class="service-visual-media">
                    <img src="{{ asset($img) }}" alt="{{ $service['title'] }}">
                    <span class="service-visual-icon {{ $service['icon_class'] }}">{!! $iconSvg !!}</span>
                </div>
                <div class="service-visual-body">
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['summary'] }}</p>
                    <span class="learn-more">View full details →</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- AI, healthcare, and marketplace innovation --}}
<section class="section innovation-showcase-section">
    <div class="innovation-showcase-header animate-on-scroll">
        <div class="innovation-showcase-heading">
            <div class="section-badge">AI + Health Innovation</div>
            <h2>Intelligent Products Built for <span class="gradient-text">Real-World Operations</span></h2>
            <p>Explore practical digital products designed to automate complex work, improve everyday decisions, and create measurable business impact.</p>
        </div>
        <div class="innovation-showcase-proof" aria-label="Portfolio highlights">
            <div>
                <strong>{{ count($innovationProjects) }}</strong>
                <span>Featured solutions</span>
            </div>
            <div>
                <strong>AI + Data</strong>
                <span>Built into every workflow</span>
            </div>
        </div>
    </div>

    <div class="innovation-showcase-grid">
        @foreach($innovationProjects as $index => $innovationProject)
            <a href="{{ route('projects.detail', $innovationProject['slug']) }}"
               class="innovation-showcase-card animate-on-scroll {{ $index === 0 ? 'innovation-showcase-featured' : '' }}">
                <div class="innovation-showcase-media">
                    <img src="{{ asset($innovationProject['image']) }}"
                         alt="{{ $innovationProject['title'] }} project interface"
                         loading="lazy">
                    <span class="innovation-project-number" aria-hidden="true">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="innovation-showcase-content">
                    <span class="innovation-project-type">{{ $innovationProject['industry'] }}</span>
                    <h3>{{ $innovationProject['title'] }}</h3>
                    <p>{{ $innovationProject['summary'] }}</p>
                    <span class="innovation-project-link">
                        Explore case study
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </a>
        @endforeach
    </div>

    <div class="innovation-capability-row animate-on-scroll" aria-label="Our product capabilities">
        <span class="innovation-capability-label">Core capabilities</span>
        <span>Agentic AI Automation</span>
        <span>RCM KPI Analytics</span>
        <span>Custom LLM Systems</span>
        <span>Visual Food AI</span>
        <span>Privacy-First Health Data</span>
        <span>Multi-Vendor Marketplaces</span>
    </div>
</section>

{{-- Industry solutions --}}
<section class="section solutions-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Industry Solutions</div>
        <h2>Tailored Software, <span class="gradient-text">Real Business Impact</span></h2>
        <p class="section-subtitle">Ready-made solution tracks we customize for your market and users.</p>
    </div>
    <div class="solutions-grid">
        @foreach($solutions as $solution)
            <article class="solution-card animate-on-scroll">
                <div class="solution-card-media">
                    <img src="{{ asset($solution['image']) }}" alt="{{ $solution['title'] }}">
                </div>
                <div class="solution-card-body">
                    <h3>{{ $solution['title'] }}</h3>
                    <p>{{ $solution['desc'] }}</p>
                    <a href="{{ route('contact') }}" class="solution-link">Discuss this solution →</a>
                </div>
            </article>
        @endforeach
    </div>
</section>

{{-- Process timeline --}}
<section class="section process-home-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">How We Work</div>
        <h2>Scale Your Business with <span class="gradient-text">Agile Development</span></h2>
        <p class="section-subtitle">Our process is easy to follow — click each step to see what happens and what you receive.</p>
    </div>
    <div class="process-home-layout">
        <div class="process-steps-nav animate-on-scroll">
            @foreach($processPhases ?? [] as $i => $phase)
                <button type="button" class="process-step-btn {{ $i === 0 ? 'is-active' : '' }}" data-process="{{ $i }}">
                    <span class="process-step-num">{{ $phase['number'] }}</span>
                    <span class="process-step-label">{{ $phase['title'] }}</span>
                </button>
            @endforeach
        </div>
        <div class="process-detail-panel animate-on-scroll">
            @foreach($processPhases ?? [] as $i => $phase)
                <article class="process-detail-card {{ $i === 0 ? 'is-active' : '' }}" data-process-panel="{{ $i }}">
                    <div class="process-detail-head">
                        <span class="process-detail-icon {{ $phase['icon_class'] }}">{{ $phase['icon'] }}</span>
                        <div>
                            <h3>{{ $phase['number'] }} — {{ $phase['title'] }}</h3>
                            <p>{{ $phase['summary'] }}</p>
                        </div>
                    </div>
                    <div class="process-detail-columns">
                        <div>
                            <h4>What we do</h4>
                            <ul>
                                @foreach($phase['activities'] ?? [] as $activity)
                                    <li>{{ $activity }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            <h4>What you get</h4>
                            <ul>
                                @foreach($phase['deliverables'] ?? [] as $deliverable)
                                    <li>{{ $deliverable }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <a href="{{ route('process') }}#{{ $phase['slug'] }}" class="learn-more">Full process page →</a>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- Visual delivery story --}}
<section class="section visual-story-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Visual Overview</div>
        <h2>How We Deliver IT Projects</h2>
        <p class="section-subtitle">A picture-based walkthrough from planning to launch.</p>
    </div>
    <div class="visual-story-grid">
        @php
            $storySteps = [
                ['img' => 'images/ecommerce_website.png', 'num' => '01', 'title' => 'Discovery & Strategy'],
                ['img' => 'images/education.png', 'num' => '02', 'title' => 'Design & Prototyping'],
                ['img' => 'images/food_app.png', 'num' => '03', 'title' => 'Development & QA'],
                ['img' => 'images/logistic.png', 'num' => '04', 'title' => 'Launch & Analytics'],
                ['img' => 'images/iot.png', 'num' => '05', 'title' => 'Cloud & Security'],
                ['img' => 'images/crm.png', 'num' => '06', 'title' => 'Growth & Support'],
            ];
        @endphp
        @foreach($storySteps as $i => $step)
            <article class="visual-story-card animate-on-scroll {{ $i === 0 ? 'visual-story-main' : '' }} {{ $i === 1 ? 'visual-story-tall' : '' }} {{ $i === 5 ? 'visual-story-wide' : '' }}">
                <img src="{{ asset($step['img']) }}" alt="{{ $step['title'] }}">
                <div class="visual-story-overlay">
                    <span>{{ $step['num'] }}</span>
                    <h3>{{ $step['title'] }}</h3>
                </div>
            </article>
        @endforeach
    </div>
</section>

{{-- Featured projects --}}
<section class="section" id="projects">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Case Studies</div>
        <h2>Discover Success Through <span class="gradient-text">Our Client Stories</span></h2>
        <p class="section-subtitle">Real apps and platforms we designed, built, and launched.</p>
    </div>
    <div class="projects-grid">
        @foreach($featuredProjects ?? [] as $project)
            @php
                $projectImage = str_starts_with($project['image'], 'http') ? $project['image'] : asset($project['image']);
            @endphp
            <a href="{{ route('projects.detail', $project['slug']) }}" class="project-card card-link animate-on-scroll">
                <div class="project-image">
                    <img src="{{ $projectImage }}" alt="{{ $project['title'] }}">
                    <span class="project-card-tag">{{ $project['industry'] ?? 'Digital Product' }}</span>
                </div>
                <div class="project-info">
                    <h3>{{ $project['title'] }}</h3>
                    <p>{{ $project['summary'] }}</p>
                    <p class="review-meta">{{ $project['duration'] ?? 'Timeline TBD' }} · {{ implode(', ', array_slice($project['tags'] ?? [], 0, 2)) }}</p>
                    <span class="learn-more">View case study →</span>
                </div>
            </a>
        @endforeach
    </div>
    <div class="center-link-row">
        <a href="{{ route('projects') }}" class="learn-more">See all projects →</a>
    </div>
</section>

{{-- Why choose us --}}
<section class="section why-section">
    <div class="why-layout">
        <div class="why-visual animate-on-scroll">
            <img src="{{ asset('images/Hewie_Website.png') }}" alt="CodoVision product dashboard" class="why-hero-img">
            <div class="why-floating-card why-floating-a">
                <strong>Weekly Updates</strong>
                <span>Know exactly what was built</span>
            </div>
            <div class="why-floating-card why-floating-b">
                <strong>Clean Handover</strong>
                <span>Docs + code your team can use</span>
            </div>
        </div>
        <div class="why-copy animate-on-scroll">
            <div class="section-badge">Why Choose Us</div>
            <h2>Your Partner for <span class="gradient-text">Reliable Delivery</span></h2>
            <p class="section-subtitle">We combine startup speed with enterprise discipline.</p>
            <div class="why-points">
                <article class="why-point">
                    <span class="positioning-chip">01</span>
                    <div>
                        <h3>Business-First Thinking</h3>
                        <p>Every feature is tied to a clear user or revenue goal — not built just because it looks cool.</p>
                    </div>
                </article>
                <article class="why-point">
                    <span class="positioning-chip">02</span>
                    <div>
                        <h3>Strong Technical Foundation</h3>
                        <p>Secure code, fast performance, and architecture that won’t break when you scale.</p>
                    </div>
                </article>
                <article class="why-point">
                    <span class="positioning-chip">03</span>
                    <div>
                        <h3>Transparent Communication</h3>
                        <p>Regular demos, honest timelines, and no surprise invoices.</p>
                    </div>
                </article>
            </div>
        </div>
    </div>
</section>

{{-- Tech marquee --}}
<section class="section tech-marquee-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Technology Stack</div>
        <h2>Tools We Use to Build <span class="gradient-text">Production-Ready Products</span></h2>
    </div>
    <div class="marquee-wrap" aria-hidden="true">
        <div class="marquee-track">
            @foreach(array_merge($techMarquee, $techMarquee) as $tech)
                <span class="marquee-chip">{{ $tech }}</span>
            @endforeach
        </div>
    </div>
    <div class="center-link-row">
        <a href="{{ route('technologies') }}" class="learn-more">Explore full technology page →</a>
    </div>
</section>

@include('partials.reviews-professional', ['testimonials' => $testimonials ?? [], 'reviewId' => 'home-review'])
<div id="reviewGridHome" style="display:none;" aria-hidden="true"></div>

{{-- FAQ --}}
<section class="section faq-home-section">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">FAQ</div>
        <h2>Everything You Need to <span class="gradient-text">Know</span></h2>
        <p class="section-subtitle">Clear answers so you can decide with confidence.</p>
    </div>
    <div class="faq-list animate-on-scroll">
        <div class="faq-item">
            <div class="faq-question">What types of projects do you build?</div>
            <div class="faq-answer">
                <p>We build mobile apps, web platforms, marketplaces, delivery systems, dashboards, IoT products, and AI-powered features for startups and enterprises.</p>
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How long does a typical project take?</div>
            <div class="faq-answer">
                <p>MVPs usually take 4–8 weeks. Larger platforms take 10–16+ weeks depending on features. We share a clear timeline before development starts.</p>
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Do you provide support after launch?</div>
            <div class="faq-answer">
                <p>Yes. We offer maintenance, bug fixes, performance improvements, and feature updates so your product stays stable as you grow.</p>
            </div>
        </div>
        <div class="faq-item">
            <div class="faq-question">How do we communicate during the project?</div>
            <div class="faq-answer">
                <p>Weekly calls, written updates, and demo sessions. You always know what was completed and what is coming next.</p>
            </div>
        </div>
    </div>
</section>

@include('partials.cta-premium')
@endsection
