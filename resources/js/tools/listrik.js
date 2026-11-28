// Modul 2: Simulasi & Kalkulator Listrik (Hukum Ohm, Daya Listrik, Tagihan Rumahan + Pie Chart kWh)
// Dibangun ulang dari legacy/page/rumus-listrik/assets/js/rumus-listrik.js dengan rumus yang persis sama.

export function computeOhmListrik({ v, i, r }) {
    const known = [!Number.isNaN(v), !Number.isNaN(i), !Number.isNaN(r)];
    if (known.filter(Boolean).length < 2) {
        return { error: 'Masukkan minimal 2 nilai (kosongkan 1 nilai yang dicari).' };
    }

    let rv = v;
    let ri = i;
    let rr = r;
    if (Number.isNaN(rv)) rv = ri * rr;
    if (Number.isNaN(ri)) ri = rv / rr;
    if (Number.isNaN(rr)) rr = rv / ri;

    return {
        v: rv,
        i: ri,
        r: rr,
        vText: `${rv.toFixed(1)} V`,
        iText: `${ri.toFixed(3)} A`,
        rText: `${rr.toFixed(1)} Ω`,
    };
}

export function computeDayaListrik({ v, i, r }) {
    let p = 0;
    if (!Number.isNaN(v) && !Number.isNaN(i)) p = v * i;
    else if (!Number.isNaN(i) && !Number.isNaN(r)) p = i * i * r;
    else if (!Number.isNaN(v) && !Number.isNaN(r)) p = (v * v) / r;

    if (p === 0) {
        return { error: 'Masukkan pasangan V & I, I & R, atau V & R.' };
    }

    return {
        p,
        wattText: `${Math.round(p).toLocaleString('id-ID')} W`,
        kwText: `${(p / 1000).toFixed(2)} kW`,
    };
}

export function computeTagihanRumahan({ alatList, tarifKwh = 1447, biayaTetap = 12500 }) {
    let totalKwh = 0;
    const breakdown = [];

    alatList.forEach((alat) => {
        const kwh = (Number(alat.watt) * Number(alat.jam) * 30) / 1000;
        const cleanKwh = Number.isNaN(kwh) ? 0 : kwh;
        totalKwh += cleanKwh;
        if (cleanKwh > 0) {
            breakdown.push({ nama: alat.nama, kwh: cleanKwh });
        }
    });

    breakdown.sort((a, b) => b.kwh - a.kwh);

    const biayaKwh = totalKwh * tarifKwh;
    const totalBayar = biayaTetap + biayaKwh;
    const ppn = totalBayar * 0.11;
    const grandTotal = totalBayar + ppn;

    return {
        totalKwh,
        biayaKwh,
        biayaTetap,
        totalBayar,
        ppn,
        grandTotal,
        breakdown,
        topItem: breakdown.length > 0 ? breakdown[0] : null,
        totalKwhText: `${totalKwh.toFixed(1)} kWh`,
    };
}

const CHECK_SVG = '<svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg>';

