// Modul 5: Simulasi & Kalkulator Hukum Archimedes (Uji Coba Massa Jenis Kapal Selam, Gaya Apung, Massa Jenis)
// Dibangun ulang dari legacy/page/rumus-archimedes/assets/js/rumus-archimedes.js dengan rumus yang persis sama.

export function computeGayaApung({ fa, rho, v }) {
    const g = 9.8;
    const blanks = [Number.isNaN(fa), Number.isNaN(rho), Number.isNaN(v)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rFa = fa;
    let rRho = rho;
    let rV = v;
    if (Number.isNaN(fa)) rFa = rho * g * v;
    else if (Number.isNaN(rho)) rRho = fa / (g * v);
    else rV = fa / (rho * g);

    return {
        fa: rFa,
        rho: rRho,
        v: rV,
        faText: `${rFa.toFixed(2)} N`,
        rhoText: `${rRho.toFixed(2)} kg/m³`,
        vText: `${rV.toFixed(4)} m³`,
    };
}

export function computeMassaJenis({ rho, m, v }) {
    const blanks = [Number.isNaN(rho), Number.isNaN(m), Number.isNaN(v)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rRho = rho;
    let rM = m;
    let rV = v;
    if (Number.isNaN(rho)) rRho = m / v;
    else if (Number.isNaN(m)) rM = rho * v;
    else rV = m / rho;

    return {
        rho: rRho,
        m: rM,
        v: rV,
        rhoText: `${rRho.toFixed(2)} kg/m³`,
        mText: `${rM.toFixed(2)} kg`,
        vText: `${rV.toFixed(4)} m³`,
    };
}

const CHECK_SVG = '<svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>';

export function mountArchimedesTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-archimedes>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Uji Coba Massa Jenis (Terapung / Melayang / Tenggelam)</h3>
                </div>

                <div class="rf-sim-stage">
                    <canvas class="rf-sim-canvas" data-archimedes-canvas width="640" height="240" aria-label="Kanvas simulasi Hukum Archimedes"></canvas>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt>ρ Benda</dt><dd data-a-read-rho>500 kg/m³</dd></div>
                    <div><dt>Gaya Berat (w)</dt><dd data-a-read-w>49.0 N</dd></div>
                    <div><dt>Gaya Apung (Fa)</dt><dd data-a-read-fa>49.0 N</dd></div>
                    <div><dt>Status</dt><dd data-a-read-status>Terapung</dd></div>
                </dl>

                <div class="rf-sim-controls">
                    <div class="rf-tool__grid-2">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-a-sim-m">Massa Benda (m): <strong class="rf-num" data-a-lbl-m>5 kg</strong></label>
                            <input type="range" id="rf-a-sim-m" min="1" max="20" step="0.5" value="5" data-a-slider-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-a-sim-v">Volume Benda (V): <strong class="rf-num" data-a-lbl-v>0.010 m³</strong></label>
                            <input type="range" id="rf-a-sim-v" min="0.002" max="0.02" step="0.001" value="0.01" data-a-slider-v>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Hukum Archimedes</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator Archimedes">
                        <button type="button" class="rf-chip is-active" data-a-tab="fa" aria-pressed="true">${CHECK_SVG} Gaya Apung (Fa = ρ · g · V)</button>
                        <button type="button" class="rf-chip" data-a-tab="rho" aria-pressed="false">${CHECK_SVG} Massa Jenis (ρ = m / V)</button>
                    </div>
                </div>

                <div data-a-panel="fa">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fa-fa">Gaya Apung Fa (N)</label>
                            <input class="rf-input rf-num" id="rf-fa-fa" type="number" step="any" placeholder="kosongkan jika dicari" data-fa-fa>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fa-rho">ρ Fluida (kg/m³)</label>
                            <input class="rf-input rf-num" id="rf-fa-rho" type="number" step="any" placeholder="kosongkan jika dicari" data-fa-rho>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fa-v">Volume Tercelup V (m³)</label>
                            <input class="rf-input rf-num" id="rf-fa-v" type="number" step="any" placeholder="kosongkan jika dicari" data-fa-v>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-fa-calc>Hitung Gaya Apung</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-fa-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-fa-res>Kosongkan satu nilai, lalu klik Hitung Gaya Apung (g = 9.8 m/s²).</div>
                    </div>
                </div>

                <div data-a-panel="rho" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-rho-rho">Massa Jenis ρ (kg/m³)</label>
                            <input class="rf-input rf-num" id="rf-rho-rho" type="number" step="any" placeholder="kosongkan jika dicari" data-rho-rho>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-rho-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-rho-m" type="number" step="any" placeholder="kosongkan jika dicari" data-rho-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-rho-v">Volume V (m³)</label>
                            <input class="rf-input rf-num" id="rf-rho-v" type="number" step="any" placeholder="kosongkan jika dicari" data-rho-v>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-rho-calc>Hitung Massa Jenis</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-rho-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-rho-res>Kosongkan satu nilai, lalu klik Hitung Massa Jenis.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    initArchimedesSimulation(container);
    initArchimedesCalculators(container);
}

function initArchimedesSimulation(root) {
    const canvas = root.querySelector('[data-archimedes-canvas]');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let simM = 5;
    let simV = 0.01;
    const rhoAir = 1000;
    let blockY = 60;

    const readRho = root.querySelector('[data-a-read-rho]');
    const readW = root.querySelector('[data-a-read-w]');
    const readFa = root.querySelector('[data-a-read-fa]');
    const readStatus = root.querySelector('[data-a-read-status]');
    const lblM = root.querySelector('[data-a-lbl-m]');
    const lblV = root.querySelector('[data-a-lbl-v]');

    const drawScene = () => {
        const W = canvas.width;
        const H = canvas.height;
        const waterY = H * 0.38;

        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#ECEEF2';
        ctx.fillRect(0, 0, W, H);

        // Kolam air
        ctx.fillStyle = 'rgba(23, 107, 72, 0.16)';
        ctx.fillRect(0, waterY, W, H - waterY);
        ctx.strokeStyle = '#176B48';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(0, waterY);
        ctx.lineTo(W, waterY);
        ctx.stroke();

        const rhoBlock = simM / simV;
        const baseS = Math.max(40, Math.min(86, 58 * Math.cbrt(simV / 0.01)));
        const subW = baseS * 1.8;
        const subH = baseS * 0.8;
        const subX = W / 2 - subW / 2;

        let targetY;
        if (rhoBlock < rhoAir) {
            const submergedRatio = rhoBlock / rhoAir;
            targetY = waterY - subH + submergedRatio * subH;
        } else if (Math.abs(rhoBlock - rhoAir) < 1e-6) {
            targetY = waterY + (H - waterY) / 2 - subH / 2;
        } else {
            targetY = H - subH - 12;
        }

        blockY += (targetY - blockY) * 0.12;
        const subY = blockY;

        // Menara kapal selam
        ctx.fillStyle = '#8A1010';
        ctx.beginPath();
        ctx.roundRect(subX + subW * 0.4, subY - subH * 0.36, subW * 0.25, subH * 0.45, 4);
        ctx.fill();

        // Badan kapal selam
        ctx.fillStyle = '#AF1919';
        ctx.beginPath();
        ctx.roundRect(subX, subY, subW, subH, subH / 2);
        ctx.fill();

        // Jendela kapal selam
        ctx.fillStyle = '#E8A627';
        ctx.beginPath();
        ctx.arc(subX + subW * 0.74, subY + subH / 2, subH * 0.22, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${simM} kg`, subX + subW * 0.42, subY + subH / 2 + 4);

        const w = simM * 9.8;
        let vCelup = 0;
        if (subY >= waterY) vCelup = simV;
        else if (subY + subH > waterY) {
            const ratio = Math.min(1, Math.max(0, (subY + subH - waterY) / subH));
            vCelup = simV * ratio;
        }
        const Fa = rhoAir * 9.8 * vCelup;

        if (readRho) readRho.textContent = `${rhoBlock.toFixed(0)} kg/m³`;
        if (readW) readW.textContent = `${w.toFixed(1)} N`;
        if (readFa) readFa.textContent = `${Fa.toFixed(1)} N`;
        if (readStatus) {
            if (rhoBlock < rhoAir) readStatus.textContent = 'Terapung';
            else if (rhoBlock > rhoAir) readStatus.textContent = 'Tenggelam';
            else readStatus.textContent = 'Melayang';
        }

        if (document.body.contains(canvas)) {
            window.requestAnimationFrame(drawScene);
        }
    };

    root.querySelector('[data-a-slider-m]')?.addEventListener('input', (e) => {
        simM = Number(e.target.value);
        if (lblM) lblM.textContent = `${simM} kg`;
    });

    root.querySelector('[data-a-slider-v]')?.addEventListener('input', (e) => {
        simV = Number(e.target.value);
        if (lblV) lblV.textContent = `${simV.toFixed(3)} m³`;
    });

    drawScene();
}

function initArchimedesCalculators(container) {
    container.querySelectorAll('[data-a-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-a-tab');
            container.querySelectorAll('[data-a-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-a-panel]').forEach((p) => {
                if (p.getAttribute('data-a-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-fa-calc]')?.addEventListener('click', () => {
        const res = computeGayaApung({
            fa: parseFloat(container.querySelector('[data-fa-fa]')?.value || ''),
            rho: parseFloat(container.querySelector('[data-fa-rho]')?.value || ''),
            v: parseFloat(container.querySelector('[data-fa-v]')?.value || ''),
        });
        const box = container.querySelector('[data-fa-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Fa</span><strong>${res.faText}</strong></div><div><span>ρ Fluida</span><strong>${res.rhoText}</strong></div><div><span>Volume (V)</span><strong>${res.vText}</strong></div></div>`;
    });

    container.querySelector('[data-fa-reset]')?.addEventListener('click', () => {
        ['[data-fa-fa]', '[data-fa-rho]', '[data-fa-v]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-fa-res]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung Gaya Apung (g = 9.8 m/s²).';
    });

    container.querySelector('[data-rho-calc]')?.addEventListener('click', () => {
        const res = computeMassaJenis({
            rho: parseFloat(container.querySelector('[data-rho-rho]')?.value || ''),
            m: parseFloat(container.querySelector('[data-rho-m]')?.value || ''),
            v: parseFloat(container.querySelector('[data-rho-v]')?.value || ''),
        });
        const box = container.querySelector('[data-rho-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Massa Jenis (ρ)</span><strong>${res.rhoText}</strong></div><div><span>Massa (m)</span><strong>${res.mText}</strong></div><div><span>Volume (V)</span><strong>${res.vText}</strong></div></div>`;
    });

    container.querySelector('[data-rho-reset]')?.addEventListener('click', () => {
        ['[data-rho-rho]', '[data-rho-m]', '[data-rho-v]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-rho-res]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung Massa Jenis.';
    });
}
