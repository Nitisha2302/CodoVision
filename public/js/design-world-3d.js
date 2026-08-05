function initXrWorld3d() {
    const mount = document.getElementById('xrWorld3d');
    if (!mount || typeof THREE === 'undefined') return;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReduced) return;

    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(52, 1, 0.1, 100);
    camera.position.z = 4.2;

    const renderer = new THREE.WebGLRenderer({ canvas: mount, alpha: true, antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.setClearColor(0x000000, 0);

    const group = new THREE.Group();
    scene.add(group);

    const coreGeo = new THREE.IcosahedronGeometry(1.35, 3);
    const coreMat = new THREE.MeshBasicMaterial({
        color: 0x8b5cf6,
        wireframe: true,
        transparent: true,
        opacity: 0.65,
    });
    const core = new THREE.Mesh(coreGeo, coreMat);
    group.add(core);

    const ringGeo = new THREE.TorusGeometry(2.1, 0.025, 16, 100);
    const ringMat = new THREE.MeshBasicMaterial({ color: 0x6366f1, transparent: true, opacity: 0.7 });
    const ring1 = new THREE.Mesh(ringGeo, ringMat);
    ring1.rotation.x = Math.PI * 0.42;
    group.add(ring1);

    const ring2 = ring1.clone();
    ring2.rotation.x = -Math.PI * 0.35;
    ring2.rotation.y = Math.PI * 0.5;
    ring2.scale.set(1.15, 1.15, 1.15);
    group.add(ring2);

    const pts = [];
    for (let i = 0; i < 420; i++) {
        const r = 1.8 + Math.random() * 1.4;
        const theta = Math.random() * Math.PI * 2;
        const phi = Math.acos(2 * Math.random() - 1);
        pts.push(
            r * Math.sin(phi) * Math.cos(theta),
            r * Math.sin(phi) * Math.sin(theta),
            r * Math.cos(phi)
        );
    }
    const starGeo = new THREE.BufferGeometry();
    starGeo.setAttribute('position', new THREE.Float32BufferAttribute(pts, 3));
    const stars = new THREE.Points(
        starGeo,
        new THREE.PointsMaterial({ color: 0xa78bfa, size: 0.028, transparent: true, opacity: 0.85 })
    );
    group.add(stars);

    const resize = () => {
        const parent = mount.parentElement;
        if (!parent) return;
        const w = parent.clientWidth;
        const h = parent.clientHeight;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };

    resize();
    window.addEventListener('resize', resize);

    let mx = 0;
    let my = 0;
    const hero = document.querySelector('.xr-hero');
    hero?.addEventListener('mousemove', (e) => {
        const rect = hero.getBoundingClientRect();
        mx = ((e.clientX - rect.left) / rect.width - 0.5) * 0.6;
        my = ((e.clientY - rect.top) / rect.height - 0.5) * 0.4;
    });

    const animate = () => {
        requestAnimationFrame(animate);
        const t = performance.now() * 0.00035;
        group.rotation.y = t + mx;
        group.rotation.x = my * 0.5;
        core.rotation.y = -t * 1.4;
        ring1.rotation.z = t * 0.8;
        ring2.rotation.z = -t * 0.6;
        stars.rotation.y = t * 0.2;
        renderer.render(scene, camera);
    };

    animate();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initXrWorld3d);
} else {
    initXrWorld3d();
}
