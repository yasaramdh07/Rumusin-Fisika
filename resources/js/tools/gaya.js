// Modul 3: Simulasi & Kalkulator Gaya Newton (Newton II, Resultan Gaya, Gaya Berat)
// Dibangun ulang dari legacy/page/rumus-gaya/assets/js/rumus-gaya.js dengan rumus yang persis sama.

export function computeFMA({ f, m, a }) {
    const blanks = [Number.isNaN(f), Number.isNaN(m), Number.isNaN(a)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rF = f;
    let rM = m;
    let rA = a;
    if (Number.isNaN(f)) rF = m * a;
    else if (Number.isNaN(m)) rM = f / a;
    else rA = f / m;

    return {
        f: rF,
        m: rM,
        a: rA,
        fText: `${rF.toFixed(3)} N`,
        mText: `${rM.toFixed(3)} kg`,
        aText: `${rA.toFixed(3)} m/s²`,
    };
}

export function computeBerat({ m, g = 9.8 }) {
    const gravity = Number.isNaN(g) || g === 0 ? 9.8 : g;
    if (Number.isNaN(m)) {
        return { error: 'Masukkan nilai massa terlebih dahulu.' };
    }
    const w = m * gravity;
    return {
        m,
        g: gravity,
        w,
        wText: `${w.toFixed(3)} N`,
    };
}

export function computeResultanGaya({ f1, f2, m }) {
    if (Number.isNaN(f1) || Number.isNaN(f2)) {
        return { error: 'Masukkan nilai F₁ dan F₂.' };
    }
    const fRes = f1 + f2;
    const aCalc = !Number.isNaN(m) && m > 0 ? fRes / m : null;

    return {
        f1,
        f2,
        fRes,
        a: aCalc,
        fResText: `${fRes.toFixed(3)} N`,
        aText: aCalc !== null ? `${aCalc.toFixed(3)} m/s²` : null,
    };
}

const CHECK_SVG = '<svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>';

export function mountGayaTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-gaya>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Gaya &amp; Gerak</h3>
                    <div class="rf-chips" role="group" aria-label="Mode simulasi gaya">
                        <button type="button" class="rf-chip is-active" data-g-sim="newton2" aria-pressed="true">${CHECK_SVG} Newton II</button>
                        <button type="button" class="rf-chip" data-g-sim="resultan" aria-pressed="false">${CHECK_SVG} Resultan</button>
                    </div>
                </div>

                <div class="rf-sim-stage">
                    <canvas class="rf-sim-canvas" data-gaya-canvas width="640" height="220" aria-label="Kanvas simulasi balok Hukum Newton"></canvas>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt>Gaya (F)</dt><dd data-g-read-f>0.0 N</dd></div>
                    <div><dt>Massa (m)</dt><dd data-g-read-m>0.0 kg</dd></div>
                    <div><dt>Percepatan (a)</dt><dd data-g-read-a>0.00 m/s²</dd></div>
                    <div><dt>Kecepatan (v)</dt><dd data-g-read-v>0.00 m/s</dd></div>
                </dl>

                <div class="rf-sim-controls" data-g-sim-controls></div>

                <div class="rf-tool__actions">
                    <button type="button" class="rf-btn rf-btn--primary" data-g-sim-play>Mulai simulasi</button>
                    <button type="button" class="rf-btn rf-btn--tertiary" data-g-sim-reset>Atur ulang</button>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Hukum Newton</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator gaya">
                        <button type="button" class="rf-chip is-active" data-g-tab="fma" aria-pressed="true">${CHECK_SVG} F = m · a</button>
                        <button type="button" class="rf-chip" data-g-tab="berat" aria-pressed="false">${CHECK_SVG} Berat (w = m · g)</button>
                        <button type="button" class="rf-chip" data-g-tab="res" aria-pressed="false">${CHECK_SVG} Resultan Gaya</button>
                    </div>
                </div>

                <div data-g-panel="fma">
                    <p class="rf-muted rf-small">Kosongkan tepat 1 nilai yang ingin dicari.</p>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fma-f">Gaya F (N)</label>
                            <input class="rf-input rf-num" id="rf-fma-f" type="number" step="any" placeholder="kosongkan jika dicari" data-fma-f>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fma-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-fma-m" type="number" step="any" placeholder="kosongkan jika dicari" data-fma-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fma-a">Percepatan a (m/s²)</label>
                            <input class="rf-input rf-num" id="rf-fma-a" type="number" step="any" placeholder="kosongkan jika dicari" data-fma-a>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-fma-calc>Hitung Hukum II Newton</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-fma-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-fma-res>Kosongkan satu nilai, lalu klik Hitung.</div>
                    </div>
                </div>

                <div data-g-panel="berat" hidden>
                    <div class="rf-tool__grid-2">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-berat-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-berat-m" type="number" step="any" placeholder="misal: 5" data-berat-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-berat-g">Gravitasi g (m/s²)</label>
                            <input class="rf-input rf-num" id="rf-berat-g" type="number" step="any" value="9.8" data-berat-g>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-berat-calc>Hitung Berat</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-berat-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-berat-res>Masukkan massa benda, lalu klik Hitung Berat.</div>
                    </div>
                </div>

                <div data-g-panel="res" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-res-f1">Gaya F₁ (N)</label>
                            <input class="rf-input rf-num" id="rf-res-f1" type="number" step="any" placeholder="misal: 20" data-res-f1>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-res-f2">Gaya F₂ (N, negatif bila berlawanan)</label>
                            <input class="rf-input rf-num" id="rf-res-f2" type="number" step="any" placeholder="misal: -8" data-res-f2>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-res-m">Massa m (opsional, kg)</label>
                            <input class="rf-input rf-num" id="rf-res-m" type="number" step="any" placeholder="misal: 4" data-res-m>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-res-calc>Hitung Resultan</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-res-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-res-res>Masukkan F₁ dan F₂, lalu klik Hitung Resultan.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    initGayaSimulation(container);
    initGayaCalculators(container);
}

