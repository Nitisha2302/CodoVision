@extends('layouts.app')

@php
    $img = fn (string $name) => asset("images/design/{$name}.jpg");
    $designVideos = [
        'experience'  => asset('images/design/scroll-experience.mp4'),
        'creation'    => asset('images/design/scroll-creation.mp4'),
        'exploration' => asset('images/design/scroll-exploration.mp4'),
        'expression'  => asset('images/design/scroll-expression.mp4'),
    ];
@endphp

@section('title', 'Design Studio | CodoVision LLP')
@section('meta_description', 'CodoVision LLP immersive design showcase: AR, VR, graphics, imagery, motion, and spatial visual experiences for modern digital brands.')
@section('meta_keywords', 'CodoVision LLP design, AR design, VR design, motion graphics, immersive experience design, UI UX visual design')
@section('meta_canonical', url('/design'))
@section('og_title', 'Design Studio | CodoVision LLP')
@section('og_description', 'Explore CodoVision LLP design through AR, VR, imagery, animation, and immersive visual experiences.')
@section('og_url', url('/design'))
@section('og_image', url('/images/logo.png'))
@section('twitter_title', 'Design Studio | CodoVision LLP')
@section('twitter_description', 'AR, VR, motion, and immersive visual experiences by CodoVision LLP.')
@section('twitter_image', url('/images/logo.png'))

@section('head_extras')
<x-seo-breadcrumbs :items="[
    ['name' => 'Home', 'url' => url('/')],
    ['name' => 'Design', 'url' => url('/design')],
]" />
<link rel="preload" href="{{ $designVideos['experience'] }}" as="video" type="video/mp4">
<link rel="stylesheet" href="{{ asset('css/design.css') }}?v=5">
<script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js" defer></script>
@endsection

