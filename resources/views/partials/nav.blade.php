<nav>
    <div class="nav-left">
        <div class="nav-toggle" id="navToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>

        <div class="logo">
            <div class="logo-icon">
                <img src="{{ asset('images/logo.png') }}" alt="logo">
            </div>
            <div class="logo-text">
                <h1>Trispark</h1>
                <p>Software Solutions</p>
            </div>
        </div>
    </div>

    <ul class="nav-links" id="navLinks">
        <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
        <li class="has-dropdown">
            <a href="{{ route('services') }}" class="{{ request()->routeIs('services*') ? 'active' : '' }}">Services</a>
            <ul class="dropdown-menu">
                @foreach(array_slice(array_values(config('portfolio.services', [])), 0, 6) as $serviceNavItem)
                    <li><a href="{{ route('services.detail', $serviceNavItem['slug']) }}">{{ $serviceNavItem['title'] }}</a></li>
                @endforeach
            </ul>
        </li>
        <li class="has-dropdown">
            <a href="{{ route('process') }}" class="{{ request()->routeIs('process') ? 'active' : '' }}">Process</a>
            <ul class="dropdown-menu">
                @foreach(config('portfolio.process', []) as $phaseNavItem)
                    <li><a href="{{ route('process') }}#{{ $phaseNavItem['slug'] }}">{{ $phaseNavItem['title'] }}</a></li>
                @endforeach
            </ul>
        </li>
        <li class="has-dropdown">
            <a href="{{ route('projects') }}" class="{{ request()->routeIs('projects*') ? 'active' : '' }}">Projects</a>
            <ul class="dropdown-menu">
                @foreach(array_slice(array_values(config('portfolio.projects', [])), 0, 6) as $projectNavItem)
                    <li><a href="{{ route('projects.detail', $projectNavItem['slug']) }}">{{ $projectNavItem['title'] }}</a></li>
                @endforeach
            </ul>
        </li>
        <li><a href="{{ route('technologies') }}" class="{{ request()->routeIs('technologies') ? 'active' : '' }}">Technologies</a></li>
        <li><a href="{{ route('design') }}" class="{{ request()->routeIs('design') ? 'active' : '' }}">Design</a></li>
        <li><a href="{{ route('home') }}#home-review">Reviews</a></li>
        <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a></li>
        <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a></li>
    </ul>

    <button class="get-in-touch-btn js-whatsapp">Get in Touch →</button>
</nav>
