@php
    $reviewItems = $testimonials ?? [];
    if (empty($reviewItems)) {
        $reviewItems = config('portfolio.testimonials', []);
    }
    $rest = array_slice($reviewItems, 1, 6);
    $averageRating = count($reviewItems)
        ? number_format(collect($reviewItems)->avg(fn ($item) => (int) ($item['rating'] ?? 5)), 1)
        : '5.0';
    $globalRegions = collect($reviewItems)
        ->pluck('country')
        ->filter()
        ->unique()
        ->count();
@endphp

<section class="section review-pro-section" id="{{ $reviewId ?? 'home-review' }}">
    <div class="section-header animate-on-scroll">
        <div class="section-badge">Client Reviews</div>
        <h2>Trusted by <span class="gradient-text">Founders & Product Teams</span></h2>
        <p class="section-subtitle">Professional feedback from real delivered projects, with genuine client context and a cleaner experience on every screen size.</p>
    </div>

    <div class="review-pro-layout animate-on-scroll">
        <aside class="review-score-panel">
            <div class="review-score-ring">
                <span class="review-score-value">{{ $averageRating }}</span>
                <span class="review-score-max">/ 5</span>
            </div>
            <div class="review-score-stars">★★★★★</div>
            <p class="review-score-label">Average client satisfaction</p>
            <div class="review-score-stats">
                <div><strong>{{ count($reviewItems) }}+</strong><span>Reviews</span></div>
                <div><strong>50+</strong><span>Projects</span></div>
                <div><strong>{{ $globalRegions }}+</strong><span>Countries</span></div>
            </div>
            <div class="review-trust-badges">
                <span class="review-trust-badge">✓ Verified Delivery</span>
                <span class="review-trust-badge">✓ Founder Friendly</span>
                <span class="review-trust-badge">✓ Global Clients</span>
            </div>
        </aside>

        <div class="review-featured-wrap">
            <button type="button" class="carousel-btn review-pro-nav" id="testimonialPrev" aria-label="Previous review">‹</button>
            <div class="review-featured-carousel" id="testimonialCarousel">
                @foreach($reviewItems as $testimonial)
                    @php
                        $data = is_array($testimonial) ? $testimonial : [];
                        $img = trim((string) ($data['image'] ?? ''));
                        $avatar = $img !== '' ? (str_starts_with($img, 'http') ? $img : asset($img)) : null;
                        $rating = (int) ($data['rating'] ?? 5);
                        $country = $data['country'] ?? '';
                        $company = $data['company'] ?? '';
                        $initials = collect(preg_split('/\s+/', trim((string) ($data['name'] ?? 'Client'))))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                            ->implode('');
                    @endphp
                    <article class="review-pro-card testimonial-slide">
                        <span class="review-quote-icon" aria-hidden="true">"</span>
                        <div class="review-pro-card-shell">
                            <div class="review-pro-media">
                                @if($avatar)
                                    <img src="{{ $avatar }}" alt="{{ $data['name'] ?? 'Client' }}">
                                @else
                                    <div class="review-avatar-fallback review-avatar-fallback-lg">{{ $initials }}</div>
                                @endif
                                @if($country)
                                    <span class="review-country-chip">{{ $country }}</span>
                                @endif
                            </div>
                            <div class="review-pro-copy">
                                <div class="review-pro-rating">
                                    <span class="stars">{{ str_repeat('★', $rating) }}</span>
                                    <span class="review-verified">Verified Client</span>
                                </div>
                                <p class="review-pro-text">{{ $data['review'] ?? 'Excellent delivery and communication throughout the project.' }}</p>
                                <footer class="review-pro-author">
                                    <div class="author-info">
                                        <h4>{{ $data['name'] ?? 'Client Name' }}</h4>
                                        <p class="review-pro-role">{{ $data['role'] ?? 'Client' }}</p>
                                        @if($company !== '')
                                            <p class="review-pro-company">{{ $company }}</p>
                                        @endif
                                        <p class="review-meta">Project: {{ $data['project'] ?? 'Software Delivery' }}</p>
                                    </div>
                                </footer>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <button type="button" class="carousel-btn review-pro-nav" id="testimonialNext" aria-label="Next review">›</button>
        </div>
    </div>

    @if(count($rest) > 0)
        <div class="review-pro-grid">
            @foreach($rest as $testimonial)
                @php
                    $data = is_array($testimonial) ? $testimonial : [];
                    $img = trim((string) ($data['image'] ?? ''));
                    $avatar = $img !== '' ? (str_starts_with($img, 'http') ? $img : asset($img)) : null;
                    $country = $data['country'] ?? '';
                    $company = $data['company'] ?? '';
                    $initials = collect(preg_split('/\s+/', trim((string) ($data['name'] ?? 'Client'))))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => strtoupper(substr($part, 0, 1)))
                        ->implode('');
                @endphp
                <article class="review-pro-mini animate-on-scroll">
                    <div class="review-pro-mini-top">
                        <div class="stars">{{ str_repeat('★', (int) ($data['rating'] ?? 5)) }}</div>
                        @if($country)
                            <span class="review-country-chip review-country-chip-mini">{{ $country }}</span>
                        @endif
                    </div>
                    <p>"{{ \Illuminate\Support\Str::limit($data['review'] ?? '', 150) }}"</p>
                    <div class="review-pro-mini-author">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $data['name'] ?? 'Client' }}">
                        @else
                            <div class="review-avatar-fallback">{{ $initials }}</div>
                        @endif
                        <div>
                            <strong>{{ $data['name'] ?? 'Client' }}</strong>
                            <span>{{ $data['role'] ?? 'Client' }}</span>
                            @if($company !== '')
                                <small>{{ $company }}</small>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
