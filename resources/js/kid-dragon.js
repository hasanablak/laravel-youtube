/*
 * Minik ejder rehber (ana sayfa).
 *
 * Amaç: okuma bilmeyen çocuğa "aşağıda daha çok kanal var" demek.
 *  - Açılışta aşağıdan uçarak gelir, başıyla fareyi takip eder.
 *  - Çocuk 4 sn kaydırmazsa aşağı dalar ve ok balonu gösterir (en fazla 3 kez).
 *  - Kaydırırken kanat çırpıp sevinir (^^ gözler).
 *  - En alta inince takla atar, konfeti patlatır, el sallayıp uçup gider;
 *    yukarı çıkılınca geri gelir.
 *  - Tıklanınca zıplar ve konuşur.
 *
 * Markup: resources/views/components/kid-dragon.blade.php (#kid-dragon-*).
 * Karakter tamamen Three.js primitiflerinden kodla çizilir (dış model dosyası yok).
 */
import {
    WebGLRenderer, Scene, PerspectiveCamera, HemisphereLight, DirectionalLight, DataTexture, RedFormat,
    NearestFilter, MeshToonMaterial, MeshBasicMaterial, BackSide, DoubleSide, SphereGeometry, ConeGeometry,
    TorusGeometry, ExtrudeGeometry, Shape, Mesh, Group,
} from 'three';

const wrap = document.getElementById('kid-dragon-wrap');
const canvas = document.getElementById('kid-dragon');
const hint = document.getElementById('kid-dragon-hint');

if (wrap && canvas && hint) {
    start();
}

