/*
 * Sağ panel koşu oyunu (?mod=tam, geniş ekran).
 *
 * Tombul bir koyun kırlarda bir patikada koşar; kamera arkasındadır (hafif yandan).
 * Engeller kendiliğinden gelmez: çocuk doğru söylediğinde jumpOver() çağrılır,
 * önüne bir engel çıkar ve koyun tam engelin üstünden atlar (zamanlama hesaplanır).
 * Bilemezse oops(): koyun yavaşlar, durup başını sallar.
 *
 * Görsel: gradyan gökyüzü + güneş + gökkuşağı, dağlar ve tepeler, akan çim/patika dokusu,
 * çitler, çimen ve çiçekler, ağaçlar, gölgeler, toz ve yıldız parıltıları.
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
const RANGE = 52;         // kayan nesneler z: -RANGE..+4 arasında döner

export function createRunner(container) {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const rand = (a, b) => a + Math.random() * (b - a);

    /* ---------- Sahne ---------- */
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    container.appendChild(renderer.domElement);
    renderer.domElement.style.cssText = 'width:100%;height:100%;display:block;border-radius:inherit;';

    const scene = new THREE.Scene();
    const HORIZON = 0xffe6f0;
    scene.fog = new THREE.Fog(HORIZON, 16, 58);

    const camera = new THREE.PerspectiveCamera(48, 1, 0.1, 140);
    // Tam arkadan, biraz yukarıdan ve geriden: koyun ve önündeki yol birlikte görünsün
    const CAM = { x: 0, y: 3.6, z: 6.6 };
    camera.position.set(CAM.x, CAM.y, CAM.z);

    scene.add(new THREE.HemisphereLight(0xffffff, 0xbfe8a8, 2.3));
    const sun = new THREE.DirectionalLight(0xfff4e0, 2.4);
    sun.position.set(4, 9, 2);
    sun.target.position.set(0, 0, -1.5);
    sun.castShadow = true;
    sun.shadow.mapSize.set(1024, 1024);
    Object.assign(sun.shadow.camera, { left: -5, right: 5, top: 6, bottom: -6, near: 1, far: 25 });
    sun.shadow.bias = -0.0006;
    scene.add(sun, sun.target);

    /* ---------- Malzemeler ---------- */
    const grad = new THREE.DataTexture(new Uint8Array([125, 200, 255]), 3, 1, THREE.RedFormat);
    grad.minFilter = grad.magFilter = THREE.NearestFilter;
    grad.needsUpdate = true;
    const toon = (color, extra = {}) => new THREE.MeshToonMaterial({ color, gradientMap: grad, ...extra });
    const outlineMat = new THREE.MeshBasicMaterial({ color: 0x5b4a6b, side: THREE.BackSide });
    const sphere = new THREE.SphereGeometry(1, 24, 16);

    const mesh = (geo, mat, parent, pos = [0, 0, 0], scale = null, edge = 0, shadow = false) => {
        const m = new THREE.Mesh(geo, mat);
        m.position.set(...pos);
        if (scale) m.scale.set(...scale);
        if (edge) { const o = new THREE.Mesh(geo, outlineMat); o.scale.setScalar(1 + edge); m.add(o); }
        if (shadow) m.castShadow = true;
        parent.add(m);
        return m;
    };

    const canvasTex = (w, h, draw, repeat) => {
        const c = document.createElement('canvas');
        c.width = w; c.height = h;
        draw(c.getContext('2d'), w, h);
        const t = new THREE.CanvasTexture(c);
        t.colorSpace = THREE.SRGBColorSpace;
        t.wrapS = t.wrapT = THREE.RepeatWrapping;
        t.repeat.set(...repeat);
        t.anisotropy = renderer.capabilities.getMaxAnisotropy();
        return t;
    };

    /* ---------- Gökyüzü, güneş, gökkuşağı ---------- */
    const sky = new THREE.Mesh(
        new THREE.SphereGeometry(110, 32, 16),
        new THREE.ShaderMaterial({
            side: THREE.BackSide, fog: false, depthWrite: false,
            uniforms: { top: { value: new THREE.Color(0x7cc8ff) }, bottom: { value: new THREE.Color(HORIZON) } },
            vertexShader: 'varying float h; void main(){ h = normalize(position).y; gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
            fragmentShader: 'uniform vec3 top; uniform vec3 bottom; varying float h; void main(){ gl_FragColor = vec4(mix(bottom, top, smoothstep(-0.02, 0.45, h)), 1.0); }',
        })
    );
    scene.add(sky);

    const sunDisc = new THREE.Group();
    sunDisc.position.set(-22, 20, -80);
    mesh(new THREE.CircleGeometry(4.2, 40), new THREE.MeshBasicMaterial({ color: 0xfff1a8, fog: false }), sunDisc);
    mesh(new THREE.CircleGeometry(6.5, 40), new THREE.MeshBasicMaterial({ color: 0xfff6c8, transparent: true, opacity: .45, fog: false }), sunDisc, [0, 0, -0.1]);
    scene.add(sunDisc);

    const rainbow = new THREE.Group();
    [0xff8fa3, 0xffb37a, 0xffe07a, 0x9be08a, 0x8ec9ff, 0xc3a6ff].forEach((c, i) => {
        mesh(new THREE.TorusGeometry(26 - i * 1.15, 0.6, 8, 64, Math.PI),
            new THREE.MeshBasicMaterial({ color: c, transparent: true, opacity: .5, fog: false }), rainbow);
    });
    rainbow.position.set(14, -3, -85);
    scene.add(rainbow);

    /* ---------- Uzak dağlar ve tepeler (sabit) ---------- */
    [[-30, 13, 0xc9b8ef], [-12, 17, 0xb9a8e6], [8, 14, 0xd2c2f2], [26, 16, 0xc0b0ea]].forEach(([x, h, c]) => {
        // Sis 58'de kapanır; dağlar daha uzakta olduğu için sisten muaf (pastel renkler zaten soluk)
        const m = mesh(new THREE.ConeGeometry(h * 0.9, h, 7), toon(c, { fog: false }), scene, [x, h / 2 - 1, -75]);
        mesh(new THREE.ConeGeometry(h * 0.9 * 0.32, h * 0.32, 7), toon(0xffffff, { fog: false }), m, [0, h * 0.34, 0]);
    });
    [[-18, 9, 0x8fd88a], [0, 11, 0x7fcf7f], [20, 10, 0x98dd8f], [-34, 8, 0x86d385], [36, 9, 0x8bd788]].forEach(([x, r, c]) =>
        mesh(sphere, toon(c), scene, [x, -r * 0.55, -48], [r * 1.6, r, r]));

    /* ---------- Zemin ve patika (akan dokular) ---------- */
    const grassTex = canvasTex(256, 256, (g, w, h) => {
        g.fillStyle = '#9BE08A'; g.fillRect(0, 0, w, h);
        const shades = ['#8AD47A', '#A9E99A', '#7FCC70', '#B4EDA6'];
        for (let i = 0; i < 1400; i++) {
            g.strokeStyle = shades[i % shades.length];
            g.lineWidth = 1.4;
            const x = Math.random() * w, y = Math.random() * h;
            g.beginPath(); g.moveTo(x, y); g.lineTo(x + rand(-2, 2), y - rand(3, 7)); g.stroke();
        }
    }, [10, 14]);
    const pathTex = canvasTex(128, 256, (g, w, h) => {
        g.fillStyle = '#F4DDAB'; g.fillRect(0, 0, w, h);
        g.fillStyle = '#E4C88F'; g.fillRect(0, 0, w * 0.08, h); g.fillRect(w * 0.92, 0, w * 0.08, h);
        for (let i = 0; i < 90; i++) {
            g.fillStyle = ['#E9CF9A', '#FBEBC9', '#DFC189'][i % 3];
            g.beginPath(); g.ellipse(rand(10, w - 10), rand(0, h), rand(2, 5), rand(1.5, 3.5), rand(0, 3), 0, Math.PI * 2); g.fill();
        }
    }, [1, 14]);

    const ground = mesh(new THREE.PlaneGeometry(100, 140), toon(0xffffff, { map: grassTex }), scene, [0, 0, -50]);
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    const path = mesh(new THREE.PlaneGeometry(2.8, 140), toon(0xffffff, { map: pathTex }), scene, [0, 0.012, -50]);
    path.rotation.x = -Math.PI / 2;
    path.receiveShadow = true;
    const GRASS_TILE = 140 / 14;
    const PATH_TILE = 140 / 14;

    /* ---------- Kayan nesneler ---------- */
    const movers = []; // { obj, base? }
    const addMover = (obj) => { movers.push(obj); scene.add(obj); return obj; };

    // Ağaçlar: yuvarlak, çam, çalı
    const trunkGeo = new THREE.CylinderGeometry(0.13, 0.19, 1, 8);
    const trunkMat = toon(0xa9744f);
    const leafMats = [toon(0x6fcf7a), toon(0x5cc06c), toon(0x8edb7e), toon(0xffb8d4), toon(0xffcfa8)];
    const pineMat = toon(0x4fb46a);
    const makeTree = (i) => {
        const t = new THREE.Group();
        const kind = i % 3;
        if (kind === 0) {
            mesh(trunkGeo, trunkMat, t, [0, 0.5, 0], null, 0, true);
            mesh(sphere, leafMats[i % leafMats.length], t, [0, 1.45, 0], [0.85, 0.8, 0.85], 0.035, true);
            mesh(sphere, leafMats[i % leafMats.length], t, [0.45, 1.2, 0.1], [0.5, 0.48, 0.5], 0.05);
            mesh(sphere, leafMats[i % leafMats.length], t, [-0.42, 1.25, -0.1], [0.5, 0.48, 0.5], 0.05);
        } else if (kind === 1) {
            mesh(trunkGeo, trunkMat, t, [0, 0.35, 0], [0.8, 0.7, 0.8], 0, true);
            [[0.85, 0.9, 0.9], [0.68, 0.8, 1.45], [0.48, 0.7, 1.95]].forEach(([r, hh, y]) =>
                mesh(new THREE.ConeGeometry(r, hh, 9), pineMat, t, [0, y, 0], null, 0.05, true));
        } else {
            const c = leafMats[(i + 2) % leafMats.length];
            [[0, 0.35, 0, 0.5], [0.45, 0.3, 0, 0.38], [-0.42, 0.3, 0.05, 0.36]].forEach(([x, y, z, r]) =>
                mesh(sphere, c, t, [x, y, z], [r, r * 0.9, r], 0.05, true));
        }
        const side = i % 2 ? 1 : -1;
        t.position.set(side * rand(2.6, 7.5), 0, -rand(0, RANGE));
        t.scale.setScalar(rand(0.8, 1.35));
        t.rotation.y = rand(0, Math.PI * 2);
        return t;
    };
    for (let i = 0; i < 22; i++) addMover(makeTree(i));

    // Patika kenarında çit direkleri (iki yanda)
    const postGeo = new THREE.BoxGeometry(0.1, 0.5, 0.1);
    const postMat = toon(0xc99a6e);
    for (let i = 0; i < 26; i++) {
        [-1.85, 1.85].forEach((x) => {
            const p = mesh(postGeo, postMat, new THREE.Group(), [0, 0.25, 0], null, 0.08);
            const g = p.parent;
            g.position.set(x, 0, -i * (RANGE / 26));
            addMover(g);
        });
    }

    // Çimen öbekleri ve çiçekler: InstancedMesh ile
    const tuftGeo = new THREE.ConeGeometry(0.07, 0.32, 5);
    const tuftMat = toon(0x6cc25e);
    const TUFTS = 140;
    const tufts = new THREE.InstancedMesh(tuftGeo, tuftMat, TUFTS);
    const tuftPos = Array.from({ length: TUFTS }, (_, i) => ({ x: (i % 2 ? 1 : -1) * rand(1.55, 6), z: -rand(0, RANGE), r: rand(-0.4, 0.4), s: rand(0.7, 1.4) }));
    scene.add(tufts);

    const FLOWERS = 70;
    const petalGeo = new THREE.SphereGeometry(0.13, 10, 6);
    petalGeo.scale(1, 0.45, 1);
    const centerGeo = new THREE.SphereGeometry(0.06, 8, 6);
    const petals = new THREE.InstancedMesh(petalGeo, toon(0xffffff), FLOWERS);
    const centers = new THREE.InstancedMesh(centerGeo, toon(0xffc94d), FLOWERS);
    const petalColors = [0xff8fb1, 0xffffff, 0xc3a6ff, 0xffd966, 0xffb0a0].map((c) => new THREE.Color(c));
    const flowerPos = Array.from({ length: FLOWERS }, (_, i) => ({ x: (i % 2 ? 1 : -1) * rand(1.6, 7), z: -rand(0, RANGE) }));
    flowerPos.forEach((_, i) => petals.setColorAt(i, petalColors[i % petalColors.length]));
    scene.add(petals, centers);

    const dummy = new THREE.Object3D();
    const syncInstances = () => {
        tuftPos.forEach((p, i) => {
            dummy.position.set(p.x, 0.15 * p.s, p.z);
            dummy.rotation.set(0, 0, p.r);
            dummy.scale.setScalar(p.s);
            dummy.updateMatrix();
            tufts.setMatrixAt(i, dummy.matrix);
        });
        flowerPos.forEach((p, i) => {
            dummy.rotation.set(0, 0, 0);
            dummy.scale.setScalar(1);
            dummy.position.set(p.x, 0.12, p.z);
            dummy.updateMatrix();
            petals.setMatrixAt(i, dummy.matrix);
            dummy.position.y = 0.16;
            dummy.updateMatrix();
            centers.setMatrixAt(i, dummy.matrix);
        });
        tufts.instanceMatrix.needsUpdate = true;
        petals.instanceMatrix.needsUpdate = true;
        centers.instanceMatrix.needsUpdate = true;
    };

    // Bulutlar
    const clouds = [];
    for (let i = 0; i < 7; i++) {
        const c = new THREE.Group();
        [[0, 0, 1.2], [1.1, -0.2, 0.9], [-1.1, -0.2, 0.85], [0.5, 0.45, 0.8], [-0.5, 0.35, 0.75]].forEach(([x, y, r]) =>
            mesh(sphere, toon(0xffffff, { fog: false }), c, [x, y, 0], [r, r * 0.75, r * 0.75]));
        c.position.set(rand(-35, 35), rand(10, 16), rand(-60, -30));
        c.scale.setScalar(rand(0.9, 1.6));
        scene.add(c);
        clouds.push(c);
    }

    /* ---------- Koyun ---------- */
    const sheep = new THREE.Group();
    scene.add(sheep);
    const body = new THREE.Group();
    sheep.add(body);
    const wool = toon(0xffffff);
    const woolShade = toon(0xf4eef8);
    const dark = toon(0x3d3445);
    const pink = toon(0xffa6c1);

    [[0, 0.98, 0, 0.6], [0.34, 1.06, 0.18, 0.4], [-0.34, 1.06, 0.18, 0.4], [0.3, 1.02, -0.3, 0.42], [-0.3, 1.02, -0.3, 0.42],
        [0, 1.33, 0.05, 0.44], [0.22, 1.28, -0.28, 0.32], [-0.22, 1.28, -0.28, 0.32], [0, 1.02, 0.48, 0.42], [0, 1.12, -0.48, 0.42],
        [0.38, 0.86, 0.02, 0.34], [-0.38, 0.86, 0.02, 0.34]].forEach(([x, y, z, r], i) =>
        mesh(sphere, i % 3 === 2 ? woolShade : wool, body, [x, y, z], [r, r, r], 0.045, true));
    const tail = mesh(sphere, wool, body, [0, 1.15, 0.78], [0.17, 0.17, 0.17], 0.08, true);

    const head = new THREE.Group();
    head.position.set(0, 1.38, -0.78);
    body.add(head);
    mesh(sphere, dark, head, [0, 0, 0], [0.31, 0.35, 0.37], 0.06, true);
    [[0, 0.3, 0.02, 0.17], [0.14, 0.26, 0.05, 0.13], [-0.14, 0.26, 0.05, 0.13]].forEach(([x, y, z, r]) =>
        mesh(sphere, wool, head, [x, y, z], [r, r * 0.85, r]));
    [-1, 1].forEach((s) => {
        const ear = new THREE.Group();
        ear.position.set(0.34 * s, 0.06, 0.02);
        ear.rotation.z = -0.45 * s;
        mesh(sphere, dark, ear, [0.08 * s, 0, 0], [0.18, 0.075, 0.11], 0.1);
        mesh(sphere, pink, ear, [0.09 * s, 0, -0.04], [0.12, 0.05, 0.07]);
        head.add(ear);
        // gözler ve yanaklar (kafa dönünce görünür)
        mesh(sphere, new THREE.MeshBasicMaterial({ color: 0xffffff }), head, [0.13 * s, 0.07, -0.3], [0.085, 0.1, 0.06]);
        mesh(sphere, new THREE.MeshBasicMaterial({ color: 0x1f1a24 }), head, [0.13 * s, 0.06, -0.345], [0.05, 0.06, 0.03]);
        mesh(sphere, new THREE.MeshBasicMaterial({ color: 0xffffff }), head, [0.145 * s, 0.085, -0.37], [0.018, 0.018, 0.01]);
        mesh(sphere, pink, head, [0.2 * s, -0.1, -0.27], [0.07, 0.045, 0.03]);
    });
    mesh(sphere, pink, head, [0, -0.13, -0.35], [0.06, 0.04, 0.03]); // burun

    const legGeo = new THREE.CylinderGeometry(0.09, 0.08, 0.58, 8);
    legGeo.translate(0, -0.29, 0);
    const hoofGeo = new THREE.CylinderGeometry(0.1, 0.11, 0.09, 8);
    const legs = [[0.24, -0.36], [-0.24, -0.36], [0.24, 0.36], [-0.24, 0.36]].map(([x, z]) => {
        const l = mesh(legGeo, dark, body, [x, 0.66, z], null, 0.08, true);
        mesh(hoofGeo, toon(0x2a2430), l, [0, -0.56, 0]);
        return l;
    });

    /* ---------- Engeller ---------- */
    const woodA = toon(0xc98c58), woodB = toon(0x9b6339), ring = toon(0xf2d7a8), hay = toon(0xf5d36a),
        hayDark = toon(0xd9a93e), rockMat = toon(0xbdb6cc), moss = toon(0x7fcf7f), red = toon(0xff7a8a);
    const castAll = (g) => g.traverse((o) => { if (o.isMesh && o.material !== outlineMat) o.castShadow = true; });
    const makeObstacle = () => {
        const g = new THREE.Group();
        const kind = Math.floor(Math.random() * 4);
        if (kind === 0) { // sivri direkli çit
            [-0.95, 0, 0.95].forEach((x) => {
                mesh(new THREE.BoxGeometry(0.17, 0.78, 0.14), woodB, g, [x, 0.39, 0], null, 0.06);
                const tip = mesh(new THREE.ConeGeometry(0.12, 0.2, 4), woodB, g, [x, 0.88, 0], null, 0.06);
                tip.rotation.y = Math.PI / 4;
            });
            [0.28, 0.58].forEach((y) => mesh(new THREE.BoxGeometry(2.15, 0.13, 0.09), woodA, g, [0, y, 0.08], null, 0.06));
        } else if (kind === 1) { // halkalı kütük + mantar
            const log = mesh(new THREE.CylinderGeometry(0.3, 0.3, 2.1, 16), woodB, g, [0, 0.3, 0], null, 0.05);
            log.rotation.z = Math.PI / 2;
            [-1.05, 1.05].forEach((x) => {
                const cap = mesh(new THREE.CircleGeometry(0.28, 16), ring, g, [x + Math.sign(x) * 0.005, 0.3, 0]);
                cap.rotation.y = Math.sign(x) * Math.PI / 2;
                const r2 = mesh(new THREE.TorusGeometry(0.15, 0.02, 6, 16), woodB, g, [x + Math.sign(x) * 0.01, 0.3, 0]);
                r2.rotation.y = Math.PI / 2;
            });
            mesh(new THREE.CylinderGeometry(0.04, 0.05, 0.14, 8), ring, g, [0.4, 0.66, 0]);
            mesh(sphere, red, g, [0.4, 0.74, 0], [0.13, 0.08, 0.13], 0.08);
        } else if (kind === 2) { // kemerli saman balyası
            const bale = mesh(new THREE.CylinderGeometry(0.42, 0.42, 1.5, 18), hay, g, [0, 0.42, 0], null, 0.04);
            bale.rotation.z = Math.PI / 2;
            [-0.4, 0.4].forEach((x) => {
                const strap = mesh(new THREE.TorusGeometry(0.43, 0.035, 6, 24), hayDark, g, [x, 0.42, 0]);
                strap.rotation.y = Math.PI / 2;
            });
        } else { // yosunlu kaya + çiçek
            mesh(new THREE.DodecahedronGeometry(0.46), rockMat, g, [0, 0.33, 0], [1.5, 0.82, 1.05], 0.06);
            mesh(sphere, moss, g, [0.1, 0.62, 0], [0.42, 0.12, 0.36]);
            mesh(sphere, toon(0xff8fb1), g, [-0.35, 0.62, 0.15], [0.09, 0.06, 0.09]);
        }
        castAll(g);
        g.position.set(0, 0, SPAWN_Z);
        scene.add(g);
        return g;
    };

    /* ---------- Toz ve yıldızlar ---------- */
    const dustMat = new THREE.MeshBasicMaterial({ color: 0xf4e3c0, transparent: true, opacity: 0, depthWrite: false });
    const dust = Array.from({ length: 10 }, () => mesh(sphere, dustMat.clone(), scene, [0, -5, 0], [0.12, 0.12, 0.12]));
    const dustState = dust.map(() => ({ t: 1, vx: 0, vz: 0 }));
    const puff = () => dust.forEach((d, i) => {
        const a = (i / dust.length) * Math.PI * 2;
        d.position.set(Math.cos(a) * 0.3, 0.08, Math.sin(a) * 0.3);
        dustState[i] = { t: 0, vx: Math.cos(a) * 1.3, vz: Math.sin(a) * 1.3 + 1.5 };
    });

    const starTex = canvasTex(64, 64, (g) => {
        g.translate(32, 32);
        g.fillStyle = '#FFE27A';
        g.beginPath();
        for (let i = 0; i < 10; i++) {
            const r = i % 2 ? 12 : 28, a = (i / 10) * Math.PI * 2 - Math.PI / 2;
            g.lineTo(Math.cos(a) * r, Math.sin(a) * r);
        }
        g.closePath(); g.fill();
    }, [1, 1]);
    const stars = Array.from({ length: 14 }, () => {
        const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: starTex, transparent: true, opacity: 0, depthWrite: false, fog: false }));
        s.scale.setScalar(0.35);
        scene.add(s);
        return s;
    });
    const starState = stars.map(() => ({ t: 1, v: new THREE.Vector3() }));
    const burst = () => stars.forEach((s, i) => {
        s.position.set(0, 1.6 + sheep.position.y, -0.2);
        starState[i] = { t: 0, v: new THREE.Vector3(rand(-2.2, 2.2), rand(1.5, 3.5), rand(-1, 1)) };
    });

    /* ---------- Sayaç ---------- */
    const hud = document.createElement('div');
    hud.className = 'runner-hud';
    hud.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="#B97A4A" stroke-width="2.4" stroke-linecap="round"><path d="M5 20V8M19 20V8M5 11h14M5 15h14"/></svg><span>0</span>';
    container.appendChild(hud);
    const hudNum = hud.querySelector('span');
    let cleared = 0;

    /* ---------- Durum ---------- */
    const queue = [];
    let obstacle = null, obstacleAt = 0, counted = false, jumpAt = -1, landed = true;
    let oopsAt = -10, happyAt = -10, speedMul = 0.55;

    const startObstacle = (t) => {
        obstacle = makeObstacle();
        obstacleAt = t;
        counted = false;
        jumpAt = t + APPROACH - JUMP / 2; // zirve engelin geçtiği ana denk gelir
        landed = false;
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
    const lookTarget = new THREE.Vector3(0, 0.8, -7);
    let time = 0;

    const loop = () => {
        const dt = Math.min(clock.getDelta(), 0.05);
        time += dt;

        if (!obstacle && queue.length) { queue.shift(); startObstacle(time); }

        // Hız: engel yaklaşırken sabit (zamanlama bozulmasın), bilemeyince yavaş
        const sinceOops = time - oopsAt;
        const target = obstacle ? 1 : sinceOops < 1.4 ? 0.12 : 0.55;
        speedMul += (target - speedMul) * (obstacle ? 1 : 0.06);
        const dz = SPEED * speedMul * dt;

        // Akan zemin
        grassTex.offset.y += dz / GRASS_TILE;
        pathTex.offset.y += dz / PATH_TILE;
        movers.forEach((m) => {
            m.position.z += dz;
            if (m.position.z > 4) m.position.z -= RANGE + 4;
        });
        tuftPos.forEach((p) => { p.z += dz; if (p.z > 4) p.z -= RANGE + 4; });
        flowerPos.forEach((p) => { p.z += dz; if (p.z > 4) p.z -= RANGE + 4; });
        syncInstances();
        clouds.forEach((c) => { c.position.x += dt * 0.35; if (c.position.x > 40) c.position.x = -40; });

        // Engel
        if (obstacle) {
            obstacle.position.z = SPAWN_Z + SPEED * (time - obstacleAt);
            if (!counted && obstacle.position.z > 0.3) {
                counted = true;
                cleared++;
                hudNum.textContent = cleared;
                hud.classList.remove('bump'); void hud.offsetWidth; hud.classList.add('bump');
                burst();
                happyAt = time;
            }
            if (obstacle.position.z > 6) { scene.remove(obstacle); obstacle = null; }
        }

        // Zıplama
        const jp = jumpAt >= 0 ? (time - jumpAt) / JUMP : -1;
        const jumping = jp >= 0 && jp <= 1;
        sheep.position.y = jumping ? 4 * JUMP_H * jp * (1 - jp) : 0;
        sheep.rotation.x = jumping ? Math.sin(jp * Math.PI * 2) * -0.18 : 0;
        if (jp > 1) { jumpAt = -1; if (!landed) { landed = true; puff(); } }

        // Koşu
        const run = reduced ? 0 : Math.sin(time * 14 * Math.max(speedMul, 0.2));
        legs.forEach((l, i) => {
            l.rotation.x = jumping ? (i < 2 ? -1 : 1) : run * 0.75 * (i % 3 === 0 ? 1 : -1) * Math.min(1, speedMul * 1.8);
        });
        body.position.y = jumping || reduced ? 0 : Math.abs(run) * 0.07 * speedMul;
        body.rotation.z = reduced ? 0 : run * 0.03 * speedMul;
        tail.position.x = Math.sin(time * 9) * 0.06;

        // Kafa: bilemeyince sallar; engeli geçince dönüp kameraya bakar
        const sinceHappy = time - happyAt;
        let headYaw = 0;
        if (sinceOops < 1.2) headYaw = Math.sin(sinceOops * 18) * 0.45 * (1 - sinceOops / 1.2);
        else if (sinceHappy < 1.6) headYaw = -1.15 * Math.sin(Math.min(sinceHappy / 1.6, 1) * Math.PI);
        head.rotation.y += (headYaw - head.rotation.y) * 0.25;
        head.rotation.x = sinceOops < 1.2 ? 0.25 : 0;

        // Toz ve yıldızlar
        dust.forEach((d, i) => {
            const s = dustState[i];
            if (s.t >= 1) { d.material.opacity = 0; return; }
            s.t += dt / 0.6;
            d.position.x += s.vx * dt; d.position.z += s.vz * dt; d.position.y += dt * 0.4;
            d.scale.setScalar(0.12 + s.t * 0.25);
            d.material.opacity = 0.7 * (1 - s.t);
        });
        stars.forEach((st, i) => {
            const s = starState[i];
            if (s.t >= 1) { st.material.opacity = 0; return; }
            s.t += dt / 1.1;
            s.v.y -= dt * 3.2;
            st.position.addScaledVector(s.v, dt);
            st.material.opacity = Math.min(1, (1 - s.t) * 1.6);
            st.material.rotation += dt * 4;
        });

        // Kamera: zıplamayı hafifçe takip et
        camera.position.set(CAM.x, CAM.y + sheep.position.y * 0.3, CAM.z);
        lookTarget.set(0, 0.8 + sheep.position.y * 0.4, -7);
        camera.lookAt(lookTarget);

        renderer.render(scene, camera);
        requestAnimationFrame(loop);
    };
    syncInstances();
    requestAnimationFrame(loop);

    return {
        // Doğru cevap: engel çıkar, koyun üstünden atlar
        jumpOver() { queue.push(1); },
        // Bilemedi: koyun yavaşlar, durup başını sallar
        oops() { oopsAt = time; },
    };
}
