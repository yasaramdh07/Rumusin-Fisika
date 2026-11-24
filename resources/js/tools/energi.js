// Modul 4: Simulasi & Kalkulator Energi (Energi Kinetik, Energi Potensial, Usaha) — dari legacy/page/rumus-energi.

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

export function mountEnergiTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-energi>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Usaha dan Energi</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator energi">
                        <button type="button" class="rf-chip is-active" data-e-tab="ek" aria-pressed="true"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Energi Kinetik</button>
                        <button type="button" class="rf-chip" data-e-tab="ep" aria-pressed="false"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Energi Potensial</button>
                        <button type="button" class="rf-chip" data-e-tab="usaha" aria-pressed="false"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Usaha (W = F · s)</button>
                    </div>
                </div>

                <div data-e-panel="ek">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ek-ek">Energi Kinetik Ek (J)</label>
                            <input class="rf-input rf-num" id="rf-ek-ek" type="number" step="any" data-ek-ek>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ek-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-ek-m" type="number" step="any" data-ek-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ek-v">Kecepatan v (m/s)</label>
                            <input class="rf-input rf-num" id="rf-ek-v" type="number" step="any" data-ek-v>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-ek-calc>Hitung Ek</button>
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
                            <input class="rf-input rf-num" id="rf-ep-ep" type="number" step="any" data-ep-ep>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ep-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-ep-m" type="number" step="any" data-ep-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-ep-h">Ketinggian h (m, g = 9.8)</label>
                            <input class="rf-input rf-num" id="rf-ep-h" type="number" step="any" data-ep-h>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-ep-calc>Hitung Ep</button>
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
                            <input class="rf-input rf-num" id="rf-us-w" type="number" step="any" data-us-w>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-us-f">Gaya F (N)</label>
                            <input class="rf-input rf-num" id="rf-us-f" type="number" step="any" data-us-f>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-us-s">Perpindahan s (m)</label>
                            <input class="rf-input rf-num" id="rf-us-s" type="number" step="any" data-us-s>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-us-calc>Hitung Usaha</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-us-res>Kosongkan satu nilai, lalu klik Hitung Usaha.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

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
}
