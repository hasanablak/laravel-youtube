/*
 * Sağ panel koşu oyunu (?mod=tam, geniş ekran).
 *
 * Tombul bir koyun kırlarda bir patikada koşar; kamera arkasındadır.
 * Engeller kendiliğinden gelmez: çocuk doğru söylediğinde jumpOver() çağrılır,
 * önüne bir engel çıkar ve koyun tam engelin üstünden atlar (zamanlama hesaplanır).
 * Bilemezse oops(): koyun durup başını sallar.
 *
 * index.html: import('./runner.js').then((m) => m.createRunner(el))
 * Three.js: ./vendor/three.module.min.js (r169, MIT)
 */
import * as THREE from './vendor/three.module.min.js';

const SPEED = 7;          // dünya kayma hızı (birim/sn) — engel yaklaşırken sabit tutulur
const APPROACH = 1.25;    // engelin koyuna ulaşma süresi (sn)
const JUMP = 0.72;        // zıplama süresi (sn)
const JUMP_H = 1.35;      // zıplama yüksekliği
const SPAWN_Z = -SPEED * APPROACH;

export function createRunner(container) {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    container.appendChild(renderer.domElement);
    renderer.domElement.style.cssText = 'width:100%;height:100%;display:block;border-radius:inherit;';

    const scene = new THREE.Scene();
    const SKY = 0xcdeaff;
    scene.background = new THREE.Color(SKY);
    scene.fog = new THREE.Fog(SKY, 14, 46);

    const camera = new THREE.PerspectiveCamera(50, 1, 0.1, 100);
    camera.position.set(0, 2.5, 4.6);
    camera.lookAt(0, 0.9, -6);

    scene.add(new THREE.HemisphereLight(0xffffff, 0xb6e3a0, 2.4));
    const sun = new THREE.DirectionalLight(0xffffff, 2.2);
    sun.position.set(4, 8, 3);
    scene.add(sun);

    // Çizgi film boyaması
    const grad = new THREE.DataTexture(new Uint8Array([120, 195, 255]), 3, 1, THREE.RedFormat);
    grad.minFilter = grad.magFilter = THREE.NearestFilter;
    grad.needsUpdate = true;
    const toon = (color) => new THREE.MeshToonMaterial({ color, gradientMap: grad });
    const outline = new THREE.MeshBasicMaterial({ color: 0x5b4a6b, side: THREE.BackSide });
    const sphere = new THREE.SphereGeometry(1, 24, 16);
    const mesh = (geo, mat, parent, pos = [0, 0, 0], scale = null, edge = 0) => {
        const m = new THREE.Mesh(geo, mat);
        m.position.set(...pos);
        if (scale) m.scale.set(...scale);
        if (edge) { const o = new THREE.Mesh(geo, outline); o.scale.setScalar(1 + edge); m.add(o); }
        parent.add(m);
        return m;
    };

    /* ---------- Zemin ve patika ---------- */
    const ground = mesh(new THREE.PlaneGeometry(80, 120), toon(0x9be08a), scene, [0, 0, -40]);
    ground.rotation.x = -Math.PI / 2;
    const path = mesh(new THREE.PlaneGeometry(2.6, 120), toon(0xf3dca8), scene, [0, 0.01, -40]);
    path.rotation.x = -Math.PI / 2;

    // Kayan dünya: patika taşları, ağaçlar, çiçekler (geri dönüşümlü)
    const movers = [];
    const RANGE = 48; // z: -RANGE..+4 arası döner
    const stoneGeo = new THREE.CylinderGeometry(0.16, 0.18, 0.04, 10);
    const stoneMat = toon(0xe2c48c);
    for (let i = 0; i < 26; i++) {
        const s = mesh(stoneGeo, stoneMat, scene, [(Math.random() - 0.5) * 1.8, 0.03, -i * (RANGE / 26)]);
        movers.push(s);
    }
    const trunkGeo = new THREE.CylinderGeometry(0.14, 0.18, 0.9, 8);
    const crownGeo = new THREE.SphereGeometry(0.8, 16, 12);
    const trunkMat = toon(0xa9744f);
    const crownMats = [toon(0x6fcf7a), toon(0x58bf6a), toon(0x8fdc7e), toon(0xffb3d1)];
    for (let i = 0; i < 16; i++) {
        const side = i % 2 ? 1 : -1;
        const tree = new THREE.Group();
        mesh(trunkGeo, trunkMat, tree, [0, 0.45, 0]);
        mesh(crownGeo, crownMats[i % crownMats.length], tree, [0, 1.35, 0], [1, 0.95, 1], 0.04);
        tree.position.set(side * (3 + Math.random() * 4), 0, -i * (RANGE / 16) - Math.random() * 2);
        tree.scale.setScalar(0.8 + Math.random() * 0.5);
        scene.add(tree);
        movers.push(tree);
    }
    const petalColors = [0xff8fb1, 0xffd966, 0xb9a4ff, 0xffffff];
    for (let i = 0; i < 40; i++) {
        const f = mesh(sphere, toon(petalColors[i % petalColors.length]), scene,
            [(Math.random() < 0.5 ? -1 : 1) * (1.6 + Math.random() * 5), 0.1, -Math.random() * RANGE], [0.11, 0.11, 0.11]);
        movers.push(f);
    }
    // Bulutlar (yavaş)
    const clouds = [];
    for (let i = 0; i < 6; i++) {
        const c = new THREE.Group();
        [[0, 0, 1], [0.9, -0.15, 0.75], [-0.9, -0.15, 0.7], [0.4, 0.4, 0.7]].forEach(([x, y, r]) =>
            mesh(sphere, toon(0xffffff), c, [x, y, 0], [r, r * 0.8, r * 0.8]));
        c.position.set((Math.random() - 0.5) * 30, 7 + Math.random() * 3, -20 - Math.random() * 20);
        scene.add(c);
        clouds.push(c);
    }

    /* ---------- Koyun (arkadan) ---------- */
    const sheep = new THREE.Group();
    scene.add(sheep);
    const body = new THREE.Group();
    sheep.add(body);
    const wool = toon(0xffffff);
    const dark = toon(0x3b3340);
    [[0, 0.95, 0, 0.62], [0.32, 1.05, 0.15, 0.42], [-0.32, 1.05, 0.15, 0.42], [0.28, 1.0, -0.3, 0.42], [-0.28, 1.0, -0.3, 0.42],
        [0, 1.3, 0, 0.45], [0, 1.0, 0.45, 0.4], [0, 1.1, -0.45, 0.42]].forEach(([x, y, z, r]) =>
        mesh(sphere, wool, body, [x, y, z], [r, r, r], 0.05));
    mesh(sphere, wool, body, [0, 1.1, 0.72], [0.18, 0.18, 0.18], 0.08); // kuyruk
    // Kafa (öne bakar: -z)
    const head = new THREE.Group();
    head.position.set(0, 1.35, -0.78);
    body.add(head);
    mesh(sphere, dark, head, [0, 0, 0], [0.32, 0.36, 0.38], 0.06);
    mesh(sphere, wool, head, [0, 0.3, 0.05], [0.26, 0.16, 0.24]); // perçem
    [-1, 1].forEach((s) => {
        const ear = mesh(sphere, dark, head, [0.36 * s, 0.05, 0.05], [0.18, 0.07, 0.1], 0.1);
        ear.rotation.z = -0.4 * s;
    });
    // Bacaklar
    const legGeo = new THREE.CylinderGeometry(0.09, 0.08, 0.6, 8);
    legGeo.translate(0, -0.3, 0); // pivot üstte
    const legs = [[0.25, -0.35], [-0.25, -0.35], [0.25, 0.35], [-0.25, 0.35]].map(([x, z]) =>
        mesh(legGeo, dark, body, [x, 0.62, z], null, 0.08));

    /* ---------- Engeller ---------- */
    const wood = toon(0xb97a4a), woodDark = toon(0x8c5a36), hay = toon(0xf2cf63), rock = toon(0xb9b3c4);
    const makeObstacle = () => {
        const g = new THREE.Group();
        const kind = Math.floor(Math.random() * 4);
        if (kind === 0) { // çit
            [-0.9, 0.9].forEach((x) => mesh(new THREE.BoxGeometry(0.16, 0.8, 0.16), woodDark, g, [x, 0.4, 0], null, 0.06));
            [0.3, 0.6].forEach((y) => mesh(new THREE.BoxGeometry(2, 0.12, 0.1), wood, g, [0, y, 0], null, 0.06));
        } else if (kind === 1) { // kütük
            const log = mesh(new THREE.CylinderGeometry(0.28, 0.28, 2, 14), wood, g, [0, 0.28, 0], null, 0.05);
            log.rotation.z = Math.PI / 2;
        } else if (kind === 2) { // saman balyası
            mesh(new THREE.BoxGeometry(1.6, 0.55, 0.6), hay, g, [0, 0.28, 0], null, 0.05);
        } else { // kaya
            mesh(new THREE.DodecahedronGeometry(0.45), rock, g, [0, 0.32, 0], [1.4, 0.8, 1], 0.06);
        }
        g.position.set(0, 0, SPAWN_Z);
        scene.add(g);
        return g;
    };

    /* ---------- Sayaç ---------- */
    const hud = document.createElement('div');
    hud.className = 'runner-hud';
    hud.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#B97A4A" stroke-width="2.4" stroke-linecap="round"><path d="M5 20V8M19 20V8M5 11h14M5 15h14"/></svg><span>0</span>';
    container.appendChild(hud);
    const hudNum = hud.querySelector('span');
    let cleared = 0;

    const sparkle = () => {
        const r = container.getBoundingClientRect();
        const colors = ['#FFD966', '#FF8FB1', '#B9A4FF', '#7FD8B0'];
        for (let i = 0; i < 18; i++) {
            const el = document.createElement('span');
            el.className = 'confetti';
            el.style.left = `${r.left + r.width / 2}px`;
            el.style.top = `${r.top + r.height * 0.55}px`;
            el.style.background = colors[i % colors.length];
            el.style.setProperty('--dx', `${(Math.random() - 0.5) * 260}px`);
            el.style.setProperty('--dy', `${-Math.random() * 220}px`);
            el.style.setProperty('--r', `${Math.random() * 540}deg`);
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 1500);
        }
        hud.classList.remove('bump'); void hud.offsetWidth; hud.classList.add('bump');
    };

    /* ---------- Durum ---------- */
    const queue = [];          // bekleyen atlamalar (arka arkaya doğru cevap)
    let obstacle = null, obstacleAt = 0, counted = false;
    let jumpAt = -1;
    let oopsAt = -10;
    let speedMul = 1;

    const startObstacle = (t) => {
        obstacle = makeObstacle();
        obstacleAt = t;
        counted = false;
        // Zirve, engelin koyunun üstünden geçtiği ana denk gelsin
        jumpAt = t + APPROACH - JUMP / 2;
    };

    /* ---------- Boyut ---------- */
    const resize = () => {
        const w = container.clientWidth, h = container.clientHeight;
        if (!w || !h) return;
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
    };
    new ResizeObserver(resize).observe(container);
    resize();

    /* ---------- Döngü ---------- */
    const clock = new THREE.Clock();
    let time = 0;
    const loop = () => {
        const dt = Math.min(clock.getDelta(), 0.05);
        time += dt;

        if (!obstacle && queue.length) { queue.shift(); startObstacle(time); }

        // Hız: engel yaklaşırken sabit (zamanlama bozulmasın), "oops" sonrası yavaş
        const sinceOops = time - oopsAt;
        const target = obstacle ? 1 : sinceOops < 1.4 ? 0.15 : 0.55;
        speedMul += (target - speedMul) * (obstacle ? 1 : 0.06);
        const dz = SPEED * speedMul * dt;

        movers.forEach((m) => {
            m.position.z += dz;
            if (m.position.z > 4) m.position.z -= RANGE + 4;
        });
        clouds.forEach((c) => { c.position.x += dt * 0.3; if (c.position.x > 18) c.position.x = -18; });

        if (obstacle) {
            obstacle.position.z = SPAWN_Z + SPEED * (time - obstacleAt);
            if (!counted && obstacle.position.z > 0.3) {
                counted = true;
                cleared++;
                hudNum.textContent = cleared;
                sparkle();
            }
            if (obstacle.position.z > 5) { scene.remove(obstacle); obstacle = null; }
        }

        // Zıplama
        const jp = jumpAt >= 0 ? (time - jumpAt) / JUMP : -1;
        const jumping = jp >= 0 && jp <= 1;
        sheep.position.y = jumping ? 4 * JUMP_H * jp * (1 - jp) : 0;
        sheep.rotation.x = jumping ? Math.sin(jp * Math.PI) * -0.25 : 0;
        if (jp > 1) jumpAt = -1;

        // Koşu animasyonu
        const run = reduced ? 0 : Math.sin(time * 14 * Math.max(speedMul, 0.2));
        legs.forEach((l, i) => { l.rotation.x = jumping ? (i < 2 ? -0.9 : 0.9) : run * 0.7 * (i % 3 === 0 ? 1 : -1) * Math.min(1, speedMul * 1.8); });
        body.position.y = jumping || reduced ? 0 : Math.abs(run) * 0.06 * speedMul;
        // Bilemeyince başını sallar
        head.rotation.y = sinceOops < 1.2 ? Math.sin(sinceOops * 18) * 0.45 * (1 - sinceOops / 1.2) : 0;

        renderer.render(scene, camera);
        requestAnimationFrame(loop);
    };
    requestAnimationFrame(loop);

    return {
        // Doğru cevap: engel çıkar, koyun üstünden atlar
        jumpOver() { queue.push(1); },
        // Bilemedi: koyun durup başını sallar
        oops() { oopsAt = time; },
    };
}