export function mountListrikTool(container) {
    const defaultAlat = () => [
        { nama: 'Kipas Angin', watt: 50, jam: 8 },
        { nama: 'TV LED 32"', watt: 40, jam: 4 },
        { nama: 'Kulkas 1 Pintu', watt: 100, jam: 24 },
        { nama: 'Lampu LED', watt: 10, jam: 6 },
        { nama: 'AC 1/2 PK', watt: 400, jam: 8 },
    ];

    let alatList = defaultAlat();

    container.innerHTML = `
        <div class="rf-tool" data-tool-listrik>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Rangkaian Listrik</h3>
                    <div class="rf-chips" role="group" aria-label="Mode simulasi listrik">
                        <button type="button" class="rf-chip is-active" data-l-sim="ohm" aria-pressed="true">${CHECK_SVG} Hukum Ohm</button>
                        <button type="button" class="rf-chip" data-l-sim="seri" aria-pressed="false">${CHECK_SVG} Seri</button>
                        <button type="button" class="rf-chip" data-l-sim="paralel" aria-pressed="false">${CHECK_SVG} Paralel</button>
                    </div>
                </div>

                <div class="rf-sim-stage">
                    <canvas class="rf-sim-canvas" data-listrik-canvas width="640" height="230" aria-label="Kanvas simulasi rangkaian listrik"></canvas>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt>Tegangan (V)</dt><dd data-l-read-v>220 V</dd></div>
                    <div><dt>Hambatan Ekiv. (R)</dt><dd data-l-read-r>100.0 Ω</dd></div>
                    <div><dt>Arus (I)</dt><dd data-l-read-i>0.00 A</dd></div>
                    <div><dt>Daya (P)</dt><dd data-l-read-p>0 W</dd></div>
                </dl>

                <div class="rf-sim-controls">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-l-sim-v">Tegangan V: <strong class="rf-num" data-l-lbl-v>220 V</strong></label>
                            <input type="range" id="rf-l-sim-v" min="12" max="380" value="220" data-l-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-l-sim-r1">Hambatan R₁: <strong class="rf-num" data-l-lbl-r1>100 Ω</strong></label>
                            <input type="range" id="rf-l-sim-r1" min="10" max="1000" value="100" data-l-r1>
                        </div>
                        <div class="rf-field" data-l-r2-wrap hidden>
                            <label class="rf-label" for="rf-l-sim-r2">Hambatan R₂: <strong class="rf-num" data-l-lbl-r2>120 Ω</strong></label>
                            <input type="range" id="rf-l-sim-r2" min="10" max="1000" value="120" data-l-r2>
                        </div>
                    </div>
                </div>

                <div class="rf-tool__actions">
                    <button type="button" class="rf-btn rf-btn--primary" data-l-sim-play>Mulai simulasi</button>
                    <button type="button" class="rf-btn rf-btn--tertiary" data-l-sim-reset>Atur ulang</button>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Listrik</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator listrik">
                        <button type="button" class="rf-chip is-active" data-l-tab="ohm" aria-pressed="true">${CHECK_SVG} Hukum Ohm</button>
                        <button type="button" class="rf-chip" data-l-tab="daya" aria-pressed="false">${CHECK_SVG} Daya Listrik</button>
                        <button type="button" class="rf-chip" data-l-tab="rumahan" aria-pressed="false">${CHECK_SVG} Tagihan Rumahan</button>
                    </div>
                </div>

                <div data-l-panel="ohm">
                    <p class="rf-muted rf-small">Isi 2 nilai untuk menghitung nilai ketiga (V = I × R).</p>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ohm-v">Tegangan V (Volt)</label>
                            <input class="rf-input rf-num" id="rf-ohm-v" type="number" step="any" placeholder="misal: 220" data-ohm-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ohm-i">Arus I (Ampere)</label>
                            <input class="rf-input rf-num" id="rf-ohm-i" type="number" step="any" placeholder="misal: 2.2" data-ohm-i>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ohm-r">Hambatan R (Ω)</label>
                            <input class="rf-input rf-num" id="rf-ohm-r" type="number" step="any" placeholder="misal: 100" data-ohm-r>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-ohm-calc>Hitung Ohm</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-ohm-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-ohm-res>Masukkan 2 nilai untuk menghitung nilai ketiga.</div>
                    </div>
                </div>

                <div data-l-panel="daya" hidden>
                    <p class="rf-muted rf-small">Masukkan V & I, I & R, atau V & R untuk menghitung Daya (P).</p>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-daya-v">Tegangan V (Volt)</label>
                            <input class="rf-input rf-num" id="rf-daya-v" type="number" step="any" placeholder="misal: 220" data-daya-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-daya-i">Arus I (Ampere)</label>
                            <input class="rf-input rf-num" id="rf-daya-i" type="number" step="any" placeholder="misal: 2.2" data-daya-i>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-daya-r">Hambatan R (Ω)</label>
                            <input class="rf-input rf-num" id="rf-daya-r" type="number" step="any" placeholder="misal: 100" data-daya-r>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-daya-calc>Hitung Daya</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-daya-reset>Kosongkan</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-daya-res>Masukkan pasangan nilai lalu klik Hitung Daya.</div>
                    </div>
                </div>

                <div data-l-panel="rumahan" hidden>
                    <p class="rf-muted rf-small">Hitung tagihan listrik bulanan (30 hari) berdasarkan daya alat dan jam pemakaian.</p>
                    <div class="rf-tool__grid-3 mb-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-daya-meteran">Daya Meteran (VA)</label>
                            <select class="rf-select" id="rf-daya-meteran" data-rumahan-va>
                                <option value="450">450 VA (Sosial)</option>
                                <option value="900">900 VA (Sosial)</option>
                                <option value="1300" selected>1300 VA (Rumah Tangga)</option>
                                <option value="2200">2200 VA (Rumah Tangga)</option>
                            </select>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-tarif-kwh">Tarif per kWh (Rp)</label>
                            <input class="rf-input rf-num" id="rf-tarif-kwh" type="number" value="1447" data-rumahan-tarif>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-biaya-tetap">Biaya tetap (Rp)</label>
                            <input class="rf-input rf-num" id="rf-biaya-tetap" type="number" value="12500" data-rumahan-tetap>
                        </div>
                    </div>

                    <div class="rf-alat-list mb-3" data-alat-grid></div>

                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--tertiary" data-alat-add>+ Tambah alat</button>
                        <button type="button" class="rf-btn rf-btn--secondary" data-rumahan-calc>Hitung Tagihan 30 Hari</button>
                        <button type="button" class="rf-btn rf-btn--tertiary" data-rumahan-reset>Atur ulang alat</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Estimasi Tagihan & Distribusi kWh di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-rumahan-res>Klik Hitung Tagihan 30 Hari untuk melihat rincian kWh, PPN 11%, dan grafik porsi pemakaian.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    initListrikSimulation(container);
    initListrikCalculators(container, () => alatList, (next) => { alatList = next; }, defaultAlat);
}

function initListrikSimulation(root) {
    const canvas = root.querySelector('[data-listrik-canvas]');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let simMode = 'ohm';
    let simRunning = false;
    let simAF = null;
    let simT = 0;
    let lastTS = null;

    const vIn = root.querySelector('[data-l-v]');
    const r1In = root.querySelector('[data-l-r1]');
    const r2In = root.querySelector('[data-l-r2]');
    const r2Wrap = root.querySelector('[data-l-r2-wrap]');
    const playBtn = root.querySelector('[data-l-sim-play]');
    const resetBtn = root.querySelector('[data-l-sim-reset]');

    const getParams = () => {
        const V = parseFloat(vIn?.value || '220') || 220;
        const R1 = Math.max(1, parseFloat(r1In?.value || '100') || 100);
        const R2 = Math.max(1, parseFloat(r2In?.value || '120') || 120);
        let Req = R1;
        if (simMode === 'seri') Req = R1 + R2;
        else if (simMode === 'paralel') Req = 1 / (1 / R1 + 1 / R2);
        const I = simRunning ? V / Req : 0;
        const P = V * I;
        return { V, R1, R2, Req, I, P };
    };

    const drawResistor = (x, y, val, label) => {
        ctx.fillStyle = '#AF1919';
        for (let i = 0; i < 6; i++) {
            ctx.fillRect(x + i * 8, y - 12, 6, 24);
        }
        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 11px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${label ? `${label}: ` : ''}${Math.round(val)}Ω`, x + 23, y - 16);
    };

    const drawCircuit = () => {
        const { V, R1, R2, Req, I } = getParams();
        const W = canvas.width;
        const H = canvas.height;
        const cx = W * 0.5;
        const cy = H * 0.52;
        const boxW = 420;
        const boxH = 140;

        ctx.clearRect(0, 0, W, H);
        ctx.fillStyle = '#ECEEF2';
        ctx.fillRect(0, 0, W, H);

        // Kabel utama
        ctx.strokeStyle = '#5B5A68';
        ctx.lineWidth = 3;
        ctx.strokeRect(cx - boxW / 2, cy - boxH / 2, boxW, boxH);

        // Sumber tegangan (kiri)
        const batX = cx - boxW / 2 - 22;
        ctx.fillStyle = '#E8A627';
        ctx.beginPath();
        ctx.roundRect(batX, cy - 22, 44, 44, 8);
        ctx.fill();
        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${V}V`, batX + 22, cy + 4);

        // Resistor di jalur atas
        const topY = cy - boxH / 2;
        if (simMode === 'ohm') {
            ctx.fillStyle = '#ECEEF2';
            ctx.fillRect(cx - 28, topY - 14, 56, 28);
            drawResistor(cx - 23, topY, R1, 'R');
        } else if (simMode === 'seri') {
            ctx.fillStyle = '#ECEEF2';
            ctx.fillRect(cx - 72, topY - 14, 54, 28);
            ctx.fillRect(cx + 18, topY - 14, 54, 28);
            drawResistor(cx - 68, topY, R1, 'R₁');
            drawResistor(cx + 22, topY, R2, 'R₂');
        } else {
            ctx.fillStyle = '#ECEEF2';
            ctx.fillRect(cx - 46, topY - 30, 92, 60);
            ctx.strokeStyle = '#5B5A68';
            ctx.lineWidth = 2.5;
            ctx.strokeRect(cx - 46, topY - 22, 92, 44);
            drawResistor(cx - 23, topY - 22, R1, 'R₁');
            drawResistor(cx - 23, topY + 22, R2, 'R₂');
        }

        // Lampu beban (kanan)
        const lampX = cx + boxW / 2;
        ctx.fillStyle = I > 0 ? '#E8A627' : '#C8CDD8';
        ctx.beginPath();
        ctx.arc(lampX, cy, 20, 0, Math.PI * 2);
        ctx.fill();
        ctx.strokeStyle = '#23222B';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Aliran arus saat simulasi berjalan
        if (simRunning && I > 0) {
            ctx.save();
            ctx.strokeStyle = '#AF1919';
            ctx.lineWidth = 3;
            ctx.setLineDash([8, 8]);
            ctx.lineDashOffset = -simT * Math.min(240, Math.max(40, I * 35));
            ctx.strokeRect(cx - boxW / 2, cy - boxH / 2, boxW, boxH);
            ctx.restore();
        }

        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'left';
        ctx.fillText(`Mode: ${simMode.toUpperCase()}  |  Req = ${Req.toFixed(1)} Ω  |  I = ${(V / Req).toFixed(2)} A`, 14, 20);
    };

    const updateReadouts = () => {
        const { V, R1, R2, Req, I, P } = getParams();
        const lblV = root.querySelector('[data-l-lbl-v]');
        const lblR1 = root.querySelector('[data-l-lbl-r1]');
        const lblR2 = root.querySelector('[data-l-lbl-r2]');
        if (lblV) lblV.textContent = `${V} V`;
        if (lblR1) lblR1.textContent = `${R1} Ω`;
        if (lblR2) lblR2.textContent = `${R2} Ω`;

        root.querySelector('[data-l-read-v]').textContent = `${V} V`;
        root.querySelector('[data-l-read-r]').textContent = `${Req.toFixed(1)} Ω`;
        root.querySelector('[data-l-read-i]').textContent = `${(simRunning ? I : V / Req).toFixed(2)} A`;
        root.querySelector('[data-l-read-p]').textContent = `${(simRunning ? P : V * (V / Req)).toFixed(0)} W`;
        drawCircuit();
    };

    const simLoop = (ts) => {
        if (!lastTS) lastTS = ts;
        const dt = (ts - lastTS) / 1000;
        lastTS = ts;
        simT += dt;
        updateReadouts();
        if (simRunning) {
            simAF = window.requestAnimationFrame(simLoop);
        }
    };

    root.querySelectorAll('[data-l-sim]').forEach((btn) => {
        btn.addEventListener('click', () => {
            simMode = btn.getAttribute('data-l-sim') || 'ohm';
            root.querySelectorAll('[data-l-sim]').forEach((b) => {
                const active = b === btn;
                b.classList.toggle('is-active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            if (r2Wrap) {
                if (simMode === 'ohm') r2Wrap.setAttribute('hidden', 'hidden');
                else r2Wrap.removeAttribute('hidden');
            }
            if (simMode !== 'ohm' && r1In && r1In.value === '100') {
                r1In.value = '60';
            }
            updateReadouts();
        });
    });

    [vIn, r1In, r2In].forEach((el) => el?.addEventListener('input', updateReadouts));

    playBtn?.addEventListener('click', () => {
        simRunning = !simRunning;
        if (simRunning) {
            lastTS = null;
            playBtn.textContent = 'Jeda simulasi';
            simAF = window.requestAnimationFrame(simLoop);
        } else {
            playBtn.textContent = 'Mulai simulasi';
            if (simAF) window.cancelAnimationFrame(simAF);
            updateReadouts();
        }
    });

    resetBtn?.addEventListener('click', () => {
        if (simAF) window.cancelAnimationFrame(simAF);
        simRunning = false;
        simT = 0;
        lastTS = null;
        if (vIn) vIn.value = '220';
        if (r1In) r1In.value = simMode === 'ohm' ? '100' : '60';
        if (r2In) r2In.value = '120';
        if (playBtn) playBtn.textContent = 'Mulai simulasi';
        updateReadouts();
    });

    updateReadouts();
}

function initListrikCalculators(container, getAlatList, setAlatList, defaultAlat) {
    container.querySelectorAll('[data-l-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-l-tab');
            container.querySelectorAll('[data-l-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-l-panel]').forEach((panel) => {
                if (panel.getAttribute('data-l-panel') === key) panel.removeAttribute('hidden');
                else panel.setAttribute('hidden', 'hidden');
            });
        });
    });

    // 1. Hukum Ohm
    container.querySelector('[data-ohm-calc]')?.addEventListener('click', () => {
        const res = computeOhmListrik({
            v: parseFloat(container.querySelector('[data-ohm-v]')?.value || ''),
            i: parseFloat(container.querySelector('[data-ohm-i]')?.value || ''),
            r: parseFloat(container.querySelector('[data-ohm-r]')?.value || ''),
        });
        const box = container.querySelector('[data-ohm-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Tegangan (V)</span><strong>${res.vText}</strong></div>
                <div><span>Arus (I)</span><strong>${res.iText}</strong></div>
                <div><span>Hambatan (R)</span><strong>${res.rText}</strong></div>
            </div>
        `;
    });

    container.querySelector('[data-ohm-reset]')?.addEventListener('click', () => {
        ['[data-ohm-v]', '[data-ohm-i]', '[data-ohm-r]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-ohm-res]');
        if (box) box.textContent = 'Masukkan 2 nilai untuk menghitung nilai ketiga.';
    });

    // 2. Daya Listrik
    container.querySelector('[data-daya-calc]')?.addEventListener('click', () => {
        const res = computeDayaListrik({
            v: parseFloat(container.querySelector('[data-daya-v]')?.value || ''),
            i: parseFloat(container.querySelector('[data-daya-i]')?.value || ''),
            r: parseFloat(container.querySelector('[data-daya-r]')?.value || ''),
        });
        const box = container.querySelector('[data-daya-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Daya (Watt)</span><strong>${res.wattText}</strong></div>
                <div><span>Daya (kW)</span><strong>${res.kwText}</strong></div>
            </div>
        `;
    });

    container.querySelector('[data-daya-reset]')?.addEventListener('click', () => {
        ['[data-daya-v]', '[data-daya-i]', '[data-daya-r]'].forEach((sel) => {
            const el = container.querySelector(sel);
            if (el) el.value = '';
        });
        const box = container.querySelector('[data-daya-res]');
        if (box) box.textContent = 'Masukkan pasangan nilai lalu klik Hitung Daya.';
    });

    // 3. Tagihan Listrik Rumahan (+ daftar alat & pie chart kWh)
    const alatGrid = container.querySelector('[data-alat-grid]');

    const renderAlatList = () => {
        if (!alatGrid) return;
        const list = getAlatList();
        alatGrid.innerHTML = list.map((alat, idx) => `
            <div class="rf-tool__grid-3 align-items-end mb-2" data-alat-row="${idx}">
                <div class="rf-field">
                    <label class="rf-label rf-small" for="rf-alat-nama-${idx}">Nama alat #${idx + 1}</label>
                    <input class="rf-input" id="rf-alat-nama-${idx}" type="text" value="${alat.nama}" data-alat-nama="${idx}">
                </div>
                <div class="rf-field">
                    <label class="rf-label rf-small" for="rf-alat-watt-${idx}">Daya (Watt)</label>
                    <input class="rf-input rf-num" id="rf-alat-watt-${idx}" type="number" min="1" max="5000" value="${alat.watt}" data-alat-watt="${idx}">
                </div>
                <div class="rf-field">
                    <label class="rf-label rf-small" for="rf-alat-jam-${idx}">Jam/hari (0–24)</label>
                    <div class="d-flex gap-2">
                        <input class="rf-input rf-num" id="rf-alat-jam-${idx}" type="number" min="0" max="24" step="0.5" value="${alat.jam}" data-alat-jam="${idx}">
                        ${list.length > 1 ? `<button type="button" class="rf-btn rf-btn--tertiary" data-alat-del="${idx}" aria-label="Hapus ${alat.nama}">Hapus</button>` : ''}
                    </div>
                </div>
            </div>
        `).join('');

        alatGrid.querySelectorAll('[data-alat-nama]').forEach((input) => {
            input.addEventListener('input', (e) => {
                const i = Number(input.getAttribute('data-alat-nama'));
                const next = [...getAlatList()];
                if (next[i]) next[i].nama = e.target.value || `Alat ${i + 1}`;
                setAlatList(next);
            });
        });

        alatGrid.querySelectorAll('[data-alat-watt]').forEach((input) => {
            input.addEventListener('input', (e) => {
                const i = Number(input.getAttribute('data-alat-watt'));
                const next = [...getAlatList()];
                if (next[i]) next[i].watt = parseFloat(e.target.value) || 0;
                setAlatList(next);
            });
        });

        alatGrid.querySelectorAll('[data-alat-jam]').forEach((input) => {
            input.addEventListener('input', (e) => {
                const i = Number(input.getAttribute('data-alat-jam'));
                const next = [...getAlatList()];
                if (next[i]) next[i].jam = parseFloat(e.target.value) || 0;
                setAlatList(next);
            });
        });

        alatGrid.querySelectorAll('[data-alat-del]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const i = Number(btn.getAttribute('data-alat-del'));
                const next = getAlatList().filter((_, idx) => idx !== i);
                if (next.length >= 1) {
                    setAlatList(next);
                    renderAlatList();
                }
            });
        });
    };

    const drawPieChart = (canvas, breakdown) => {
        if (!canvas || !breakdown.length) return;
        const ctx = canvas.getContext('2d');
        const W = canvas.width;
        const H = canvas.height;
        const cx = W / 2;
        const cy = H / 2;
        const r = Math.min(W, H) * 0.42;
        const total = breakdown.reduce((sum, x) => sum + x.kwh, 0);
        if (total <= 0) return;

        const palette = ['#AF1919', '#E8A627', '#23222B', '#176B48', '#5B5A68', '#8A1010', '#B07408'];
        ctx.clearRect(0, 0, W, H);

        let startAngle = -Math.PI / 2;
        breakdown.forEach((slice, idx) => {
            const frac = slice.kwh / total;
            const endAngle = startAngle + frac * Math.PI * 2;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, r, startAngle, endAngle);
            ctx.closePath();
            ctx.fillStyle = palette[idx % palette.length];
            ctx.fill();
            startAngle = endAngle;
        });

        // Lubang donat tengah
        ctx.fillStyle = '#ECEEF2';
        ctx.beginPath();
        ctx.arc(cx, cy, r * 0.5, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#23222B';
        ctx.font = 'bold 12px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(`${total.toFixed(1)} kWh`, cx, cy + 4);
    };

    const runRumahanCalc = () => {
        const tarif = parseFloat(container.querySelector('[data-rumahan-tarif]')?.value || '1447') || 1447;
        const tetap = parseFloat(container.querySelector('[data-rumahan-tetap]')?.value || '12500') || 12500;
        const res = computeTagihanRumahan({ alatList: getAlatList(), tarifKwh: tarif, biayaTetap: tetap });
        const box = container.querySelector('[data-rumahan-res]');
        if (!box) return;

        const total = res.totalKwh > 0 ? res.totalKwh : 1;
        const legendHtml = res.breakdown.map((it) => {
            const pct = ((it.kwh / total) * 100).toFixed(1);
            return `<li><strong>${it.nama}</strong>: ${it.kwh.toFixed(1)} kWh (${pct}%)</li>`;
        }).join('');

        box.innerHTML = `
            <div class="rf-calc-outcomes mb-3">
                <div><span>Total Pemakaian</span><strong>${res.totalKwhText}</strong></div>
                <div><span>Biaya kWh</span><strong>Rp ${Math.round(res.biayaKwh).toLocaleString('id-ID')}</strong></div>
                <div><span>Biaya Tetap</span><strong>Rp ${Math.round(res.biayaTetap).toLocaleString('id-ID')}</strong></div>
                <div><span>Subtotal</span><strong>Rp ${Math.round(res.totalBayar).toLocaleString('id-ID')}</strong></div>
                <div><span>PPN 11%</span><strong>Rp ${Math.round(res.ppn).toLocaleString('id-ID')}</strong></div>
                <div><span>Total Tagihan</span><strong>Rp ${Math.round(res.grandTotal).toLocaleString('id-ID')}</strong></div>
            </div>
            <div class="rf-tool__grid-2 align-items-center">
                <div>
                    <canvas data-rumahan-pie width="180" height="180" aria-label="Diagram lingkaran porsi kWh per alat"></canvas>
                </div>
                <div>
                    ${res.topItem ? `<p class="rf-small mb-2">Terbesar: <strong>${res.topItem.nama}</strong> (${res.topItem.kwh.toFixed(1)} kWh)</p>` : ''}
                    <ul class="rf-small mb-0 ps-3">${legendHtml}</ul>
                </div>
            </div>
        `;

        drawPieChart(box.querySelector('[data-rumahan-pie]'), res.breakdown);
    };

    container.querySelector('[data-alat-add]')?.addEventListener('click', () => {
        const next = [...getAlatList(), { nama: `Alat ${getAlatList().length + 1}`, watt: 100, jam: 4 }];
        setAlatList(next);
        renderAlatList();
    });

    container.querySelector('[data-rumahan-calc]')?.addEventListener('click', runRumahanCalc);

    container.querySelector('[data-rumahan-reset]')?.addEventListener('click', () => {
        setAlatList(defaultAlat());
        const va = container.querySelector('[data-rumahan-va]');
        const tarif = container.querySelector('[data-rumahan-tarif]');
        const tetap = container.querySelector('[data-rumahan-tetap]');
        if (va) va.value = '1300';
        if (tarif) tarif.value = '1447';
        if (tetap) tetap.value = '12500';
        renderAlatList();
        const box = container.querySelector('[data-rumahan-res]');
        if (box) box.textContent = 'Klik Hitung Tagihan 30 Hari untuk melihat rincian kWh, PPN 11%, dan grafik porsi pemakaian.';
    });

    renderAlatList();
}
