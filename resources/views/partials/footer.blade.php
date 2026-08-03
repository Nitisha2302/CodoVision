<footer>
    <div class="footer-content">
        <div class="footer-about">
            <div class="logo">
                <div class="logo-icon">
                    <img src="{{ asset('images/logo.png') }}" alt="logo">
                </div>
                <div class="logo-text">
                    <h1>Trispark</h1>
                    <h5>Software Solutions</h5>
                </div>
            </div>
            <p>Building innovative digital solutions that transform businesses.</p>
            <div class="footer-social">
                <a href="https://www.linkedin.com/company/trisparksoftwaresolutions/" target="_blank" style="text-decoration: none; color: inherit;">
                    <div class="social-icon">in</div>
                </a>
                <div class="social-icon">git</div>
                <div class="social-icon">X</div>
            </div>
        </div>

        <div class="footer-links">
            <h4>Company</h4>
            <ul>
                <li><a href="{{ route('about') }}">About Us</a></li>
                <li><a href="{{ route('services') }}">Services</a></li>
                <li><a href="{{ route('projects') }}">Projects</a></li>
                <li><a href="{{ route('process') }}">Process</a></li>
                <li><a href="{{ route('technologies') }}">Technologies</a></li>
            </ul>
        </div>

        <div class="footer-links">
            <h4>Services</h4>
            <ul>
                <li><a href="{{ route('services') }}">Web Development</a></li>
                <li><a href="{{ route('services') }}">Mobile Apps</a></li>
                <li><a href="{{ route('services') }}">UI/UX Design</a></li>
                <li><a href="{{ route('services') }}">Automation</a></li>
                <li><a href="{{ route('contact') }}">Consulting</a></li>
            </ul>
        </div>

        <div class="footer-contact">
            <h4>Get in Touch</h4>
            <div class="contact-item">
                <span>✉</span>
                <a href="https://mail.google.com/mail/?view=cm&fs=1&to=trisparksoftwaresolutions@gmail.com" target="_blank" style="color: inherit; text-decoration: none;">
                    trisparksoftwaresolutions@gmail.com
                </a>
            </div>
            <div class="contact-item">
                <span>📞</span>
                <a href="tel:+917973776933" style="color: inherit; text-decoration: none;">
                    +91 79737-76933
                </a>
            </div>
            <div class="contact-item">
                <span>📍</span>
                Sector 71, Mohali, Punjab, India
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© 2026 Trispark Software Solutions. All rights reserved.</p>
    </div>
</footer>
