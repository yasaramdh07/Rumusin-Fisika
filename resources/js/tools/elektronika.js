// Modul 6: Simulasi & Kalkulator Elektronika Dasar (Simulasi Sirkuit LED & Resistor, Hukum Ohm, Resistor Pengganti Seri/Paralel)
// Dibangun ulang dari legacy/page/rumus-elektronik/assets/js/rumus-elektronik.js dengan rumus yang persis sama.

export function computeOhmElektronika({ v, i, r }) {
    const blanks = [Number.isNaN(v), Number.isNaN(i), Number.isNaN(r)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rV = v;
    let rI = i;
    let rR = r;
    if (Number.isNaN(v)) rV = i * r;
    else if (Number.isNaN(i)) rI = v / r;
    else rR = v / i;

    return {
        v: rV,
        i: rI,
        r: rR,
        vText: `${rV.toFixed(2)} V`,
        iText: `${rI.toFixed(2)} A`,
        rText: `${rR.toFixed(2)} Ω`,
    };
}

export function computeResistorPengganti({ r1, r2 }) {
    if (Number.isNaN(r1) || Number.isNaN(r2)) {
        return { error: 'Masukkan nilai R1 dan R2 dengan benar.' };
    }

    const rs = r1 + r2;
    const rp = (r1 * r2) / (r1 + r2);

    return {
        rs,
        rp,
        rsText: `${rs.toFixed(2)} Ω`,
        rpText: `${rp.toFixed(2)} Ω`,
    };
}

const CHECK_SVG = '<svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>';

export function mountElektronikaTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-elektronika>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Sirkuit: Nyala Lampu LED &amp; Resistor</h3>
                    <div class="rf-chips" role="group" aria-label="Susunan resistor simulasi elektronika">
                        <button type="button" class="rf-chip is-active" data-el-sim="tunggal" aria-pressed="true">${CHECK_SVG} 1 Resistor</button>
                        <button type="button" class="rf-chip" data-el-sim="seri" aria-pressed="false">${CHECK_SVG} Seri</button>
                        <button type="button" class="rf-chip" data-el-sim="paralel" aria-pressed="false">${CHECK_SVG} Paralel</button>
                    </div>
                </div>

                <div class="rf-sim-stage">
                    <canvas class="rf-sim-canvas" data-elektronika-canvas width="640" height="230" aria-label="Kanvas simulasi sirkuit LED dan resistor"></canvas>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt>Tegangan Sumber (V)</dt><dd data-el-read-v>9 V</dd></div>
                    <div><dt>Hambatan Total (R)</dt><dd data-el-read-r>220.0 Ω</dd></div>
                    <div><dt>Arus (I)</dt><dd data-el-read-i>31.8 mA</dd></div>
                    <div><dt>Status Lampu LED</dt><dd data-el-read-status>Menyala (100%)</dd></div>
                </dl>

                <div class="rf-sim-controls">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-sim-v">Tegangan Baterai (V): <strong class="rf-num" data-el-lbl-v>9 V</strong></label>
                            <input type="range" id="rf-el-sim-v" min="3" max="24" step="1" value="9" data-el-slider-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-sim-r1">Resistor 1 (R₁): <strong class="rf-num" data-el-lbl-r1>220 Ω</strong></label>
                            <input type="range" id="rf-el-sim-r1" min="10" max="1000" step="10" value="220" data-el-slider-r1>
                        </div>
                        <div class="rf-field" data-el-sim-r2-wrap hidden>
                            <label class="rf-label" for="rf-el-sim-r2">Resistor 2 (R₂): <strong class="rf-num" data-el-lbl-r2>220 Ω</strong></label>
                            <input type="range" id="rf-el-sim-r2" min="10" max="1000" step="10" value="220" data-el-slider-r2>
                        </div>
                    </div>
                </div>

                <div class="rf-tool__actions">
                    <button type="button" class="rf-btn rf-btn--secondary" data-el-switch-btn aria-pressed="true">Matikan Saklar</button>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Elektronika Dasar</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator elektronika">
                        <button type="button" class="rf-chip is-active" data-el-tab="ohm" aria-pressed="true">${CHECK_SVG} Hukum Ohm (V = I · R)</button>
                        <button type="button" class="rf-chip" data-el-tab="res" aria-pressed="false">${CHECK_SVG} Resistor Pengganti</button>
                    </div>
                </div>

                <div data-el-panel="ohm">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-v">Tegangan V (Volt)</label>
                            <input class="rf-input rf-num" id="rf-el-v" type="number" step="any" placeholder="kosongkan jika dicari" data-el-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-i">Arus I (Ampere)</label>
                            <input class="rf-input rf-num" id="rf-el-i" type="number" step="any" placeholder="kosongkan jika dicari" data-el-i>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-r">Hambatan R (Ω)</label>
                            <input class="rf-input rf-num" id="rf-el-r" type="number" step="any" placeholder="kosongkan jika dicari" data-el-r>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-el-ohm-calc>Hitung Ohm</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-el-ohm-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-el-ohm-out>Kosongkan satu nilai, lalu klik Hitung Ohm.</div>
                    </div>
                </div>

                <div data-el-panel="res" hidden>
                    <div class="rf-tool__grid-2">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-r1">Resistor 1 / R₁ (Ω)</label>
                            <input class="rf-input rf-num" id="rf-el-r1" type="number" step="any" placeholder="Nilai R1" data-el-r1>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-el-r2">Resistor 2 / R₂ (Ω)</label>
                            <input class="rf-input rf-num" id="rf-el-r2" type="number" step="any" placeholder="Nilai R2" data-el-r2>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-el-res-calc>Hitung R Pengganti</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-el-res-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-el-res-out>Masukkan R₁ dan R₂, lalu klik Hitung R Pengganti.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    initElektronikaSimulation(container);
    initElektronikaCalculators(container);
}

