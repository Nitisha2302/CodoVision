(function () {
    const root = document.getElementById('design-xr');
    if (!root) return;

    document.body.classList.add('design-page');

    /* Page scroll progress (Vaulk-style) */
    const pageProgress = document.getElementById('designScrollProgress');
    const updatePageProgress = () => {
        if (!pageProgress) return;
        const doc = document.documentElement;
        const max = doc.scrollHeight - doc.clientHeight;
        const pct = max > 0 ? (window.scrollY / max) * 100 : 0;
        pageProgress.style.width = `${pct}%`;
    };
    window.addEventListener('scroll', updatePageProgress, { passive: true });
    updatePageProgress();

    const setActive = (buttons, activeBtn) => {
        buttons.forEach((btn) => {
            const on = btn === activeBtn;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-selected', on ? 'true' : 'false');
        });
    };

    /* —— Video engine: reliable autoplay —— */
    const allVideos = root.querySelectorAll('video');
    let mediaStarted = false;

    const tryPlay = (video) => {
        if (!video) return;
        video.muted = true;
        video.setAttribute('playsinline', '');
        video.setAttribute('webkit-playsinline', '');
        const playPromise = video.play();
        if (playPromise?.then) {
            playPromise
                .then(() => {
                    video.classList.remove('is-paused');
                })
                .catch(() => {
                    video.classList.add('is-paused');
                });
        }
    };

    const startAllMedia = () => {
        if (mediaStarted) return;
        mediaStarted = true;
        allVideos.forEach(tryPlay);
        document.getElementById('cinemaPlayBtn')?.classList.add('is-hidden');
    };

    document.getElementById('xrPlayMedia')?.addEventListener('click', startAllMedia);
    document.getElementById('cinemaPlayBtn')?.addEventListener('click', startAllMedia);

    document.addEventListener(
        'click',
        () => startAllMedia(),
        { once: true, passive: true }
    );

    if ('IntersectionObserver' in window) {
        const mediaObs = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    const v = entry.target;
                    if (entry.isIntersecting) {
                        if (mediaStarted || v.classList.contains('xr-hero__stage-video')) {
                            tryPlay(v);
                        }
                    } else if (!v.classList.contains('xr-hero__stage-video')) {
                        v.pause();
                    }
                });
            },
            { threshold: 0.15 }
        );
        allVideos.forEach((v) => mediaObs.observe(v));
    }

    setTimeout(startAllMedia, 400);

    /* Hero stage — 3D tilt on mouse */
    const heroStage = document.getElementById('heroStage');
    const heroFrame = heroStage?.querySelector('.xr-hero__stage-frame');
    const heroSection = root.querySelector('.xr-hero--mega');

    if (heroFrame && heroSection && window.matchMedia('(pointer: fine)').matches) {
        heroSection.addEventListener('mousemove', (e) => {
            const rect = heroStage.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            heroFrame.style.setProperty('--stage-ry', `${x * 8}deg`);
            heroFrame.style.setProperty('--stage-rx', `${2 - y * 6}deg`);
        });
        heroSection.addEventListener('mouseleave', () => {
            heroFrame.style.setProperty('--stage-ry', '0deg');
            heroFrame.style.setProperty('--stage-rx', '2deg');
        });
    }

    /* Hero stage video — play as soon as visible */
    const stageVideo = root.querySelector('.xr-hero__stage-video');
    if (stageVideo) {
        tryPlay(stageVideo);
    }

    /* Particles */
    const canvas = document.getElementById('xrParticles');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        let w = 0;
        let h = 0;
        let mouse = { x: 0.5, y: 0.5 };

        const resize = () => {
            const hero = root.querySelector('.xr-hero');
            if (!hero) return;
            w = canvas.width = hero.offsetWidth;
            h = canvas.height = hero.offsetHeight;
        };

        const initParticles = () => {
            const count = Math.min(140, Math.floor((w * h) / 10000));
            particles = Array.from({ length: count }, () => ({
                x: Math.random() * w,
                y: Math.random() * h,
                z: Math.random(),
                vx: (Math.random() - 0.5) * 0.4,
                vy: (Math.random() - 0.5) * 0.4,
                r: 1 + Math.random() * 2.5,
            }));
        };

        const draw = () => {
            ctx.clearRect(0, 0, w, h);
            particles.forEach((p) => {
                p.x += p.vx + (mouse.x - 0.5) * p.z * 0.5;
                p.y += p.vy + (mouse.y - 0.5) * p.z * 0.5;
                if (p.x < 0) p.x = w;
                if (p.x > w) p.x = 0;
                if (p.y < 0) p.y = h;
                if (p.y > h) p.y = 0;
                const alpha = 0.12 + p.z * 0.5;
                const size = p.r * (0.5 + p.z);
                ctx.fillStyle = `rgba(167, 139, 250, ${alpha})`;
                ctx.beginPath();
                ctx.arc(p.x, p.y, size, 0, Math.PI * 2);
                ctx.fill();
            });
            requestAnimationFrame(draw);
        };

        resize();
        initParticles();
        draw();
        window.addEventListener('resize', () => {
            resize();
            initParticles();
        });

        root.querySelector('.xr-hero')?.addEventListener('mousemove', (e) => {
            const rect = canvas.getBoundingClientRect();
            mouse.x = (e.clientX - rect.left) / rect.width;
            mouse.y = (e.clientY - rect.top) / rect.height;
        });
    }

    /* Cinema scroll sections */
    const featureTabs = root.querySelectorAll('#featureTabs .xr-tab');
    const cinemaLayers = root.querySelectorAll('.xr-cinema__layer');
    const cinemaSteps = root.querySelectorAll('.xr-cinema__step');
    const progressFill = document.getElementById('scrollStoryProgress');
    const cinemaCounter = document.getElementById('cinemaCounter');
    const cinemaScreen = document.getElementById('cinemaScreen');
    const panelOrder = ['experience', 'workspace', 'explore', 'access'];
    let cinemaLocked = false;

    const activateCinema = (panelId) => {
        const index = panelOrder.indexOf(panelId);
        if (index < 0) return;

        cinemaLayers.forEach((layer) => {
            const active = layer.getAttribute('data-panel') === panelId;
            layer.classList.toggle('is-active', active);
            const video = layer.querySelector('video');
            if (active) {
                tryPlay(video);
            } else {
                video?.pause();
            }
        });

        const tab = root.querySelector(`#featureTabs .xr-tab[data-panel="${panelId}"]`);
        if (tab) setActive(featureTabs, tab);

        if (progressFill) {
            progressFill.style.width = `${((index + 1) / panelOrder.length) * 100}%`;
        }
        if (cinemaCounter) {
            cinemaCounter.textContent = `${String(index + 1).padStart(2, '0')} / 04`;
        }
    };

    const updateCinemaScale = () => {
        if (!cinemaScreen) return;
        const rect = cinemaScreen.getBoundingClientRect();
        const vh = window.innerHeight;
        const center = vh * 0.5;
        const elCenter = rect.top + rect.height * 0.5;
        const dist = Math.abs(elCenter - center) / vh;
        const scale = Math.max(0.9, 1 - dist * 0.12);
        cinemaScreen.style.setProperty('--cinema-scale', scale.toFixed(3));
    };

    window.addEventListener('scroll', () => {
        requestAnimationFrame(updateCinemaScale);
    }, { passive: true });
    updateCinemaScale();

    featureTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const id = tab.getAttribute('data-panel');
            const step = root.querySelector(`.xr-cinema__step[data-panel="${id}"]`);
            if (!step) return;
            cinemaLocked = true;
            activateCinema(id);
            step.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                cinemaLocked = false;
            }, 800);
        });
    });

    if (cinemaSteps.length && 'IntersectionObserver' in window) {
        const stepObs = new IntersectionObserver(
            (entries) => {
                if (cinemaLocked) return;
                let best = null;
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    if (!best || entry.intersectionRatio > best.ratio) {
                        best = { id: entry.target.getAttribute('data-panel'), ratio: entry.intersectionRatio };
                    }
                });
                if (best?.id) activateCinema(best.id);
            },
            { threshold: [0.35, 0.5, 0.65], rootMargin: '-30% 0px -30% 0px' }
        );
        cinemaSteps.forEach((step) => stepObs.observe(step));
        activateCinema('experience');
    }

    /* Discipline tabs */
    const disciplineTabs = root.querySelectorAll('#disciplineTabs .xr-pill');
    const disciplinePanels = root.querySelectorAll('.xr-discipline-panel');

    disciplineTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const id = tab.getAttribute('data-discipline');
            setActive(disciplineTabs, tab);
            disciplinePanels.forEach((panel) => {
                const active = panel.getAttribute('data-discipline') === id;
                panel.classList.toggle('is-active', active);
                if (active) {
                    panel.classList.add('xr-discipline-enter');
                    setTimeout(() => panel.classList.remove('xr-discipline-enter'), 600);
                }
            });
        });
    });

    /* Category pills — swap stage poster */
    const stageVideoEl = root.querySelector('.xr-hero__stage-video');
    const categoryPills = root.querySelectorAll('#categoryPills .xr-pill');

    categoryPills.forEach((pill) => {
        pill.addEventListener('click', () => {
            const src = pill.getAttribute('data-img');
            setActive(categoryPills, pill);
            if (stageVideoEl && src) {
                stageVideoEl.poster = src;
            }
        });
    });

    /* Scroll reveal */
    root.querySelectorAll('.animate-on-scroll').forEach((el) => {
        if (!('IntersectionObserver' in window)) {
            el.classList.add('is-visible');
            return;
        }
        const obs = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                });
            },
            { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
        );
        obs.observe(el);
    });

    root.querySelector('.xr-hero__title')?.classList.add('xr-title-ready');
})();
