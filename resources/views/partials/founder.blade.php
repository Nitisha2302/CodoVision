@php
    $founderName = 'Raghav Tomar';
    $founderRole = 'Founder & CEO';
    $founderImage = asset('images/team/raghav-tomar.png');
    $founderLinkedIn = 'https://www.linkedin.com/in/raghav-tomar-791ba6253/';
@endphp

<section class="section founder-section" id="founder">
    <div class="founder-shell animate-on-scroll">
        <div class="founder-glow" aria-hidden="true"></div>

        <div class="founder-media">
            <div class="founder-frame">
                <img src="{{ $founderImage }}" alt="{{ $founderName }}, {{ $founderRole }} of CodoVision" loading="lazy">
                <span class="founder-frame-ring" aria-hidden="true"></span>
            </div>
            <div class="founder-badge-float">
                <span class="founder-badge-dot"></span>
                Leadership
            </div>
        </div>

        <div class="founder-copy">
            <div class="section-badge">Founder &amp; CEO</div>
            <p class="founder-kicker">Leading CodoVision with clarity, craft, and delivery focus</p>
            <h2 class="founder-name">{{ $founderName }}</h2>
            <p class="founder-role">{{ $founderRole }}, CodoVision</p>
            <a href="{{ $founderLinkedIn }}" class="founder-linkedin" target="_blank" rel="noopener noreferrer" aria-label="Raghav Tomar LinkedIn profile">
                <span class="founder-linkedin-icon" aria-hidden="true">in</span>
                Connect on LinkedIn
            </a>

            <blockquote class="founder-quote">
                “We build software that businesses can trust — clear roadmaps, strong engineering, and products that create real impact.”
            </blockquote>

            <p class="founder-bio">
                Raghav leads product strategy and client partnerships at CodoVision, helping startups and growing companies turn ideas into scalable mobile apps, web platforms, and intelligent digital systems.
            </p>

            <ul class="founder-highlights">
                <li>
                    <strong>6+ years</strong>
                    <span>building digital products</span>
                </li>
                <li>
                    <strong>50+ projects</strong>
                    <span>delivered across industries</span>
                </li>
                <li>
                    <strong>Client-first</strong>
                    <span>transparent delivery model</span>
                </li>
            </ul>

            <div class="founder-actions">
                <a href="{{ route('contact') }}" class="btn-primary" style="text-decoration:none;">Talk to Leadership →</a>
                <a href="{{ $founderLinkedIn }}" class="founder-link" target="_blank" rel="noopener noreferrer">LinkedIn profile →</a>
            </div>
        </div>
    </div>
</section>
