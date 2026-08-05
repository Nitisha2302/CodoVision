@php
    $ctaTitle = $ctaTitle ?? 'Launch Your MVP in 4–8 Weeks';
    $ctaSubtitle = $ctaSubtitle ?? 'Share your idea and receive a clear roadmap, timeline, and budget range — explained in simple steps.';
    $ctaImage = $ctaImage ?? 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=90';
@endphp

<section class="cta-section cta-premium-section">
    <div class="cta-premium-card animate-on-scroll">
        <div class="cta-premium-glow" aria-hidden="true"></div>
        <div class="cta-premium-grid">
            <div class="cta-premium-copy">
                <span class="cta-premium-badge blink-hint-soft">Free Strategy Call Available</span>
                <h2>{{ $ctaTitle }}</h2>
                <p>{{ $ctaSubtitle }}</p>
                <ul class="cta-premium-list">
                    <li>✓ Clear scope & milestone plan</li>
                    <li>✓ Fixed timeline estimate</li>
                    <li>✓ No technical jargon — plain English</li>
                </ul>
                <div class="cta-buttons cta-premium-buttons">
                    <button type="button" class="btn-cta-glow js-whatsapp">Discuss Your App Idea →</button>
                    <a href="{{ route('contact') }}" class="btn-cta-ghost">Schedule Discovery Call</a>
                </div>
            </div>
            <div class="cta-premium-visual" style="position: relative;">
                <div class="cta-image-frame">
                    <img src="{{ $ctaImage }}" alt="Team collaboration" loading="lazy">
                </div>
                <div class="cta-float-stat cta-float-a">
                    <strong>4–8 Weeks</strong>
                    <span>Average MVP launch</span>
                </div>
                <div class="cta-float-stat cta-float-b">
                    <strong>98%</strong>
                    <span>On-time delivery</span>
                </div>
            </div>
        </div>
    </div>
</section>
