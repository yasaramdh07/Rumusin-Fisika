// Modul 4: Simulasi & Kalkulator Energi (Usaha Mendorong, Jatuh Bebas Ep -> Ek, Energi Kinetik, Energi Potensial, Usaha)
// Dibangun ulang dari legacy/page/rumus-energi/assets/js/rumus-energi.js dengan rumus yang persis sama.

export function computeEnergiKinetik({ ek, m, v }) {
    const blanks = [Number.isNaN(ek), Number.isNaN(m), Number.isNaN(v)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rEk = ek;
    let rM = m;
    let rV = v;
    if (Number.isNaN(ek)) rEk = 0.5 * m * (v * v);
    else if (Number.isNaN(m)) rM = (2 * ek) / (v * v);
    else rV = Math.sqrt((2 * ek) / m);

    return {
        ek: rEk,
        m: rM,
        v: rV,
        ekText: `${rEk.toFixed(3)} J`,
        mText: `${rM.toFixed(3)} kg`,
        vText: `${rV.toFixed(3)} m/s`,
    };
}

export function computeEnergiPotensial({ ep, m, h }) {
    const g = 9.8;
    const blanks = [Number.isNaN(ep), Number.isNaN(m), Number.isNaN(h)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rEp = ep;
    let rM = m;
    let rH = h;
    if (Number.isNaN(ep)) rEp = m * g * h;
    else if (Number.isNaN(m)) rM = ep / (g * h);
    else rH = ep / (m * g);

    return {
        ep: rEp,
        m: rM,
        h: rH,
        epText: `${rEp.toFixed(3)} J`,
        mText: `${rM.toFixed(3)} kg`,
        hText: `${rH.toFixed(3)} m`,
    };
}

export function computeUsaha({ w, f, s }) {
    const blanks = [Number.isNaN(w), Number.isNaN(f), Number.isNaN(s)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rW = w;
    let rF = f;
    let rS = s;
    if (Number.isNaN(w)) rW = f * s;
    else if (Number.isNaN(f)) rF = w / s;
    else rS = w / f;

    return {
        w: rW,
        f: rF,
        s: rS,
        wText: `${rW.toFixed(3)} J`,
        fText: `${rF.toFixed(3)} N`,
        sText: `${rS.toFixed(3)} m`,
    };
}

const CHECK_SVG = '<svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>';

export function mountEnergiTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-energi>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Energi &amp; Usaha</h3>
                    <div class="rf-chips" role="group" aria-label="Mode simulasi energi">
                        <button type="button" class="rf-chip is-active" data-e-sim="usaha" aria-pressed="true">${CHECK_SVG} Usaha Mendorong</button>
                        <button type="button" class="rf-chip" data-e-sim="ep_ek" aria-pressed="false">${CHECK_SVG} Jatuh Bebas (Ep → Ek)</button>
                    </div>
                </div>

                <div class="rf-sim-stage">
                    <canvas class="rf-sim-canvas" data-energi-canvas width="640" height="230" aria-label="Kanvas simulasi usaha dan energi"></canvas>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt data-e-lbl-1>Usaha (W)</dt><dd data-e-read-1>0.0 J</dd></div>
                    <div><dt data-e-lbl-2>Jarak (s)</dt><dd data-e-read-2>0.00 m</dd></div>
                    <div><dt data-e-lbl-3>Kecepatan (v)</dt><dd data-e-read-3>0.00 m/s</dd></div>
                    <div><dt data-e-lbl-4>Daya (P)</dt><dd data-e-read-4>0.0 W</dd></div>
                </dl>

                <div class="rf-sim-controls" data-e-sim-controls></div>

                <div class="rf-tool__actions">
                    <button type="button" class="rf-btn rf-btn--primary" data-e-sim-play>Mulai simulasi</button>
                    <button type="button" class="rf-btn rf-btn--tertiary" data-e-sim-reset>Atur ulang</button>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Usaha dan Energi</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator energi">
                        <button type="button" class="rf-chip is-active" data-e-tab="ek" aria-pressed="true">${CHECK_SVG} Energi Kinetik</button>
                        <button type="button" class="rf-chip" data-e-tab="ep" aria-pressed="false">${CHECK_SVG} Energi Potensial</button>
                        <button type="button" class="rf-chip" data-e-tab="usaha" aria-pressed="false">${CHECK_SVG} Usaha (W = F · s)</button>
                    </div>
                </div>

                <div data-e-panel="ek">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ek-ek">Energi Kinetik Ek (J)</label>
                            <input class="rf-input rf-num" id="rf-ek-ek" type="number" step="any" placeholder="kosongkan jika dicari" data-ek-ek>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ek-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-ek-m" type="number" step="any" placeholder="kosongkan jika dicari" data-ek-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ek-v">Kecepatan v (m/s)</label>
                            <input class="rf-input rf-num" id="rf-ek-v" type="number" step="any" placeholder="kosongkan jika dicari" data-ek-v>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-ek-calc>Hitung Ek</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-ek-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-ek-res>Kosongkan satu nilai, lalu klik Hitung Ek.</div>
                    </div>
                </div>

                <div data-e-panel="ep" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ep-ep">Energi Potensial Ep (J)</label>
                            <input class="rf-input rf-num" id="rf-ep-ep" type="number" step="any" placeholder="kosongkan jika dicari" data-ep-ep>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ep-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-ep-m" type="number" step="any" placeholder="kosongkan jika dicari" data-ep-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ep-h">Ketinggian h (m, g = 9.8)</label>
                            <input class="rf-input rf-num" id="rf-ep-h" type="number" step="any" placeholder="kosongkan jika dicari" data-ep-h>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-ep-calc>Hitung Ep</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-ep-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-ep-res>Kosongkan satu nilai, lalu klik Hitung Ep.</div>
                    </div>
                </div>

                <div data-e-panel="usaha" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-us-w">Usaha W (J)</label>
                            <input class="rf-input rf-num" id="rf-us-w" type="number" step="any" placeholder="kosongkan jika dicari" data-us-w>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-us-f">Gaya F (N)</label>
                            <input class="rf-input rf-num" id="rf-us-f" type="number" step="any" placeholder="kosongkan jika dicari" data-us-f>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-us-s">Perpindahan s (m)</label>
                            <input class="rf-input rf-num" id="rf-us-s" type="number" step="any" placeholder="kosongkan jika dicari" data-us-s>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-us-calc>Hitung Usaha</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-us-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-us-res>Kosongkan satu nilai, lalu klik Hitung Usaha.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    initEnergiSimulation(container);
    initEnergiCalculators(container);
}

function initEnergiSimulation(root) {
    const canvas = root.querySelector('[data-energi-canvas]');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let simMode = 'usaha';
    let simRunning = false;
    let simAF = null;
    let simT = 0;
    let lastTS = null;

    // Mode Usaha Mendorong
    let usF = 50;
    let usM = 10;
    let usS = 0;
    let usV = 0;

    // Mode Jatuh Bebas (Ep -> Ek)
    let epekH0 = 10;
    let epekH = 10;
    let epekM = 5;
    let epekV = 0;

    const lbl1 = root.querySelector('[data-e-lbl-1]');
    const lbl2 = root.querySelector('[data-e-lbl-2]');
    const lbl3 = root.querySelector('[data-e-lbl-3]');
    const lbl4 = root.querySelector('[data-e-lbl-4]');
    const read1 = root.querySelector('[data-e-read-1]');
    const read2 = root.querySelector('[data-e-read-2]');
    const read3 = root.querySelector('[data-e-read-3]');
    const read4 = root.querySelector('[data-e-read-4]');
    const playBtn = root.querySelector('[data-e-sim-play]');
    const resetBtn = root.querySelector('[data-e-sim-reset]');
    const controlsWrap = root.querySelector('[data-e-sim-controls]');

    const drawScene = (elapsed) => {
        const W = canvas.width;
        const H = canvas.height;

        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#ECEEF2';
        ctx.fillRect(0, 0, W, H);

        if (simMode === 'usaha') {
            const groundY = H * 0.72;
            ctx.fillStyle = '#C8CDD8';
            ctx.fillRect(0, groundY, W, H - groundY);
            ctx.strokeStyle = '#5B5A68';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(0, groundY);
            ctx.lineTo(W, groundY);
            ctx.stroke();

            const originX = 60;
            const scale = (W - 140) / 20;
            const boxW = 54;
            const boxH = 42;
            const boxX = originX + usS * scale;
            const boxY = groundY - boxH;

            ctx.fillStyle = '#AF1919';
            ctx.beginPath();
            ctx.roundRect(boxX, boxY, boxW, boxH, 5);
            ctx.fill();
            ctx.fillStyle = '#FFFFFF';
            ctx.font = 'bold 12px sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(`${usM} kg`, boxX + boxW / 2, boxY + boxH / 2 + 4);

            // Panah dorongan
            if (usF > 0) {
                const ax = boxX;
                const ay = boxY + boxH / 2;
                ctx.strokeStyle = '#E8A627';
                ctx.lineWidth = 4;
                ctx.beginPath();
                ctx.moveTo(ax - 42, ay);
                ctx.lineTo(ax - 6, ay);
                ctx.stroke();
                ctx.fillStyle = '#E8A627';
                ctx.beginPath();
                ctx.moveTo(ax - 6, ay);
                ctx.lineTo(ax - 14, ay - 6);
                ctx.lineTo(ax - 14, ay + 6);
                ctx.closePath();
                ctx.fill();
            }

            const wVal = usF * usS;
            const pVal = elapsed > 0 ? wVal / elapsed : 0;
            ctx.fillStyle = '#23222B';
            ctx.font = 'bold 12px sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText(`W = ${wVal.toFixed(1)} J  |  s = ${usS.toFixed(1)} m  |  P = ${pVal.toFixed(1)} W  |  t = ${elapsed.toFixed(1)} s`, 14, 22);
        } else {
            const groundY = H - 28;
            ctx.fillStyle = '#C8CDD8';
            ctx.fillRect(0, groundY, W, 28);

            const ballX = W / 2;
            const scale = (groundY - 52) / Math.max(1, epekH0);
            const ballY = groundY - epekH * scale;
            const R = 16;

            ctx.fillStyle = '#E8A627';
            ctx.beginPath();
            ctx.arc(ballX, ballY - R, R, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#AF1919';
            ctx.lineWidth = 3;
            ctx.stroke();

            ctx.strokeStyle = '#5B5A68';
            ctx.setLineDash([4, 4]);
            ctx.beginPath();
            ctx.moveTo(ballX + 26, groundY);
            ctx.lineTo(ballX + 26, ballY - R);
            ctx.stroke();
            ctx.setLineDash([]);

            ctx.fillStyle = '#23222B';
            ctx.font = 'bold 11px sans-serif';
            ctx.textAlign = 'left';
            ctx.fillText(`h = ${epekH.toFixed(1)} m`, ballX + 34, ballY - R + 4);

            const Ep = epekM * 9.8 * epekH;
            const Ek = 0.5 * epekM * epekV * epekV;
            const Em = Ep + Ek;
            ctx.font = 'bold 12px sans-serif';
            ctx.fillText(`Ep = ${Ep.toFixed(0)} J  |  Ek = ${Ek.toFixed(0)} J  |  Em = ${Em.toFixed(0)} J  |  v = ${epekV.toFixed(1)} m/s`, 14, 22);
        }
    };

    const simLoop = (ts) => {
        if (!lastTS) lastTS = ts;
        const dt = (ts - lastTS) / 1000;
        lastTS = ts;
        simT += dt;

        if (simMode === 'usaha') {
            const a = usF / usM;
            usV += a * dt;
            usS += usV * dt;
            if (usS > 20) {
                usS = 0;
                usV = 0;
                simT = 0;
            }
            if (read1) read1.textContent = `${(usF * usS).toFixed(1)} J`;
            if (read2) read2.textContent = `${usS.toFixed(2)} m`;
            if (read3) read3.textContent = `${usV.toFixed(2)} m/s`;
            if (read4) read4.textContent = `${(simT > 0 ? (usF * usS) / simT : 0).toFixed(1)} W`;
        } else {
            epekV += 9.8 * dt;
            epekH -= epekV * dt;
            if (epekH <= 0) {
                epekH = 0;
                simRunning = false;
                if (playBtn) playBtn.textContent = 'Ulangi jatuh bebas';
            }
            const Ep = epekM * 9.8 * epekH;
            const Ek = 0.5 * epekM * epekV * epekV;
            if (read1) read1.textContent = `${Ep.toFixed(1)} J`;
            if (read2) read2.textContent = `${Ek.toFixed(1)} J`;
            if (read3) read3.textContent = `${epekV.toFixed(2)} m/s`;
            if (read4) read4.textContent = `${epekH.toFixed(2)} m`;
        }

        drawScene(simT);
        if (simRunning) {
            simAF = window.requestAnimationFrame(simLoop);
        }
    };

    const resetSim = () => {
        if (simAF) window.cancelAnimationFrame(simAF);
        simRunning = false;
        simT = 0;
        lastTS = null;
        if (playBtn) playBtn.textContent = 'Mulai simulasi';

        if (simMode === 'usaha') {
            usS = 0;
            usV = 0;
            if (lbl1) lbl1.textContent = 'Usaha (W)';
            if (lbl2) lbl2.textContent = 'Jarak (s)';
            if (lbl3) lbl3.textContent = 'Kecepatan (v)';
            if (lbl4) lbl4.textContent = 'Daya (P)';
            if (read1) read1.textContent = '0.0 J';
            if (read2) read2.textContent = '0.00 m';
            if (read3) read3.textContent = '0.00 m/s';
            if (read4) read4.textContent = '0.0 W';
        } else {
            epekH = epekH0;
            epekV = 0;
            const Ep = epekM * 9.8 * epekH;
            if (lbl1) lbl1.textContent = 'Ep';
            if (lbl2) lbl2.textContent = 'Ek';
            if (lbl3) lbl3.textContent = 'Kecepatan (v)';
            if (lbl4) lbl4.textContent = 'Tinggi (h)';
            if (read1) read1.textContent = `${Ep.toFixed(1)} J`;
            if (read2) read2.textContent = '0.0 J';
            if (read3) read3.textContent = '0.00 m/s';
            if (read4) read4.textContent = `${epekH.toFixed(1)} m`;
        }

        drawScene(0);
    };

    const buildControls = () => {
        if (!controlsWrap) return;
        if (simMode === 'usaha') {
            controlsWrap.innerHTML = `
                <div class="rf-tool__grid-2">
                    <div class="rf-field">
                        <label class="rf-label" for="rf-e-sim-f">Gaya Dorong (F): <strong class="rf-num" data-e-lbl-f>${usF} N</strong></label>
                        <input type="range" id="rf-e-sim-f" min="10" max="200" value="${usF}" data-e-slider-f>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-e-sim-m">Massa (m): <strong class="rf-num" data-e-lbl-m>${usM} kg</strong></label>
                        <input type="range" id="rf-e-sim-m" min="1" max="50" value="${usM}" data-e-slider-m>
                    </div>
                </div>
            `;
            controlsWrap.querySelector('[data-e-slider-f]')?.addEventListener('input', (e) => {
                usF = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-e-lbl-f]');
                if (lbl) lbl.textContent = `${usF} N`;
                resetSim();
            });
            controlsWrap.querySelector('[data-e-slider-m]')?.addEventListener('input', (e) => {
                usM = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-e-lbl-m]');
                if (lbl) lbl.textContent = `${usM} kg`;
                resetSim();
            });
        } else {
            controlsWrap.innerHTML = `
                <div class="rf-tool__grid-2">
                    <div class="rf-field">
                        <label class="rf-label" for="rf-e-sim-h0">Tinggi Awal (h₀): <strong class="rf-num" data-e-lbl-h0>${epekH0} m</strong></label>
                        <input type="range" id="rf-e-sim-h0" min="5" max="50" value="${epekH0}" data-e-slider-h0>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-e-sim-m2">Massa (m): <strong class="rf-num" data-e-lbl-m2>${epekM} kg</strong></label>
                        <input type="range" id="rf-e-sim-m2" min="1" max="20" value="${epekM}" data-e-slider-m2>
                    </div>
                </div>
            `;
            controlsWrap.querySelector('[data-e-slider-h0]')?.addEventListener('input', (e) => {
                epekH0 = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-e-lbl-h0]');
                if (lbl) lbl.textContent = `${epekH0} m`;
                resetSim();
            });
            controlsWrap.querySelector('[data-e-slider-m2]')?.addEventListener('input', (e) => {
                epekM = Number(e.target.value);
                const lbl = controlsWrap.querySelector('[data-e-lbl-m2]');
                if (lbl) lbl.textContent = `${epekM} kg`;
                resetSim();
            });
        }
    };

    root.querySelectorAll('[data-e-sim]').forEach((tab) => {
        tab.addEventListener('click', () => {
            simMode = tab.getAttribute('data-e-sim') || 'usaha';
            root.querySelectorAll('[data-e-sim]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            buildControls();
            resetSim();
        });
    });

    playBtn?.addEventListener('click', () => {
        if (simMode === 'ep_ek' && epekH <= 0) {
            resetSim();
            return;
        }
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

function initEnergiCalculators(container) {
    container.querySelectorAll('[data-e-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-e-tab');
            container.querySelectorAll('[data-e-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-e-panel]').forEach((p) => {
                if (p.getAttribute('data-e-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-ek-calc]')?.addEventListener('click', () => {
        const res = computeEnergiKinetik({
            ek: parseFloat(container.querySelector('[data-ek-ek]')?.value || ''),
            m: parseFloat(container.querySelector('[data-ek-m]')?.value || ''),
            v: parseFloat(container.querySelector('[data-ek-v]')?.value || ''),
        });
        const box = container.querySelector('[data-ek-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Ek</span><strong>${res.ekText}</strong></div><div><span>Massa (m)</span><strong>${res.mText}</strong></div><div><span>Kecepatan (v)</span><strong>${res.vText}</strong></div></div>`;
    });

    container.querySelector('[data-ek-reset]')?.addEventListener('click', () => {
        ['[data-ek-ek]', '[data-ek-m]', '[data-ek-v]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-ek-res]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung Ek.';
    });

    container.querySelector('[data-ep-calc]')?.addEventListener('click', () => {
        const res = computeEnergiPotensial({
            ep: parseFloat(container.querySelector('[data-ep-ep]')?.value || ''),
            m: parseFloat(container.querySelector('[data-ep-m]')?.value || ''),
            h: parseFloat(container.querySelector('[data-ep-h]')?.value || ''),
        });
        const box = container.querySelector('[data-ep-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Ep</span><strong>${res.epText}</strong></div><div><span>Massa (m)</span><strong>${res.mText}</strong></div><div><span>Tinggi (h)</span><strong>${res.hText}</strong></div></div>`;
    });

    container.querySelector('[data-ep-reset]')?.addEventListener('click', () => {
        ['[data-ep-ep]', '[data-ep-m]', '[data-ep-h]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-ep-res]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung Ep.';
    });

    container.querySelector('[data-us-calc]')?.addEventListener('click', () => {
        const res = computeUsaha({
            w: parseFloat(container.querySelector('[data-us-w]')?.value || ''),
            f: parseFloat(container.querySelector('[data-us-f]')?.value || ''),
            s: parseFloat(container.querySelector('[data-us-s]')?.value || ''),
        });
        const box = container.querySelector('[data-us-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Usaha (W)</span><strong>${res.wText}</strong></div><div><span>Gaya (F)</span><strong>${res.fText}</strong></div><div><span>Jarak (s)</span><strong>${res.sText}</strong></div></div>`;
    });

    container.querySelector('[data-us-reset]')?.addEventListener('click', () => {
        ['[data-us-w]', '[data-us-f]', '[data-us-s]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-us-res]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung Usaha.';
    });
}