function start() {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

    let renderer;
    try {
        renderer = new WebGLRenderer({ canvas, alpha: true, antialias: true });
    } catch (e) {
        wrap.remove(); // WebGL yoksa ejder hiç görünmesin
        return;
    }
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));

    const scene = new Scene();
    const camera = new PerspectiveCamera(34, 1, 0.1, 100);
    camera.position.set(0, 0.9, 10.5);
    camera.lookAt(0, 0.55, 0);

    // three r155+ fiziksel ışık birimleri: eski görünüm için ~π katı
    scene.add(new HemisphereLight(0xffffff, 0xffc9dc, 2.6));
    const sun = new DirectionalLight(0xffffff, 2.3);
    sun.position.set(3, 5, 5);
    scene.add(sun);

    // Çizgi film boyaması: 3 tonlu gölge + koyu kontur
    const grad = new DataTexture(new Uint8Array([110, 190, 255]), 3, 1, RedFormat);
    grad.minFilter = grad.magFilter = NearestFilter;
    grad.needsUpdate = true;
    const toon = (color, extra = {}) => new MeshToonMaterial({ color, gradientMap: grad, ...extra });
    const outlineMat = new MeshBasicMaterial({ color: 0x6b4e8a, side: BackSide });
    const sphere = new SphereGeometry(1, 32, 24);

    const part = (parent, geo, mat, pos, scale, outline = 0) => {
        const m = new Mesh(geo, mat);
        m.position.set(...pos);
        if (scale) m.scale.set(...scale);
        if (outline) {
            const o = new Mesh(geo, outlineMat);
            o.scale.setScalar(1 + outline);
            m.add(o);
        }
        parent.add(m);
        return m;
    };

    const C = { body: 0xb9a4ff, belly: 0xffe9bf, snout: 0xd3c6ff, wing: 0xffb3d1, horn: 0xffd27a, spike: 0xff9ec4, cheek: 0xff8fb1, dark: 0x3b2f4a };

    const dragon = new Group();
    scene.add(dragon);
    const rig = new Group(); // zıplama / esneme
    dragon.add(rig);

    // Gövde
    part(rig, sphere, toon(C.body), [0, 0, 0], [1, 1.1, 0.95], 0.045);
    part(rig, sphere, toon(C.belly), [0, -0.12, 0.5], [0.68, 0.8, 0.5]);

    // Sırt dikenleri
    const cone = new ConeGeometry(0.16, 0.38, 16);
    [[0.75, -0.55], [0.3, -0.85], [-0.2, -0.88]].forEach(([y, z], i) => {
        const s = part(rig, cone, toon(C.spike), [0, y, z], null, 0.08);
        s.rotation.x = -0.9 - i * 0.25;
    });

    // Kollar ve ayaklar
    part(rig, sphere, toon(C.body), [-0.62, 0.15, 0.55], [0.2, 0.26, 0.2], 0.08);
    const armWave = part(rig, sphere, toon(C.body), [0.62, 0.15, 0.55], [0.2, 0.26, 0.2], 0.08);
    part(rig, sphere, toon(C.body), [-0.45, -1.02, 0.25], [0.32, 0.2, 0.38], 0.07);
    part(rig, sphere, toon(C.body), [0.45, -1.02, 0.25], [0.32, 0.2, 0.38], 0.07);

    // Kanatlar
    const ws = new Shape();
    ws.moveTo(0, 0);
    ws.bezierCurveTo(0.35, 0.9, 1.25, 1.25, 1.75, 0.95);
    ws.bezierCurveTo(1.45, 0.75, 1.5, 0.5, 1.8, 0.32);
    ws.bezierCurveTo(1.4, 0.2, 1.4, -0.05, 1.6, -0.25);
    ws.bezierCurveTo(1.05, -0.22, 0.6, -0.35, 0, 0);
    const wingGeo = new ExtrudeGeometry(ws, { depth: 0.05, bevelEnabled: true, bevelThickness: 0.03, bevelSize: 0.03, bevelSegments: 2, curveSegments: 18 });
    const wingMat = toon(C.wing, { side: DoubleSide });
    const makeWing = (side) => {
        const pivot = new Group();
        pivot.position.set(0.5 * side, 0.65, -0.5);
        const m = new Mesh(wingGeo, wingMat);
        m.scale.set(side, 1, 1);
        const o = new Mesh(wingGeo, outlineMat);
        o.scale.setScalar(1.04);
        m.add(o);
        pivot.add(m);
        rig.add(pivot);
        return pivot;
    };
    const wingL = makeWing(-1);
    const wingR = makeWing(1);

    // Kuyruk
    const tail = [];
    for (let i = 0; i < 7; i++) {
        const r = 0.3 * (1 - i * 0.1);
        tail.push(part(rig, sphere, toon(C.body), [0, 0, 0], [r, r, r], 0.09));
    }
    const tailTip = part(rig, new ConeGeometry(0.22, 0.42, 4), toon(C.spike), [0, 0, 0], null, 0.08);

    // Kafa
    const head = new Group();
    head.position.set(0, 1.42, 0.08);
    rig.add(head);
    part(head, sphere, toon(C.body), [0, 0, 0], [0.88, 0.82, 0.82], 0.045);
    part(head, sphere, toon(C.snout), [0, -0.22, 0.62], [0.55, 0.38, 0.42], 0.06);
    part(head, sphere, toon(C.dark), [-0.15, -0.14, 1.0], [0.05, 0.05, 0.04]);
    part(head, sphere, toon(C.dark), [0.15, -0.14, 1.0], [0.05, 0.05, 0.04]);
    part(head, sphere, toon(C.cheek), [-0.56, -0.2, 0.52], [0.15, 0.1, 0.06]);
    part(head, sphere, toon(C.cheek), [0.56, -0.2, 0.52], [0.15, 0.1, 0.06]);
    [-1, 1].forEach((s) => {
        const h = part(head, new ConeGeometry(0.13, 0.48, 16), toon(C.horn), [0.34 * s, 0.72, -0.1], null, 0.1);
        h.rotation.z = -0.35 * s;
        h.rotation.x = -0.25;
        const f = part(head, new ConeGeometry(0.14, 0.4, 16), toon(C.spike), [0.82 * s, 0.1, -0.1], null, 0.1);
        f.rotation.z = -1.5 * s;
    });

    // Gözler
    const eyes = new Group();
    head.add(eyes);
    const pupils = [];
    [-1, 1].forEach((s) => {
        part(eyes, sphere, new MeshBasicMaterial({ color: 0xffffff }), [0.3 * s, 0.12, 0.66], [0.25, 0.29, 0.14], 0.12);
        const p = new Group();
        p.position.set(0.3 * s, 0.1, 0.78);
        part(p, sphere, new MeshBasicMaterial({ color: C.dark }), [0, 0, 0], [0.15, 0.18, 0.06]);
        part(p, sphere, new MeshBasicMaterial({ color: 0xffffff }), [0.05, 0.07, 0.05], [0.05, 0.05, 0.03]);
        eyes.add(p);
        pupils.push(p);
    });

    // Mutlu gözler (^^)
    const happyEyes = new Group();
    head.add(happyEyes);
    const arcGeo = new TorusGeometry(0.17, 0.045, 10, 20, Math.PI);
    [-1, 1].forEach((s) => {
        const a = new Mesh(arcGeo, new MeshBasicMaterial({ color: C.dark }));
        a.position.set(0.3 * s, 0.06, 0.74);
        happyEyes.add(a);
    });
    happyEyes.visible = false;

    // Ağız
    const mouth = new Mesh(new TorusGeometry(0.12, 0.035, 8, 16, Math.PI), new MeshBasicMaterial({ color: C.dark }));
    mouth.position.set(0, -0.36, 0.98);
    mouth.rotation.z = Math.PI;
    head.add(mouth);

    // Sol alt köşede durduğu için sayfaya (sağa) dönük
    const BASE_YAW = 0.38;
    dragon.rotation.y = BASE_YAW;

    /* ---------- Boyut ---------- */
    const resize = () => {
        const w = canvas.clientWidth;
        const h = canvas.clientHeight;
        if (!w || !h) return;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    window.addEventListener('resize', resize);
    resize();

    /* ---------- Durum ---------- */
    const now = () => performance.now();
    const scrollRoom = () => document.documentElement.scrollHeight - window.innerHeight;
    const atBottom = () => scrollRoom() > 40 && window.scrollY >= scrollRoom() - 40;

    let state = 'intro';
    let stateAt = now();
    const pageAt = now();
    let everScrolled = window.scrollY > 40;
    let lastScrollAt = 0;
    let lastHintAt = 0;
    let hintCount = 0;
    let celebrated = false;
    let scrollVel = 0;
    let lastScrollY = window.scrollY;
    let hopAt = -10000;
    let wrapX = 0;
    let wrapY = 0;
    let wrapOpacity = 1;
    let spin = 0;
    const pointer = { x: 0, y: 0, has: false };
    const setState = (s) => { state = s; stateAt = now(); };

    const say = (text) => {
        try {
            const synth = window.speechSynthesis;
            if (!synth) return;
            synth.cancel();
            const u = new SpeechSynthesisUtterance(text);
            u.lang = 'tr-TR';
            const v = synth.getVoices().find((x) => x.lang === 'tr-TR');
            if (v) u.voice = v;
            u.pitch = 1.35;
            synth.speak(u);
        } catch (e) { /* sesli okuma yoksa sessiz kal */ }
    };

    const confetti = () => {
        if (reduced) return;
        const r = canvas.getBoundingClientRect();
        const colors = ['#FF8FB1', '#B9A4FF', '#FFD27A', '#7FD8B0', '#7CC4FF'];
        for (let i = 0; i < 28; i++) {
            const el = document.createElement('span');
            el.className = 'kid-dragon-confetti';
            el.style.left = `${r.left + r.width * 0.5}px`;
            el.style.top = `${r.top + r.height * 0.35}px`;
            el.style.background = colors[i % colors.length];
            el.style.setProperty('--dx', `${(Math.random() - 0.35) * 320}px`);
            el.style.setProperty('--dy', `${(Math.random() - 0.7) * 300}px`);
            el.style.setProperty('--r', `${Math.random() * 720 - 360}deg`);
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 1400);
        }
    };

    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        scrollVel = y - lastScrollY;
        lastScrollY = y;
        lastScrollAt = now();
        if (y > 40) everScrolled = true;

        if (atBottom()) {
            if (!celebrated && state !== 'celebrate') {
                celebrated = true;
                setState('celebrate');
                confetti();
                say('Yaşasın! Hepsini gördün!');
            }
        } else if (state === 'gone' && y < scrollRoom() - 300) {
            celebrated = false;
            setState('return');
        } else if ((state === 'idle' || state === 'hint') && scrollVel !== 0) {
            setState('follow');
        }
    }, { passive: true });

    window.addEventListener('mousemove', (e) => {
        pointer.x = e.clientX;
        pointer.y = e.clientY;
        pointer.has = true;
    }, { passive: true });

    const poke = () => {
        hopAt = now();
        say(atBottom() ? 'Hepsini gördün, aferin!' : 'Aşağıda daha çok kanal var!');
        if (!everScrolled && state === 'idle') setState('hint');
    };
    canvas.addEventListener('click', poke);
    canvas.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            poke();
        }
    });

    /* ---------- Döngü ---------- */
    const ease = (t) => 1 - Math.pow(1 - t, 3);
    const easeBack = (t) => { const c = 1.7; return 1 + (c + 1) * Math.pow(t - 1, 3) + c * Math.pow(t - 1, 2); };
    const clamp = (v, a, b) => Math.max(a, Math.min(b, v));

    const frame = () => {
        const t = now();
        const since = (t - stateAt) / 1000;
        const time = t / 1000;

        let flapSpeed = 7;
        let flapAmp = 0.45;
        let tiltX = 0;
        let tiltZ = 0;
        let targetX = 0;
        let targetY = 0;
        let opacity = 1;
        let happy = false;
        let showHint = false;

        if (state === 'intro' || state === 'return') {
            const p = clamp(since / 1.3, 0, 1);
            targetY = (1 - easeBack(p)) * 360;
            flapSpeed = 14;
            if (p >= 1) setState('idle');
        } else if (state === 'idle') {
            // Kaydırılacak yer yoksa ısrar etme
            if (!everScrolled && scrollRoom() > 40 && hintCount < 3 && t - pageAt > 4000 && t - lastHintAt > 3500) setState('hint');
        } else if (state === 'hint') {
            const p = clamp(since / 2.2, 0, 1);
            showHint = true;
            if (!reduced) {
                targetY = Math.abs(Math.sin(p * Math.PI * 2)) * 58;
                tiltX = Math.sin(p * Math.PI * 2) * 0.35 + 0.25;
            }
            flapSpeed = 11;
            if (p >= 1) {
                hintCount++;
                lastHintAt = t;
                setState('idle');
            }
        } else if (state === 'follow') {
            happy = true;
            flapSpeed = 16;
            flapAmp = 0.6;
            tiltX = clamp(scrollVel * 0.02, -0.4, 0.4);
            tiltZ = Math.sin(time * 6) * 0.08;
            if (t - lastScrollAt > 350) {
                scrollVel = 0;
                setState('idle');
            }
        } else if (state === 'celebrate') {
            happy = true;
            flapSpeed = 18;
            flapAmp = 0.65;
            if (!reduced) {
                spin = since < 1 ? ease(since) * Math.PI * 2 : 0;
                targetY = -Math.abs(Math.sin(Math.min(since, 1.6) * Math.PI * 1.25)) * 40;
            }
            armWave.position.y = 0.15 + (since > 1 ? Math.abs(Math.sin(time * 9)) * 0.5 : 0); // el sallar
            if (since > 2.6) setState('leave');
        } else if (state === 'leave') {
            const p = clamp(since / 1.1, 0, 1);
            happy = true;
            flapSpeed = 18;
            targetX = ease(p) * 160;
            targetY = -ease(p) * 380;
            opacity = 1 - p;
            if (p >= 1) setState('gone');
        } else if (state === 'gone') {
            opacity = 0;
            targetY = -380;
        }
        if (state !== 'celebrate') armWave.position.y += (0.15 - armWave.position.y) * 0.2;

        // Büyük hareketler sarmalayıcıyla
        const snap = state === 'intro' || state === 'return' ? 1 : 0.18;
        wrapX += (targetX - wrapX) * snap;
        wrapY += (targetY - wrapY) * snap;
        wrapOpacity += (opacity - wrapOpacity) * 0.25;
        wrap.style.transform = `translate(${wrapX.toFixed(1)}px, ${wrapY.toFixed(1)}px)`;
        wrap.style.opacity = wrapOpacity.toFixed(3);
        wrap.style.visibility = wrapOpacity < 0.02 ? 'hidden' : 'visible';
        hint.classList.toggle('is-on', showHint);

        // Süzülme, zıplama
        const hopP = (t - hopAt) / 600;
        const hop = hopP >= 0 && hopP < 1 ? Math.sin(hopP * Math.PI) : 0;
        dragon.position.y = (reduced ? 0 : Math.sin(time * 2.2) * 0.1) + hop * 0.7;
        rig.scale.set(1 + hop * 0.06, 1 - hop * 0.05, 1);
        dragon.rotation.x += (tiltX - dragon.rotation.x) * 0.12;
        dragon.rotation.z += (tiltZ - dragon.rotation.z) * 0.12;
        dragon.rotation.y = BASE_YAW + spin + (hop ? Math.sin(hopP * Math.PI * 2) * 0.3 : 0);

        // Kanat çırpma
        const flap = reduced ? 0.2 : Math.sin(time * flapSpeed) * flapAmp + 0.15;
        wingR.rotation.set(0, -0.55, flap);
        wingL.rotation.set(0, 0.55, -flap);

        // Kuyruk sallanması
        tail.forEach((s, i) => {
            const k = (i + 1) / tail.length;
            s.position.set(Math.sin(time * 2.6 - i * 0.55) * 0.22 * k, -0.62 - k * 0.42 + k * k * 0.25, -0.62 - k * 1.05);
        });
        const lastSeg = tail[tail.length - 1].position;
        tailTip.position.set(lastSeg.x * 1.1, lastSeg.y + 0.12, lastSeg.z - 0.25);
        tailTip.rotation.set(-1.2, 0, Math.sin(time * 2.6 - 4) * 0.3);

        // Kafa fareyi takip eder
        let yaw = -0.25;
        let pitch = 0.05;
        if (pointer.has) {
            const r = canvas.getBoundingClientRect();
            const dx = pointer.x - (r.left + r.width * 0.5);
            const dy = pointer.y - (r.top + r.height * 0.3);
            yaw = clamp(dx / 500, -1, 1) * 0.7 - BASE_YAW;
            pitch = clamp(dy / 500, -1, 1) * 0.45;
            pupils.forEach((p, i) => {
                p.position.x = 0.3 * (i ? 1 : -1) + clamp(dx / 900, -1, 1) * 0.06;
                p.position.y = 0.1 - clamp(dy / 900, -1, 1) * 0.05;
            });
        }
        if (state === 'hint') {
            yaw = -0.2;
            pitch = 0.45;
        }
        head.rotation.y += (yaw - head.rotation.y) * 0.1;
        head.rotation.x += (pitch - head.rotation.x) * 0.1;

        // Göz kırpma, mutlu gözler, ağız
        const blinking = !reduced && (t % 4200) < 130;
        eyes.visible = !happy;
        happyEyes.visible = happy;
        eyes.scale.y = blinking ? 0.12 : 1;
        mouth.scale.setScalar(happy || hop ? 1.35 : 1);

        renderer.render(scene, camera);
        requestAnimationFrame(frame);
    };
    requestAnimationFrame(frame);
}
