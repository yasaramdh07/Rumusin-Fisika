// Modul 1: Simulasi & Kalkulator Gerak Lurus (GLB & GLBB) — dibangun ulang dari legacy/page/rumus-gerak.
// Rumus kalkulator dipertahankan persis sama dengan versi lama.

export function computeGLB({ s, v, t }) {
    const known = [!Number.isNaN(s), !Number.isNaN(v), !Number.isNaN(t)];
    const count = known.filter(Boolean).length;
    if (count < 2) {
        return { error: 'Masukkan minimal 2 nilai (kosongkan yang ingin dicari).' };
    }

    let rS = s;
    let rV = v;
    let rT = t;
    if (!known[0]) rS = v * t;
    else if (!known[1]) rV = s / t;
    else if (!known[2]) rT = s / v;

    return {
        s: rS,
        v: rV,
        t: rT,
        sText: `${rS.toFixed(3)} m`,
        vText: `${rV.toFixed(3)} m/s`,
        tText: `${rT.toFixed(3)} s`,
    };
}

export function computeGLBB({ v0, a, t, sIn }) {
    if (Number.isNaN(v0) || Number.isNaN(a) || Number.isNaN(t)) {
        return { error: 'Masukkan nilai v₀, a, dan t terlebih dahulu.' };
    }

    const vFinal = v0 + a * t;
    const sFinal = v0 * t + 0.5 * a * t * t;
    const v2 = Math.sqrt(Math.abs(v0 * v0 + 2 * a * (Number.isNaN(sIn) ? sFinal : sIn)));

    return {
        vFinal,
        sFinal,
        v2,
        vFinalText: `${vFinal.toFixed(3)} m/s`,
        sFinalText: `${sFinal.toFixed(3)} m`,
        v2Text: `${v2.toFixed(3)} m/s`,
    };
}

export function mountGerakTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-gerak>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Gerak Lurus</h3>
                    <div class="rf-chips" role="group" aria-label="Mode simulasi gerak">
                        <button type="button" class="rf-chip is-active" data-sim-tab="glb" aria-pressed="true"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> GLB</button>
                        <button type="button" class="rf-chip" data-sim-tab="glbb" aria-pressed="false"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> GLBB</button>
                    </div>
                </div>

                <div class="rf-sim-stage">
                    <canvas class="rf-sim-canvas" data-gerak-canvas width="640" height="220" aria-label="Kanvas simulasi mobil gerak lurus"></canvas>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt>Jarak (s)</dt><dd data-read-s>0.0 m</dd></div>
                    <div><dt>Kecepatan (v)</dt><dd data-read-v>0.0 m/s</dd></div>
                    <div><dt>Waktu (t)</dt><dd data-read-t>0.0 s</dd></div>
                    <div><dt>Percepatan (a)</dt><dd data-read-a>0.0 m/s²</dd></div>
                </dl>

                <div class="rf-sim-controls" data-sim-controls></div>

                <div class="rf-tool__actions">
                    <button type="button" class="rf-btn rf-btn--primary" data-sim-play>Mulai simulasi</button>
                    <button type="button" class="rf-btn rf-btn--tertiary" data-sim-reset>Atur ulang</button>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Rumus Gerak</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator gerak">
                        <button type="button" class="rf-chip is-active" data-calc-tab="glb" aria-pressed="true"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> GLB (s = v · t)</button>
                        <button type="button" class="rf-chip" data-calc-tab="glbb" aria-pressed="false"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> GLBB</button>
                    </div>
                </div>

                <div data-calc-panel="glb">
                    <p class="rf-muted rf-small">Isi minimal 2 nilai (kosongkan 1 nilai yang ingin dicari), lalu klik <strong>Hitung GLB</strong>.</p>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glb-s">Jarak s (m)</label>
                            <input class="rf-input rf-num" id="rf-glb-s" type="number" step="any" placeholder="kosongkan bila dicari" data-glb-s>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glb-v">Kecepatan v (m/s)</label>
                            <input class="rf-input rf-num" id="rf-glb-v" type="number" step="any" placeholder="contoh: 15" data-glb-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glb-t">Waktu t (s)</label>
                            <input class="rf-input rf-num" id="rf-glb-t" type="number" step="any" placeholder="contoh: 8" data-glb-t>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-glb-calc>Hitung GLB</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-glb-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-glb-result>
                            Masukkan minimal 2 nilai, lalu klik Hitung GLB.
                        </div>
                    </div>
                </div>

                <div data-calc-panel="glbb" hidden>
                    <p class="rf-muted rf-small">Masukkan kecepatan awal (v₀), percepatan (a), dan waktu (t). Jarak (s) opsional untuk uji rumus v² = v₀² + 2as.</p>
                    <div class="rf-tool__grid-4">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glbb-v0">Kec. awal v₀ (m/s)</label>
                            <input class="rf-input rf-num" id="rf-glbb-v0" type="number" step="any" placeholder="contoh: 0" data-glbb-v0>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glbb-a">Percepatan a (m/s²)</label>
                            <input class="rf-input rf-num" id="rf-glbb-a" type="number" step="any" placeholder="contoh: 4" data-glbb-a>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glbb-t">Waktu t (s)</label>
                            <input class="rf-input rf-num" id="rf-glbb-t" type="number" step="any" placeholder="contoh: 5" data-glbb-t>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-glbb-s">Jarak s (opsional, m)</label>
                            <input class="rf-input rf-num" id="rf-glbb-s" type="number" step="any" placeholder="opsional" data-glbb-s>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-glbb-calc>Hitung GLBB</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-glbb-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-glbb-result>
                            Masukkan nilai v₀, a, dan t, lalu klik Hitung GLBB.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    initGerakSimulation(container);
    initGerakCalculator(container);
}