function initElektronikaSimulation(root) {
    const canvas = root.querySelector('[data-elektronika-canvas]');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let simMode = 'tunggal';
    let simV = 9;
    let simR1 = 220;
    let simR2 = 220;
    let simSwitch = true;
    const simLedV = 2;

    const readV = root.querySelector('[data-el-read-v]');
    const readR = root.querySelector('[data-el-read-r]');
    const readI = root.querySelector('[data-el-read-i]');
    const readStatus = root.querySelector('[data-el-read-status]');
    const lblV = root.querySelector('[data-el-lbl-v]');
    const lblR1 = root.querySelector('[data-el-lbl-r1]');
    const lblR2 = root.querySelector('[data-el-lbl-r2]');
    const r2Wrap = root.querySelector('[data-el-sim-r2-wrap]');
    const switchBtn = root.querySelector('[data-el-switch-btn]');

    const drawRes = (x, y, val) => {
        const rw = 44;
        const rh = 16;
        ctx.fillStyle = '#E8A627';
        ctx.strokeStyle = '#23222B';
        ctx.lineWidth = 1.5;
        ctx.fillRect(x - rw / 2, y - rh / 2, rw, rh);
        ctx.strokeRect(x - rw / 2, y - rh / 2, rw, rh);
        ctx.fillStyle = '#AF1919';
        ctx.fillRect(x - rw / 2 + 7, y - rh / 2, 4, rh);
        ctx.fillStyle = '#23222B';
        ctx.fillRect(x - rw / 2 + 18, y - rh / 2, 4, rh);
        ctx.font = 'bold 11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${val} Ω`, x, y - 13);
    };

    const drawScene = () => {
        const W = canvas.width;
        const H = canvas.height;
        const cx = W / 2;
        const cy = H / 2;
        const rectW = 280;
        const rectH = 130;
        const topY = cy - rectH / 2;
        const botY = cy + rectH / 2;
        const leftX = cx - rectW / 2;
        const rightX = cx + rectW / 2;

        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#ECEEF2';
        ctx.fillRect(0, 0, W, H);

        let rTotal = simR1;
        if (simMode === 'seri') rTotal = simR1 + simR2;
        else if (simMode === 'paralel') rTotal = (simR1 * simR2) / (simR1 + simR2);

        let currentI = 0;
        if (simSwitch && simV > simLedV) {
            currentI = (simV - simLedV) / rTotal;
        }
        const currentMa = currentI * 1000;

        // Kabel sirkuit
        ctx.strokeStyle = '#5B5A68';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(leftX, topY);
        ctx.lineTo(leftX, botY);

        if (simMode === 'tunggal' || simMode === 'seri') {
            ctx.moveTo(leftX, topY);
            ctx.lineTo(rightX, topY);
        } else {
            ctx.moveTo(leftX, topY);
            ctx.lineTo(cx - 54, topY);
            ctx.moveTo(cx + 54, topY);
            ctx.lineTo(rightX, topY);
            ctx.moveTo(cx - 54, topY - 22);
            ctx.lineTo(cx - 54, topY + 22);
            ctx.moveTo(cx + 54, topY - 22);
            ctx.lineTo(cx + 54, topY + 22);
            ctx.moveTo(cx - 54, topY - 22);
            ctx.lineTo(cx + 54, topY - 22);
            ctx.moveTo(cx - 54, topY + 22);
            ctx.lineTo(cx + 54, topY + 22);
        }

        ctx.moveTo(rightX, topY);
        ctx.lineTo(rightX, botY);
        ctx.moveTo(rightX, botY);
        ctx.lineTo(cx + 24, botY);
        ctx.moveTo(leftX, botY);
        ctx.lineTo(cx - 24, botY);
        ctx.stroke();

        // Saklar di jalur bawah
        ctx.fillStyle = '#23222B';
        ctx.beginPath();
        ctx.arc(cx - 24, botY, 4, 0, Math.PI * 2);
        ctx.arc(cx + 24, botY, 4, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = '#AF1919';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(cx - 24, botY);
        ctx.lineTo(cx + 22, simSwitch ? botY : botY - 20);
        ctx.stroke();

        // Gambar resistor
        if (simMode === 'tunggal') {
            drawRes(cx, topY, simR1);
        } else if (simMode === 'seri') {
            drawRes(cx - 42, topY, simR1);
            drawRes(cx + 42, topY, simR2);
        } else {
            drawRes(cx, topY - 22, simR1);
            drawRes(cx, topY + 22, simR2);
        }

        // Baterai (kiri)
        ctx.fillStyle = '#C8CDD8';
        ctx.fillRect(leftX - 18, cy - 22, 36, 44);
        ctx.fillStyle = '#AF1919';
        ctx.fillRect(leftX - 18, cy - 22, 36, 12);
        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${simV}V`, leftX - 38, cy + 5);

        // LED (kanan)
        const isBurned = currentMa > 40;
        const brightness = currentMa > 0 && !isBurned ? Math.min(1, currentMa / 25) : 0;
        ctx.fillStyle = isBurned ? '#AF1919' : (brightness > 0 ? '#E8A627' : '#C8CDD8');
        ctx.strokeStyle = '#23222B';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(rightX, cy, 16, 0, Math.PI * 2);
        ctx.fill();
        ctx.stroke();
        ctx.fillStyle = '#23222B';
        ctx.fillText('LED', rightX + 34, cy + 4);

        if (readV) readV.textContent = `${simV} V`;
        if (readR) readR.textContent = `${rTotal.toFixed(1)} Ω`;
        if (readI) readI.textContent = `${currentMa.toFixed(1)} mA`;
        if (readStatus) {
            if (!simSwitch) readStatus.textContent = 'Saklar Terbuka (Mati)';
            else if (currentMa <= 0) readStatus.textContent = 'Tegangan Kurang (Mati)';
            else if (isBurned) readStatus.textContent = 'PUTUS! (Arus > 40 mA)';
            else readStatus.textContent = `Menyala (${Math.round(brightness * 100)}%)`;
        }
    };

    root.querySelectorAll('[data-el-sim]').forEach((btn) => {
        btn.addEventListener('click', () => {
            simMode = btn.getAttribute('data-el-sim') || 'tunggal';
            root.querySelectorAll('[data-el-sim]').forEach((b) => {
                const active = b === btn;
                b.classList.toggle('is-active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            if (r2Wrap) {
                if (simMode === 'tunggal') r2Wrap.setAttribute('hidden', 'hidden');
                else r2Wrap.removeAttribute('hidden');
            }
            drawScene();
        });
    });

    root.querySelector('[data-el-slider-v]')?.addEventListener('input', (e) => {
        simV = Number(e.target.value);
        if (lblV) lblV.textContent = `${simV} V`;
        drawScene();
    });

    root.querySelector('[data-el-slider-r1]')?.addEventListener('input', (e) => {
        simR1 = Number(e.target.value);
        if (lblR1) lblR1.textContent = `${simR1} Ω`;
        drawScene();
    });

    root.querySelector('[data-el-slider-r2]')?.addEventListener('input', (e) => {
        simR2 = Number(e.target.value);
        if (lblR2) lblR2.textContent = `${simR2} Ω`;
        drawScene();
    });

    switchBtn?.addEventListener('click', () => {
        simSwitch = !simSwitch;
        switchBtn.setAttribute('aria-pressed', simSwitch ? 'true' : 'false');
        switchBtn.textContent = simSwitch ? 'Matikan Saklar' : 'Nyalakan Saklar';
        drawScene();
    });

    drawScene();
}

function initElektronikaCalculators(container) {
    container.querySelectorAll('[data-el-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-el-tab');
            container.querySelectorAll('[data-el-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-el-panel]').forEach((p) => {
                if (p.getAttribute('data-el-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-el-ohm-calc]')?.addEventListener('click', () => {
        const res = computeOhmElektronika({
            v: parseFloat(container.querySelector('[data-el-v]')?.value || ''),
            i: parseFloat(container.querySelector('[data-el-i]')?.value || ''),
            r: parseFloat(container.querySelector('[data-el-r]')?.value || ''),
        });
        const box = container.querySelector('[data-el-ohm-out]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Tegangan (V)</span><strong>${res.vText}</strong></div><div><span>Arus (I)</span><strong>${res.iText}</strong></div><div><span>Hambatan (R)</span><strong>${res.rText}</strong></div></div>`;
    });

    container.querySelector('[data-el-ohm-reset]')?.addEventListener('click', () => {
        ['[data-el-v]', '[data-el-i]', '[data-el-r]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-el-ohm-out]');
        if (box) box.textContent = 'Kosongkan satu nilai, lalu klik Hitung Ohm.';
    });

    container.querySelector('[data-el-res-calc]')?.addEventListener('click', () => {
        const res = computeResistorPengganti({
            r1: parseFloat(container.querySelector('[data-el-r1]')?.value || ''),
            r2: parseFloat(container.querySelector('[data-el-r2]')?.value || ''),
        });
        const box = container.querySelector('[data-el-res-out]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Jika Diseri (Rs)</span><strong>${res.rsText}</strong></div><div><span>Jika Diparalel (Rp)</span><strong>${res.rpText}</strong></div></div>`;
    });

    container.querySelector('[data-el-res-reset]')?.addEventListener('click', () => {
        ['[data-el-r1]', '[data-el-r2]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-el-res-out]');
        if (box) box.textContent = 'Masukkan R₁ dan R₂, lalu klik Hitung R Pengganti.';
    });
}