function initGayaSimulation(root) {
    const canvas = root.querySelector('[data-gaya-canvas]');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let simMode = 'newton2';
    let simRunning = false;
    let simAF = null;
    let simT = 0;
    let objX = 0;
    let objV = 0;
    let lastTS = null;

    let simF = 20;
    let simM = 5;
    let simF1 = 15;
    let simF2 = -5;

    const readF = root.querySelector('[data-g-read-f]');
    const readM = root.querySelector('[data-g-read-m]');
    const readA = root.querySelector('[data-g-read-a]');
    const readV = root.querySelector('[data-g-read-v]');
    const playBtn = root.querySelector('[data-g-sim-play]');
    const resetBtn = root.querySelector('[data-g-sim-reset]');
    const controlsWrap = root.querySelector('[data-g-sim-controls]');

    const getFVal = () => (simMode === 'newton2' ? simF : simF1 + simF2);
    const getA = () => getFVal() / simM;

    const drawScene = (elapsed) => {
        const W = canvas.width;
        const H = canvas.height;
        const groundY = H * 0.72;
        const originX = 60;

        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#ECEEF2';
        ctx.fillRect(0, 0, W, H);

        // Lantai
        ctx.fillStyle = '#C8CDD8';
        ctx.fillRect(0, groundY, W, H - groundY);
        ctx.strokeStyle = '#5B5A68';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, groundY);
        ctx.lineTo(W, groundY);
        ctx.stroke();

        // Balok benda
        const boxW = 60;
        const boxH = 52;
        const boxX = originX + objX;
        const boxY = groundY - boxH;

        ctx.fillStyle = '#AF1919';
        ctx.beginPath();
        ctx.roundRect(boxX, boxY, boxW, boxH, 6);
        ctx.fill();

        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 13px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${simM} kg`, boxX + boxW / 2, boxY + boxH / 2 + 5);

        const fVal = getFVal();
        const a = getA();

        // Panah gaya
        if (Math.abs(fVal) > 0) {
            const arrowLen = Math.min(Math.abs(fVal) * 2.2, 90);
            const arrowDir = fVal >= 0 ? 1 : -1;
            const ax = fVal >= 0 ? boxX + boxW : boxX;
            const ay = boxY + boxH / 2;

            ctx.strokeStyle = '#E8A627';
            ctx.lineWidth = 4;
            ctx.beginPath();
            ctx.moveTo(ax, ay);
            ctx.lineTo(ax + arrowDir * arrowLen, ay);
            ctx.stroke();

            ctx.fillStyle = '#E8A627';
            ctx.beginPath();
            ctx.moveTo(ax + arrowDir * arrowLen, ay);
            ctx.lineTo(ax + arrowDir * (arrowLen - 10), ay - 6);
            ctx.lineTo(ax + arrowDir * (arrowLen - 10), ay + 6);
            ctx.closePath();
            ctx.fill();

            ctx.fillStyle = '#23222B';
            ctx.font = 'bold 12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(`F = ${fVal.toFixed(0)} N`, ax + (arrowDir * arrowLen) / 2, ay - 12);
        }

        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`a = ${a.toFixed(2)} m/s²  |  v = ${objV.toFixed(2)} m/s  |  t = ${elapsed.toFixed(1)} s`, 14, 22);
    };

    const simLoop = (ts) => {
        if (!lastTS) lastTS = ts;
        const dt = (ts - lastTS) / 1000;
        lastTS = ts;
        simT += dt;

        const a = getA();
        objV += a * dt;
        objX += objV * dt * 40;

        const maxX = canvas.width - 130;
        if (objX > maxX) {
            objX = maxX;
            objV = Math.abs(a) > 0 ? -objV * 0.4 : 0;
        }
        if (objX < 0) {
            objX = 0;
            objV = 0;
        }

        if (readF) readF.textContent = `${getFVal().toFixed(1)} N`;
        if (readM) readM.textContent = `${simM.toFixed(1)} kg`;
        if (readA) readA.textContent = `${a.toFixed(2)} m/s²`;
        if (readV) readV.textContent = `${objV.toFixed(2)} m/s`;

        drawScene(simT);
        if (simRunning) {
            simAF = window.requestAnimationFrame(simLoop);
        }
    };

    const resetSim = () => {
        if (simAF) window.cancelAnimationFrame(simAF);
        simRunning = false;
        simT = 0;
        objX = 0;
        objV = 0;
        lastTS = null;
        if (playBtn) playBtn.textContent = 'Mulai simulasi';
        if (readF) readF.textContent = `${getFVal().toFixed(1)} N`;
        if (readM) readM.textContent = `${simM.toFixed(1)} kg`;
        if (readA) readA.textContent = `${getA().toFixed(2)} m/s²`;
        if (readV) readV.textContent = '0.00 m/s';
        drawScene(0);
    };

    const buildControls = () => {
        if (!controlsWrap) return;
        if (simMode === 'newton2') {
            controlsWrap.innerHTML = `
                <div class="rf-tool__grid-2">
                    <div class="rf-field">
                        <label class="rf-label" for="rf-g-sim-f">Gaya (F): <strong class="rf-num" data-g-lbl-f>${simF} N</strong></label>
                        <input type="range" id="rf-g-sim-f" min="1" max="100" value="${simF}" data-g-slider-f>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-g-sim-m">Massa (m): <strong class="rf-num" data-g-lbl-m>${simM} kg</strong></label>
                        <input type="range" id="rf-g-sim-m" min="1" max="30" value="${simM}" data-g-slider-m>
                    </div>
                </div>
            `;
            controlsWrap.querySelector('[data-g-slider-f]')?.addEventListener('input', (e) => {
                simF = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-g-lbl-f]');
                if (lbl) lbl.textContent = `${simF} N`;
                resetSim();
            });
            controlsWrap.querySelector('[data-g-slider-m]')?.addEventListener('input', (e) => {
                simM = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-g-lbl-m]');
                if (lbl) lbl.textContent = `${simM} kg`;
                resetSim();
            });
        } else {
            controlsWrap.innerHTML = `
                <div class="rf-tool__grid-3">
                    <div class="rf-field">
                        <label class="rf-label" for="rf-g-sim-f1">Gaya 1 (F₁): <strong class="rf-num" data-g-lbl-f1>${simF1} N</strong></label>
                        <input type="range" id="rf-g-sim-f1" min="0" max="60" value="${simF1}" data-g-slider-f1>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-g-sim-f2">Gaya 2 (F₂): <strong class="rf-num" data-g-lbl-f2>${simF2} N</strong></label>
                        <input type="range" id="rf-g-sim-f2" min="-60" max="0" value="${simF2}" data-g-slider-f2>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-g-sim-m2">Massa (m): <strong class="rf-num" data-g-lbl-m2>${simM} kg</strong></label>
                        <input type="range" id="rf-g-sim-m2" min="1" max="30" value="${simM}" data-g-slider-m2>
                    </div>
                </div>
            `;
            controlsWrap.querySelector('[data-g-slider-f1]')?.addEventListener('input', (e) => {
                simF1 = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-g-lbl-f1]');
                if (lbl) lbl.textContent = `${simF1} N`;
                resetSim();
            });
            controlsWrap.querySelector('[data-g-slider-f2]')?.addEventListener('input', (e) => {
                simF2 = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-g-lbl-f2]');
                if (lbl) lbl.textContent = `${simF2} N`;
                resetSim();
            });
            controlsWrap.querySelector('[data-g-slider-m2]')?.addEventListener('input', (e) => {
                simM = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-g-lbl-m2]');
                if (lbl) lbl.textContent = `${simM} kg`;
                resetSim();
            });
        }
    };

    root.querySelectorAll('[data-g-sim]').forEach((tab) => {
        tab.addEventListener('click', () => {
            simMode = tab.getAttribute('data-g-sim') || 'newton2';
            root.querySelectorAll('[data-g-sim]').forEach((other) => {
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

function initGayaCalculators(container) {
    container.querySelectorAll('[data-g-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-g-tab');
            container.querySelectorAll('[data-g-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-g-panel]').forEach((p) => {
                if (p.getAttribute('data-g-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-fma-calc]')?.addEventListener('click', () => {
        const res = computeFMA({
            f: parseFloat(container.querySelector('[data-fma-f]')?.value || ''),
            m: parseFloat(container.querySelector('[data-fma-m]')?.value || ''),
            a: parseFloat(container.querySelector('[data-fma-a]')?.value || ''),
        });
        const box = container.querySelector('[data-fma-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Gaya (F)</span><strong>${res.fText}</strong></div>
                <div><span>Massa (m)</span><strong>${res.mText}</strong></div>
                <div><span>Percepatan (a)</span><strong>${res.aText}</strong></div>
            </div>
        `;
    });

    container.querySelector('[data-fma-reset]')?.addEventListener('click', () => {
        ['[data-fma-f]', '[data-fma-m]', '[data-fma-a]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-fma-res]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung.';
    });

    container.querySelector('[data-berat-calc]')?.addEventListener('click', () => {
        const res = computeBerat({
            m: parseFloat(container.querySelector('[data-berat-m]')?.value || ''),
            g: parseFloat(container.querySelector('[data-berat-g]')?.value || '9.8'),
        });
        const box = container.querySelector('[data-berat-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Massa (m)</span><strong>${res.m} kg</strong></div>
                <div><span>Gravitasi (g)</span><strong>${res.g} m/s²</strong></div>
                <div><span>Berat (w)</span><strong>${res.wText}</strong></div>
            </div>
        `;
    });

    container.querySelector('[data-berat-reset]')?.addEventListener('click', () => {
        const mEl = container.querySelector('[data-berat-m]');
        const gEl = container.querySelector('[data-berat-g]');
        if (mEl) mEl.value = '';
        if (gEl) gEl.value = '9.8';
        const box = container.querySelector('[data-berat-res]');
        if (box) box.textContent = 'Masukkan massa benda, lalu klik Hitung Berat.';
    });

    container.querySelector('[data-res-calc]')?.addEventListener('click', () => {
        const res = computeResultanGaya({
            f1: parseFloat(container.querySelector('[data-res-f1]')?.value || ''),
            f2: parseFloat(container.querySelector('[data-res-f2]')?.value || ''),
            m: parseFloat(container.querySelector('[data-res-m]')?.value || ''),
        });
        const box = container.querySelector('[data-res-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Resultan (ΣF)</span><strong>${res.fResText}</strong></div>
                ${res.aText ? `<div><span>Percepatan (a)</span><strong>${res.aText}</strong></div>` : ''}
            </div>
        `;
    });

    container.querySelector('[data-res-reset]')?.addEventListener('click', () => {
        ['[data-res-f1]', '[data-res-f2]', '[data-res-m]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-res-res]');
        if (box) box.textContent = 'Masukkan F₁ dan F₂, lalu klik Hitung Resultan.';
    });
}