function initGerakSimulation(root) {
    const canvas = root.querySelector('[data-gerak-canvas]');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let simMode = 'glb';
    let simRunning = false;
    let simAF = null;
    let simT = 0;
    let carX = 0;
    let lastTS = null;

    let simV = 40;
    let simV0 = 0;
    let simA = 20;

    const readS = root.querySelector('[data-read-s]');
    const readV = root.querySelector('[data-read-v]');
    const readT = root.querySelector('[data-read-t]');
    const readA = root.querySelector('[data-read-a]');
    const playBtn = root.querySelector('[data-sim-play]');
    const resetBtn = root.querySelector('[data-sim-reset]');
    const controlsWrap = root.querySelector('[data-sim-controls]');

    function drawScene(elapsed) {
        const W = canvas.width;
        const H = canvas.height;
        const trackY = H * 0.66;
        const scale = W / 200;

        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#ECEEF2';
        ctx.fillRect(0, 0, W, H);

        // Jalan
        ctx.fillStyle = '#C8CDD8';
        ctx.fillRect(0, trackY, W, H * 0.24);

        ctx.setLineDash([20, 14]);
        ctx.strokeStyle = '#5B5A68';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, trackY + H * 0.12);
        ctx.lineTo(W, trackY + H * 0.12);
        ctx.stroke();
        ctx.setLineDash([]);

        for (let m = 0; m <= 200; m += 25) {
            const mx = m * scale;
            ctx.fillStyle = '#5B5A68';
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(`${m}m`, mx, trackY - 6);
        }

        // Mobil
        const carW = 58;
        const carH = 26;
        const cy = trackY - carH - 8;
        ctx.fillStyle = '#AF1919';
        ctx.beginPath();
        ctx.roundRect(carX, cy, carW, carH, 6);
        ctx.fill();

        ctx.fillStyle = '#E8A627';
        ctx.beginPath();
        ctx.roundRect(carX + 12, cy - 12, 34, 14, 4);
        ctx.fill();

        ctx.fillStyle = '#23222B';
        ctx.beginPath();
        ctx.arc(carX + 14, cy + carH + 2, 8, 0, Math.PI * 2);
        ctx.arc(carX + carW - 14, cy + carH + 2, 8, 0, Math.PI * 2);
        ctx.fill();

        const vDisplay = simMode === 'glb' ? simV.toFixed(1) : Math.min(simV0 + simA * elapsed, 400).toFixed(1);
        const sDisplay = Math.max(0, carX / scale).toFixed(1);
        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`v = ${vDisplay} m/s  |  s = ${sDisplay} m  |  t = ${elapsed.toFixed(1)} s`, 14, 22);
    }

    function simLoop(ts) {
        if (!lastTS) lastTS = ts;
        const dt = (ts - lastTS) / 1000;
        lastTS = ts;
        simT += dt;

        const W = canvas.width;
        const scale = W / 200;

        let vNow;
        if (simMode === 'glb') {
            vNow = simV;
            carX += vNow * scale * dt;
        } else {
            vNow = simV0 + simA * simT;
            carX = (simV0 * simT + 0.5 * simA * simT * simT) * scale;
        }

        if (readS) readS.textContent = `${(carX / scale).toFixed(1)} m`;
        if (readV) readV.textContent = `${vNow.toFixed(1)} m/s`;
        if (readT) readT.textContent = `${simT.toFixed(1)} s`;
        if (readA) readA.textContent = `${(simMode === 'glb' ? 0 : simA).toFixed(1)} m/s²`;

        drawScene(simT);

        if (carX > W + 60) {
            carX = 0;
            simT = 0;
        }

        if (simRunning) {
            simAF = window.requestAnimationFrame(simLoop);
        }
    }

    function resetSim() {
        if (simAF) window.cancelAnimationFrame(simAF);
        simRunning = false;
        simT = 0;
        carX = 0;
        lastTS = null;
        if (playBtn) playBtn.textContent = 'Mulai simulasi';
        if (readS) readS.textContent = '0.0 m';
        if (readV) readV.textContent = '0.0 m/s';
        if (readT) readT.textContent = '0.0 s';
        if (readA) readA.textContent = '0.0 m/s²';
        drawScene(0);
    }

    function buildControls() {
        if (!controlsWrap) return;
        if (simMode === 'glb') {
            controlsWrap.innerHTML = `
                <div class="rf-field">
                    <label class="rf-label" for="rf-sim-glb-v">Kecepatan tetap v: <strong class="rf-num" data-lbl-v>${simV} m/s</strong></label>
                    <input type="range" id="rf-sim-glb-v" min="5" max="120" value="${simV}" data-slider-v>
                </div>
            `;
            controlsWrap.querySelector('[data-slider-v]')?.addEventListener('input', (e) => {
                simV = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-lbl-v]');
                if (lbl) lbl.textContent = `${simV} m/s`;
                drawScene(simT);
            });
        } else {
            controlsWrap.innerHTML = `
                <div class="rf-tool__grid-2">
                    <div class="rf-field">
                        <label class="rf-label" for="rf-sim-glbb-v0">Kecepatan awal v₀: <strong class="rf-num" data-lbl-v0>${simV0} m/s</strong></label>
                        <input type="range" id="rf-sim-glbb-v0" min="0" max="60" value="${simV0}" data-slider-v0>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-sim-glbb-a">Percepatan a: <strong class="rf-num" data-lbl-a>${simA} m/s²</strong></label>
                        <input type="range" id="rf-sim-glbb-a" min="2" max="50" value="${simA}" data-slider-a>
                    </div>
                </div>
            `;
            controlsWrap.querySelector('[data-slider-v0]')?.addEventListener('input', (e) => {
                simV0 = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-lbl-v0]');
                if (lbl) lbl.textContent = `${simV0} m/s`;
                drawScene(simT);
            });
            controlsWrap.querySelector('[data-slider-a]')?.addEventListener('input', (e) => {
                simA = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-lbl-a]');
                if (lbl) lbl.textContent = `${simA} m/s²`;
                drawScene(simT);
            });
        }
    }

    root.querySelectorAll('[data-sim-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            simMode = tab.getAttribute('data-sim-tab') || 'glb';
            root.querySelectorAll('[data-sim-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            buildControls();
            resetSim();
        });
    });

    playBtn?.addEventListener('click', () => {
        simRunning = !simRunning;
        if (simRunning) {
            lastTS = null;
            playBtn.textContent = 'Jeda simulasi';
            simAF = window.requestAnimationFrame(simLoop);
        } else {
            playBtn.textContent = 'Lanjutkan simulasi';
            if (simAF) window.cancelAnimationFrame(simAF);
        }
    });

    resetBtn?.addEventListener('click', resetSim);

    buildControls();
    resetSim();
}

