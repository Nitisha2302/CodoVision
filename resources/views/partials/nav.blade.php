@php
    $navTechnologies = collect(config('portfolio.technologies', []))->groupBy('category');
    $categorySlug = fn (string $category) => \Illuminate\Support\Str::slug($category);
@endphp
<nav>
    <div class="nav-left">
        <button type="button" class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="navLinks">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <a href="{{ route('home') }}" class="logo" aria-label="CodoVision home">
            <div class="logo-icon">
                <img src="{{ asset('images/logo.png') }}" alt="CodoVision LLP">
            </div>
            <div class="logo-text">
                <h1>CodoVision</h1>
            </div>
        </a>
    </div>

    <ul class="nav-links" id="navLinks">
        <li>
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
        </li>

        <li class="has-dropdown">
            <button type="button" class="nav-parent {{ request()->routeIs('services*') ? 'active' : '' }}" aria-expanded="false">
                <span>Services</span>
                <span class="nav-chevron" aria-hidden="true"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ route('services') }}">All Services</a></li>
                @foreach(array_slice(array_values(config('portfolio.services', [])), 0, 6) as $serviceNavItem)
                    <li><a href="{{ route('services.detail', $serviceNavItem['slug']) }}">{{ $serviceNavItem['title'] }}</a></li>
                @endforeach
            </ul>
        </li>

        <li class="has-dropdown has-tech-menu">
            <button type="button" class="nav-parent {{ request()->routeIs('technologies') ? 'active' : '' }}" aria-expanded="false">
                <span>Technologies</span>
                <span class="nav-chevron" aria-hidden="true"></span>
            </button>
            <ul class="dropdown-menu tech-dropdown">
                <li><a href="{{ route('technologies') }}">All Technologies</a></li>
                @foreach($navTechnologies as $category => $items)
                    <li class="has-submenu">
                        <button type="button" class="submenu-parent" aria-expanded="false">
                            <span>{{ $category }}</span>
                            <span class="nav-chevron" aria-hidden="true"></span>
                        </button>
                        <ul class="submenu">
                            <li>
                                <a href="{{ route('technologies') }}#{{ $categorySlug($category) }}">All {{ $category }}</a>
                            </li>
                            @foreach($items as $techItem)
                                <li>
                                    <a href="{{ route('technologies') }}#{{ $techItem['slug'] ?? $categorySlug($techItem['name']) }}">
                                        {{ $techItem['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </li>

        <li class="has-dropdown">
            <button type="button" class="nav-parent {{ request()->routeIs('projects*') ? 'active' : '' }}" aria-expanded="false">
                <span>Projects</span>
                <span class="nav-chevron" aria-hidden="true"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ route('projects') }}">All Projects</a></li>
                @foreach(array_slice(array_values(config('portfolio.projects', [])), 0, 5) as $projectNavItem)
                    <li><a href="{{ route('projects.detail', $projectNavItem['slug']) }}">{{ $projectNavItem['title'] }}</a></li>
                @endforeach
            </ul>
        </li>

        <li class="has-dropdown">
            <button type="button" class="nav-parent {{ request()->routeIs('process') || request()->routeIs('design') || request()->routeIs('about') ? 'active' : '' }}" aria-expanded="false">
                <span>More</span>
                <span class="nav-chevron" aria-hidden="true"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="{{ route('process') }}" class="{{ request()->routeIs('process') ? 'active' : '' }}">Process</a></li>
                <li><a href="{{ route('design') }}" class="{{ request()->routeIs('design') ? 'active' : '' }}">Design</a></li>
                <li><a href="{{ route('home') }}#home-review">Reviews</a></li>
                <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
            </ul>
        </li>

        <li>
            <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
        </li>
    </ul>

    <button class="get-in-touch-btn js-whatsapp" type="button">Get in Touch →</button>
</nav>
