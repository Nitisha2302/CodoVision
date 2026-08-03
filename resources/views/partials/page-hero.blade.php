@php
    $heroBadge = $heroBadge ?? 'Trispark Software Solutions';
    $heroTitle = $heroTitle ?? 'Page Title';
    $heroSubtitle = $heroSubtitle ?? '';
    $heroImage = $heroImage ?? 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1600&q=90';
@endphp

<section class="page-hero">
    <div class="page-hero-glow" aria-hidden="true"></div>
    <div class="page-hero-inner animate-on-scroll is-visible">
        <div class="page-hero-content">
            <span class="section-badge pulse-badge">{{ $heroBadge }}</span>
            <h1>{{ $heroTitle }}</h1>
            @if($heroSubtitle)
                <p class="page-hero-subtitle">{{ $heroSubtitle }}</p>
            @endif
        </div>
        <div class="page-hero-media">
            <div class="page-hero-image-box">
                <img
                    src="{{ $heroImage }}"
                    alt="{{ strip_tags($heroTitle) }}"
                    loading="eager"
                    onerror="this.onerror=null; this.src='{{ asset('images/ecommerce_website.png') }}';"
                >
            </div>
        </div>
    </div>
</section>