function initGerakCalculator(root) {
    root.querySelectorAll('[data-calc-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-calc-tab');
            root.querySelectorAll('[data-calc-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            root.querySelectorAll('[data-calc-panel]').forEach((panel) => {
                if (panel.getAttribute('data-calc-panel') === target) {
                    panel.removeAttribute('hidden');
                } else {
                    panel.setAttribute('hidden', 'hidden');
                }
            });
        });
    });

    const glbS = root.querySelector('[data-glb-s]');
    const glbV = root.querySelector('[data-glb-v]');
    const glbT = root.querySelector('[data-glb-t]');
    const glbResult = root.querySelector('[data-glb-result]');

    root.querySelector('[data-glb-calc]')?.addEventListener('click', () => {
        const res = computeGLB({
            s: parseFloat(glbS?.value || ''),
            v: parseFloat(glbV?.value || ''),
            t: parseFloat(glbT?.value || ''),
        });

        if (!glbResult) return;
        if (res.error) {
            glbResult.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }

        glbResult.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Jarak (s)</span><strong>${res.sText}</strong></div>
                <div><span>Kecepatan (v)</span><strong>${res.vText}</strong></div>
                <div><span>Waktu (t)</span><strong>${res.tText}</strong></div>
            </div>
        `;
    });

    root.querySelector('[data-glb-reset]')?.addEventListener('click', () => {
        if (glbS) glbS.value = '';
        if (glbV) glbV.value = '';
        if (glbT) glbT.value = '';
        if (glbResult) glbResult.textContent = 'Masukkan minimal 2 nilai, lalu klik Hitung GLB.';
    });

    const glbbV0 = root.querySelector('[data-glbb-v0]');
    const glbbA = root.querySelector('[data-glbb-a]');
    const glbbT = root.querySelector('[data-glbb-t]');
    const glbbS = root.querySelector('[data-glbb-s]');
    const glbbResult = root.querySelector('[data-glbb-result]');

    root.querySelector('[data-glbb-calc]')?.addEventListener('click', () => {
        const v0 = parseFloat(glbbV0?.value || '');
        const a = parseFloat(glbbA?.value || '');
        const t = parseFloat(glbbT?.value || '');
        const sIn = parseFloat(glbbS?.value || '');

        const res = computeGLBB({ v0, a, t, sIn });
        if (!glbbResult) return;
        if (res.error) {
            glbbResult.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }

        glbbResult.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Kecepatan akhir (v)</span><strong>${res.vFinalText}</strong></div>
                <div><span>Jarak tempuh (s)</span><strong>${res.sFinalText}</strong></div>
                <div><span>v dari √|v₀² + 2as|</span><strong>${res.v2Text}</strong></div>
            </div>
            <p class="rf-small rf-muted mt-2 mb-0">
                v = ${v0} + ${a} × ${t} = <strong>${res.vFinalText}</strong> ·
                s = ${v0}×${t} + ½×${a}×${t}² = <strong>${res.sFinalText}</strong>
            </p>
        `;
    });

    root.querySelector('[data-glbb-reset]')?.addEventListener('click', () => {
        if (glbbV0) glbbV0.value = '';
        if (glbbA) glbbA.value = '';
        if (glbbT) glbbT.value = '';
        if (glbbS) glbbS.value = '';
        if (glbbResult) glbbResult.textContent = 'Masukkan nilai v₀, a, dan t, lalu klik Hitung GLBB.';
    });
}
