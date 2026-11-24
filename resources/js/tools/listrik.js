// Modul 2: Simulasi & Kalkulator Listrik (Hukum Ohm, Daya Listrik, Tagihan Rumahan) — dari legacy/page/rumus-listrik.
// Rumus dipertahankan persis sama dengan versi lama.

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
    alatList.forEach((alat) => {
        totalKwh += (Number(alat.watt) * Number(alat.jam) * 30) / 1000;
    });

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
        totalKwhText: `${totalKwh.toFixed(1)} kWh`,
    };
}

export function mountListrikTool(container) {
    let alatList = [
        { nama: 'Kipas Angin', watt: 50, jam: 8 },
        { nama: 'TV LED 32"', watt: 40, jam: 4 },
        { nama: 'Kulkas 1 Pintu', watt: 100, jam: 24 },
        { nama: 'Lampu LED', watt: 10, jam: 6 },
        { nama: 'AC 1/2 PK', watt: 400, jam: 8 },
    ];

    container.innerHTML = `
        <div class="rf-tool" data-tool-listrik>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Simulasi Rangkaian Listrik</h3>
                    <div class="rf-chips">
                        <button type="button" class="rf-chip is-active" data-l-sim="ohm" aria-pressed="true">Ohm</button>
                        <button type="button" class="rf-chip" data-l-sim="seri" aria-pressed="false">Seri</button>
                        <button type="button" class="rf-chip" data-l-sim="paralel" aria-pressed="false">Paralel</button>
                    </div>
                </div>

                <dl class="rf-sim-readouts rf-num">
                    <div><dt>Tegangan (V)</dt><dd data-l-read-v>220 V</dd></div>
                    <div><dt>Hambatan Ekiv. (R)</dt><dd data-l-read-r>100.0 Ω</dd></div>
                    <div><dt>Arus (I)</dt><dd data-l-read-i>2.20 A</dd></div>
                    <div><dt>Daya (P)</dt><dd data-l-read-p>484 W</dd></div>
                </dl>

                <div class="rf-tool__grid-3">
                    <div class="rf-field">
                        <label class="rf-label" for="rf-l-sim-v">Tegangan V (12–380 V)</label>
                        <input class="rf-input rf-num" id="rf-l-sim-v" type="number" min="12" max="380" value="220" data-l-v>
                    </div>
                    <div class="rf-field">
                        <label class="rf-label" for="rf-l-sim-r1">Hambatan R₁ (Ω)</label>
                        <input class="rf-input rf-num" id="rf-l-sim-r1" type="number" min="10" max="1000" value="100" data-l-r1>
                    </div>
                    <div class="rf-field" data-l-r2-wrap hidden>
                        <label class="rf-label" for="rf-l-sim-r2">Hambatan R₂ (Ω)</label>
                        <input class="rf-input rf-num" id="rf-l-sim-r2" type="number" min="10" max="1000" value="120" data-l-r2>
                    </div>
                </div>
            </div>

            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Listrik</h3>
                    <div class="rf-chips">
                        <button type="button" class="rf-chip is-active" data-l-tab="ohm" aria-pressed="true">Hukum Ohm</button>
                        <button type="button" class="rf-chip" data-l-tab="daya" aria-pressed="false">Daya Listrik</button>
                        <button type="button" class="rf-chip" data-l-tab="rumahan" aria-pressed="false">Tagihan Rumahan</button>
                    </div>
                </div>

                <div data-l-panel="ohm">
                    <p class="rf-muted rf-small">Isi 2 nilai untuk menghitung nilai ketiga (V = I × R).</p>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ohm-v">Tegangan V (Volt)</label>
                            <input class="rf-input rf-num" id="rf-ohm-v" type="number" step="any" data-ohm-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ohm-i">Arus I (Ampere)</label>
                            <input class="rf-input rf-num" id="rf-ohm-i" type="number" step="any" data-ohm-i>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ohm-r">Hambatan R (Ω)</label>
                            <input class="rf-input rf-num" id="rf-ohm-r" type="number" step="any" data-ohm-r>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-ohm-calc>Hitung Ohm</button>
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
                            <input class="rf-input rf-num" id="rf-daya-v" type="number" step="any" data-daya-v>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-daya-i">Arus I (Ampere)</label>
                            <input class="rf-input rf-num" id="rf-daya-i" type="number" step="any" data-daya-i>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-daya-r">Hambatan R (Ω)</label>
                            <input class="rf-input rf-num" id="rf-daya-r" type="number" step="any" data-daya-r>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-daya-calc>Hitung Daya</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-daya-res>Masukkan pasangan nilai lalu klik Hitung Daya.</div>
                    </div>
                </div>

                <div data-l-panel="rumahan" hidden>
                    <div class="rf-tool__grid-2">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-tarif-kwh">Tarif per kWh (Rp)</label>
                            <input class="rf-input rf-num" id="rf-tarif-kwh" type="number" value="1447" data-rumahan-tarif>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-biaya-tetap">Biaya tetap (Rp)</label>
                            <input class="rf-input rf-num" id="rf-biaya-tetap" type="number" value="12500" data-rumahan-tetap>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-rumahan-calc>Hitung Tagihan 30 Hari</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Estimasi Tagihan di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-rumahan-res>Klik Hitung Tagihan 30 Hari untuk melihat rincian kWh dan PPN 11%.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    let simMode = 'ohm';
    const vIn = container.querySelector('[data-l-v]');
    const r1In = container.querySelector('[data-l-r1]');
    const r2In = container.querySelector('[data-l-r2]');
    const r2Wrap = container.querySelector('[data-l-r2-wrap]');

    const updateSim = () => {
        const V = parseFloat(vIn?.value || '220') || 220;
        const R1 = Math.max(1, parseFloat(r1In?.value || '100') || 100);
        const R2 = Math.max(1, parseFloat(r2In?.value || '120') || 120);
        let Req = R1;
        if (simMode === 'seri') Req = R1 + R2;
        else if (simMode === 'paralel') Req = 1 / (1 / R1 + 1 / R2);

        const I = V / Req;
        const P = V * I;
        container.querySelector('[data-l-read-v]').textContent = `${V} V`;
        container.querySelector('[data-l-read-r]').textContent = `${Req.toFixed(1)} Ω`;
        container.querySelector('[data-l-read-i]').textContent = `${I.toFixed(2)} A`;
        container.querySelector('[data-l-read-p]').textContent = `${P.toFixed(0)} W`;
    };

    container.querySelectorAll('[data-l-sim]').forEach((btn) => {
        btn.addEventListener('click', () => {
            simMode = btn.getAttribute('data-l-sim') || 'ohm';
            container.querySelectorAll('[data-l-sim]').forEach((b) => {
                const active = b === btn;
                b.classList.toggle('is-active', active);
                b.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            if (r2Wrap) {
                if (simMode === 'ohm') r2Wrap.setAttribute('hidden', 'hidden');
                else r2Wrap.removeAttribute('hidden');
            }
            updateSim();
        });
    });

    [vIn, r1In, r2In].forEach((el) => el?.addEventListener('input', updateSim));

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

    container.querySelector('[data-rumahan-calc]')?.addEventListener('click', () => {
        const tarif = parseFloat(container.querySelector('[data-rumahan-tarif]')?.value || '1447') || 1447;
        const tetap = parseFloat(container.querySelector('[data-rumahan-tetap]')?.value || '12500') || 12500;
        const res = computeTagihanRumahan({ alatList, tarifKwh: tarif, biayaTetap: tetap });
        const box = container.querySelector('[data-rumahan-res]');
        if (!box) return;
        box.innerHTML = `
            <div class="rf-calc-outcomes">
                <div><span>Total Pemakaian</span><strong>${res.totalKwhText}</strong></div>
                <div><span>Subtotal</span><strong>Rp ${Math.round(res.totalBayar).toLocaleString('id-ID')}</strong></div>
                <div><span>Total + PPN 11%</span><strong>Rp ${Math.round(res.grandTotal).toLocaleString('id-ID')}</strong></div>
            </div>
        `;
    });
}