@section('content')
<div class="design-xr design-xr--vaulk" id="design-xr">
    <div class="xr-page-progress" aria-hidden="true"><span id="designScrollProgress"></span></div>

    <section class="xr-hero xr-hero--mega" id="top">
        <div class="xr-hero__bg-dim" aria-hidden="true"></div>
        <div class="xr-hero__bg-lines" aria-hidden="true"></div>
        <canvas id="xrParticles" aria-hidden="true"></canvas>

        <div class="xr-hero__world xr-hero__world--center" aria-hidden="true">
            <canvas id="xrWorld3d"></canvas>
            <div class="xr-orbit-world xr-orbit-world--lg">
                <span class="xr-orbit-ring xr-orbit-ring--a"></span>
                <span class="xr-orbit-ring xr-orbit-ring--b"></span>
                <span class="xr-orbit-ring xr-orbit-ring--c"></span>
                <span class="xr-orbit-core"></span>
            </div>
        </div>

        <div class="xr-hero__grid-floor" aria-hidden="true"></div>
        <div class="xr-hero__aurora" aria-hidden="true"></div>
        <div class="xr-hero__glow" aria-hidden="true"></div>

        <div class="xr-hero__float-tags" aria-hidden="true">
            <span class="xr-float-tag xr-float-tag--1">AR</span>
            <span class="xr-float-tag xr-float-tag--2">VR</span>
            <span class="xr-float-tag xr-float-tag--3">3D</span>
            <span class="xr-float-tag xr-float-tag--4">Motion</span>
        </div>

        <div class="xr-hero__mega-wrap">
            <div class="xr-hero__inner animate-on-scroll">
                <span class="xr-eyebrow xr-eyebrow--pulse">Immersive Visual Design</span>
                <h1 class="xr-hero__title"><span class="xr-hero__title-line">Reality,</span> <strong class="xr-hero__title-gradient">expanded.</strong></h1>
                <p class="xr-hero__lead">
                    A spatial design showcase — cinematic motion, depth, and imagery built for
                    unforgettable AR &amp; VR experiences.
                </p>
                <div class="xr-hero__actions">
                    <a href="#explorer" class="xr-btn xr-btn--primary xr-btn--lg">Enter the gallery</a>
                    <button type="button" class="xr-btn xr-btn--ghost xr-btn--lg" id="xrPlayMedia">Play motion</button>
                </div>
            </div>

            <div class="xr-hero__stage animate-on-scroll" id="heroStage">
                <div class="xr-hero__stage-glow" aria-hidden="true"></div>
                <div class="xr-hero__stage-ring xr-hero__stage-ring--1" aria-hidden="true"></div>
                <div class="xr-hero__stage-ring xr-hero__stage-ring--2" aria-hidden="true"></div>
                <div class="xr-hero__stage-frame">
                    <video class="xr-hero__stage-video" muted loop playsinline autoplay preload="auto" poster="{{ $img('hero-vr') }}">
                        <source src="{{ $designVideos['creation'] }}" type="video/mp4">
                    </video>
                    <div class="xr-hero__stage-scan" aria-hidden="true"></div>
                    <div class="xr-hero__stage-vignette" aria-hidden="true"></div>
                    <span class="xr-hero__hud" aria-hidden="true"></span>
                    <div class="xr-hero__stage-label">
                        <span class="xr-hero__stage-live"></span>
                        Live spatial preview
                    </div>
                </div>
            </div>
        </div>

        <div class="xr-hero__scroll-hint" aria-hidden="true">
            <span>Scroll to explore</span>
            <i></i>
        </div>
    </section>

    <div class="xr-marquee-band" aria-hidden="true">
        <div class="xr-marquee-band__track">
            <span>AR Visuals</span><span>VR Worlds</span><span>Motion Graphics</span><span>3D Composition</span><span>Brand Identity</span><span>Experience Design</span>
            <span>AR Visuals</span><span>VR Worlds</span><span>Motion Graphics</span><span>3D Composition</span><span>Brand Identity</span><span>Experience Design</span>
        </div>
    </div>

    <section class="xr-spotlight xr-vaulk-section">
        <span class="xr-section-num">001</span>
        <div class="xr-spotlight__card animate-on-scroll">
            <div>
                <span class="xr-eyebrow">Immersive design is here</span>
                <h2>High-fidelity imagery for every experience.</h2>
                <p>
                    We shape color, depth, light, and motion into spatial scenes, brand worlds,
                    and cinematic visuals — crafted to feel as real as the moment itself.
                </p>
                <a href="{{ route('contact') }}" class="xr-btn xr-btn--primary">Discuss your vision</a>
            </div>
            <div class="xr-spotlight__visual xr-spotlight__visual--motion">
                <img src="{{ $img('spotlight') }}" alt="Abstract immersive design composition" loading="lazy" decoding="async">
                <div class="xr-spotlight__motion-layer" aria-hidden="true"></div>
                <div class="xr-spotlight__motion-layer xr-spotlight__motion-layer--2" aria-hidden="true"></div>
                <div class="xr-spotlight__visual-frame" aria-hidden="true"></div>
            </div>
        </div>
    </section>

    <section class="xr-categories" aria-label="Design disciplines">
        <p class="xr-categories__label">Creative disciplines</p>
        <div class="xr-pill-rail" role="tablist" id="categoryPills">
            <button type="button" class="xr-pill is-active" role="tab" aria-selected="true" data-category="vr" data-img="{{ $img('hero-vr') }}">VR Experiences</button>
            <button type="button" class="xr-pill" role="tab" data-category="ar" data-img="{{ $img('hero-ar') }}">AR Visuals</button>
            <button type="button" class="xr-pill" role="tab" data-category="graphics" data-img="{{ $img('hero-web') }}">Graphic Design</button>
            <button type="button" class="xr-pill" role="tab" data-category="motion" data-img="{{ $img('hero-motion') }}">Motion Graphics</button>
            <button type="button" class="xr-pill" role="tab" data-category="brand" data-img="{{ $img('hero-brand') }}">Brand &amp; Identity</button>
        </div>
    </section>

    <section class="xr-cinema xr-vaulk-section" id="explorer">
        <span class="xr-section-num">002</span>
        <div class="xr-section__head animate-on-scroll">
            <h2>Unlock a whole new dimension<br>of visual experience.</h2>
            <p>Scroll through four cinematic scenes — each with its own motion clip.</p>
        </div>

        <div class="xr-cinema__nav">
            <div class="xr-tab-rail" role="tablist" id="featureTabs">
                <button type="button" class="xr-tab is-active" role="tab" aria-selected="true" data-panel="experience">Experience</button>
                <button type="button" class="xr-tab" role="tab" data-panel="workspace">Creation</button>
                <button type="button" class="xr-tab" role="tab" data-panel="explore">Exploration</button>
                <button type="button" class="xr-tab" role="tab" data-panel="access">Expression</button>
            </div>
            <div class="xr-cinema__progress"><span id="scrollStoryProgress"></span></div>
        </div>

        <div class="xr-cinema__body">
            <div class="xr-cinema__pin">
                <div class="xr-cinema__screen" id="cinemaScreen">
                    <span class="xr-cinema__counter" id="cinemaCounter">01 / 04</span>
                    <div class="xr-cinema__layer is-active" data-panel="experience" role="tabpanel">
                        <video class="xr-cinema__video" muted loop playsinline autoplay preload="auto" poster="{{ $img('panel-experience') }}" data-video-slot="experience">
                            <source src="{{ $designVideos['experience'] }}" type="video/mp4">
                        </video>
                        <div class="xr-cinema__overlay">
                            <span class="xr-cinema__tag">01 · Experience</span>
                            <h3>A front-row seat to pure immersive experience.</h3>
                        </div>
                    </div>
                    <div class="xr-cinema__layer" data-panel="workspace" role="tabpanel">
                        <video class="xr-cinema__video" muted loop playsinline preload="auto" poster="{{ $img('panel-workspace') }}" data-video-slot="creation">
                            <source src="{{ $designVideos['creation'] }}" type="video/mp4">
                        </video>
                        <div class="xr-cinema__overlay">
                            <span class="xr-cinema__tag">02 · Creation</span>
                            <h3>Shape ideas in a limitless creative canvas.</h3>
                        </div>
                    </div>
                    <div class="xr-cinema__layer" data-panel="explore" role="tabpanel">
                        <video class="xr-cinema__video" muted loop playsinline preload="auto" poster="{{ $img('panel-explore') }}" data-video-slot="exploration">
                            <source src="{{ $designVideos['exploration'] }}" type="video/mp4">
                        </video>
                        <div class="xr-cinema__overlay">
                            <span class="xr-cinema__tag">03 · Exploration</span>
                            <h3>Explore imagined worlds through image and depth.</h3>
                        </div>
                    </div>
                    <div class="xr-cinema__layer" data-panel="access" role="tabpanel">
                        <video class="xr-cinema__video" muted loop playsinline preload="auto" poster="{{ $img('panel-access') }}" data-video-slot="expression">
                            <source src="{{ $designVideos['expression'] }}" type="video/mp4">
                        </video>
                        <div class="xr-cinema__overlay">
                            <span class="xr-cinema__tag">04 · Expression</span>
                            <h3>Express your story through color, type, and imagery.</h3>
                        </div>
                    </div>
                    <div class="xr-panel__mesh" aria-hidden="true"></div>
                    <div class="xr-panel__scan" aria-hidden="true"></div>
                    <button type="button" class="xr-cinema__play-btn" id="cinemaPlayBtn" aria-label="Play video">▶</button>
                </div>
            </div>

            <div class="xr-cinema__steps" id="featurePanels">
                <div class="xr-cinema__step" data-panel="experience"></div>
                <div class="xr-cinema__step" data-panel="workspace"></div>
                <div class="xr-cinema__step" data-panel="explore"></div>
                <div class="xr-cinema__step" data-panel="access"></div>
            </div>
        </div>
    </section>

    <section class="xr-immersive-strip">
        <div class="xr-immersive-strip__grid" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>
        <div class="xr-immersive-strip__content animate-on-scroll">
            <h2>Spatial design that moves with you.</h2>
            <p>Depth, parallax, and motion graphics layered like a living 3D world — clean, premium, and built for experience-first storytelling.</p>
        </div>
    </section>

    <section class="xr-section xr-section--wide">
        <div class="xr-section__head animate-on-scroll">
            <h2>Creative direction that elevates every frame.</h2>
            <p>Thoughtful visuals, balanced composition, and motion that supports the feeling you want people to remember.</p>
        </div>

        <div class="xr-cap-grid">
            <article class="xr-cap-card animate-on-scroll">
                <div class="xr-cap-card__bg">
                    <img src="{{ $img('cap-ai') }}" alt="Neon futuristic light trails and atmospheric visual design" loading="lazy" decoding="async">
                </div>
                <div class="xr-cap-card__content">
                    <div class="xr-cap-card__icon" aria-hidden="true">✦</div>
                    <h3>Design that feels alive.</h3>
                    <p>Layered imagery, atmospheric lighting, and spatial hierarchy that pull viewers into the experience.</p>
                </div>
            </article>

            <article class="xr-cap-card animate-on-scroll">
                <div class="xr-cap-card__bg">
                    <img src="{{ $img('cap-refine') }}" alt="Graphic designer refining artwork on a creative workspace" loading="lazy" decoding="async">
                </div>
                <div class="xr-cap-card__content">
                    <div class="xr-cap-card__icon" aria-hidden="true">◎</div>
                    <h3>See it, refine it, perfect it.</h3>
                    <p>From rough mood boards to final art direction — every pixel tuned for clarity, emotion, and impact.</p>
                </div>
            </article>
        </div>
    </section>

    <section class="xr-disciplines" id="disciplines">
        <div class="xr-section__head animate-on-scroll">
            <h2>Every detail,<br>seen through a designer’s lens.</h2>
            <p>Imagery, spatial layout, brand graphics, and motion — united in one visual language.</p>
        </div>

        <div class="xr-pill-rail" role="tablist" id="disciplineTabs">
            <button type="button" class="xr-pill is-active" role="tab" data-discipline="ui">Visual Systems</button>
            <button type="button" class="xr-pill" role="tab" data-discipline="spatial">Spatial / 3D</button>
            <button type="button" class="xr-pill" role="tab" data-discipline="content">Imagery &amp; Layout</button>
            <button type="button" class="xr-pill" role="tab" data-discipline="motion">Motion Design</button>
        </div>

        <div class="xr-discipline-panels">
            <div class="xr-discipline-panel is-active" data-discipline="ui">
                <div class="xr-discipline-panel__media">
                    <img src="{{ $img('discipline-ui') }}" alt="Graphic design flat lay with pens and creative materials" width="800" height="800" loading="lazy" decoding="async">
                </div>
                <div>
                    <h3>A smarter way to show your visual world.</h3>
                    <p>Color palettes, type pairings, grids, and iconography that stay consistent across every touchpoint of the experience.</p>
                    <ul class="xr-tag-list">
                        <li>Color theory</li>
                        <li>Typography</li>
                        <li>Visual rhythm</li>
                    </ul>
                </div>
            </div>

            <div class="xr-discipline-panel" data-discipline="spatial">
                <div class="xr-discipline-panel__media">
                    <img src="{{ $img('discipline-spatial') }}" alt="Person exploring spatial VR environment with headset" width="800" height="800" loading="lazy" decoding="async">
                </div>
                <div>
                    <h3>Navigate depth without losing beauty.</h3>
                    <p>AR overlays, VR environments, and mixed-reality scenes with comfortable scale, lighting, and focal depth.</p>
                    <ul class="xr-tag-list">
                        <li>AR scenes</li>
                        <li>VR worlds</li>
                        <li>3D composition</li>
                    </ul>
                </div>
            </div>

            <div class="xr-discipline-panel" data-discipline="content">
                <div class="xr-discipline-panel__media">
                    <img src="{{ $img('discipline-content') }}" alt="Camera and photography tools for visual storytelling" width="800" height="800" loading="lazy" decoding="async">
                </div>
                <div>
                    <h3>Capture attention with visual storytelling.</h3>
                    <p>Hero imagery, photo direction, illustration, and layout flows that guide the eye and build emotion.</p>
                    <ul class="xr-tag-list">
                        <li>Photo art direction</li>
                        <li>Illustration</li>
                        <li>Editorial layout</li>
                    </ul>
                </div>
            </div>

            <div class="xr-discipline-panel" data-discipline="motion">
                <div class="xr-discipline-panel__media">
                    <img src="{{ $img('discipline-motion') }}" alt="Long exposure light painting for motion graphics inspiration" width="800" height="800" loading="lazy" decoding="async">
                </div>
                <div>
                    <h3>Feel the rhythm in every transition.</h3>
                    <p>Animated graphics, looping visuals, and choreographed motion that make experiences feel fluid and alive.</p>
                    <ul class="xr-tag-list">
                        <li>Motion graphics</li>
                        <li>Loop animations</li>
                        <li>Visual effects</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="xr-section" id="gallery">
        <div class="xr-section__head animate-on-scroll">
            <h2>See the craft in every frame.</h2>
            <p>A curated gallery of visuals — imagery, graphics, and experience-led design moments.</p>
        </div>
    </section>

    <div class="xr-gallery">
        <figure class="xr-gallery__item xr-gallery__item--lg animate-on-scroll">
            <img src="{{ $img('gallery-1') }}" alt="Neon abstract light art for immersive visual design" loading="lazy" decoding="async" width="1200" height="750">
            <div class="xr-gallery__overlay"><span>Immersive visuals</span></div>
        </figure>
        <figure class="xr-gallery__item xr-gallery__item--md animate-on-scroll">
            <img src="{{ $img('gallery-2') }}" alt="Glowing abstract digital art composition" loading="lazy" decoding="async" width="600" height="600">
            <div class="xr-gallery__overlay"><span>Digital art</span></div>
        </figure>
        <figure class="xr-gallery__item xr-gallery__item--md animate-on-scroll">
            <img src="{{ $img('gallery-3') }}" alt="Creative professional at work on visual design" loading="lazy" decoding="async" width="600" height="600">
            <div class="xr-gallery__overlay"><span>Creative process</span></div>
        </figure>
        <figure class="xr-gallery__item xr-gallery__item--sm animate-on-scroll">
            <img src="{{ $img('gallery-4') }}" alt="Abstract 3D render with soft gradient forms" loading="lazy" decoding="async" width="500" height="500">
            <div class="xr-gallery__overlay"><span>3D graphics</span></div>
        </figure>
        <figure class="xr-gallery__item xr-gallery__item--sm animate-on-scroll">
            <img src="{{ $img('gallery-5') }}" alt="Scenic vista for environmental experience design" loading="lazy" decoding="async" width="500" height="500">
            <div class="xr-gallery__overlay"><span>Motion stills</span></div>
        </figure>
    </div>

    <section class="xr-section">
        <div class="xr-section__head animate-on-scroll">
            <h2>Animation showcase</h2>
            <p>Motion studies that bring graphics, shapes, and imagery to life in the experience.</p>
        </div>
        <div class="xr-motion-lab">
            <div class="xr-motion-card animate-on-scroll">
                <div class="xr-motion-card__demo"></div>
                <span>Rotation</span>
            </div>
            <div class="xr-motion-card animate-on-scroll">
                <div class="xr-motion-card__demo"></div>
                <span>Pulse</span>
            </div>
            <div class="xr-motion-card animate-on-scroll">
                <div class="xr-motion-card__demo"></div>
                <span>Bounce</span>
            </div>
            <div class="xr-motion-card animate-on-scroll">
                <div class="xr-motion-card__demo"></div>
                <span>Morph</span>
            </div>
        </div>
    </section>

    <div class="xr-stats">
        <div class="xr-stat animate-on-scroll">
            <strong>500+</strong>
            <span>Visual concepts crafted</span>
        </div>
        <div class="xr-stat animate-on-scroll">
            <strong>48</strong>
            <span>Immersive experience designs</span>
        </div>
        <div class="xr-stat animate-on-scroll">
            <strong>∞</strong>
            <span>Creative possibilities</span>
        </div>
        <div class="xr-stat animate-on-scroll">
            <strong>100%</strong>
            <span>Design-focused craft</span>
        </div>
    </div>

    <section class="xr-build animate-on-scroll">
        <h2>Create the next generation of visual experiences.</h2>
        <p>
            This page is a design-only demo — a taste of how AR, VR, imagery, and motion can feel
            when every detail is built for experience, not utility. Let’s design something unforgettable together.
        </p>
        <div class="xr-hero__actions">
            <a href="{{ route('contact') }}" class="xr-btn xr-btn--primary">Request design work</a>
            <a href="#gallery" class="xr-btn xr-btn--ghost">View image gallery</a>
        </div>
    </section>

    <div class="xr-faq-wrap">
        <h2>Frequently asked questions</h2>
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">What kind of design does this page represent?</div>
                <div class="faq-answer">
                    <p>Immersive visual design — AR and VR experiences, graphic design, brand identity, imagery, illustration, and motion graphics. This showcase is about how things look and feel in space.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Is this a real product or a design demo?</div>
                <div class="faq-answer">
                    <p>It is a creative demo inspired by leading XR visual experiences (such as Android XR). It shows our approach to imagery, animation, and immersive layout — not a software product listing.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Can you design for AR and VR experiences?</div>
                <div class="faq-answer">
                    <p>Yes. We design spatial scenes, environmental art, interface aesthetics, 3D composition, and motion for headsets and augmented overlays — always with comfort, clarity, and visual impact in mind.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Do you work with photography and custom graphics?</div>
                <div class="faq-answer">
                    <p>Absolutely. We art-direct photography, create custom illustrations, build graphic systems, and refine imagery so every frame supports the story and emotion of your experience.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/design-world-3d.js') }}?v=5" defer></script>
<script src="{{ asset('js/design-xr.js') }}?v=5" defer></script>
@endsection
