<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="@yield('meta_author', 'CodoVision')">
    <meta name="description" content="@yield('meta_description', 'CodoVision builds scalable mobile and web apps for startups and businesses.')">
    <meta name="keywords" content="@yield('meta_keywords', 'mobile app development, startup MVP development, marketplace app development, flutter app development, react native development, AI app integration')">
    <meta name="robots" content="index,follow">
    <meta name="googlebot" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
    <link rel="canonical" href="@yield('meta_canonical', url()->current())">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="@yield('og_site_name', 'CodoVision')">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', 'CodoVision')))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description', 'CodoVision builds scalable mobile and web apps for startups and businesses.')))">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/logo.png'))">
    <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('twitter_title', trim($__env->yieldContent('title', 'CodoVision')))">
    <meta name="twitter:description" content="@yield('twitter_description', trim($__env->yieldContent('meta_description', 'CosoVision builds scalable mobile and web apps for startups and businesses.')))">
    <meta name="twitter:image" content="@yield('twitter_image', asset('images/logo.png'))">
    <title>@yield('title', 'CodoVision')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('favicon_trispark.png') }}" type="image/x-icon">
    @yield('head_extras')
</head>
<body>
    <div class="starfield" id="starfield"></div>

    @include('partials.nav')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    @include('partials.floating-actions')

    <button id="chatbotToggle" class="chatbot-toggle" type="button">AI Chat</button>
    <section id="chatbotPanel" class="chatbot-panel" aria-hidden="true">
        <div class="chatbot-header">
            <h3>Project Assistant</h3>
            <button id="chatbotClose" type="button">×</button>
        </div>
        <div id="chatbotMessages" class="chatbot-messages"></div>
        <div id="chatbotActions" class="chatbot-actions"></div>
    </section>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const starfield = document.getElementById('starfield');
        if (starfield) {
            for (let i = 0; i < 100; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.animationDelay = Math.random() * 3 + 's';
                starfield.appendChild(star);
            }
        }

        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', function (e) {
                const selector = this.getAttribute('href');
                const target = document.querySelector(selector);

                if (!target) {
                    return;
                }

                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        });

        const faqItems = document.querySelectorAll('.faq-item');
        faqItems.forEach((item) => {
            const question = item.querySelector('.faq-question');
            if (!question) {
                return;
            }

            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');

                faqItems.forEach((otherItem) => {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });

                item.classList.toggle('active', !isActive);
            });
        });

        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');
        if (navToggle && navLinks) {
            navToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
            });
        }

        document.querySelectorAll('.js-whatsapp').forEach((button) => {
            button.addEventListener('click', () => {
                const phoneNumber = '917973776933';
                const message = encodeURIComponent("Hi, I’d like to schedule a call.");
                const whatsappURL = `https://wa.me/${phoneNumber}?text=${message}`;
                window.open(whatsappURL, '_blank');
            });
        });

        document.querySelectorAll('.js-view-projects').forEach((button) => {
            button.addEventListener('click', () => {
                const projectsSection = document.querySelector('#projects');

                if (projectsSection) {
                    projectsSection.scrollIntoView({ behavior: 'smooth' });
                    return;
                }

                window.location.href = "{{ route('projects') }}";
            });
        });

        const revealTargets = document.querySelectorAll(
            '.service-card, .project-card, .feature-card, .detail-card, .testimonial-card, .process-step, .animate-on-scroll, .trusted-item, .service-visual-card, .solution-card, .visual-story-card, .why-point, .about-showcase > *'
        );
        revealTargets.forEach((item) => item.classList.add('animate-on-scroll'));

        if ('IntersectionObserver' in window) {
            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.12,
                rootMargin: '0px 0px -40px 0px',
            });

            revealTargets.forEach((item) => revealObserver.observe(item));
        } else {
            revealTargets.forEach((item) => item.classList.add('is-visible'));
        }

        if (window.matchMedia('(pointer:fine)').matches) {
            document.querySelectorAll('.project-card, .service-card, .testimonial-card').forEach((card) => {
                card.addEventListener('mousemove', (event) => {
                    const rect = card.getBoundingClientRect();
                    const x = (event.clientX - rect.left) / rect.width;
                    const y = (event.clientY - rect.top) / rect.height;
                    const rotateY = (x - 0.5) * 8;
                    const rotateX = (0.5 - y) * 8;
                    card.style.transform = `translateY(-8px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                });

                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                });
            });
        }

        document.querySelectorAll('.js-select-package').forEach((button) => {
            button.addEventListener('click', () => {
                const packageId = button.getAttribute('data-package-id');
                const select = document.getElementById('packageSelect');
                const modeSelect = document.getElementById('packageModeForm');
                const bookingSection = document.getElementById('booking-form');
                if (select && packageId) {
                    select.value = packageId;
                }
                if (modeSelect) {
                    if (packageId === 'custom') {
                        modeSelect.value = 'custom';
                    } else if (packageId.endsWith('-basic') || packageId.endsWith('-advanced') || packageId.endsWith('-premium')) {
                        modeSelect.value = 'tier';
                    } else {
                        modeSelect.value = 'fixed';
                    }
                }
                if (bookingSection) {
                    bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        const technologyFilter = document.getElementById('technologyFilter');
        const packageMode = document.getElementById('packageMode');
        const packageCards = document.querySelectorAll('#packageCards .package-card');
        const packageProjectCards = document.querySelectorAll('#packageProjectCards .project-card');

        const applyPackageFilter = () => {
            if (!packageCards.length) return;
            const techValue = technologyFilter?.value || '';
            const modeValue = packageMode?.value || 'fixed';

            const showOnlyDefault = !techValue && modeValue === 'fixed';
            packageCards.forEach((card) => {
                const packageTech = card.getAttribute('data-tech') || '';
                const cardMode = card.getAttribute('data-mode') || 'fixed';
                const isDefault = card.getAttribute('data-default') === '1';
                const techMatch = !techValue || packageTech.includes(techValue);
                const modeMatch = modeValue === 'all' || modeValue === cardMode;
                const shouldShow = showOnlyDefault ? isDefault : (techMatch && modeMatch);
                card.style.display = shouldShow ? '' : 'none';
            });

            if (packageProjectCards.length) {
                packageProjectCards.forEach((card) => {
                    const cardTech = card.getAttribute('data-project-tech') || '';
                    const techMatch = !techValue || cardTech.includes(techValue);
                    card.style.display = techMatch ? '' : 'none';
                });
            }
        };

        technologyFilter?.addEventListener('change', applyPackageFilter);
        packageMode?.addEventListener('change', applyPackageFilter);
        applyPackageFilter();

        const projectTechnologyFilter = document.getElementById('projectTechnologyFilter');
        const projectCards = document.querySelectorAll('#projectCards .project-card');
        const applyProjectFilter = () => {
            if (!projectCards.length) return;
            const selectedTech = projectTechnologyFilter?.value || '';
            projectCards.forEach((card) => {
                const projectTech = card.getAttribute('data-project-tech') || '';
                const show = !selectedTech || projectTech.includes(selectedTech);
                card.style.display = show ? '' : 'none';
            });
        };
        projectTechnologyFilter?.addEventListener('change', applyProjectFilter);
        applyProjectFilter();

        const homeReviewSection = document.getElementById('home-review');
        const revealHomeReview = (scrollToSection = true) => {
            if (!homeReviewSection) return;
            homeReviewSection.hidden = false;
            homeReviewSection.classList.add('is-open');
            if (scrollToSection) {
                homeReviewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        if (homeReviewSection) {
            const hasReviewHash = window.location.hash === '#home-review';
            if (hasReviewHash) {
                revealHomeReview(false);
            }

            document.querySelectorAll('a[href$="#home-review"], a[href="#home-review"]').forEach((link) => {
                link.addEventListener('click', (event) => {
                    const url = new URL(link.href, window.location.origin);
                    if (url.pathname !== window.location.pathname) {
                        return;
                    }
                    event.preventDefault();
                    history.replaceState(null, '', '#home-review');
                    revealHomeReview(true);
                });
            });
        }

        const reviewGrid = document.getElementById('reviewGrid');
        const reviewGridHome = document.getElementById('reviewGridHome');
        const reviewForm = document.getElementById('reviewForm');
        const reviewMessage = document.getElementById('reviewMessage');

        const toAvatar = (review) => {
            if (!review.image) return '/images/client_1.png';
            if (review.image.startsWith('http')) return review.image;
            return review.image.startsWith('/') ? review.image : `/${review.image}`;
        };

        const buildReviewCard = (review) => `
            <article class="testimonial-card is-visible">
                <div class="stars">${'★'.repeat(Number(review.rating || 5))}</div>
                <p class="testimonial-text">"${review.review}"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="${toAvatar(review)}" alt="${review.name}">
                    </div>
                    <div class="author-info">
                        <h4>${review.name}</h4>
                        <p>${review.role} at ${review.company}</p>
                        <p class="review-meta">Project: ${review.project}</p>
                    </div>
                </div>
            </article>
        `;

        const renderReviews = (reviews) => {
            if (!Array.isArray(reviews) || reviews.length === 0) {
                return;
            }
            const html = reviews.slice(0, 6).map(buildReviewCard).join('');
            if (reviewGrid) reviewGrid.innerHTML = html;
            if (reviewGridHome) reviewGridHome.innerHTML = reviews.slice(0, 4).map(buildReviewCard).join('');
        };

        const loadReviews = async () => {
            try {
                const response = await fetch('{{ route('reviews.feed') }}', { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;
                const payload = await response.json();
                if (Array.isArray(payload.reviews)) {
                    renderReviews(payload.reviews);
                }
            } catch (error) {
                console.error(error);
            }
        };

        if (reviewForm) {
            reviewForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                reviewMessage.textContent = 'Submitting your review...';
                const formData = new FormData(reviewForm);
                try {
                    const response = await fetch('{{ route('reviews.submit') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    const payload = await response.json();
                    if (!response.ok) {
                        reviewMessage.textContent = payload.message || 'Unable to submit review.';
                        return;
                    }
                    reviewMessage.textContent = payload.message;
                    reviewForm.reset();
                    await loadReviews();
                } catch (error) {
                    reviewMessage.textContent = 'Unable to submit review at the moment.';
                }
            });
        }

        if (reviewGrid || reviewGridHome) {
            loadReviews();
            setInterval(loadReviews, 15000);
        }

        const chatbotToggle = document.getElementById('chatbotToggle');
        const chatbotPanel = document.getElementById('chatbotPanel');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotMessages = document.getElementById('chatbotMessages');
        const chatbotActions = document.getElementById('chatbotActions');

        const chatbotState = {
            step: 0,
            answers: {},
            transcript: [],
            estimateShown: false,
        };

        const chatbotFlow = [
            { key: 'project_type', question: 'What type of project do you want to create? (Web App, Mobile App, Ecommerce, AI, Dashboard)' },
            { key: 'requirements', question: 'Briefly describe your key requirements.', multiline: true },
            { key: 'preferred_technology', question: 'Any preferred technology stack? (Laravel, React, Flutter, etc.)' },
            { key: 'budget_range', question: 'What is your expected budget range? (e.g. $2k-$5k)' },
            { key: 'timeline', question: 'Target timeline for launch?' },
            { key: 'name', question: 'Please share your full name.' },
            { key: 'email', question: 'What is your email address?' },
            { key: 'mobile', question: 'Your mobile number?' },
            { key: 'country', question: 'Which country are you from?' },
        ];

        const appendMessage = (text, type = 'bot') => {
            const node = document.createElement('div');
            node.className = `chat-msg ${type}`;
            node.textContent = text;
            chatbotMessages.appendChild(node);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            chatbotState.transcript.push(`${type.toUpperCase()}: ${text}`);
        };

        const showInput = () => {
            chatbotActions.innerHTML = '';
            if (chatbotState.step >= chatbotFlow.length) {
                if (!chatbotState.estimateShown) {
                    const estimate = (() => {
                        const budgetText = (chatbotState.answers.budget_range || '').toLowerCase();
                        if (budgetText.includes('1') || budgetText.includes('2') || budgetText.includes('3')) return 'Starter Web Package';
                        if (budgetText.includes('4') || budgetText.includes('5') || budgetText.includes('6')) return 'Growth App Package';
                        return 'Scale Suite / Custom Package';
                    })();
                    appendMessage(`Based on your inputs, recommended package: ${estimate}.`, 'bot');
                    chatbotState.estimateShown = true;
                }
                const submitButton = document.createElement('button');
                submitButton.type = 'button';
                submitButton.className = 'btn-primary';
                submitButton.textContent = 'Submit Details';
                submitButton.addEventListener('click', submitChatbotLead);
                chatbotActions.appendChild(submitButton);
                return;
            }

            const current = chatbotFlow[chatbotState.step];
            const input = current.multiline ? document.createElement('textarea') : document.createElement('input');
            if (!current.multiline) {
                input.type = current.key === 'email' ? 'email' : 'text';
            }
            input.placeholder = 'Type your answer';
            input.className = 'chat-input';

            const sendButton = document.createElement('button');
            sendButton.type = 'button';
            sendButton.className = 'btn-primary';
            sendButton.textContent = 'Send';
            sendButton.addEventListener('click', () => {
                const value = input.value.trim();
                if (!value) return;
                chatbotState.answers[current.key] = value;
                appendMessage(value, 'user');
                chatbotState.step += 1;
                if (chatbotState.step < chatbotFlow.length) {
                    appendMessage(chatbotFlow[chatbotState.step].question, 'bot');
                } else {
                    appendMessage('Great. Click "Submit Details" and I will send your project request with full chat transcript.', 'bot');
                }
                showInput();
            });

            chatbotActions.appendChild(input);
            chatbotActions.appendChild(sendButton);
            input.focus();
        };

        const openChatbot = () => {
            chatbotPanel.classList.add('open');
            chatbotPanel.setAttribute('aria-hidden', 'false');
            if (!chatbotMessages.childElementCount) {
                appendMessage('Hello. I will collect your project details and send them to our team.', 'bot');
                appendMessage(chatbotFlow[0].question, 'bot');
                showInput();
            }
        };

        const closeChatbot = () => {
            chatbotPanel.classList.remove('open');
            chatbotPanel.setAttribute('aria-hidden', 'true');
        };

        const submitChatbotLead = async () => {
            const payload = {
                ...chatbotState.answers,
                chat_transcript: chatbotState.transcript.join('\\n'),
            };
            try {
                const response = await fetch('{{ route('chatbot.submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();
                if (!response.ok) {
                    appendMessage(data.message || 'Unable to submit details. Please try again.', 'bot');
                    return;
                }
                appendMessage(data.message, 'bot');
                chatbotActions.innerHTML = '<button type=\"button\" class=\"btn-secondary\" id=\"chatbotReset\">Start New Chat</button>';
                document.getElementById('chatbotReset')?.addEventListener('click', () => {
                    chatbotState.step = 0;
                    chatbotState.answers = {};
                    chatbotState.transcript = [];
                    chatbotState.estimateShown = false;
                    chatbotMessages.innerHTML = '';
                    appendMessage('Hello. I will collect your project details and send them to our team.', 'bot');
                    appendMessage(chatbotFlow[0].question, 'bot');
                    showInput();
                });
            } catch (error) {
                appendMessage('Unable to submit details. Please try again.', 'bot');
            }
        };

        chatbotToggle?.addEventListener('click', openChatbot);
        chatbotClose?.addEventListener('click', closeChatbot);

        /* Hero carousel */
        const heroSlides = document.querySelectorAll('.hero-slide');
        const heroDots = document.querySelectorAll('.hero-dot');
        let heroIndex = 0;
        let heroTimer;

        const setHeroSlide = (index) => {
            if (!heroSlides.length) return;
            heroIndex = (index + heroSlides.length) % heroSlides.length;
            heroSlides.forEach((slide, i) => slide.classList.toggle('is-active', i === heroIndex));
            heroDots.forEach((dot, i) => dot.classList.toggle('is-active', i === heroIndex));
        };

        const startHeroAutoplay = () => {
            clearInterval(heroTimer);
            heroTimer = setInterval(() => setHeroSlide(heroIndex + 1), 6000);
        };

        document.getElementById('heroPrev')?.addEventListener('click', () => {
            setHeroSlide(heroIndex - 1);
            startHeroAutoplay();
        });
        document.getElementById('heroNext')?.addEventListener('click', () => {
            setHeroSlide(heroIndex + 1);
            startHeroAutoplay();
        });
        heroDots.forEach((dot) => {
            dot.addEventListener('click', () => {
                setHeroSlide(Number(dot.getAttribute('data-slide')));
                startHeroAutoplay();
            });
        });
        if (heroSlides.length) {
            startHeroAutoplay();
        }

        /* Animated counters */
        const counters = document.querySelectorAll('.counter');
        const runCounter = (el) => {
            const target = Number(el.getAttribute('data-target') || 0);
            const duration = 1400;
            const start = performance.now();
            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const value = Math.floor(progress * target);
                el.textContent = String(value);
                if (progress < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        };

        if (counters.length && 'IntersectionObserver' in window) {
            const counterObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    runCounter(entry.target);
                    observer.unobserve(entry.target);
                });
            }, { threshold: 0.4 });
            counters.forEach((counter) => counterObserver.observe(counter));
        }

        /* Process step tabs */
        const processBtns = document.querySelectorAll('.process-step-btn');
        const processPanels = document.querySelectorAll('.process-detail-card');
        processBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                const index = btn.getAttribute('data-process');
                processBtns.forEach((b) => b.classList.toggle('is-active', b === btn));
                processPanels.forEach((panel) => {
                    panel.classList.toggle('is-active', panel.getAttribute('data-process-panel') === index);
                });
            });
        });

        /* Testimonial / review carousel */
        const testimonialSlides = document.querySelectorAll('.testimonial-slide');
        let testimonialIndex = 0;
        let testimonialTimer;

        const setTestimonialSlide = (index) => {
            if (!testimonialSlides.length) return;
            testimonialIndex = (index + testimonialSlides.length) % testimonialSlides.length;
            testimonialSlides.forEach((slide, i) => slide.classList.toggle('is-active', i === testimonialIndex));
        };

        const startTestimonialAutoplay = () => {
            clearInterval(testimonialTimer);
            testimonialTimer = setInterval(() => setTestimonialSlide(testimonialIndex + 1), 7000);
        };

        document.getElementById('testimonialPrev')?.addEventListener('click', () => {
            setTestimonialSlide(testimonialIndex - 1);
            startTestimonialAutoplay();
        });
        document.getElementById('testimonialNext')?.addEventListener('click', () => {
            setTestimonialSlide(testimonialIndex + 1);
            startTestimonialAutoplay();
        });
        if (testimonialSlides.length) {
            setTestimonialSlide(0);
            startTestimonialAutoplay();
        }

        /* Floating actions: hide hint after scroll */
        const floatingHint = document.querySelector('.floating-hint');
        if (floatingHint) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 400) {
                    floatingHint.classList.add('is-hidden');
                } else {
                    floatingHint.classList.remove('is-hidden');
                }
            }, { passive: true });
        }
    </script>
</body>
</html>
